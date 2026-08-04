<?php
// Ajuste o caminho conforme a localização do seu config.php
require_once __DIR__ . '/../../config.php';

try {
    $sql = "SELECT 
                c.id, 
                c.quantidade, 
                p.nome, 
                p.preco, 
                p.estoque,
                v.nome_loja as loja,
                v.id as loja_id,
                COALESCE(p.cor, 'Padrão') as cor,
                COALESCE(p.tamanho, 'Único') as tamanho,
                (SELECT pi.imagem FROM produto_imagens pi WHERE pi.produto_id = p.id LIMIT 1) as imagem
            FROM carrinho_usuario c
            JOIN produtos p ON c.produto_id = p.id
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE c.usuario_id = ? 
            ORDER BY v.id ASC";
            
    $stmt = $pdo->prepare($sql);
    $usuario_id = $_SESSION['usuario_id'] ?? 1; // ID 1 para testes
    $stmt->execute([$usuario_id]);
    $cartProdutos = $stmt->fetchAll();

} catch (PDOException $e) {
    $cartProdutos = [];
    $erroBanco = "Erro de conexão: " . $e->getMessage();
}

$lojas = [];
foreach ($cartProdutos as $produto) {
    $lojas[$produto['loja_id']]['nome'] = $produto['loja'];
    $lojas[$produto['loja_id']]['produtos'][] = $produto;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>RELPJAM — Seu Carrinho</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../public/assets/css/carrinho.css">
</head>
<body>
<header class="site-header">
    <a href="index.php?page=home" class="site-logo">RELP<span>JAM</span></a>
    <nav class="site-nav">
        <a href="index.php?page=home">Home</a>
        <a href="index.php?page=carrinho" class="active">Carrinho (<?= count($cartProdutos ) ?>)</a>
    </nav>
</header>

<main class="container">
    <div class="cart-grid">
        <div class="cart-items-container">
            <?php if (count($cartProdutos) > 0): ?>
                <?php foreach ($lojas as $lojaId => $loja): ?>
                    <section class="cart-section">
                        <div class="section-header">
                            <input type="checkbox" class="loja-checkbox" data-loja="<?= $lojaId ?>" checked>
                            <h2 class="seller-title"><?= htmlspecialchars($loja['nome']) ?></h2>
                        </div>
                        <?php foreach ($loja['produtos'] as $produto): ?>
                            <div class="product-item" id="item-<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>">
                                <input type="checkbox" class="item-checkbox" data-loja="<?= $lojaId ?>" data-id="<?= $produto['id'] ?>" checked>
                                <img src="<?= htmlspecialchars($produto['imagem'] ?? '../../public/images/nike.png') ?>" class="product-img">
                                <div class="product-details">
                                    <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                                    <p class="product-meta">Cor: <?= $produto['cor'] ?> | Tam: <?= $produto['tamanho'] ?></p>
                                    <p class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                                    <button class="btn-action remove" onclick="removerItem(<?= $produto['id'] ?>)">Remover</button>
                                </div>
                                <div class="qty-wrapper">
                                    <div class="item-subtotal" id="subtotal-<?= $produto['id'] ?>">R$ <?= number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.') ?></div>
                                    <div class="qty-control">
                                        <button onclick="atualizarQuantidade(<?= $produto['id'] ?>, -1)">−</button>
                                        <input type="text" id="qty-<?= $produto['id'] ?>" value="<?= $produto['quantidade'] ?>" data-max="<?= $produto['estoque'] ?>" readonly>
                                        <button onclick="atualizarQuantidade(<?= $produto['id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="cart-section" style="text-align:center; padding: 2rem;">Seu carrinho está vazio.</div>
            <?php endif; ?>
        </div>
        <aside class="summary-box">
            <h2>Resumo</h2>
            <div class="summary-row"><span>Subtotal</span><b id="resumo-subtotal">R$ 0,00</b></div>
            <div class="summary-row total"><span>Total</span><b id="resumo-total">R$ 0,00</b></div>
            <button class="btn-checkout" onclick="alert('Compra finalizada!')">Finalizar Compra</button>
        </aside>
    </div>
</main>

<script>
    function formatar(v) { return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
    
    function recalcular() {
        let total = 0;
        $('.item-checkbox:checked').each(function() {
            let id = $(this).data('id');
            let preco = parseFloat($('#item-'+id).data('preco'));
            let qtd = parseInt($('#qty-'+id).val());
            total += (preco * qtd);
        });
        $('#resumo-subtotal, #resumo-total').text(formatar(total));
    }

    function atualizarQuantidade(id, delta) {
        let input = $('#qty-'+id);
        let novaQtd = parseInt(input.val()) + delta;
        if (novaQtd < 1 || novaQtd > input.data('max')) return;
        input.val(novaQtd);
        let preco = parseFloat($('#item-'+id).data('preco'));
        $('#subtotal-'+id).text(formatar(preco * novaQtd));
        $.post('api_carrinho.php', { id: id, acao: 'atualizar', quantidade: novaQtd });
        recalcular();
    }

    function removerItem(id) {
        if(confirm('Remover item?')) {
            $.post('api_carrinho.php', { id: id, acao: 'remover' }, function() {
                $('#item-'+id).fadeOut(function() { $(this).remove(); recalcular(); });
            });
        }
    }

    $(document).ready(recalcular);
    $('.item-checkbox, .loja-checkbox').change(recalcular);
</script>
</body>
</html>
