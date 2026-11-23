<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true); // evita fixação de sessão
                // Salva todas as informações necessárias
                $_SESSION['usuario_id'] = $usuario['id']; // ID do usuário
                $_SESSION['usuario_nome'] = $usuario['nome']; // Nome
                $_SESSION['usuario'] = $usuario['email']; // Email
                $_SESSION['usuario_nivel'] = $usuario['nivel']; // Nível de acesso

                header("Location: ../index.php");
                exit;
            }
        }
        $erro = "Email ou senha inválidos!";
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - InnovaMeta</title>
    <link rel="stylesheet" href="./css/login.css" />
</head>
<body>

    <div class="login-container">
        <img src="images/ims4.0-removebg-preview.png" alt="Logo" class="logo" />


        <div class="separator"><span>ou</span></div>

        <?php if (isset($erro)): ?>
            <p style="color:red; text-align:center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="login-form">
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="senha" placeholder="Senha" required />
            <div class="form-options">
                <label><input type="checkbox" name="lembrar" /> Lembrar de mim</label>
                <a href="./pages/recuperar_senha.php">Esqueceu a senha?</a>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="signup">
            Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>

    <a href="../index.php" class="btn-voltar">
      Voltar para a Home
    </a>
</body>
</html>