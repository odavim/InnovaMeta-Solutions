<?php
require_once 'conexao.php';

$id = $_POST['id'];
$nivel = $_POST['nivel'];

$stmt = $conn->prepare("UPDATE usuarios SET nivel = ? WHERE id = ?");
$stmt->bind_param("si", $nivel, $id);
$stmt->execute();

// Se o nível for admin, insere na tabela de funcionários (se ainda não estiver)
if ($nivel === 'admin') {
    $verifica = $conn->prepare("SELECT id FROM funcionarios WHERE usuario_id = ?");
    $verifica->bind_param("i", $id);
    $verifica->execute();
    $verifica->store_result();

    if ($verifica->num_rows === 0) {
        $cargo = 'admin';
        $ativo = 1;
        $insere = $conn->prepare("INSERT INTO funcionarios (usuario_id, cargo, ativo) VALUES (?, ?, ?)");
        $insere->bind_param("isi", $id, $cargo, $ativo);
        $insere->execute();
    }
}

header("Location: dashboard.php");
exit;
?>
