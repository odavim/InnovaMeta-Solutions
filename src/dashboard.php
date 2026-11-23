<?php
session_start();
require_once 'conexao.php';

// Verificação de acesso: apenas admin pode ver este dashboard
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Prepara filtros (Mantido seu código original)
$busca = $_POST['busca'] ?? '';
$inicio = $_POST['inicio'] ?? '';
$fim = $_POST['fim'] ?? '';
$condicoes = [];
$params = [];
$tipos = '';
$filtro = false;

if (!empty($busca)) {
    $condicoes[] = "(nome LIKE ? OR email LIKE ? OR telefone LIKE ? OR cpf LIKE ?)";
    $buscaParam = "%$busca%";
    $params = array_merge($params, array_fill(0, 4, $buscaParam));
    $tipos .= 'ssss';
}

if (!empty($inicio) && !empty($fim)) {
    $condicoes[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $inicio;
    $params[] = $fim;
    $tipos .= 'ss';
}

$sql = "SELECT id, nome, email, telefone, cpf, nivel, created_at FROM usuarios";
if ($condicoes) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
    $filtro = true;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($condicoes) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Métricas para cards
$totalUsuarios = $result->num_rows;
$totalOrcamentos = $conn->query("SELECT COUNT(*) as total FROM orcamentos")->fetch_assoc()['total'];
$orcamentosPendentes = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'pendente'")->fetch_assoc()['total'];

// Gráfico de cadastros (últimos 30 dias)
$graficoQuery = $conn->query("SELECT 
                                 DATE_FORMAT(created_at, '%d/%m') as data, 
                                 COUNT(*) as total 
                               FROM usuarios 
                               WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                               GROUP BY DATE(created_at) 
                               ORDER BY created_at ASC");
$datas = [];
$totais = [];
while ($row = $graficoQuery->fetch_assoc()) {
    $datas[] = $row['data'];
    $totais[] = $row['total'];
}

// Orçamentos recentes
$orcamentos = $conn->query("
    SELECT o.id, u.nome as usuario, p.nome as plano, o.status, o.created_at 
    FROM orcamentos o
    JOIN usuarios u ON o.usuario_id = u.id
    JOIN planos p ON o.plano_id = p.id 
    ORDER BY o.created_at DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - InnovaMeta</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
        --primary-color: #008060;
        --primary-dark: #00664c;
        --text-color: #eee;
        --bg-color: #0a0b0f;
    }
    .btn-purple, .btn-action, .card i{
        color: var(--primary-color);
    }
    .sidebar-header h2 {
      color: var(--text-color);
    }
    .btn-purple {
        background-color: var(--primary-dark);
        border-color: var(--primary-color);
        color: #fff;
    }
    .btn-purple:hover {
        background-color: var(--primary-color);
    }
    .sidebar a:hover, .sidebar a.active {
        color: var(--primary-color);
    }
    .form-nivel button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 8px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .form-nivel button:hover {
        background-color: var(--primary-dark);
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    .status-pendente { background: #ffc107; color: #000; }
    .status-aprovado { background: #28a745; color: #fff; }
    .status-recusado { background: #dc3545; color: #fff; }
    .status-em_analise { background: #17a2b8; color: #fff; }
    .status-rascunho { background: #6c757d; color: #fff; }
  </style>
</head>
<body>

  <div class="hamburger" onclick="toggleSidebar()">&#9776;</div>

  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="images/ims3.0-removebg-preview.png" alt="Logo" class="logo-side">
      <span class="close-btn" onclick="toggleSidebar()">×</span>
    </div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
       <li><a href="dashboard_precos.php"><i class="fas fa-tags"></i> Gerenciar Preços</a></li>
      <li><a href="dashboard_orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a></li>
      <li><a href="dashboard_configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
      <li><a href="../index.php"><i class="fas fa-home"></i> Voltar ao Início</a></li>
    </ul>
  </div>

  <div class="main-content" id="main-content">
    <div class="header">
      <h1><i class="fas fa-tachometer-alt"></i> Dashboard InnovaMeta</h1>
      <div class="buttons">
        <a href="../index.php"><button class="btn-purple"><i class="fas fa-home"></i> Voltar ao Início</button></a>
        <a href="../pages/logout.php"><button class="btn-purple"><i class="fas fa-sign-out-alt"></i> Sair</button></a>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <i class="fas fa-users"></i>
        <h2><?= $totalUsuarios ?></h2>
        <p>Total de Usuários</p>
      </div>
      <div class="card">
        <i class="fas fa-file-invoice"></i>
        <h2><?= $totalOrcamentos ?></h2>
        <p>Total de Orçamentos</p>
      </div>
      <div class="card">
        <i class="fas fa-clock"></i>
        <h2><?= $orcamentosPendentes ?></h2>
        <p>Orçamentos Pendentes</p>
      </div>
      <div class="card">
        <i class="fas fa-calendar-day"></i>
        <h2><?= date('d/m/Y') ?></h2>
        <p>Data de Hoje</p>
      </div>
    </div>

    <div class="filters">
      <form method="POST">
        <input type="text" name="busca" placeholder="🔍 Nome, email, telefone ou CPF" value="<?= htmlspecialchars($busca) ?>">
        <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
        <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
        <button type="submit" class="btn-purple"><i class="fas fa-filter"></i> Filtrar</button>
      </form>
      <div class="export-buttons">
        <a href="relatorio.php"><button class="btn-purple"><i class="fas fa-file-csv"></i> Exportar Usuários</button></a>
        <a href="relatorio_orcamentos.php"><button class="btn-purple"><i class="fas fa-file-csv"></i> Exportar Orçamentos</button></a>
        <a href="dashboard_orcamentos.php"><button class="btn-purple"><i class="fas fa-file-invoice"></i> Gerenciar Orçamentos</button></a>
      </div>
    </div>

    <h2><i class="fas fa-table"></i> <?= $filtro ? 'Usuários Filtrados' : 'Todos os Usuários' ?></h2>

    <table id="tabela">
      <thead>
        <tr>
          <th><i class="fas fa-user"></i> Nome</th>
          <th><i class="fas fa-envelope"></i> Email</th>
          <th><i class="fas fa-phone"></i> Telefone</th>
          <th><i class="fas fa-id-card"></i> CPF</th>
          <th><i class="fas fa-calendar"></i> Data Cadastro</th>
          <th><i class="fas fa-star"></i> Nível</th>
          <th><i class="fas fa-cog"></i> Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['nome']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['telefone']) ?></td>
              <td><?= htmlspecialchars($row['cpf']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
              <td>
                <form method="POST" action="update_nivel.php" class="form-nivel">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <select name="nivel">
                    <option value="cliente" <?= $row['nivel'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="premium" <?= $row['nivel'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    <option value="admin" <?= $row['nivel'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                  </select>
                  <button type="submit"><i class="fas fa-save"></i></button>
                </form>
              </td>
              <td>
                <button class="btn-action" onclick="showUserActions(<?= $row['id'] ?>)">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">Nenhum registro encontrado</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h2><i class="fas fa-chart-line"></i> Cadastros (Últimos 30 dias)</h2>
    <div class="chart-container">
      <canvas id="grafico"></canvas>
    </div>

    <h2><i class="fas fa-file-invoice-dollar"></i> Orçamentos Recentes</h2>
    <table id="orcamentos">
      <thead>
        <tr>
          <th><i class="fas fa-user"></i> Usuário</th>
          <th><i class="fas fa-cube"></i> Plano</th>
          <th><i class="fas fa-info-circle"></i> Status</th>
          <th><i class="fas fa-calendar"></i> Data</th>
          <th><i class="fas fa-tools"></i> Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($orcamentos && $orcamentos->num_rows > 0): ?>
          <?php while ($row = $orcamentos->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['usuario']) ?></td>
            <td><?= htmlspecialchars($row['plano']) ?></td>
            <td>
              <span class="status-badge status-<?= $row['status'] ?>">
                <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
              </span>
            </td>
            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
            <td>
              <a href="dashboard_orcamentos.php?action=view&id=<?= $row['id'] ?>" class="btn-action">
                <i class="fas fa-eye"></i> Ver
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">Nenhum orçamento encontrado</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
      document.querySelector(".hamburger").classList.toggle("active");
    }

    document.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('grafico').getContext('2d');
      
      const chartData = {
        labels: <?= json_encode($datas) ?>,
        datasets: [{
          label: 'Cadastros por Dia',
          backgroundColor: 'rgba(0, 128, 96, 0.2)',
          borderColor: 'rgba(0, 128, 96, 1)',
          data: <?= json_encode($totais) ?>,
          borderWidth: 2,
          tension: 0.3,
          fill: true
        }]
      };

      new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.7)' } },
            y: { 
              beginAtZero: true,
              grid: { color: 'rgba(255,255,255,0.1)' },
              ticks: { color: 'rgba(255,255,255,0.7)', precision: 0 }
            }
          }
        }
      });
    });

    function showUserActions(userId) {
      console.log('Ações para usuário:', userId);
    }
  </script>
</body>
</html>