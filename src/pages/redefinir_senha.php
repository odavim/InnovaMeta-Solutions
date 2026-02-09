<?php
require_once '../conexao.php';
$mensagem = '';

// Pega o token da URL
$token = $_GET['token'] ?? '';
$form_valido = false;

// DEBUG: Verificar se o token está chegando
error_log("Token recebido na URL: " . $token);

// 1. Verifica se o token existe e não expirou
if ($token) {
    $stmt = $conn->prepare("SELECT id, reset_token_expira FROM usuarios WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        $mensagem = "Token inválido ou já utilizado.";
        error_log("Token não encontrado no banco: " . $token);
    } else {
        $usuario = $resultado->fetch_assoc();
        $expiracao = strtotime($usuario['reset_token_expira']);
        $agora = time();
        
        error_log("Token expira em: " . $usuario['reset_token_expira']);
        error_log("Tempo atual: " . date('Y-m-d H:i:s', $agora));
        
        if ($expiracao < $agora) {
            $mensagem = "Token expirado! Solicite uma nova recuperação de senha.";
            error_log("Token expirado");
        } else {
            // Token é válido e não expirou, pode exibir o formulário
            $form_valido = true;
            error_log("Token válido - ID usuário: " . $usuario['id']);
        }
    }
} else {
    $mensagem = "Token não informado.";
    error_log("Token vazio na URL");
}

// 2. Quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'], $_POST['confirmar'], $_POST['token'])) {
    $senha = $_POST['senha'];
    $confirmar = $_POST['confirmar'];
    $token_form = $_POST['token'];

    error_log("Formulário enviado - Token: " . $token_form);

    // Validações
    if ($senha !== $confirmar) {
        $mensagem = "As senhas não coincidem.";
        $form_valido = true;
    } elseif (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
        $mensagem = "A senha deve ter no mínimo 8 caracteres, incluindo letras e números.";
        $form_valido = true;
    } else {
        // Primeiro verifica se o token ainda é válido
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
        $check_stmt->bind_param("s", $token_form);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $mensagem = "Token inválido ou expirado. Solicite um novo link.";
            $form_valido = false;
        } else {
            // Atualiza senha e remove token
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, reset_token_expira = NULL WHERE reset_token = ?");
            $update->bind_param("ss", $hash, $token_form);
            
            if ($update->execute()) {
                $mensagem = "Senha alterada com sucesso! Agora você já pode fazer login.";
                $form_valido = false;
                error_log("Senha redefinida com sucesso para token: " . $token_form);
            } else {
                $mensagem = "Erro ao redefinir senha. Tente novamente.";
                $form_valido = true;
                error_log("Erro ao atualizar senha: " . $conn->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha</title>
    <link rel="stylesheet" href="../css/cadastro.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            flex-direction: column;
            padding: 20px;
        }
        #container { 
            max-width: 450px; 
        }
        h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }
        .mensagem-status {
            font-size: 0.95rem;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .sucesso { 
            color: #d1e7dd; 
            background-color: #1a4d31; 
        }
        .erro { 
            color: #f8d7da; 
            background-color: #721c24; 
        }
        .login-link {
            text-align: center;
            margin-top: 2rem;
        }
        .login-link a {
            color: #008060;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .input-group {
            margin-bottom: 20px; 
        }
        .debug-info {
            background: #2d3748;
            color: #e2e8f0;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 0.8rem;
            font-family: monospace;
        }
    </style>
</head>
<body>
<div id="container"> 
    <h2>Redefinir Senha</h2>

    <?php if ($mensagem): ?>
        <p class="mensagem-status <?= (strpos($mensagem, 'sucesso') !== false) ? 'sucesso' : 'erro' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </p>
    <?php endif; ?>

    <!-- DEBUG: Mostrar token para teste (remova em produção) -->
    <?php if ($token && $mensagem): ?>
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Token: <?= htmlspecialchars($token) ?><br>
            URL Completa: <?= $_SERVER['REQUEST_URI'] ?>
        </div>
    <?php endif; ?>

    <?php if ($form_valido): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="input-group">
                <label for="senha">Nova senha</label>
                <input type="password" name="senha" id="senha" required 
                       placeholder="Mín. 8 caracteres, letras e números"
                       pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$"
                       title="Mínimo 8 caracteres com letras e números">
            </div>
            
            <div class="input-group">
                <label for="confirmar">Confirmar nova senha</label>
                <input type="password" name="confirmar" id="confirmar" required 
                       placeholder="Repita a senha">
            </div>
            
            <button type="submit" class="btn-submit">Redefinir Senha</button>
        </form>
    <?php endif; ?>
    
    <div class="login-link">
        <?php if (strpos($mensagem, 'sucesso') !== false): ?>
            <a href="../login.php">Fazer Login</a>
        <?php else: ?>
            <a href="../login.php">Voltar para Login</a>
            <?php if (strpos($mensagem, 'inválido') !== false || strpos($mensagem, 'expirado') !== false): ?>
                <br><a href="../recuperar_senha.php">Solicitar Novo Link</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Validação em tempo real das senhas
document.addEventListener('DOMContentLoaded', function() {
    const senha = document.getElementById('senha');
    const confirmar = document.getElementById('confirmar');
    
    function validarSenhas() {
        if (senha.value && confirmar.value) {
            if (senha.value !== confirmar.value) {
                confirmar.setCustomValidity('As senhas não coincidem');
            } else {
                confirmar.setCustomValidity('');
            }
        }
    }
    
    senha.addEventListener('input', validarSenhas);
    confirmar.addEventListener('input', validarSenhas);
});
</script>
</body>
</html>