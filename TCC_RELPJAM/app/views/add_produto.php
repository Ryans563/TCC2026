<?php

require_once "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = "/TCC_RELPJAM";

// ---------------------------------------------------------------
// Vendedor logado. Ajuste para o seu sistema de autenticação real.
// Aqui eu uso a sessão se existir, senão caio no vendedor_id = 1
// (que era o valor fixo do código original) só para não quebrar.
// ---------------------------------------------------------------
$vendedor_id = $_SESSION['vendedor_id'] ?? 1;

// ---------------------------------------------------------------
// Config do Supabase Storage
// ATENÇÃO: a service_role key NUNCA deveria ficar exposta no
// código fonte / repositório. Mova isso para variáveis de
// ambiente (getenv('SUPABASE_KEY')) ou para o config.php,
// que deve estar fora do controle de versão (.gitignore).
// Deixei aqui pra não travar o funcionamento, mas troque assim
// que possível.
// ---------------------------------------------------------------
$supabaseUrl = "https://enkfnnaebiiqyycmegyp.supabase.co";
$supabaseBucket = "produtos";
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImVua2ZubmFlYmlpcXl5Y21lZ3lwIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4MTA2OTQ3NiwiZXhwIjoyMDk2NjQ1NDc2fQ.dsa2_kej67S5GG_lAXCw3nrSrg7Mvz5xNx_0KNTlMF0";

/**
 * Faz upload de um arquivo enviado via $_FILES para o Supabase Storage.
 * Retorna a URL pública em caso de sucesso, ou lança uma Exception em caso de erro.
 */
function uploadImagemSupabase(array $file, string $supabaseUrl, string $bucket, string $key): string
{
    // Validações básicas do arquivo (o código original não validava nada)
    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $tamanhoMaximo = 5 * 1024 * 1024; // 5MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $mimesPermitidos, true)) {
        throw new Exception("Formato de imagem não suportado. Envie JPG, PNG, WEBP ou GIF.");
    }

    if ($file['size'] > $tamanhoMaximo) {
        throw new Exception("A imagem deve ter no máximo 5MB.");
    }

    $fileTmp = $file['tmp_name'];
    $fileName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $fileData = file_get_contents($fileTmp);

    $uploadUrl = $supabaseUrl . "/storage/v1/object/" . $bucket . "/" . $fileName;

    $ch = curl_init($uploadUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $key",
        "Content-Type: " . $mime,
        "x-upsert: true"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);

    $response = curl_exec($ch);
    $curlErro = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        throw new Exception("Erro de conexão com o Supabase: " . $curlErro);
    }

    if ($httpCode == 200 || $httpCode == 201) {
        return $supabaseUrl . "/storage/v1/object/public/" . $bucket . "/" . $fileName;
    }

    throw new Exception("Erro upload Supabase: " . $response);
}

/**
 * Gera um slug a partir de um texto (sem hífen sobrando no início/fim).
 */
