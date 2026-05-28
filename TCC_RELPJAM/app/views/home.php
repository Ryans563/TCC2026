<?php 
$base = "/TCC_RELPJAM";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RELPJAM </title>
<link rel="stylesheet" href="<?= $base ?>/public/assets/css/style.css">
</head>
<body>

<header>
    <div class="logo">RELPJAM</div>
    <div class="pesquisa">
        <input type="text" id="campoBusca" placeholder="Buscar Produtos">
    </div>
    <div class="icones">
        <span>Carrinho</span>
        <span>Perfil</span>
    </div>
</header>

<!-- ================= CARROSSEL ================= -->
<div class="carrossel-container">
    <button class="carrossel-btn btn-prev" id="btnPrev">
        <svg viewBox="0 0 24 24">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
    </button>

    <div class="carrossel-wrapper">
        <div class="carrossel-track" id="carrosselTrack"></div>
    </div>

    <button class="carrossel-btn btn-next" id="btnNext">
        <svg viewBox="0 0 24 24">
            <path d="M9 6l6 6-6 6"></path>
        </svg>
    </button>

    <div class="carrossel-indicadores" id="carrosselIndicadores"></div>
</div>

<!-- ================= CATEGORIAS ================= -->
<nav class="categorias">
    <ul>
        <button class="btn-categoria" data-categoria="Todos">
            <li class="ativo">Todos</li>
        </button>

        <button class="btn-categoria" data-categoria="Esporte">
            <li>Esporte</li>
        </button>

        <button class="btn-categoria" data-categoria="Tecnologia">
            <li>Tecnologia</li>
        </button>

        <button class="btn-categoria" data-categoria="Acessorio">
            <li>Acessorio</li>
        </button>

        <button class="btn-categoria" data-categoria="Gamer">
            <li>Gamer</li>
        </button>

        <button class="btn-categoria" data-categoria="Esporte">
            <li>Esporte</li>
        </button>

        <button class="btn-categoria" data-categoria="Tecnologia">
            <li>Tecnologia</li>
        </button>

        <button class="btn-categoria" data-categoria="Acessorio">
            <li>Acessorio</li>
        </button>

        <button class="btn-categoria" data-categoria="Gamer">
            <li>Gamer</li>
        </button>

        <button class="btn-categoria" data-categoria="Esporte">
            <li>Esporte</li>
        </button>

        <button class="btn-categoria" data-categoria="Tecnologia">
            <li>Tecnologia</li>
        </button>

        <button class="btn-categoria" data-categoria="Acessorio">
            <li>Acessorio</li>
        </button>

        <button class="btn-categoria" data-categoria="Gamer">
            <li>Gamer</li>
        </button>


        <button class="btn-categoria" data-categoria="Esporte">
            <li>Esporte</li>
        </button>

        <button class="btn-categoria" data-categoria="Tecnologia">
            <li>Tecnologia</li>
        </button>

        <button class="btn-categoria" data-categoria="Acessorio">
            <li>Acessorio</li>
        </button>

        <button class="btn-categoria" data-categoria="Gamer">
            <li>Gamer</li>
        </button>
    </ul>
</nav>

<!-- ================= PRODUTOS ================= -->
<main class="produtos" id="containerProdutos"></main>

