<?php
session_start();
require_once '../conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'redirect' => '../login.php']);
    exit();
}

if (!isset($_POST['produto_id'])) {
    echo json_encode(['success' => false, 'error' => 'Produto não informado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$produto_id = intval($_POST['produto_id']);

// Verifica se já existe no carrinho
$sql = "SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND produto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $produto_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $item = $res->fetch_assoc();
    $novaQtd = $item['quantidade'] + 1;
    $update = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
    $update->bind_param("ii", $novaQtd, $item['id']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $usuario_id, $produto_id);
    $insert->execute();
}

echo json_encode(['success' => true]);
