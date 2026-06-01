<?php

require_once "config.php";

$base = "/TCC_RELPJAM";

// BUSCAR CATEGORIAS
$sqlCategorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome ASC");
$categorias = $sqlCategorias->fetchAll(PDO::FETCH_ASSOC);

$mensagem = "";

// CADASTRAR PRODUTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $categoria_id = $_POST['categoria_id'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $estoque = $_POST['estoque'];
    $marca = trim($_POST['marca']);
    $sku = trim($_POST['sku']);

    // GERAR SLUG
    $slug = strtolower($nome);
    $slug = preg_replace('/[^a-zA-Z0-9]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);

    // IMAGEM
    $imagemNome = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {

        $pasta = "public/images/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $imagemNome = uniqid() . '-' . $_FILES['imagem']['name'];

        move_uploaded_file(
            $_FILES['imagem']['tmp_name'],
            $pasta . $imagemNome
        );
    }

    try {

        // INSERIR PRODUTO
        $sql = $pdo->prepare("
            INSERT INTO produtos (
                vendedor_id,
                categoria_id,
                nome,
                slug,
                descricao,
                sku,
                preco,
                estoque,
                marca,
                status
            )
            VALUES (
                :vendedor_id,
                :categoria_id,
                :nome,
                :slug,
                :descricao,
                :sku,
                :preco,
                :estoque,
                :marca,
                'ativo'
            )
        ");

        $sql->execute([
            ':vendedor_id' => 1,
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

        // INSERIR IMAGEM
        if ($imagemNome) {

            $sqlImagem = $pdo->prepare("
                INSERT INTO produto_imagens (
                    produto_id,
                    imagem,
                    principal
                )
                VALUES (
                    :produto_id,
                    :imagem,
                    1
                )
            ");

            $sqlImagem->execute([
                ':produto_id' => $produto_id,
                ':imagem' => $imagemNome
            ]);
        }

        $mensagem = "Produto cadastrado com sucesso!";

    } catch (PDOException $e) {

        $mensagem = "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Adicionar Produto</title>
<link rel="stylesheet" href="<?= $base ?>/public/assets/css/style.css">

</head>
<body>

<div class="container">

    <h1>Adicionar Produto</h1>

    <button class="btn-voltar" onclick="window.location.href='vendedor.php'">
     Voltar
    </button>

    <?php if($mensagem): ?>
        <div class="mensagem">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-grid">

            <div class="form-group">
                <label>Nome do Produto</label>
                <input type="text" name="nome" required>
            </div>

            <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca">
            </div>

            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" required>
            </div>

            <div class="form-group">
                <label>Preço</label>
                <input type="text" name="preco" required>
            </div>

            <div class="form-group">
                <label>Estoque</label>
                <input type="number" name="estoque" required>
            </div>

            <div class="form-group">

                <label>Categoria</label>

                <div class="categoria-box">

                    <select name="categoria_id" required>

                        <option value="">Selecione</option>

                        <?php foreach($categorias as $categoria): ?>

                            <option value="<?= $categoria['id'] ?>">
                                <?= $categoria['nome'] ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button
                        type="button"
                        class="btn-categoria"
                        onclick="abrirModal()"
                    >
                        +
                    </button>

                </div>

            </div>

            <div class="form-group form-group-full">
                <label>Descrição</label>
                <textarea name="descricao" required></textarea>
            </div>

            <div class="form-group form-group-full">

                <label>Imagem</label>

                <input
                    type="file"
                    name="imagem"
                    accept="image/*"
                    id="imagemInput"
                >

                <div class="preview" id="preview">
                    <img id="previewImg">
                </div>

            </div>

        </div>

        <button class="btn" type="submit">
            Cadastrar Produto
        </button>

    </form>

</div>

<!-- MODAL -->
<div class="modal" id="modalCategoria">

    <div class="modal-content">

        <h2>Nova Categoria</h2>

        <form method="POST" action="salvar-categoria.php">

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
    }

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

</script>

</body>
</html>