function gerarSlug(string $texto): string
{
    $slug = strtolower($texto);
    $slug = preg_replace('/[^a-z0-9]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Mensagem de flash (guardada na sessão para sobreviver ao redirect)
$mensagem = $_SESSION['flash_mensagem'] ?? "";
$mensagemTipo = $_SESSION['flash_tipo'] ?? "sucesso";
unset($_SESSION['flash_mensagem'], $_SESSION['flash_tipo']);

// BUSCAR CATEGORIAS
$sqlCategorias = $pdo->query("SELECT * FROM categorias WHERE ativo = TRUE ORDER BY nome ASC");
$categorias = $sqlCategorias->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------------------------------------------
// CADASTRAR / EDITAR PRODUTO
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $produto_id  = !empty($_POST['produto_id']) ? (int) $_POST['produto_id'] : null;
    $editando    = $produto_id !== null;

    $nome        = trim($_POST['nome'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $precoRaw    = $_POST['preco'] ?? '';
    $estoqueRaw  = $_POST['estoque'] ?? '';
    $marca       = trim($_POST['marca'] ?? '');
    $sku         = trim($_POST['sku'] ?? '');

    $erros = [];

    if ($nome === '') {
        $erros[] = "Informe o nome do produto.";
    }

    if ($sku === '') {
        $erros[] = "Informe o SKU do produto.";
    }

    if ($categoria_id === '') {
        $erros[] = "Selecione uma categoria.";
    }

    $preco = (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $precoRaw));
    if ($preco <= 0) {
        $erros[] = "Informe um preço válido.";
    }

    $estoque = filter_var($estoqueRaw, FILTER_VALIDATE_INT);
    if ($estoque === false || $estoque < 0) {
        $erros[] = "Informe uma quantidade de estoque válida.";
    }

    if (empty($erros)) {

        try {

            $imagemUrl = null;

            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
                $imagemUrl = uploadImagemSupabase($_FILES['imagem'], $supabaseUrl, $supabaseBucket, $supabaseKey);
            }

            $slug = gerarSlug($nome);

            if ($editando) {

                // Confere se o produto realmente pertence a esse vendedor antes de alterar
                $sqlCheck = $pdo->prepare("SELECT id FROM produtos WHERE id = :id AND vendedor_id = :vendedor_id");
                $sqlCheck->execute([':id' => $produto_id, ':vendedor_id' => $vendedor_id]);

                if (!$sqlCheck->fetch()) {
                    throw new Exception("Produto não encontrado ou você não tem permissão para editá-lo.");
                }

                $sql = $pdo->prepare("
                    UPDATE produtos SET
                        categoria_id = :categoria_id,
                        nome = :nome,
                        slug = :slug,
                        descricao = :descricao,
                        sku = :sku,
                        preco = :preco,
                        estoque = :estoque,
                        marca = :marca
                    WHERE id = :id AND vendedor_id = :vendedor_id
                ");

                $sql->execute([
                    ':categoria_id' => $categoria_id,
                    ':nome' => $nome,
                    ':slug' => $slug,
                    ':descricao' => $descricao,
                    ':sku' => $sku,
                    ':preco' => $preco,
                    ':estoque' => $estoque,
                    ':marca' => $marca,
                    ':id' => $produto_id,
                    ':vendedor_id' => $vendedor_id
                ]);

                // Só mexe na imagem se o usuário enviou uma nova
                if ($imagemUrl) {
                    $pdo->prepare("UPDATE produto_imagens SET principal = false WHERE produto_id = :produto_id")
                        ->execute([':produto_id' => $produto_id]);

                    $pdo->prepare("
                        INSERT INTO produto_imagens (produto_id, imagem, principal)
                        VALUES (:produto_id, :imagem, true)
                    ")->execute([
                        ':produto_id' => $produto_id,
                        ':imagem' => $imagemUrl
                    ]);
                }

                $mensagem = "Produto atualizado com sucesso!";

            } else {

                $sql = $pdo->prepare("
                    INSERT INTO produtos (
                        vendedor_id, categoria_id, nome, slug, descricao,
                        sku, preco, estoque, marca, status
                    )
                    VALUES (
                        :vendedor_id, :categoria_id, :nome, :slug, :descricao,
                        :sku, :preco, :estoque, :marca, 'ativo'
                    )
                ");

                $sql->execute([
                    ':vendedor_id' => $vendedor_id,
                    ':categoria_id' => $categoria_id,
                    ':nome' => $nome,
                    ':slug' => $slug,
                    ':descricao' => $descricao,
                    ':sku' => $sku,
                    ':preco' => $preco,
                    ':estoque' => $estoque,
                    ':marca' => $marca
                ]);

                $produto_id = $pdo->lastInsertId();

                if ($imagemUrl) {
                    $pdo->prepare("
                        INSERT INTO produto_imagens (produto_id, imagem, principal)
                        VALUES (:produto_id, :imagem, true)
                    ")->execute([
                        ':produto_id' => $produto_id,
                        ':imagem' => $imagemUrl
                    ]);
                }

                $mensagem = "Produto cadastrado com sucesso!";
            }

            $mensagemTipo = "sucesso";

        } catch (Exception $e) {
            $mensagem = "Erro ao salvar: " . $e->getMessage();
            $mensagemTipo = "erro";
        }

    } else {
        $mensagem = implode(" ", $erros);
        $mensagemTipo = "erro";
    }

    // PRG (Post/Redirect/Get) evita reenvio do formulário ao dar F5
    $_SESSION['flash_mensagem'] = $mensagem;
    $_SESSION['flash_tipo'] = $mensagemTipo;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// -----------------------------------------------------------------
// LISTAR PRODUTOS DO VENDEDOR (para montar os blocos)
// -----------------------------------------------------------------
$sqlProdutos = $pdo->prepare("
    SELECT
        p.id, p.nome, p.descricao, p.sku, p.preco, p.estoque,
        p.marca, p.status, p.categoria_id,
        c.nome AS categoria_nome,
        pi.imagem
    FROM produtos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN produto_imagens pi ON pi.produto_id = p.id AND pi.principal = true
    WHERE p.vendedor_id = :vendedor_id
    ORDER BY p.id DESC
");
$sqlProdutos->execute([':vendedor_id' => $vendedor_id]);
$produtos = $sqlProdutos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produtos</title>
<link rel="stylesheet" href="<?= $base ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= $base ?>/public/assets/css/style_add_prod.css">

<style>
    /* Estilos da listagem em blocos + botão editar. */
    .produtos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 18px;
        margin-top: 25px;
    }

    .produto-card {
        border: 1px solid #e2e2e2;
        border-radius: 10px;
        padding: 14px;
        background: #fff;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 6px rgba(0,0,0,.05);
    }

    .produto-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        background: #f2f2f2;
        margin-bottom: 10px;
    }

    .produto-card .sem-imagem {
        width: 100%;
        height: 150px;
        border-radius: 8px;
        background: #f2f2f2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .produto-card h3 {
        font-size: 16px;
        margin: 0 0 4px;
    }

    .produto-card .produto-meta {
        font-size: 13px;
        color: #666;
        margin-bottom: 4px;
    }

    .produto-card .produto-preco {
        font-size: 17px;
        font-weight: bold;
        margin: 6px 0;
        color: #222;
    }

    .produto-card .badge {
        display: inline-block;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        margin-bottom: 8px;
        width: fit-content;
        text-transform: uppercase;
    }

    .badge.ativo { background: #e6f7ec; color: #1e8e46; }
    .badge.inativo { background: #f3f3f3; color: #888; }

    .btn-editar-produto {
        margin-top: auto;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        background: #2f6feb;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-editar-produto:hover {
        background: #2559c1;
    }

    .sem-produtos {
        color: #777;
        margin-top: 20px;
    }

    #btnCancelarEdicao {
        display: none;
        margin-left: 10px;
        background: #999;
    }

    .secao-titulo {
        margin-top: 45px;
    }
</style>

</head>

<body>

<div class="container">

    <h1 id="tituloForm">Adicionar Produto</h1>

    <button class="btn-voltar" onclick="window.location.href='vendedor.php'">
     Voltar
    </button>

    <?php if($mensagem): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            mostrarMensagem(<?= json_encode($mensagem) ?>, <?= json_encode($mensagemTipo) ?>);
        });
    </script>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="formProduto">

        <input type="hidden" name="produto_id" id="produto_id" value="">

        <div class="form-grid">

            <div class="form-group">
                <label>Nome do Produto</label>
                <input type="text" name="nome" id="campo_nome" required>
            </div>

            <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca" id="campo_marca">
            </div>

            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" id="campo_sku" required>
            </div>

            <div class="form-group">
                <label>Preço</label>
                <input type="text" name="preco" id="campo_preco" required>
            </div>

            <div class="form-group">
                <label>Estoque</label>
                <input type="number" name="estoque" id="campo_estoque" min="0" required>
            </div>

            <div class="form-group">

                <label>Categoria</label>

                <div class="categoria-box">

                    <select name="categoria_id" id="campo_categoria" required>

                        <option value="">Selecione</option>

                        <?php foreach($categorias as $categoria): ?>

                            <option value="<?= htmlspecialchars($categoria['id']) ?>">
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button type="button" class="btn-categoria" onclick="abrirModal()">
                        +
                    </button>

                </div>

            </div>

            <div class="form-group form-group-full">
                <label>Descrição</label>
                <textarea name="descricao" id="campo_descricao" required></textarea>
            </div>

            <div class="form-group form-group-full">

                <label>Imagem <small id="labelImagemInfo"></small></label>

                <input type="file" name="imagem" accept="image/*" id="imagemInput">

                <div class="preview" id="preview">
                    <img id="previewImg">
                </div>

            </div>

        </div>

        <button class="btn" type="submit" id="btnSubmit">
            Cadastrar Produto
        </button>

        <button class="btn" type="button" id="btnCancelarEdicao" onclick="cancelarEdicao()">
            Cancelar edição
        </button>

    </form>

    <h2 class="secao-titulo">Meus Produtos</h2>

    <?php if (empty($produtos)): ?>

        <p class="sem-produtos">Nenhum produto cadastrado ainda.</p>

    <?php else: ?>

        <div class="produtos-grid">

            <?php foreach ($produtos as $produto): ?>

                <div class="produto-card">

                    <?php if (!empty($produto['imagem'])): ?>
                        <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    <?php else: ?>
                        <div class="sem-imagem">Sem imagem</div>
                    <?php endif; ?>

                    <span class="badge <?= $produto['status'] === 'ativo' ? 'ativo' : 'inativo' ?>">
                        <?= htmlspecialchars($produto['status']) ?>
                    </span>

                    <h3><?= htmlspecialchars($produto['nome']) ?></h3>

                    <div class="produto-meta">SKU: <?= htmlspecialchars($produto['sku']) ?></div>
                    <div class="produto-meta">Categoria: <?= htmlspecialchars($produto['categoria_nome'] ?? '—') ?></div>
                    <div class="produto-meta">Estoque: <?= (int) $produto['estoque'] ?> un.</div>

                    <div class="produto-preco">
                        R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?>
                    </div>

                    <button
                        type="button"
                        class="btn-editar-produto"
                        onclick='editarProduto(<?= json_encode([
                            "id" => $produto['id'],
                            "nome" => $produto['nome'],
                            "marca" => $produto['marca'],
                            "sku" => $produto['sku'],
                            "preco" => $produto['preco'],
                            "estoque" => $produto['estoque'],
                            "categoria_id" => $produto['categoria_id'],
                            "descricao" => $produto['descricao'],
                            "imagem" => $produto['imagem'],
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                    >
                        Editar Produto
                    </button>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<!-- MODAL -->
