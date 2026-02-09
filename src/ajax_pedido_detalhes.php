<?php
header('Content-Type: application/json');
session_start();
require_once '../conexao.php';

$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    $response['error'] = 'Acesso negado.';
    echo json_encode($response);
    exit();
}

$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);

if (!$pedido_id) {
    $response['error'] = 'ID do pedido inválido.';
    echo json_encode($response);
    exit();
}

try {
    // 1. Buscar informações do pedido (Cliente, Total, Status)
    $stmt = $conn->prepare("SELECT p.total, p.status, u.nome as cliente
                            FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id
                            WHERE p.id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $pedido_info = $stmt->get_result()->fetch_assoc();

    if (!$pedido_info) {
        throw new Exception("Pedido não encontrado.");
    }
    
    // 2. Buscar itens do pedido
    $stmt_itens = $conn->prepare("SELECT pi.quantidade, pi.preco, prod.nome as produto
                                  FROM pedido_itens pi
                                  JOIN produtos prod ON pi.produto_id = prod.id
                                  WHERE pi.pedido_id = ?");
    $stmt_itens->bind_param("i", $pedido_id);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    
    $itens = [];
    while ($row = $result_itens->fetch_assoc()) {
        $itens[] = [
            'produto' => htmlspecialchars($row['produto']),
            'quantidade' => $row['quantidade'],
            'preco_unitario' => number_format($row['preco'], 2, ',', '.'),
            'subtotal' => number_format($row['preco'] * $row['quantidade'], 2, ',', '.')
        ];
    }

    $response['success'] = true;
    $response['cliente'] = htmlspecialchars($pedido_info['cliente']);
    $response['total'] = number_format($pedido_info['total'], 2, ',', '.');
    $response['status'] = htmlspecialchars(ucfirst($pedido_info['status']));
    $response['itens'] = $itens;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>