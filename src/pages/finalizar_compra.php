<?php
session_start();
require_once '../conexao.php';
require_once '../vendor/autoload.php'; // Biblioteca para gerar QR Code

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Pega o carrinho
$sql = "SELECT c.produto_id, c.quantidade, p.preco 
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id
        WHERE c.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Carrinho vazio!");
}

// Calcula total
$total = 0;
$itens = [];
while ($row = $result->fetch_assoc()) {
    $total += $row['preco'] * $row['quantidade'];
    $itens[] = $row;
}

// Cria pedido
$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
$stmt->bind_param("id", $usuario_id, $total);
$stmt->execute();
$pedido_id = $stmt->insert_id;

// Insere itens
foreach ($itens as $item) {
    $stmt = $conn->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $pedido_id, $item['produto_id'], $item['quantidade'], $item['preco']);
    $stmt->execute();
}

// Limpa carrinho
$stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

// Geração PIX (Exemplo usando QR Code estático/dinâmico)
$chavePix = "SEU_PIX@EMAIL.COM";
$descricao = "Pagamento Pedido #$pedido_id";
$valor = number_format($total, 2, '.', '');
$pixCode = "00020126360014BR.GOV.BCB.PIX0114$chavePix520400005303986540$valor5802BR5913InnovaMeta6009SAO PAULO62070503***6304"; // PIX simplificado

// QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$qrCode = new QrCode($pixCode);
$qrCode->setSize(300);
$writer = new PngWriter();
$qrImage = base64_encode($writer->write($qrCode)->getString());
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pagamento PIX</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="section-container">
        <h1>Pagamento do Pedido #<?= $pedido_id ?></h1>
        <h2>Total: R$ <?= number_format($total, 2, ',', '.') ?></h2>
        <p>Use o QR Code abaixo para pagar via PIX:</p>
        <img src="data:image/png;base64,<?= $qrImage ?>" alt="QR Code PIX">
        <p>Código copia e cola:</p>
        <textarea style="width:100%;height:100px;" readonly><?= $pixCode ?></textarea>
    </div>
</body>
</html>
