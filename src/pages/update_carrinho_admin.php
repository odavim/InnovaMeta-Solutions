<?php
session_start();
require_once '../conexao.php';

header('Content-Type: application/json');

// Verifica se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado']);
    exit();
}

// Valida os dados de entrada
if (!isset($_POST['id']) || !isset($_POST['qtd'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit();
}

$id = intval($_POST['id']);
$qtd = max(1, intval($_POST['qtd'])); // Garante que a quantidade seja no mínimo 1

try {
    // Atualiza a quantidade no carrinho (admin pode editar qualquer carrinho)
    $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
    $stmt->bind_param("ii", $qtd, $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Item não encontrado']);
        exit();
    }

    // Obtém os dados atualizados para cálculo
    $sql = "SELECT 
                c.quantidade, 
                p.preco, 
                (c.quantidade * p.preco) as subtotal,
                u.id as usuario_id
            FROM carrinho c 
            JOIN produtos p ON c.produto_id = p.id
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    // Calcula o total do usuário dono do carrinho
    $sqlTotal = "SELECT SUM(c.quantidade * p.preco) as total 
                FROM carrinho c 
                JOIN produtos p ON c.produto_id = p.id 
                WHERE c.usuario_id = ?";
    
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->bind_param("i", $item['usuario_id']);
    $stmtTotal->execute();
    $total = $stmtTotal->get_result()->fetch_assoc()['total'];

    // Formata os valores para exibição
    $response = [
        'success' => true,
        'subtotal' => number_format($item['subtotal'], 2, ',', '.'),
        'total' => number_format($total, 2, ',', '.')
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro no servidor: ' . $e->getMessage()]);
}