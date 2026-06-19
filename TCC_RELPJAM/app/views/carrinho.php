<?php
require_once 'config.php';

try {
    // SQL ajustado para buscar a imagem na tabela produto_imagens
    // Usamos DISTINCT para pegar apenas uma imagem por produto
    $sql = "SELECT 
                c.id, 
                c.quantidade, 
                p.nome, 
                p.preco, 
                p.estoque,
                v.nome_loja as loja,
                v.id as loja_id,
                (SELECT pi.imagem FROM produto_imagens pi WHERE pi.produto_id = p.id LIMIT 1) as imagem
            FROM carrinho_usuario c
            JOIN produtos p ON c.produto_id = p.id
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE c.usuario_id = ? 
            ORDER BY v.id ASC";
            
    $stmt = $pdo->prepare($sql);
    
    // IMPORTANTE: Se o login não estiver pronto, usamos o ID 1 para testes
    $usuario_id = $_SESSION['usuario_id'] ?? 1; 
    $stmt->execute([$usuario_id]);
    $cartProdutos = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<div style='color:red; background:white; padding:10px;'>Erro no Banco: " . $e->getMessage() . "</div>";
    $cartProdutos = []; 
}

// O restante do processamento de $lojas e cálculos permanece IGUAL ao seu original
$lojas = [];
foreach ($cartProdutos as $produto) {
    $lojas[$produto['loja_id']]['nome'] = $produto['loja'];
    $lojas[$produto['loja_id']]['produtos'][] = $produto;
}

$subtotal = 0;
foreach ($cartProdutos as $p) {
    $subtotal += $p['preco'] * $p['quantidade'];
}
$fretePorLoja = 19.90;
$freteTotal = $fretePorLoja * count($lojas);
$totalGeral = $subtotal + $freteTotal;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RELPJAM — Seu Carrinho</title>
    
    <!-- Fontes e Dependências -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="<?= $base ?>/public/assets/css/style.css">
    
</head>
<body>

<header class="site-header">
    <a href="home.php" class="site-logo">RELP<span>JAM</span></a>
    <nav class="site-nav">
        <a href="home.php">Home</a>
        <a href="produto_form.php">Produtos</a>
        <a href="carrinho.php" class="active">Carrinho (<?= count($cartProdutos) ?>)</a>
    </nav>
</header>

