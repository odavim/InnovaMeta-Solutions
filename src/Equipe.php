<?php
session_start();
$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipe</title>
  <link rel="stylesheet" href="./css/equipe.css" />
  <link rel="stylesheet" href="./css/navbar.css">
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
      <h1>Conheça os membros</h1>
      <h1>da nossa empresa</h1>
    </div>
  </header>

  <section class="equipe">
    <div class="section-container equipe-container">
      <p class="section-header">Nossa equipe de</p>
      <p class="section-header">Profissionais altamente qualificados</p>

      <div class="equipe-grid">
        <div class="equipe-card">
          <img src="images/david.png" alt="David Nea" />
          <h3>David Nea</h3>
          <p>CEO / Programador</p>
        </div>

        <div class="equipe-card">
          <img src="images/machado.png" alt="Lucas Silva" />
          <h3>Davi machado</h3>
          <p>Desenvolvedor Front-end</p>
        </div>

        <div class="equipe-card">
          <img src="images/eduardo.png" alt="Ana Costa" />
          <h3>Eduardo dos santos</h3>
          <p>Designer</p>
        </div>

        <div class="equipe-card">
          <img src="images/daniel.png" alt="Felipe Moura" />
          <h3>Daniel Jose</h3>
          <p>Engenheiro de Software</p>
        </div>

        <div class="equipe-card">
          <img src="images/juan.png" alt="Marina Lopes" />
          <h3>Juan pablo</h3>
          <p>Gerente de Projeto</p>
        </div>

        <div class="equipe-card">
          <img src="images/rosario.png" alt="João Pedro" />
          <h3>Davi do rosario</h3>
          <p>Designer</p>
        </div>

        <div class="equipe-card">
          <img src="images/guilherme.png" alt="Renata Lima" />
          <h3>guilherme batista</h3>
          <p>Marketing Digital</p>
        </div>

        <div class="equipe-card">
          <img src="images/joao.png" alt="Thiago Rocha" />
          <h3>João victor</h3>
          <p>Desenvolvedor Back-end</p>
        </div>
      </div>
    </div>
  </section>

   <script>
    const dropdownToggle = document.getElementById('dropdownToggle');
const dropdownMenu = document.getElementById('dropdownMenu');

dropdownToggle?.addEventListener('click', function(e) {
  e.preventDefault();
  dropdownMenu.classList.toggle('show');
});

// Fecha ao clicar fora
document.addEventListener('click', function(e) {
  if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
    dropdownMenu.classList.remove('show');
  }
});
  </script>

</body>
</html>
