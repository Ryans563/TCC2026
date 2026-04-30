<?php 
$base = "/TCC_RELPJAM";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RELPJAM</title>

<link rel="stylesheet" href="<?= $base ?>/public/assets/css/style.css">
</head>

<body>

<header>
    <div class="logo">RELPJAM</div>

    <div class="search">
        <input type="text" placeholder="Buscar Produtos">
    </div>

    <div class="icons">
        <span></span>
        <span></span>
    </div>
</header>

<!-- ================= CARROSSEL CORRIGIDO ================= -->
<section class="carousel">

    <button class="nav prev" onclick="moveCarousel(-1)">
        <svg viewBox="0 0 24 24">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
    </button>

    <div class="viewport">
        <div class="track" id="carouselTrack">

            <div class="card">
                <img src="<?= $base ?>/public/images/adidas1.png">
                <span>Adidas</span>
            </div>

            <div class="card">
                <img src="<?= $base ?>/public/images/nike.png">
                <span>Nike</span>
            </div>

            <div class="card">
                <img src="<?= $base ?>/public/images/nike.png">
                <span>Tesla</span>
            </div>

            <div class="card">
                <img src="<?= $base ?>/public/images/nike.png">
                <span>Air270</span>
            </div>

            <div class="card">
                <img src="<?= $base ?>/public/images/nike.png">
                <span>Botas</span>
            </div>

        </div>
    </div>

    <button class="nav next" onclick="moveCarousel(1)">
        <svg viewBox="0 0 24 24">
            <path d="M9 6l6 6-6 6"></path>
        </svg>
    </button>

</section>

<!-- ================= CATEGORIAS (NÃO MEXIDO) ================= -->
<nav class="categories">
  <ul>
    <a href="http://localhost/TCC_RELPJAM/app/views/class.php"><li class="active">CLASS</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class2.php"><li class="active">CLASS2</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class3.php"><li class="active">CLASS3></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class4.php"><li class="active">CLASS4</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class5.php"><li class="active">CLASS5</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class6.php"><li class="active">CLASS6</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class7.php"><li class="active">CLASS7</li></a>
    <a href="http://localhost/TCC_RELPJAM/app/views/class8.php"><li class="active">CLASS8</li></a>
  </ul>
</nav>

<!-- ================= PRODUTOS (NÃO MEXIDO) ================= -->
<main class="products">
  <div class="product-card">
    <img src="<?= $base ?>/public/images/adidas1.png">
    <p>Nome do produto</p>
  </div>

  <div class="product-card">
    <img src="<?= $base ?>/public/images/nike.png">
    <p>Nome do produto</p>
  </div>
</main>

<script>
let index = 0;

function moveCarousel(dir) {
    const track = document.getElementById("carouselTrack");
    const cards = document.querySelectorAll(".card");

    const gap = 20;
    const cardWidth = cards[0].offsetWidth + gap;

    const viewport = document.querySelector(".viewport");
    const visible = Math.floor(viewport.offsetWidth / cardWidth);

    const max = Math.max(0, cards.length - visible);

    index = Math.max(0, Math.min(index + dir, max));

    track.style.transform = `translateX(-${index * cardWidth}px)`;
}
</script>

</body>
</html>