<main class="container">
    <div class="cart-grid">
        
        <!-- Lado Esquerdo: Itens -->
        <div class="cart-items-container">
            
            <?php if (count($cartProdutos) > 0): ?>
                <!-- Selecionar Tudo -->
                <div class="cart-section" style="padding: 1rem 1.5rem; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" id="select-all" checked>
                        <span style="font-size: 0.9rem; font-weight: 500;">Selecionar todos os itens</span>
                    </label>
                </div>

                <?php foreach ($lojas as $lojaId => $loja): ?>
                    <section class="cart-section" data-loja-id="<?= $lojaId ?>">
                        <div class="section-header">
                            <input type="checkbox" class="loja-checkbox" data-loja="<?= $lojaId ?>" checked>
                            <span class="seller-badge">Loja</span>
                            <h2 class="seller-title"><?= htmlspecialchars($loja['nome']) ?></h2>
                        </div>

                        <?php foreach ($loja['produtos'] as $produto): ?>
                            <div class="product-item" id="item-<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>">
                                <input type="checkbox" class="item-checkbox" 
                                       data-loja="<?= $lojaId ?>" 
                                       data-id="<?= $produto['id'] ?>" 
                                       checked>
                                
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="product-img">
                                
                                <div class="product-details">
                                    <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                                    <p class="product-meta">
                                        Cor: <b><?= htmlspecialchars($produto['cor']) ?></b> | 
                                        Tam: <b><?= htmlspecialchars($produto['tamanho']) ?></b>
                                    </p>
                                    <p class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                                    
                                    <div class="product-actions">
                                        <button class="btn-action" onclick="favoritar(<?= $produto['id'] ?>)">Salvar para depois</button>
                                        <button class="btn-action remove" onclick="removerItem(<?= $produto['id'] ?>)">Remover</button>
                                    </div>
                                </div>

                                <div class="qty-wrapper">
                                    <div class="item-subtotal" id="subtotal-<?= $produto['id'] ?>">
                                        R$ <?= number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.') ?>
                                    </div>
                                    <div class="qty-control">
                                        <button class="qty-btn" onclick="atualizarQuantidade(<?= $produto['id'] ?>, -1)">−</button>
                                        <input type="text" class="qty-input" id="qty-<?= $produto['id'] ?>" 
                                               value="<?= $produto['quantidade'] ?>" 
                                               data-max="<?= $produto['estoque'] ?>" readonly>
                                        <button class="qty-btn" onclick="atualizarQuantidade(<?= $produto['id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Frete da Loja -->
                        <div style="padding: 1rem 1.5rem; background: rgba(0,0,0,0.1); border-top: 1px solid var(--border);">
                            <div class="input-group">
                                <input type="text" class="input-text cep-mask" placeholder="Calcular Frete (CEP)" maxlength="9">
                                <button class="btn-secondary" onclick="msgFrete()">Calcular</button>
                                <span style="margin-left: auto; font-size: 0.85rem; color: var(--accent);">Frete: R$ 19,90</span>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>

                <!-- Cupom -->
                <div class="cart-section" style="padding: 1.5rem;">
                    <p style="font-size: 0.9rem; margin-bottom: 0.75rem; font-weight: 500;">Possui um cupom de desconto?</p>
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" id="cupom-input" class="input-text" placeholder="Ex: RELPJAM10">
                        <button class="btn-secondary" onclick="aplicarCupom()">Aplicar Cupom</button>
                    </div>
                </div>

            <?php else: ?>
                <div class="cart-section" style="padding: 4rem 2rem; text-align: center;">
                    <h2 style="margin-bottom: 1rem;">Seu carrinho está vazio </h2>
                    <p style="color: var(--muted); margin-bottom: 2rem;">Parece que você ainda não adicionou nenhum produto.</p>
                    <a href="home.php" class="btn-checkout" style="text-decoration: none; display: inline-block; width: auto;">Voltar para a Loja</a>
                </div>
            <?php endif; ?>

        </div>

        <!-- Lado Direito: Resumo -->
        <aside>
            <div class="summary-box">
                <h2>Resumo do Pedido</h2>
                
                <div class="summary-row">
                    <span>Produtos (<span id="count-selecionados">0</span> selecionados)</span>
                    <b id="resumo-subtotal">R$ 0,00</b>
                </div>
                
                <div class="summary-row">
                    <span>Frete Total</span>
                    <b id="resumo-frete">R$ 0,00</b>
                </div>

                <div class="summary-row discount" id="row-desconto" style="display: none;">
                    <span>Desconto</span>
                    <b id="resumo-desconto">- R$ 0,00</b>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <b id="resumo-total">R$ 0,00</b>
                </div>

                <p style="font-size: 0.75rem; color: var(--muted); text-align: right; margin-top: 5px;">
                    ou em até 12x de <span id="resumo-parcela">R$ 0,00</span>
                </p>

                <button class="btn-checkout" onclick="finalizarCompra()">Finalizar Compra</button>
                
                <div style="margin-top: 1.5rem; text-align: center;">
                        <p style="font-size: 0.7rem; color: var(--muted); margin-top: 10px;">Compra 100% Segura</p>
                </div>
            </div>
        </aside>

    </div>
</main>

