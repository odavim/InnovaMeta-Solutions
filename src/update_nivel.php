<?php
require_once 'auth.php'; // somente admin logado
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nivel = $_POST['nivel'] ?? '';

if (!$id || !in_array($nivel, ['admin', 'cliente', 'premium'], true)) {
    http_response_code(400);
    exit('Dados inválidos.');
}

// Impede que o admin remova o próprio acesso sem querer
if ($id === (int) ($_SESSION['usuario_id'] ?? 0) && $nivel !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$stmt = $conn->prepare("UPDATE usuarios SET nivel = ? WHERE id = ?");
$stmt->bind_param("si", $nivel, $id);
$stmt->execute();

header("Location: dashboard.php");
exit;
?>
