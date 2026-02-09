<?php
session_start();
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