<script>
    // Configurações Globais
    const FRETE_POR_LOJA = 19.90;
    let valorDesconto = 0;

    /**
     * Formata números para o padrão de moeda brasileiro (R$)
     */
    function formatarMoeda(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    /**
     * Lógica Principal de Recálculo do Carrinho
     */
    function recalcularCarrinho() {
        let subtotal = 0;
        let itensSelecionados = 0;
        let lojasAtivas = new Set();

        $('.item-checkbox:checked').each(function() {
            const id = $(this).data('id');
            const lojaId = $(this).data('loja');
            const preco = parseFloat($('#item-' + id).data('preco'));
            const qtd = parseInt($('#qty-' + id).val());

            subtotal += (preco * qtd);
            itensSelecionados++;
            lojasAtivas.add(lojaId);
        });

        const freteTotal = lojasAtivas.size * FRETE_POR_LOJA;
        const total = Math.max(0, subtotal + freteTotal - valorDesconto);

        $('#count-selecionados').text(itensSelecionados);
        $('#resumo-subtotal').text(formatarMoeda(subtotal));
        $('#resumo-frete').text(formatarMoeda(freteTotal));
        $('#resumo-total').text(formatarMoeda(total));
        $('#resumo-parcela').text(formatarMoeda(total / 12));

        $('.btn-checkout').prop('disabled', itensSelecionados === 0).css('opacity', itensSelecionados === 0 ? 0.5 : 1);
    }

    /**
     * Gerencia a alteração de quantidade de um produto
     */
    function atualizarQuantidade(id, delta) {
        const input = $('#qty-' + id);
        const max = parseInt(input.data('max'));
        let novaQtd = parseInt(input.val()) + delta;

        if (novaQtd < 1) novaQtd = 1;
        if (novaQtd > max) {
            Swal.fire({ icon: 'info', title: 'Limite de estoque', text: 'Desculpe, temos apenas ' + max + ' unidades disponíveis.', background: '#132035', color: '#f0f4ff' });
            novaQtd = max;
        }

        input.val(novaQtd);
        
        // Atualiza subtotal do item na linha
        const preco = parseFloat($('#item-' + id).data('preco'));
        $('#subtotal-' + id).text(formatarMoeda(preco * novaQtd));

        // --- CONEXÃO COM O BANCO ---
        $.post('api_carrinho.php', { id: id, acao: 'atualizar', quantidade: novaQtd });
        // ---------------------------

        recalcularCarrinho();
    }

    /**
     * Remove um item do carrinho com confirmação
     */
    function removerItem(id) {
        Swal.fire({
            title: 'Remover produto?',
            text: "Você pode adicioná-lo novamente mais tarde.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#374151',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar',
            background: '#132035',
            color: '#f0f4ff'
        }).then((result) => {
            if (result.isConfirmed) {
                // --- CONEXÃO COM O BANCO ---
                $.post('api_carrinho.php', { id: id, acao: 'remover' });
                // ---------------------------

                const item = $('#item-' + id);
                const lojaContainer = item.closest('.cart-section');
                
                item.fadeOut(300, function() { 
                    $(this).remove(); 
                    if (lojaContainer.find('.product-item').length === 0) {
                        lojaContainer.remove();
                    }
                    recalcularCarrinho();
                    if ($('.product-item').length === 0) {
                        location.reload();
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        $('#select-all').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.loja-checkbox, .item-checkbox').prop('checked', isChecked);
            recalcularCarrinho();
        });

        $('.loja-checkbox').on('change', function() {
            const lojaId = $(this).data('loja');
            const isChecked = $(this).is(':checked');
            $('.item-checkbox[data-loja="' + lojaId + '"]').prop('checked', isChecked);
            atualizarStatusSelectAll();
            recalcularCarrinho();
        });

        $('.item-checkbox').on('change', function() {
            const lojaId = $(this).data('loja');
            const totalItensLoja = $('.item-checkbox[data-loja="' + lojaId + '"]').length;
            const itensMarcadosLoja = $('.item-checkbox[data-loja="' + lojaId + '"]:checked').length;
            $('.loja-checkbox[data-loja="' + lojaId + '"]').prop('checked', totalItensLoja === itensMarcadosLoja);
            atualizarStatusSelectAll();
            recalcularCarrinho();
        });

        function atualizarStatusSelectAll() {
            const total = $('.item-checkbox').length;
            const marcados = $('.item-checkbox:checked').length;
            $('#select-all').prop('checked', total === marcados && total > 0);
        }

        $('.cep-mask').on('input', function() {
            let v = $(this).val().replace(/\D/g, '');
            if (v.length > 5) v = v.substring(0, 5) + '-' + v.substring(5, 8);
            $(this).val(v);
        });

        recalcularCarrinho();
    });

    function favoritar(id) {
        Swal.fire({ icon: 'success', title: 'Salvo nos Favoritos!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#132035', color: '#f0f4ff' });
    }

    function msgFrete() {
        Swal.fire({ icon: 'success', title: 'Frete Atualizado', text: 'O valor foi calculado com sucesso para o seu endereço.', background: '#132035', color: '#f0f4ff', confirmButtonColor: '#00d9a5' });
    }

    function aplicarCupom() {
        const cupom = $('#cupom-input').val().trim().toUpperCase();
        if (cupom === 'RELPJAM10') {
            valorDesconto = 10.00;
            $('#row-desconto').show();
            $('#resumo-desconto').text('- ' + formatarMoeda(valorDesconto));
            recalcularCarrinho();
            Swal.fire({ icon: 'success', title: 'Cupom Aplicado!', text: 'Você ganhou R$ 10,00 de desconto.', background: '#132035', color: '#f0f4ff' });
        } else {
            Swal.fire({ icon: 'error', title: 'Cupom Inválido', text: 'Tente o código RELPJAM10.', background: '#132035', color: '#f0f4ff' });
        }
    }

    function finalizarCompra() {
        Swal.fire({
            title: 'Quase lá!',
            text: 'Deseja prosseguir para o pagamento seguro?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00d9a5',
            confirmButtonText: 'Ir para Pagamento',
            cancelButtonText: 'Revisar Carrinho',
            background: '#132035',
            color: '#f0f4ff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Redirecionando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                // window.location.href = 'checkout.php';
            }
        });
    }
</script>


</body>
</html>
