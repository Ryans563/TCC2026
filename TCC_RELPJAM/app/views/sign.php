
```php
<?php
session_start();
require_once 'config.php';

$mensagem = '';
$tipoMensagem = '';

// CADASTRO
if (isset($_POST['cadastrar'])) {

    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    $nomePadrao = explode('@', $email)[0];

    if ($email === '' || $senha === '') {

        $mensagem = 'Preencha todos os campos obrigatórios.';
        $tipoMensagem = 'erro';

    } else {

        try {

            $stmtCheck = $pdo->prepare("
                SELECT id
                FROM usuarios
                WHERE email = :email
            ");

            $stmtCheck->execute([
                'email' => $email
            ]);

            if ($stmtCheck->rowCount() > 0) {

                $mensagem = 'Este e-mail já está em uso.';
                $tipoMensagem = 'erro';

            } else {

                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $sql = "
                    INSERT INTO usuarios
                    (
                        nome,
                        email,
                        senha,
                        tipo,
                        status
                    )
                    VALUES
                    (
                        :nome,
                        :email,
                        :senha,
                        'cliente',
                        1
                    )
                ";

                $stmtInsert = $pdo->prepare($sql);

                $sucesso = $stmtInsert->execute([
                    'nome'  => $nomePadrao,
                    'email' => $email,
                    'senha' => $senhaHash
                ]);

                if ($sucesso) {

                    $mensagem = 'Cadastro realizado com sucesso!';
                    $tipoMensagem = 'sucesso';

                } else {

                    $mensagem = 'Erro ao realizar o cadastro.';
                    $tipoMensagem = 'erro';
                }
            }

        } catch (PDOException $e) {

            $mensagem = 'Erro no banco de dados: ' .
                $e->getMessage();

            $tipoMensagem = 'erro';
        }
    }
}

// EXCLUSÃO

if (isset($_POST['excluir'])) {

    $emailExcluir = trim(
        $_POST['email_excluir'] ?? ''
    );

    if ($emailExcluir === '') {

        $mensagem = 'Informe um e-mail para exclusão.';
        $tipoMensagem = 'erro';

    } else {

        try {

            $stmtDel = $pdo->prepare("
                DELETE FROM usuarios
                WHERE email = :email
            ");

            $stmtDel->execute([
                'email' => $emailExcluir
            ]);

            if ($stmtDel->rowCount() > 0) {

                $mensagem = 'Usuário excluído com sucesso.';
                $tipoMensagem = 'sucesso';

            } else {

                $mensagem = 'E-mail não encontrado.';
                $tipoMensagem = 'erro';
            }

        } catch (PDOException $e) {

            $mensagem = 'Erro ao excluir: ' .
                $e->getMessage();

            $tipoMensagem = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>RELPJAM - Cadastro</title>

    <link
        rel="stylesheet"
        href="css/cadastro.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="cad-body">

    <header class="cad-header">

        <h1 class="cad-logo">
            RELPJAM
        </h1>

        <p class="cad-subtitle">
            Cadastro de Usuário
        </p>

    </header>

    <main class="cad-container">

        <!-- CADASTRO -->

        <section class="cad-card">

            <h2 class="cad-title">
                Criar Conta
            </h2>

            <form
                method="POST"
                action="cadastro.php"
                class="cad-form">

                <div class="cad-group">

                    <label class="cad-label">
                        E-mail
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="cad-input"
                        placeholder="Digite seu e-mail"
                        required>

                </div>

                <div class="cad-group">

                    <label class="cad-label">
                        Senha
                    </label>

                    <input
                        type="password"
                        name="senha"
                        class="cad-input"
                        placeholder="Digite sua senha"
                        required>

                </div>

                <button
                    type="submit"
                    name="cadastrar"
                    class="cad-btn">

                    Cadastrar

                </button>

            </form>

            <div class="cad-link">

                <a href="auth.php">
                    Já tem conta? Faça login
                </a>

            </div>

        </section>

        <!-- EXCLUIR -->

        <section class="cad-card">

            <h2 class="cad-title cad-danger">
                Excluir Usuário
            </h2>

            <form
                method="POST"
                action="cadastro.php"
                class="cad-form">

                <div class="cad-group">

                    <label class="cad-label">
                        E-mail
                    </label>

                    <input
                        type="email"
                        name="email_excluir"
                        class="cad-input"
                        placeholder="Digite o e-mail"
                        required>

                </div>

                <button
                    type="submit"
                    name="excluir"
                    class="cad-btn-danger">

                    Excluir Usuário

                </button>

            </form>

        </section>

    </main>

<?php if($mensagem != ''): ?>

<script>

Swal.fire({
    icon:'<?= $tipoMensagem == "sucesso" ? "success" : "error"; ?>',
    title:'<?= $tipoMensagem == "sucesso" ? "Sucesso" : "Erro"; ?>',
    text:'<?= $mensagem; ?>',
    confirmButtonColor:'#00d9a5'
});

</script>

<?php endif; ?>

</body>
</html>
```
