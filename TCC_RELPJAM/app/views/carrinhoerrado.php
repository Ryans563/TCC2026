<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_ajax'])) {
    $id = $_POST['id'] ?? null;
    $acao = $_POST['acao_ajax'] ?? null;
    if ($id && $acao) {
        try {
            if ($acao === 'atualizar') {
                $quantidade = $_POST['quantidade'];
                $stmt = $pdo->prepare("UPDATE carrinho_usuario SET quantidade = ? WHERE id = ?");
                $stmt->execute([$quantidade, $id]);
            } elseif ($acao === 'remover') {
                $stmt = $pdo->prepare("DELETE FROM carrinho_usuario WHERE id = ?");
                $stmt->execute([$id]);
            }
            echo json_encode(['status' => 'sucesso']);
        } catch (PDOException $e) {
            http_response_code(500 );
            echo json_encode(['status' => 'erro', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

try {
    $sql = "SELECT c.id, c.quantidade, p.nome, p.preco, p.estoque, v.nome_loja as loja, v.id as loja_id,
            (SELECT pi.imagem FROM produto_imagens pi WHERE pi.produto_id = p.id LIMIT 1) as imagem
            FROM carrinho_usuario c
            JOIN produtos p ON c.produto_id = p.id
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE c.usuario_id = ? ORDER BY v.id ASC";
    $stmt = $pdo->prepare($sql);
    $usuario_id = $_SESSION['usuario_id'] ?? 1;
    $stmt->execute([$usuario_id]);
    $cartProdutos = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro_db = $e->getMessage();
    $cartProdutos = []; 
}

$lojas = []; $subtotal = 0;
foreach ($cartProdutos as $p) {
    $lojas[$p['loja_id']]['nome'] = $p['loja'];
    $lojas[$p['loja_id']]['produtos'][] = $p;
    $subtotal += $p['preco'] * $p['quantidade'];
}
$freteTotal = 19.90 * count($lojas);
$totalGeral = $subtotal + $freteTotal;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RELPJAM — Seu Carrinho</title>
    <link rel="stylesheet" href="../../public/css/stylecarrinho.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="car-body">

<header class="car-site-header">
    <a href="#" class="car-site-logo">RELP<span>JAM</span></a>
    <nav class="car-site-nav">
        <a href="home.php">Home</a>
        <a href="produtos.php">Produtos</a>
        <a href="carrinho.php" class="car-active">Carrinho (<?= count($cartProdutos ) ?>)</a>
    </nav>
</header>

<main class="car-container">
    <?php if (isset($erro_db)): ?>
        <div class="car-db-error"><b>Erro de Banco:</b> <?= $erro_db ?></div>
    <?php endif; ?>

    <div class="car-cart-grid">
        <div class="car-cart-items-container">
            <?php if (count($cartProdutos) > 0): ?>
                <div class="car-cart-section car-select-all-box">
                    <label class="car-label-checkbox">
                        <input type="checkbox" id="select-all" checked>
                        <span>Selecionar todos os itens</span>
                    </label>
                </div>

                <?php foreach ($lojas as $lojaId => $loja): ?>
                    <section class="car-cart-section" data-loja-id="<?= $lojaId ?>">
                        <div class="car-section-header">
                            <input type="checkbox" class="loja-checkbox" data-loja="<?= $lojaId ?>" checked>
                            <span class="car-seller-badge">Loja</span>
                            <h2 class="car-seller-title"><?= htmlspecialchars($loja['nome']) ?></h2>
                        </div>

                        <?php foreach ($loja['produtos'] as $p): ?>
                            <div class="car-product-item" id="item-<?= $p['id'] ?>" data-preco="<?= $p['preco'] ?>">
                                <input type="checkbox" class="item-checkbox" data-loja="<?= $lojaId ?>" data-id="<?= $p['id'] ?>" checked>
                                <img src="<?= htmlspecialchars($p['imagem'] ?? 'https://via.placeholder.com/100' ) ?>" class="car-product-img">
                                <div class="car-product-details">
                                    <h3><?= htmlspecialchars($p['nome']) ?></h3>
                                    <p class="car-product-price">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
                                    <div class="car-product-actions">
                                        <button class="car-btn-action car-remove" onclick="removerItem(<?= $p['id'] ?>)">Remover</button>
                                    </div>
                                </div>
                                <div class="car-qty-wrapper">
                                    <div class="car-qty-control">
                                        <button class="car-qty-btn" onclick="atualizarQuantidade(<?= $p['id'] ?>, -1)">-</button>
                                        <input type="text" readonly class="car-qty-input" id="qty-<?= $p['id'] ?>" value="<?= $p['quantidade'] ?>" data-max="<?= $p['estoque'] ?>">
                                        <button class="car-qty-btn" onclick="atualizarQuantidade(<?= $p['id'] ?>, 1)">+</button>
                                    </div>
                                    <span class="car-item-subtotal" id="subtotal-<?= $p['id'] ?>">R$ <?= number_format($p['preco'] * $p['quantidade'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="car-empty-cart">
                    <h2>Seu carrinho está vazio</h2>
                    <p>Parece que você ainda não adicionou nenhum produto.</p>
                    <a href="produtos.php" class="car-btn-primary">Voltar para a Loja</a>
                </div>
            <?php endif; ?>
        </div>

        <aside>
            <div class="car-summary-box">
                <h2>Resumo do Pedido</h2>
                <div class="car-summary-row"><span>Produtos</span> <b id="resumo-subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></b></div>
                <div class="car-summary-row"><span>Frete Total</span> <b id="resumo-frete">R$ <?= number_format($freteTotal, 2, ',', '.') ?></b></div>
                <div class="car-summary-row car-total"><span>Total</span> <b id="resumo-total">R$ <?= number_format($totalGeral, 2, ',', '.') ?></b></div>
                <button class="car-btn-checkout" onclick="finalizarCompra()">Finalizar Compra</button>
            </div>
        </aside>
    </div>
</main>

<script>
    function formatarMoeda(valor) { return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
    function recalcularCarrinho() {
        let subtotal = 0; let lojasAtivas = new Set();
        $('.item-checkbox:checked').each(function() {
            const id = $(this).data('id');
            const preco = parseFloat($('#item-' + id).data('preco'));
            const qtd = parseInt($('#qty-' + id).val());
            subtotal += (preco * qtd);
            lojasAtivas.add($(this).data('loja'));
        });
        const frete = lojasAtivas.size * 19.90;
        const total = subtotal + frete;
        $('#resumo-subtotal').text(formatarMoeda(subtotal));
        $('#resumo-frete').text(formatarMoeda(frete));
        $('#resumo-total').text(formatarMoeda(total));
    }
    function atualizarQuantidade(id, delta) {
        const input = $('#qty-' + id);
        const max = parseInt(input.data('max'));
        let novaQtd = parseInt(input.val()) + delta;
        if (novaQtd < 1) novaQtd = 1;
        if (novaQtd > max) { Swal.fire({ icon: 'info', title: 'Limite de estoque', text: 'Máximo: ' + max }); novaQtd = max; }
        input.val(novaQtd);
        $.post('carrinho.php', { id: id, acao_ajax: 'atualizar', quantidade: novaQtd });
        const preco = parseFloat($('#item-' + id).data('preco'));
        $('#subtotal-' + id).text(formatarMoeda(preco * novaQtd));
        recalcularCarrinho();
    }
    function removerItem(id) {
        Swal.fire({ title: 'Remover?', showCancelButton: true, confirmButtonColor: '#ef4444' }).then((result) => {
            if (result.isConfirmed) {
                $.post('carrinho.php', { id: id, acao_ajax: 'remover' });
                $('#item-' + id).fadeOut(300, function() { $(this).remove(); recalcularCarrinho(); location.reload(); });
            }
        });
    }
    $(document).ready(function() {
        $('.item-checkbox, .loja-checkbox, #select-all').on('change', function() { recalcularCarrinho(); });
    });
</script>
</body>
</html>
