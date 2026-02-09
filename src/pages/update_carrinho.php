<?php
session_start();
require_once '../conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Você precisa estar logado para atualizar o carrinho',
        'redirect'=> 'login.php'
    ]);
    exit();
}

$id  = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$qtd = filter_input(INPUT_POST, 'qtd', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$id || !$qtd) {
    echo json_encode([
        'success' => false,
        'error'   => 'Dados inválidos'
    ]);
    exit();
}

try {
    $conn->begin_transaction();

    // Atualiza quantidade
    $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("iii", $qtd, $id, $_SESSION['usuario_id']);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Item não encontrado']);
        exit();
    }

    // Subtotal do item
    $stmtItem = $conn->prepare("
        SELECT p.preco, (c.quantidade * p.preco) AS subtotal, p.nome 
        FROM carrinho c
        JOIN produtos p ON c.produto_id = p.id
        WHERE c.id = ? AND c.usuario_id = ?
    ");
    $stmtItem->bind_param("ii", $id, $_SESSION['usuario_id']);
    $stmtItem->execute();
    $item = $stmtItem->get_result()->fetch_assoc();

    // Total do carrinho
    $stmtTotal = $conn->prepare("
        SELECT SUM(c.quantidade * p.preco) AS total, COUNT(c.id) AS itens_total
        FROM carrinho c
        JOIN produtos p ON c.produto_id = p.id
        WHERE c.usuario_id = ?
    ");
    $stmtTotal->bind_param("i", $_SESSION['usuario_id']);
    $stmtTotal->execute();
    $totalInfo = $stmtTotal->get_result()->fetch_assoc();

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'subtotal' => number_format($item['subtotal'], 2, ',', '.'),
        'total'    => number_format($totalInfo['total'], 2, ',', '.'),
        'item' => [
            'nome'          => $item['nome'],
            'quantidade'    => $qtd,
            'preco_unitario'=> number_format($item['preco'], 2, ',', '.')
        ],
        'carrinho' => [
            'itens_total'   => $totalInfo['itens_total']
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro update_carrinho: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro no servidor']);
}