<div class="modal" id="modalCategoria">

    <div class="modal-content">

        <h2>Nova Categoria</h2>

        <form method="POST" action="salvar_categoria.php">

            <div class="form-group">
                <label>Nome da Categoria</label>
                <input type="text" name="nome_categoria" required>
            </div>

            <button class="btn" type="submit">
                Salvar Categoria
            </button>

        </form>

    </div>

</div>

<script>

// PREVIEW IMAGEM
const imagemInput = document.getElementById('imagemInput');
const preview = document.getElementById('preview');
const previewImg = document.getElementById('previewImg');

imagemInput.addEventListener('change', (e) => {
    const arquivo = e.target.files[0];
    if (!arquivo) return;

    const reader = new FileReader();
    reader.onload = function(evento) {
        preview.style.display = 'block';
        previewImg.src = evento.target.result;
    };
    reader.readAsDataURL(arquivo);
});

// MODAL
const modal = document.getElementById('modalCategoria');

function abrirModal() {
    modal.style.display = 'flex';
}

window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// -----------------------------------------------------------
// EDIÇÃO DE PRODUTO
// -----------------------------------------------------------
function editarProduto(produto) {

    document.getElementById('produto_id').value = produto.id;
    document.getElementById('campo_nome').value = produto.nome ?? '';
    document.getElementById('campo_marca').value = produto.marca ?? '';
    document.getElementById('campo_sku').value = produto.sku ?? '';
    document.getElementById('campo_preco').value = produto.preco ?? '';
    document.getElementById('campo_estoque').value = produto.estoque ?? '';
    document.getElementById('campo_categoria').value = produto.categoria_id ?? '';
    document.getElementById('campo_descricao').value = produto.descricao ?? '';

    // Imagem não é obrigatória ao editar: mostra a atual e avisa
    // que só precisa enviar um arquivo novo se quiser trocá-la.
    imagemInput.required = false;
    if (produto.imagem) {
        preview.style.display = 'block';
        previewImg.src = produto.imagem;
        document.getElementById('labelImagemInfo').textContent =
            '(envie apenas se quiser trocar a imagem atual)';
    } else {
        preview.style.display = 'none';
        document.getElementById('labelImagemInfo').textContent = '';
    }

    document.getElementById('tituloForm').textContent = 'Editar Produto';
    document.getElementById('btnSubmit').textContent = 'Atualizar Produto';
    document.getElementById('btnCancelarEdicao').style.display = 'inline-block';

    document.getElementById('formProduto').scrollIntoView({ behavior: 'smooth' });
}

