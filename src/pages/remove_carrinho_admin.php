<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado']);
    exit();
}

// Valida o ID
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID não informado']);
    exit();
}

$id = intval($_POST['id']);

try {
    // Primeiro obtém o usuário dono do carrinho para calcular o total depois
    $sqlUser = "SELECT usuario_id FROM carrinho WHERE id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $result = $stmtUser->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Item não encontrado']);
        exit();
    }
    
    $usuario_id = $result->fetch_assoc()['usuario_id'];

    // Remove o item do carrinho
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Calcula o novo total do usuário
    $sqlTotal = "SELECT SUM(c.quantidade * p.preco) as total 
                FROM carrinho c 
                JOIN produtos p ON c.produto_id = p.id 
                WHERE c.usuario_id = ?";
    
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->bind_param("i", $usuario_id);
    $stmtTotal->execute();
    $total_result = $stmtTotal->get_result();
    $total = $total_result->fetch_assoc()['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'total' => number_format($total, 2, ',', '.')
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro no servidor: ' . $e->getMessage()]);
}