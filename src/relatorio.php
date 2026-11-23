<?php
require_once 'auth.php'; // somente admin
require_once 'conexao.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_usuarios.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nome', 'Email', 'Telefone', 'CPF', 'Data Cadastro', 'Nível']);

$sql = "SELECT nome, email, telefone, cpf, created_at, nivel FROM usuarios ORDER BY created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['nome'],
        $row['email'],
        $row['telefone'],
        $row['cpf'],
        date('d/m/Y H:i', strtotime($row['created_at'])),
        $row['nivel']
    ]);
}
fclose($output);
exit;
?>
