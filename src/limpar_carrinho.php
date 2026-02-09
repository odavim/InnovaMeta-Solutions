<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();

echo json_encode(['success' => true]);
