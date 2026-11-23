<?php
session_start();

// Gera token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;

// Variáveis para mensagens de status
$status_message = '';
$status_class = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $status_message = "✅ Mensagem enviada com sucesso! Em breve, entraremos em contato.";
        $status_class = "response-success";
    } elseif ($_GET['status'] == 'error') {
        $status_message = "❌ Erro ao enviar: " . ($_GET['msg'] ?? "Verifique os dados e tente novamente.");
        $status_class = "response-error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contato</title>
  <link rel="stylesheet" href="./css/contato.css" />
  <link rel="stylesheet" href="./css/fonts.css" />
  <link rel="stylesheet" href="./css/navbar.css"> 
  <link rel="stylesheet" href="../src/css/style.css" /> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
  
 <nav>
        <div id="logo">
            <img src="../src/images/ims3.0-removebg-preview.png" alt="Logo" id="img-logo" />
        </div>
        
        <div class="menu-toggle" id="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <ul id="menu">
            <li><a href="../index.php">Início</a></li>
            <li><a href="Equipe.php">Equipe</a></li>
            <li><a href="Sobre.php">Sobre</a></li>
            <li><a href="contato.php">Contato</a></li>

            <?php if ($logado): ?>
                <li class="dropdown">
                    <a href="#" class="btn dropdown-toggle" id="dropdownToggle">
                        Olá, <?= htmlspecialchars($nomeUsuario) ?> <i class="fas fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <?php if (isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === 'admin'): ?>
                            <li><a href="dashboard.php"><i class="fas fa-cogs"></i> Painel Administrativo</a></li>
                        <?php endif; ?>
                        <li><a href="meus_orcamentos.php"><i class="fas fa-file-invoice"></i> Meus Orçamentos</a></li>
                        <li><a href="./pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="login.php" id="login" class="btn">Já tem uma conta?</a></li>
            <?php endif; ?>
        </ul>
    </nav>

<header class="header">
    <div class="section-container header__content">
      <h1>Fale conosco</h1>
      <p>Entre em contato com a InnovaMeta Solutions</p>
    </div>
</header>

<!-- MENSAGEM DE STATUS AQUI - ACIMA DO CONTAINER PRINCIPAL -->
<?php if ($status_message): ?>
<div class="status-container">
    <div class="<?= $status_class ?> response-message">
        <?= $status_message ?>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <div class="form-container">
      <form action="envia_contato.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="form-group">
          <input type="text" name="nome" placeholder="Seu nome" required 
                 value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" />
        </div>
        <div class="form-group">
          <input type="email" name="email" placeholder="Seu e-mail" required 
                 value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
        </div>
        <div class="form-group">
          <input type="text" name="assunto" placeholder="Assunto" required 
                 value="<?= isset($_POST['assunto']) ? htmlspecialchars($_POST['assunto']) : '' ?>" />
        </div>
        <div class="form-group">
          <textarea name="mensagem" placeholder="Sua mensagem" required><?= isset($_POST['mensagem']) ? htmlspecialchars($_POST['mensagem']) : '' ?></textarea>
        </div>
        <div class="form-group">
          <button type="submit">Enviar mensagem</button>
        </div>
      </form>
    </div>
</div>

<script>
    // Script do dropdown
    const dropdownToggle = document.getElementById('dropdownToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');

    dropdownToggle?.addEventListener('click', function(e) {
      e.preventDefault();
      dropdownMenu.classList.toggle('show');
    });

    // Fecha ao clicar fora
    document.addEventListener('click', function(e) {
      if (dropdownToggle && !dropdownToggle.contains(e.target) && dropdownMenu && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
      }
    });

    // Script do menu hamburguer
    const menuToggle = document.getElementById('menu-toggle');
    const navList = document.getElementById('nav-list');
    
    menuToggle.addEventListener('click', function() {
        navList.classList.toggle('show');
    });

    // Auto-hide das mensagens de status após 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const statusMessage = document.querySelector('.response-message');
        if (statusMessage) {
            setTimeout(function() {
                statusMessage.style.opacity = '0';
                statusMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    statusMessage.remove();
                }, 500);
            }, 5000);
        }
    });
</script>

</body>
</html>