<script>

    // DADOS DO CARROSSEL
    const itensCarrossel = [
        { nome: "Adidas Ultraboost", preco: "R$ 399,90", imagem: "<?= $base ?>/public/images/adidas1.png", emblema: "Lancamento" },
        { nome: "Nike Air Max", preco: "R$ 499,90", imagem: "<?= $base ?>/public/images/nike.png", emblema: "Mais Vendido" },
        { nome: "Tesla Edition", preco: "R$ 129,90", imagem: "<?= $base ?>/public/images/nike.png", emblema: "Exclusivo" },
        { nome: "Air270", preco: "R$ 599,90", imagem: "<?= $base ?>/public/images/nike.png", emblema: "Limitado" },
        { nome: "Botas Premium", preco: "R$ 259,90", imagem: "<?= $base ?>/public/images/nike.png", emblema: "Oferta" },
        { nome: "Jordan Retro", preco: "R$ 699,90", imagem: "<?= $base ?>/public/images/adidas1.png", emblema: "Mais Vendido" },
        { nome: "Puma Suede", preco: "R$ 349,90", imagem: "<?= $base ?>/public/images/nike.png", emblema: "Promocao" },
        { nome: "Vans Old Skool", preco: "R$ 299,90", imagem: "<?= $base ?>/public/images/adidas1.png", emblema: "Popular" }
    ];

    // DADOS DOS PRODUTOS
    const itensProdutos = [
        { nome: "Tenis Adidas Ultraboost", preco: "R$ 399,90",categoria: "Esporte", imagem: "<?= $base ?>/public/images/adidas1.png" },
        { nome: "Nike Air Max 270", preco: "R$ 499,90",categoria: "Esporte", imagem: "<?= $base ?>/public/images/nike.png" },
        { nome: "Camisa Tesla Edition", preco: "R$ 129,90", categoria: "Street", imagem: "<?= $base ?>/public/images/nike.png" },
        { nome: "Tenis Air Jordan", preco: "R$ 699,90", categoria: "Esporte", imagem: "<?= $base ?>/public/images/nike.png" },
        { nome: "Bota Coturno", preco: "R$ 259,90", categoria: "Camping", imagem: "<?= $base ?>/public/images/nike.png" },
        { nome: "Mochila Gamer", preco: "R$ 189,90", categoria: "Acessorio", imagem: "<?= $base ?>/public/images/adidas1.png" },
        { nome: "Fone Bluetooth", preco: "R$ 99,90", categoria: "Tecnologia", imagem: "<?= $base ?>/public/images/nike.png" },
        { nome: "Smartwatch Pro", preco: "R$ 299,90", categoria: "Tecnologia", imagem: "<?= $base ?>/public/images/adidas1.png" }
    ];

    // ELEMENTOS
    const track = document.getElementById("carrosselTrack");
    const indicadoresContainer = document.getElementById("carrosselIndicadores");
    const btnPrev = document.getElementById("btnPrev");
    const btnNext = document.getElementById("btnNext");

    let cards = [];
    let indiceCentral = 0;
    let intervaloAuto;
    let totalCards = 0;
    let larguraCard = 385;

    // CONSTRUIR CARROSSEL
    function construirCarrossel() {
        track.innerHTML = '';
        const cardsDuplicados = [...itensCarrossel, ...itensCarrossel, ...itensCarrossel];
        
        cardsDuplicados.forEach((item, idx) => {
            const card = document.createElement('div');
            card.className = 'carrossel-card';
            if (idx === itensCarrossel.length) card.classList.add('ativo');
            card.innerHTML = `
                <div class="card-emblema">${item.emblema}</div>
                <img src="${item.imagem}" alt="${item.nome}" 
                     onerror="this.src='https://placehold.co/280x200/667eea/white?text=RELPJAM'">
                <span>${item.nome}</span>
                <div class="card-preco">${item.preco}</div>
            `;
            card.onclick = () => alert(`Produto: ${item.nome}\nPreco: ${item.preco}`);
            track.appendChild(card);
        });
        
        cards = document.querySelectorAll('.carrossel-card');
        totalCards = itensCarrossel.length;
        indiceCentral = totalCards;
    }

    // CONSTRUIR INDICADORES
    function construirIndicadores() {
        indicadoresContainer.innerHTML = '';
        for (let i = 0; i < totalCards; i++) {
            const indicador = document.createElement('button');
            indicador.className = 'indicador';
            if (i === 0) indicador.classList.add('ativo');
            indicador.onclick = () => irParaSlide(i);
            indicadoresContainer.appendChild(indicador);
        }
    }

    // ATUALIZAR INDICADORES
    function atualizarIndicadores() {
        const indicadores = document.querySelectorAll('.indicador');
        const indiceOriginal = ((indiceCentral - totalCards) % totalCards + totalCards) % totalCards;
        
        indicadores.forEach((ind, idx) => {
            if (idx === indiceOriginal) {
                ind.classList.add('ativo');
            } else {
                ind.classList.remove('ativo');
            }
        });
        
        cards.forEach((card, idx) => {
            if (idx === indiceCentral) {
                card.classList.add('ativo');
            } else {
                card.classList.remove('ativo');
            }
        });
    }

    // ATUALIZAR POSICAO DO CARROSSEL
    function atualizarCarrossel() {
        const wrapper = document.querySelector('.carrossel-wrapper');
        const larguraWrapper = wrapper.offsetWidth;
        const cardAtivo = cards[indiceCentral];
        const larguraCardAtivo = cardAtivo.offsetWidth;
        
        const posicaoCentral = (larguraWrapper / 2) - (larguraCardAtivo / 2);
        const deslocamento = (indiceCentral * larguraCard) - posicaoCentral;
        
        track.style.transform = `translateX(-${deslocamento}px)`;
        atualizarIndicadores();
    }

    // IR PARA SLIDE ESPECIFICO
    function irParaSlide(indice) {
        const novoIndice = indice + totalCards;
        indiceCentral = novoIndice;
        atualizarCarrossel();
        reiniciarTimer();
    }

    // MOVER CARROSSEL
    function moverCarrossel(direcao) {
        const novoIndice = indiceCentral + direcao;
        indiceCentral = novoIndice;
        atualizarCarrossel();
        
        setTimeout(() => {
            if (indiceCentral >= totalCards * 2) {
                track.style.transition = 'none';
                indiceCentral = totalCards;
                atualizarCarrossel();
                setTimeout(() => {
                    track.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                }, 50);
            }
            if (indiceCentral < totalCards) {
                track.style.transition = 'none';
                indiceCentral = totalCards;
                atualizarCarrossel();
                setTimeout(() => {
                    track.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                }, 50);
            }
        }, 500);
        
        reiniciarTimer();
    }

    // PROXIMO SLIDE
    function proximoSlide() {
        moverCarrossel(1);
    }

    // SLIDE ANTERIOR
    function slideAnterior() {
        moverCarrossel(-1);
    }

    // REINICIAR TIMER AUTOMATICO
    function reiniciarTimer() {
        if (intervaloAuto) clearInterval(intervaloAuto);
        intervaloAuto = setInterval(() => {
            moverCarrossel(1);
        }, 6000);
    }

    // CONSTRUIR PRODUTOS
    function construirProdutos() {
        const container = document.getElementById('containerProdutos');
        container.innerHTML = '';
        
        itensProdutos.forEach(produto => {
            const card = document.createElement('div');
            card.className = 'produto-card';
            card.innerHTML = `
                <img src="${produto.imagem}" alt="${produto.nome}" 
                     onerror="this.src='https://placehold.co/220x160/cccccc/666?text=RELPJAM'">
                <p>${produto.nome}</p>
                    <span class="produto-categoria">${produto.categoria}</span>
                <div class="produto-preco">${produto.preco}</div>
            `;
            card.onclick = () => alert(`Produto: ${produto.nome}\nPreco: ${produto.preco}`);
            container.appendChild(card);
        });
    }

    // FUNCAO DE BUSCA
    const campoBusca = document.getElementById('campoBusca');
    if (campoBusca) {
        campoBusca.addEventListener('input', function(e) {
            const termo = e.target.value.toLowerCase();
            const produtos = document.querySelectorAll('.produto-card');
            produtos.forEach(produto => {
                const nome = produto.querySelector('p')?.innerText.toLowerCase() || '';
                if (nome.includes(termo)) {
                    produto.style.display = '';
                } else {
                    produto.style.display = 'none';
                }
            });
        });
    }

    // EVENTOS DOS BOTOES
    btnPrev.addEventListener('click', slideAnterior);
    btnNext.addEventListener('click', proximoSlide);

    // TECLADO
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            slideAnterior();
            reiniciarTimer();
        } else if (e.key === 'ArrowRight') {
            proximoSlide();
            reiniciarTimer();
        }
    });

    // REDIMENSIONAMENTO
    let tempoResize;
    window.addEventListener('resize', () => {
        clearTimeout(tempoResize);
        tempoResize = setTimeout(() => {
            larguraCard = 385;
            atualizarCarrossel();
        }, 200);
    });

    // INICIALIZAR
    function iniciar() {
        construirCarrossel();
        construirIndicadores();
        construirProdutos();
        setTimeout(() => {
            atualizarCarrossel();
        }, 100);
        reiniciarTimer();
        console.log("Carrossel iniciado");
    }

    // ================= SCROLL COM ARRASTAR =================

