<?php
require_once "config.php";

try {
    $sql = "SELECT c.id, c.quantidade, p.nome, p.preco, p.estoque, v.nome_loja as loja, v.id as loja_id,
            COALESCE(p.cor, 'Padrão') as cor, COALESCE(p.tamanho, 'Único') as tamanho,
            (SELECT pi.imagem 
            FROM produto_imagens pi 
            WHERE pi.produto_id = p.id LIMIT 1) as imagem
            FROM carrinho_usuario c 
            JOIN produtos p 
            ON c.produto_id = p.id
            JOIN vendedores v ON p.vendedor_id = v.id 
            WHERE c.usuario_id = ? 
            ORDER BY v.id ASC";

    $stmt = $pdo->prepare($sql);
    $usuario_id = $_SESSION['usuario_id'] ?? 1;
    $stmt->execute([$usuario_id]);
    $cartProdutos = $stmt->fetchAll();

} catch (PDOException $e) { $cartProdutos = []; }

$lojas = [];
foreach ($cartProdutos as $p) {
    $lojas[$p['loja_id']]['nome'] = $p['loja'];
    $lojas[$p['loja_id']]['produtos'][] = $p;
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
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body class="carrinho-body">
<div class="carrinho-container">
    <div class="carrinho-grid">
        <div class="carrinho-items-container">
            <?php if (count($cartProdutos ) > 0): ?>
                <?php foreach ($lojas as $lojaId => $loja): ?>
                    <section class="carrinho-section">
                        <div class="carrinho-section-header">
                            <input type="checkbox" class="carrinho-loja-checkbox" data-loja="<?= $lojaId ?>" checked>
                            <h2 class="carrinho-seller-title"><?= htmlspecialchars($loja['nome']) ?></h2>
                        </div>
                        <?php foreach ($loja['produtos'] as $p): ?>
                            <div class="carrinho-product-item" id="item-<?= $p['id'] ?>" data-preco="<?= $p['preco'] ?>">
                                <input type="checkbox" class="carrinho-item-checkbox" data-loja="<?= $lojaId ?>" data-id="<?= $p['id'] ?>" checked>
                                <img src="<?= htmlspecialchars($p['imagem'] ?? '../../public/images/nike.png') ?>" class="carrinho-product-img">
                                <div class="carrinho-product-details">
                                    <h3><?= htmlspecialchars($p['nome']) ?></h3>
                                    <p class="carrinho-product-meta">Cor: <?= $p['cor'] ?> | Tam: <?= $p['tamanho'] ?></p>
                                    <p class="carrinho-product-price">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
                                    <button class="carrinho-btn-action carrinho-remove" onclick="removerItem(<?= $p['id'] ?>)">Remover</button>
                                </div>
                                <div style="text-align: right;">
                                    <span class="carrinho-item-subtotal" id="subtotal-<?= $p['id'] ?>">R$ <?= number_format($p['preco'] * $p['quantidade'], 2, ',', '.') ?></span>
                                    <div class="carrinho-qty-control">
                                        <button onclick="atualizarQuantidade(<?= $p['id'] ?>, -1)">−</button>
                                        <input type="text" id="qty-<?= $p['id'] ?>" value="<?= $p['quantidade'] ?>" data-max="<?= $p['estoque'] ?>" readonly>
                                        <button onclick="atualizarQuantidade(<?= $p['id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carrinho-section" style="text-align:center; padding: 40px;">Seu carrinho está vazio.</div>
            <?php endif; ?>
        </div>
        <aside class="carrinho-summary-box">
            <h2>Resumo</h2>
            <div class="carrinho-summary-row"><span>Subtotal</span><b id="resumo-subtotal">R$ 0,00</b></div>
            <div class="carrinho-summary-row carrinho-total"><span>Total</span><b id="resumo-total">R$ 0,00</b></div>
            <button class="carrinho-btn-checkout" onclick="alert('Finalizando...')">Finalizar Compra</button>
        </aside>
    </div>
</div>

<script>
    function formatar(v) { return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
    function recalcular() {
        let total = 0;
        $('.carrinho-item-checkbox:checked').each(function() {
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
        $('#subtotal-'+id).text(formatar(parseFloat($('#item-'+id).data('preco')) * novaQtd));
        $.post('api_carrinho.php', { id: id, acao: 'atualizar', quantidade: novaQtd });
        recalcular();
    }
    function removerItem(id) {
        if(confirm('Remover?')) {
            $.post('api_carrinho.php', { id: id, acao: 'remover' }, function() {
                $('#item-'+id).fadeOut(function() { $(this).remove(); recalcular(); });
            });
        }
    }
    $(document).ready(recalcular);
    $('.carrinho-item-checkbox, .carrinho-loja-checkbox').change(recalcular);
</script>
</body>
</html>
