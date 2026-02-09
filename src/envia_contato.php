<?php
session_start();

// Inclui o PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Configurações
$destinatario = "innovametasolutions@gmail.com"; // SEU EMAIL QUE RECEBERÁ AS MENSAGENS
$assunto_site = "Contato pelo Site - InnovaMeta Solutions";

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contato.php?status=error&msg=Método não permitido");
    exit;
}

// Verifica token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    header("Location: contato.php?status=error&msg=Token de segurança inválido");
    exit;
}

// Valida e sanitiza os dados
$nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$assunto = trim(htmlspecialchars($_POST['assunto'] ?? ''));
$mensagem = trim(htmlspecialchars($_POST['mensagem'] ?? ''));

// Validação dos campos
$erros = [];
if (empty($nome)) $erros[] = "Nome é obrigatório";
if (!$email) $erros[] = "Email inválido";
if (empty($assunto)) $erros[] = "Assunto é obrigatório";
if (empty($mensagem)) $erros[] = "Mensagem é obrigatória";

if (!empty($erros)) {
    $msg_erro = urlencode(implode(", ", $erros));
    header("Location: contato.php?status=error&msg=$msg_erro");
    exit;
}

try {
    $mail = new PHPMailer(true);
    
    // Configurações do servidor - GMAIL
    $mail->SMTPDebug = 0; // Mude para 2 para ver detalhes do erro
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'innovametasolutions@gmail.com'; // SEU EMAIL GMAIL
    $mail->Password = 'gvsp cvmm pdvm bfwh'; // SUA SENHA DE APP
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]
];
    
    // Configurações adicionais importantes
    $mail->Timeout = 30; // Timeout de 30 segundos
    $mail->CharSet = 'UTF-8';
    
    // Remetente e destinatário - CORRIGIDO
    $mail->setFrom('innovametasolutions@gmail.com', 'Site InnovaMeta'); // DEVE SER O MESMO EMAIL DO GMAIL
    $mail->addAddress($destinatario); // PARA ONDE VOCÊ QUER RECEBER
    $mail->addReplyTo($email, $nome); // PARA RESPONDER DIRETO PARA QUEM ENVIOU
    
    // Conteúdo
    $mail->isHTML(false);
    $mail->Subject = $assunto_site . ' - ' . $assunto;
    $mail->Body = "
Nome: $nome
Email: $email
Assunto: $assunto

Mensagem:
$mensagem

---
Enviado através do formulário de contato do site InnovaMeta Solutions
Data/Hora: " . date('d/m/Y H:i:s');
    
    $mail->send();
    
    // Limpa o token CSRF após uso bem-sucedido
    unset($_SESSION['csrf_token']);
    
    header("Location: contato.php?status=success");
    exit;
    
} catch (Exception $e) {
    // Log detalhado do erro
    error_log("Erro PHPMailer: " . $e->getMessage());
    error_log("Erro detalhado: " . $mail->ErrorInfo);
    
    $msg_erro = "Erro no envio: " . $e->getMessage();
    header("Location: contato.php?status=error&msg=" . urlencode($msg_erro));
    exit;
}
?>

ENVIA_CONTATO.PHP