const sliderCategorias = document.querySelector('.categorias');

let segurando = false;
let inicioX;
let scrollInicial;

sliderCategorias.addEventListener('mousedown', (e) => {
    segurando = true;
    sliderCategorias.classList.add('ativo-scroll');

    inicioX = e.pageX - sliderCategorias.offsetLeft;
    scrollInicial = sliderCategorias.scrollLeft;
});

sliderCategorias.addEventListener('mouseleave', () => {
    segurando = false;
});

sliderCategorias.addEventListener('mouseup', () => {
    segurando = false;
});

sliderCategorias.addEventListener('mousemove', (e) => {
    if (!segurando) return;

    e.preventDefault();

    const x = e.pageX - sliderCategorias.offsetLeft;
    const distancia = (x - inicioX) * 2;

    sliderCategorias.scrollLeft = scrollInicial - distancia;
});

// ================= FILTRO POR CATEGORIA =================

const botoesCategoria = document.querySelectorAll('.btn-categoria');

botoesCategoria.forEach(botao => {

    botao.addEventListener('click', () => {

        // REMOVE ATIVO DE TODOS OS <li>
        botoesCategoria.forEach(btn => {
            btn.querySelector('li').classList.remove('ativo');
        });

        // ADICIONA ATIVO NO <li> DO BOTAO CLICADO
        botao.querySelector('li').classList.add('ativo');

        const categoria = botao.dataset.categoria.toLowerCase();

        const produtos = document.querySelectorAll('.produto-card');

        produtos.forEach(produto => {

            const nomeCategoria = produto
                .querySelector('.produto-categoria')
                ?.innerText
                .toLowerCase();

            // MOSTRAR TODOS
            if (categoria === 'todos') {
                produto.style.display = 'block';
                return;
            }

            // FILTRAR
            if (nomeCategoria.includes(categoria)) {
                produto.style.display = 'block';
            } else {
                produto.style.display = 'none';
            }

        });

    });

});

    iniciar();
</script>

</body>
</html>
