<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - InnovaMeta</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #008060;
            --primary-dark: #00664c;
        }
        
        .config-container {
            background-color: #1a1a1a;
            border-radius: 12px;
            padding: 2rem;
            margin: 1rem 0;
        }
        
        .sidebar a:hover, .sidebar a.active {
        color: var(--primary-color);
    }
        .config-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #333;
        }
        
        .config-section h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #444;
            background: #2a2a2a;
            color: white;
        }
        
        .btn-save {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-save:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>
<body>

<div class="hamburger" onclick="toggleSidebar()">&#9776;</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2><img src="../images/ims-removebg-preview.png" alt="Logo" class="logo-side"> InnovaMeta</h2>
        <span class="close-btn" onclick="toggleSidebar()">×</span>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="dashboard_precos.php"><i class="fas fa-tags"></i> Gerenciar Preços</a></li>
        <li><a href="dashboard_carrinhos.php"><i class="fas fa-shopping-cart"></i> Carrinhos</a></li>
        <li><a href="dashboard_configuracoes.php" class="active"><i class="fas fa-cog"></i> Configurações</a></li>
        <li><a href="../index.php"><i class="fas fa-home"></i> Voltar ao Início</a></li>
    </ul>
</div>

<div class="main-content" id="main-content">
    <div class="header">
        <h1><i class="fas fa-cog"></i> Configurações do Sistema</h1>
        <div class="buttons">
            <a href="dashboard.php"><button class="btn-purple"><i class="fas fa-arrow-left"></i> Voltar</button></a>
        </div>
    </div>

    <div class="config-container">
        <div class="config-section">
            <h3><i class="fas fa-shopping-cart"></i> Configurações de E-commerce</h3>
            <form method="POST" action="salvar_configuracoes.php">
                <div class="form-group">
                    <label>Frete Grátis a partir de:</label>
                    <input type="number" name="frete_gratis" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Taxa de Frete Padrão:</label>
                    <input type="number" name="taxa_frete" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Prazo de Entrega (dias):</label>
                    <input type="number" name="prazo_entrega" placeholder="7">
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Salvar Configurações</button>
            </form>
        </div>

        <div class="config-section">
            <h3><i class="fas fa-bell"></i> Notificações</h3>
            <form method="POST" action="salvar_configuracoes.php">
                <div class="form-group">
                    <label>Email para Notificações:</label>
                    <input type="email" name="email_notificacoes" placeholder="admin@innovameta.com">
                </div>
                <div class="form-group">
                    <label>Notificar novos pedidos:</label>
                    <select name="notificar_pedidos">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Salvar Notificações</button>
            </form>
        </div>

        <div class="config-section">
            <h3><i class="fas fa-paint-brush"></i> Aparência</h3>
            <form method="POST" action="salvar_configuracoes.php">
                <div class="form-group">
                    <label>Cor Principal do Site:</label>
                    <input type="color" name="cor_principal" value="#008060">
                </div>
                <div class="form-group">
                    <label>Logo do Site (URL):</label>
                    <input type="text" name="logo_url" placeholder="../images/logo.png">
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Salvar Aparência</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
    document.querySelector(".hamburger").classList.toggle("active");
}
</script>

</body>
</html>