function cancelarEdicao() {
    document.getElementById('formProduto').reset();
    document.getElementById('produto_id').value = '';

    preview.style.display = 'none';
    document.getElementById('labelImagemInfo').textContent = '';

    document.getElementById('tituloForm').textContent = 'Adicionar Produto';
    document.getElementById('btnSubmit').textContent = 'Cadastrar Produto';
    document.getElementById('btnCancelarEdicao').style.display = 'none';
}

</script>

<div id="toast" class="toast"></div>
<script>

function mostrarMensagem(texto, tipo = "sucesso") {

    const toast = document.getElementById("toast");

    toast.innerHTML = texto;
    toast.className = "toast";
    toast.classList.add(tipo === "erro" ? "erro" : "sucesso");
    toast.classList.add("mostrar");

    setTimeout(() => {
        toast.classList.remove("mostrar");
    }, 3500);
}

</script>

<style>
    .toast{
        position:fixed;
        top:25px;
        right:25px;
        min-width:320px;
        max-width:450px;
        padding:18px;
        border-radius:8px;
        color:#fff;
        font-weight:bold;
        box-shadow:0 10px 25px rgba(0,0,0,.25);
        transform:translateX(500px);
        transition:.4s;
        z-index:99999;
    }

    .toast.mostrar{
        transform:translateX(0);
    }

    .toast.sucesso{
        background:#28a745;
    }

    .toast.erro{
        background:#dc3545;
    }
</style>
</body>
</html>