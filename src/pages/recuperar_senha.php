<?php
require_once '../conexao.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclua os arquivos do PHPMailer - CAMINHOS AJUSTADOS
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

$mensagem = '';
$email_digitado = ''; // Para manter o email no campo após o erro
$classe_mensagem = ''; // Para controlar a classe CSS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $email_digitado = htmlspecialchars($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Formato de e-mail inválido!';
        $classe_mensagem = 'erro';
    } else {
        // Busca o usuário
        $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Mensagem padrão para segurança (evita informar se o email existe)
        $mensagem = 'Se este e-mail estiver cadastrado, um link de redefinição de senha foi enviado.';
        $classe_mensagem = 'sucesso';

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Atualiza token no banco
            $update = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?");
            $update->bind_param("ssi", $token, $expira, $usuario['id']);
            
            if ($update->execute()) {
                // Link de redefinição
                // ATENÇÃO: SUBSTITUA 'http://localhost/Innovameta' PELO SEU DOMÍNIO DE PRODUÇÃO
                $dominio = "http://localhost/Innovameta";
                $link = "$dominio/redefinir_senha.php?token=$token";

                // Configura PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Configurações do servidor SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'davidnea1234567@gmail.com'; // ⚠️ SEU E-MAIL REMETENTE
                    $mail->Password = 'maxf urqn hpbo bowd'; // ⚠️ SUA SENHA/APP PASSWORD
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    
                    // Configurações adicionais para melhor compatibilidade
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Timeout = 30;

                    // Remetente e nome exibido
                    $mail->setFrom('davidnea1234567@gmail.com', 'Suporte InnovaMeta');
                    $mail->addReplyTo('davidnea1234567@gmail.com', 'Suporte InnovaMeta');

                    // Destinatário
                    $mail->addAddress($email, $usuario['nome'] ?? '');

                    // Conteúdo
                    $mail->isHTML(true);
                    $mail->Subject = 'Redefinição de senha - InnovaMeta';
                    
                    // 🎨 Estilo do botão atualizado para o verde #008060
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #008060; text-align: center;'>Recuperação de senha</h2>
                            <p>Olá " . ($usuario['nome'] ?? '') . ",</p>
                            <p>Você solicitou a redefinição da sua senha. Clique no botão abaixo para continuar:</p>
                            <p style='text-align: center;'>
                                <a href='$link' style='padding:12px 25px; background:#008060; color:white; text-decoration:none; border-radius: 8px; display: inline-block; font-weight: bold;'>
                                    Redefinir senha
                                </a>
                            </p>
                            <p>Ou copie e cole este link no seu navegador:</p>
                            <p style='word-break: break-all; background: #f4f4f4; padding: 10px; border-radius: 5px;'>$link</p>
                            <p>Se você não solicitou esta redefinição, ignore este e-mail.</p>
                            <p style='font-size: 0.9em; color: #777;'>Este link expira em <b>1 hora</b>.</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='font-size: 0.8em; color: #999;'>Equipe InnovaMeta Solutions</p>
                        </div>
                    ";
                    
                    // Versão texto simples para clientes de email que não suportam HTML
                    $mail->AltBody = "Recuperação de senha - InnovaMeta\n\n" .
                        "Olá " . ($usuario['nome'] ?? '') . ",\n\n" .
                        "Você solicitou a redefinição da sua senha. Use o link abaixo para redefinir sua senha:\n\n" .
                        "$link\n\n" .
                        "Este link expira em 1 hora.\n\n" .
                        "Se você não solicitou esta redefinição, ignore este e-mail.\n\n" .
                        "Atenciosamente,\nEquipe InnovaMeta Solutions";

                    if ($mail->send()) {
                        error_log("Email de recuperação enviado com sucesso para: $email");
                    } else {
                        throw new Exception("Falha no envio do email");
                    }
                    
                } catch (Exception $e) {
                    error_log("Erro ao enviar e-mail para $email: " . $mail->ErrorInfo);
                    $mensagem = 'Erro ao enviar e-mail de recuperação. Tente novamente mais tarde.';
                    $classe_mensagem = 'erro';
                    
                    // Remove o token do banco se o email falhou
                    $cleanup = $conn->prepare("UPDATE usuarios SET reset_token = NULL, reset_token_expira = NULL WHERE id = ?");
                    $cleanup->bind_param("i", $usuario['id']);
                    $cleanup->execute();
                }
            } else {
                $mensagem = 'Erro interno ao processar sua solicitação.';
                $classe_mensagem = 'erro';
            }
        }
        // Se o email não existir, mantém a mensagem padrão de sucesso por segurança
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha</title>
    <link rel="stylesheet" href="../css/cadastro.css"> 
    <style>
        /* Ajustes de layout para este formulário específico */
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
            padding: 3rem; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }
        /* Estilos de mensagem de status */
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
            border: 1px solid #0f5132;
        }
        .erro { 
            color: #f8d7da;
            background-color: #721c24;
            border: 1px solid #491217;
        }
        /* Ajuste do link Voltar */
        .login-link {
            text-align: center;
            margin-top: 2rem;
        }
        .login-link a {
            color: #008060;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div id="container">
    <h2>Recuperar Senha</h2>

    <?php if($mensagem): ?>
        <p class="mensagem-status <?= $classe_mensagem ?>">
            <?= htmlspecialchars($mensagem) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="recuperar-form">
        <div class="input-group">
            <label for="email">Digite seu e-mail</label>
            <input type="email" name="email" id="email" required value="<?= $email_digitado ?>">
        </div>
        <button type="submit" class="btn-submit">Enviar link</button>
    </form>
    
    <div class="login-link"><a href="../login.php">Voltar para o Login</a></div>
</div>
</body>
</html>