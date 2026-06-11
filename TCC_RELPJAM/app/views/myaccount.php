<?php
session_start();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Exemplo simples
    if ($usuario === "admin" && $senha === "123456") {
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RelPJamLogo - Login</title>

<link rel="stylesheet" href="stylecad.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

<div class="background">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</div>

<div class="login-container">

    <div class="logo-area">
        <div class="logo-circle">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <h1>RelPJamLogo</h1>
        <p>Sistema Inteligente de Gestão</p>
    </div>

    <?php if($erro): ?>
        <div class="erro">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="login-form">

        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="usuario" placeholder="Usuário" required>
        </div>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="senha" placeholder="Senha" required>
            <i class="fa-solid fa-eye togglePassword"></i>
        </div>

        <button type="submit">
            Entrar
        </button>

    </form>

</div>

<script >
    // Mostrar/Ocultar senha

const toggle = document.querySelector('.togglePassword');
const senha = document.querySelector('input[type="password"]');

toggle.addEventListener('click', () => {

    if(senha.type === "password"){
        senha.type = "text";
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    }else{
        senha.type = "password";
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }

});

// Efeito 3D no card

const card = document.querySelector('.login-container');

document.addEventListener('mousemove',(e)=>{

    let x = (window.innerWidth/2 - e.pageX)/30;
    let y = (window.innerHeight/2 - e.pageY)/30;

    card.style.transform =
        `rotateY(${x}deg) rotateX(${-y}deg)`;

});

document.addEventListener('mouseleave',()=>{
    card.style.transform =
    'rotateY(0deg) rotateX(0deg)';
});
</script>

</body>
</html>