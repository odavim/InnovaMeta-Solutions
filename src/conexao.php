<?php
require_once __DIR__ . '/config.php';

$conn = @new mysqli(
    config('db_host', 'localhost'),
    config('db_user', 'root'),
    config('db_pass', ''),
    config('db_name', 'innova')
);

if ($conn->connect_error) {
    error_log('Falha na conexão com o banco: ' . $conn->connect_error);
    http_response_code(500);
    die('Erro ao conectar ao banco de dados.');
}

$conn->set_charset('utf8mb4');
?>
