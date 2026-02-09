<?php
session_start();
require_once 'conexao.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if ($email && $senha) {
  $query = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $resultado = $stmt->get_result();

  if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
    if (password_verify($senha, $usuario['senha'])) {
      $_SESSION['usuario'] = $usuario['nome'];
      header("Location: dashboard.php"); // página protegida
      exit;
    }
  }
}

$_SESSION['erro'] = "Email ou senha inválidos!";
header("Location: login.php");
exit;
