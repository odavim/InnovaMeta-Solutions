<?php
session_start();
require_once '../conexao.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Você precisa estar logado para remover itens',
        'redirect'=> 'login.php'
    ]);
    exit();
}

// Valida entrada
if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'ID do item não informado'
    ]);
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false) {
    echo json_encode([
        'success' => false,
        'error'   => 'ID inválido'
    ]);
    exit();
}

try {
    $conn->begin_transaction();

    // Remove item do carrinho
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'error'   => 'Item não encontrado ou não pertence ao usuário'
        ]);
        exit();
    }

    // Recalcula total
    $sqlTotal = "SELECT SUM(c.quantidade * p.preco) as total, COUNT(c.id) as itens_total
                 FROM carrinho c
                 JOIN produtos p ON c.produto_id = p.id
                 WHERE c.usuario_id = ?";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->bind_param("i", $_SESSION['usuario_id']);
    $stmtTotal->execute();
    $totalInfo = $stmtTotal->get_result()->fetch_assoc();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'total'   => number_format($totalInfo['total'] ?? 0, 2, ',', '.'),
        'carrinho'=> [
            'itens_total' => $totalInfo['itens_total'] ?? 0
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao remover item: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error'   => 'Erro ao remover item do carrinho',
        'debug'   => $e->getMessage()
    ]);
}
