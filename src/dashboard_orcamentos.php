<?php
session_start();
require_once 'conexao.php';

// Verificação de acesso: apenas admin pode ver este dashboard
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Processar alteração de status via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'alterar_status') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if ($id && $status) {
        $statuses_validos = ['pendente', 'em_analise', 'aprovado', 'recusado', 'cancelado'];
        if (in_array($status, $statuses_validos)) {
            $sql = "UPDATE orcamentos SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
            
            // Mensagem de sucesso
            $success_message = "Status alterado com sucesso!";
        }
    }
}

// Filtros para orçamentos
$status = $_GET['status'] ?? '';
$busca = $_GET['busca'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fim = $_GET['fim'] ?? '';

$condicoes = [];
$params = [];
$tipos = '';

if (!empty($status)) {
    $condicoes[] = "o.status = ?";
    $params[] = $status;
    $tipos .= 's';
}

if (!empty($busca)) {
    $condicoes[] = "(u.nome LIKE ? OR u.email LIKE ? OR p.nome LIKE ? OR o.codigo LIKE ?)";
    $buscaParam = "%$busca%";
    $params = array_merge($params, array_fill(0, 4, $buscaParam));
    $tipos .= 'ssss';
}

if (!empty($inicio) && !empty($fim)) {
    $condicoes[] = "DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $inicio;
    $params[] = $fim;
    $tipos .= 'ss';
}

// Query principal para orçamentos
$sql = "SELECT o.*, u.nome as usuario_nome, u.email, u.telefone, p.nome as plano_nome, p.preco_base 
        FROM orcamentos o
        JOIN usuarios u ON o.usuario_id = u.id
        JOIN planos p ON o.plano_id = p.id";

if ($condicoes) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if ($condicoes) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orcamentos = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas
$totalOrcamentos = $conn->query("SELECT COUNT(*) as total FROM orcamentos")->fetch_assoc()['total'];
$orcamentosPendentes = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'pendente'")->fetch_assoc()['total'];
$orcamentosAprovados = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'aprovado'")->fetch_assoc()['total'];
$valorTotalAprovado = $conn->query("SELECT SUM(COALESCE(orcamento_negociado, orcamento_base)) as total FROM orcamentos WHERE status = 'aprovado'")->fetch_assoc()['total'] ?? 0;

// Função para formatar status
function formatarStatus($status) {
    $status_map = [
        'rascunho' => ['Rascunho', '#6c757d'],
        'pendente' => ['Pendente', '#ffc107'],
        'em_analise' => ['Em Análise', '#17a2b8'],
        'aprovado' => ['Aprovado', '#28a745'],
        'recusado' => ['Recusado', '#dc3545'],
        'cancelado' => ['Cancelado', '#6c757d']
    ];
    
    return $status_map[$status] ?? ['Desconhecido', '#6c757d'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar Orçamentos - InnovaMeta</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
        --primary-color: #008060;
        --primary-dark: #00664c;
        --text-color: #eee;
        --bg-color: #0a0b0f;
    }
    .btn-purple, .btn-action, .card i, .sidebar-header h2 {
        color: var(--primary-color);
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
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        display: inline-block;
        min-width: 100px;
        text-align: center;
        cursor: pointer;
    }
    .status-pendente { background: #ffc107; color: #000; }
    .status-aprovado { background: #28a745; color: #fff; }
    .status-recusado { background: #dc3545; color: #fff; }
    .status-em_analise { background: #17a2b8; color: #fff; }
    .status-rascunho { background: #6c757d; color: #fff; }
    .status-cancelado { background: #6c757d; color: #fff; }
    
    .filters {
        background: rgba(255,255,255,0.05);
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    
    .filters form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }
    
    .descricao-resumida {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    
    .valor-negociado {
        color: #25D366 !important;
        font-weight: bold;
    }
    
    .valor-base {
        color: #ffc107 !important;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
    }
    
    .modal-content {
        background-color: #1a1a1a;
        margin: 5% auto;
        padding: 0;
        border-radius: 10px;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        background: var(--primary-color);
        color: white;
        padding: 1.5rem;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .close {
        color: white;
        font-size: 2rem;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
    }
    
    .close:hover {
        color: #ccc;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .info-item {
        background: rgba(255,255,255,0.02);
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
    }
    
    .info-label {
        color: #ccc;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .info-value {
        color: #fff;
        font-weight: 500;
    }
    
    .descricao-completa {
        background: rgba(255,255,255,0.02);
        padding: 1.5rem;
        border-radius: 8px;
        margin: 1.5rem 0;
        white-space: pre-wrap;
        line-height: 1.6;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.2);
        border: 1px solid #28a745;
        color: #28a745;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .badge-negociado {
        background: rgba(37, 211, 102, 0.2);
        color: #25D366;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        border: 1px solid #25D366;
    }
    
    .badge-base {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        border: 1px solid #ffc107;
    }
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
      <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="dashboard_precos.php"><i class="fas fa-tags"></i> Gerenciar Preços</a></li>
      <li><a href="dashboard_orcamentos.php" class="active"><i class="fas fa-file-invoice"></i> Orçamentos</a></li>
      <li><a href="dashboard_configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
      <li><a href="../index.php"><i class="fas fa-home"></i> Voltar ao Início</a></li>
    </ul>
  </div>

  <div class="main-content" id="main-content">
    <div class="header">
      <h1><i class="fas fa-file-invoice"></i> Gerenciar Orçamentos</h1>
      <div class="buttons">
        <a href="dashboard.php"><button class="btn-purple"><i class="fas fa-tachometer-alt"></i> Dashboard</button></a>
        <a href="../index.php"><button class="btn-purple"><i class="fas fa-home"></i> Voltar ao Início</button></a>
      </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
        </div>
    <?php endif; ?>

    <div class="grid">
      <div class="card">
        <i class="fas fa-file-invoice"></i>
        <h2><?= $totalOrcamentos ?></h2>
        <p>Total de Orçamentos</p>
      </div>
      <div class="card">
        <i class="fas fa-clock"></i>
        <h2><?= $orcamentosPendentes ?></h2>
        <p>Pendentes</p>
      </div>
      <div class="card">
        <i class="fas fa-check-circle"></i>
        <h2><?= $orcamentosAprovados ?></h2>
        <p>Aprovados</p>
      </div>
      <div class="card">
        <i class="fas fa-dollar-sign"></i>
        <h2>R$ <?= number_format($valorTotalAprovado, 0, ',', '.') ?></h2>
        <p>Valor Aprovado</p>
      </div>
    </div>

    <div class="filters">
      <form method="GET">
        <div>
          <label>Status:</label>
          <select name="status">
            <option value="">Todos os Status</option>
            <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
            <option value="em_analise" <?= $status === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
            <option value="aprovado" <?= $status === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
            <option value="recusado" <?= $status === 'recusado' ? 'selected' : '' ?>>Recusado</option>
            <option value="cancelado" <?= $status === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
          </select>
        </div>
        <div>
          <label>Buscar:</label>
          <input type="text" name="busca" placeholder="Cliente, email, plano ou código" value="<?= htmlspecialchars($busca) ?>">
        </div>
        <div>
          <label>Data Início:</label>
          <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
        </div>
        <div>
          <label>Data Fim:</label>
          <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
        </div>
        <div>
          <button type="submit" class="btn-purple"><i class="fas fa-filter"></i> Filtrar</button>
          <a href="dashboard_orcamentos.php" class="btn-purple" style="text-decoration: none; display: inline-block; padding: 10px 15px;"><i class="fas fa-sync"></i> Limpar</a>
        </div>
      </form>
    </div>

    <h2><i class="fas fa-list"></i> Lista de Orçamentos (<?= count($orcamentos) ?>)</h2>

    <table id="tabela">
      <thead>
        <tr>
          <th><i class="fas fa-hashtag"></i> Código</th>
          <th><i class="fas fa-user"></i> Cliente</th>
          <th><i class="fas fa-cube"></i> Plano</th>
          <th><i class="fas fa-dollar-sign"></i> Valor Base</th>
          <th><i class="fas fa-handshake"></i> Valor Final</th>
          <th><i class="fas fa-comment"></i> Descrição</th>
          <th><i class="fas fa-info-circle"></i> Status</th>
          <th><i class="fas fa-calendar"></i> Data</th>
          <th><i class="fas fa-tools"></i> Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($orcamentos) > 0): ?>
          <?php foreach($orcamentos as $orcamento): 
            list($status_text, $status_color) = formatarStatus($orcamento['status']);
            // Determinar valor final
            $valor_final = $orcamento['orcamento_negociado'] ?? $orcamento['orcamento_base'];
            $tem_negociacao = !empty($orcamento['orcamento_negociado']);
            $desconto = $tem_negociacao ? $orcamento['orcamento_base'] - $orcamento['orcamento_negociado'] : 0;
          ?>
            <tr>
              <td><strong><?= htmlspecialchars($orcamento['codigo']) ?></strong></td>
              <td>
                <div><?= htmlspecialchars($orcamento['usuario_nome']) ?></div>
                <small style="color: #ccc;"><?= htmlspecialchars($orcamento['email']) ?></small>
              </td>
              <td><?= htmlspecialchars($orcamento['plano_nome']) ?></td>
              <td>
                <div>R$ <?= number_format($orcamento['orcamento_base'], 2, ',', '.') ?></div>
              </td>
              
              <!-- COLUNA VALOR FINAL -->
              <td>
                <div class="<?= $tem_negociacao ? 'valor-negociado' : 'valor-base' ?>" style="font-weight: bold;">
                  R$ <?= number_format($valor_final, 2, ',', '.') ?>
                </div>
                <div style="margin-top: 4px;">
                  <?php if ($tem_negociacao): ?>
                    <span class="badge-negociado">
                      <i class="fas fa-handshake"></i> Negociado
                    </span>
                    <small style="color: #25D366; font-size: 0.7rem;">
                      -R$ <?= number_format($desconto, 2, ',', '.') ?>
                    </small>
                  <?php else: ?>
                    <span class="badge-base">
                      <i class="fas fa-tag"></i> Base
                    </span>
                  <?php endif; ?>
                </div>
              </td>
              
              <td class="descricao-resumida" onclick="verDetalhes(<?= htmlspecialchars(json_encode($orcamento)) ?>)">
                <?= htmlspecialchars(substr($orcamento['descricao_projeto'], 0, 50)) ?>...
              </td>
              <td>
                <span class="status-badge" style="background-color: <?= $status_color ?>;" 
                      onclick="alterarStatus(<?= $orcamento['id'] ?>, '<?= $orcamento['status'] ?>')">
                  <?= $status_text ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($orcamento['created_at'])) ?></td>
              <td>
                <div class="action-buttons">
                  <button class="btn-purple btn-small" onclick="verDetalhes(<?= htmlspecialchars(json_encode($orcamento)) ?>)">
                    <i class="fas fa-eye"></i>
                  </button>
                  
                  <?php if ($orcamento['status'] === 'pendente' || $orcamento['status'] === 'em_analise'): ?>
                    <button class="btn-purple btn-small" onclick="alterarStatusRapido(<?= $orcamento['id'] ?>, 'aprovado')">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-purple btn-small" onclick="alterarStatusRapido(<?= $orcamento['id'] ?>, 'recusado')">
                      <i class="fas fa-times"></i>
                    </button>
                  <?php endif; ?>
                  
                  <?php if ($orcamento['status'] === 'pendente' || $orcamento['status'] === 'em_analise'): ?>
                    <a href="dashboard_precos.php" class="btn-purple btn-small" style="text-decoration: none;">
                      <i class="fas fa-handshake"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 2rem;">
              <i class="fas fa-inbox" style="font-size: 3rem; color: #666; margin-bottom: 1rem;"></i>
              <br>
              Nenhum orçamento encontrado
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal de Detalhes -->
  <div id="modalDetalhes" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 style="margin: 0; color: white;"><i class="fas fa-file-invoice"></i> Detalhes do Orçamento</h2>
        <button class="close">&times;</button>
      </div>
      <div class="modal-body" id="modalDetalhesBody">
        <!-- Conteúdo carregado via JavaScript -->
      </div>
    </div>
  </div>

  <!-- Modal de Alterar Status -->
  <div id="modalStatus" class="modal">
    <div class="modal-content" style="max-width: 500px;">
      <div class="modal-header">
        <h2 style="margin: 0; color: white;"><i class="fas fa-edit"></i> Alterar Status</h2>
        <button class="close">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" id="formStatus">
          <input type="hidden" name="action" value="alterar_status">
          <input type="hidden" name="id" id="statusOrcamentoId">
          
          <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Novo Status:</label>
            <select name="status" id="statusSelect" style="width: 100%; padding: 10px; border-radius: 5px; background: #2a2a2a; color: white; border: 1px solid #444;">
              <option value="pendente">Pendente</option>
              <option value="em_analise">Em Análise</option>
              <option value="aprovado">Aprovado</option>
              <option value="recusado">Recusado</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
          
          <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" class="btn-purple" onclick="fecharModal('modalStatus')">Cancelar</button>
            <button type="submit" class="btn-purple">Alterar Status</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
      document.querySelector(".hamburger").classList.toggle("active");
    }

    // Modal functions
    const modals = {
        modalDetalhes: document.getElementById('modalDetalhes'),
        modalStatus: document.getElementById('modalStatus')
    };

    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(btn => {
        btn.onclick = function() {
            Object.values(modals).forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });

    window.onclick = function(event) {
        Object.values(modals).forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    function fecharModal(modalId) {
        modals[modalId].style.display = 'none';
    }

    function verDetalhes(orcamento) {
        const modalBody = document.getElementById('modalDetalhesBody');
        const statusInfo = getStatusInfo(orcamento.status);
        const valorFinal = orcamento.orcamento_negociado || orcamento.orcamento_base;
        const temNegociacao = !!orcamento.orcamento_negociado;
        const desconto = temNegociacao ? (orcamento.orcamento_base - orcamento.orcamento_negociado) : 0;
        const percentualDesconto = temNegociacao ? ((desconto / orcamento.orcamento_base) * 100).toFixed(1) : 0;
        
        modalBody.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h3 style="color: #008060; margin: 0;">${orcamento.codigo}</h3>
                <span class="status-badge" style="background-color: ${statusInfo.color};">
                    ${statusInfo.text}
                </span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value">${orcamento.usuario_nome}</div>
                    <div style="color: #ccc; font-size: 0.9rem;">${orcamento.email}</div>
                    <div style="color: #ccc; font-size: 0.9rem;">${orcamento.telefone || 'Não informado'}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Plano</div>
                    <div class="info-value">${orcamento.plano_nome}</div>
                    <div style="color: #ccc; font-size: 0.9rem;">R$ ${parseFloat(orcamento.orcamento_base).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                </div>
                
                <div class="info-item" style="border-left-color: ${temNegociacao ? '#25D366' : '#008060'};">
                    <div class="info-label">Valor Final</div>
                    <div class="info-value" style="color: ${temNegociacao ? '#25D366' : '#fff'}; font-weight: bold; font-size: 1.2rem;">
                        R$ ${parseFloat(valorFinal).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                    <div style="color: ${temNegociacao ? '#25D366' : '#ccc'}; font-size: 0.9rem;">
                        ${temNegociacao ? '💰 Valor negociado' : '💲 Valor base'}
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Data de Solicitação</div>
                    <div class="info-value">${new Date(orcamento.created_at).toLocaleString('pt-BR')}</div>
                </div>
            </div>

            ${temNegociacao ? `
                <div class="info-grid">
                    <div class="info-item" style="border-left-color: #25D366;">
                        <div class="info-label">Valor Negociado</div>
                        <div class="info-value" style="color: #25D366; font-weight: bold;">
                            R$ ${parseFloat(orcamento.orcamento_negociado).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </div>
                    </div>
                    <div class="info-item" style="border-left-color: #ffc107;">
                        <div class="info-label">Desconto Aplicado</div>
                        <div class="info-value" style="color: #ffc107; font-weight: bold;">
                            -R$ ${parseFloat(desconto).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </div>
                        <div style="color: #ffc107; font-size: 0.9rem;">
                            (${percentualDesconto}% de desconto)
                        </div>
                    </div>
                </div>
            ` : ''}

            <div class="info-item">
                <div class="info-label">Prazo Desejado</div>
                <div class="info-value">${orcamento.prazo_desejado || 'Não informado'}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Orçamento Máximo</div>
                <div class="info-value">
                    ${orcamento.orcamento_maximo ? 'R$ ' + parseFloat(orcamento.orcamento_maximo).toLocaleString('pt-BR', {minimumFractionDigits: 2}) : 'Não informado'}
                </div>
            </div>

            <h3 style="margin-top: 2rem; color: #008060;">Descrição do Projeto</h3>
            <div class="descricao-completa">
                ${orcamento.descricao_projeto.replace(/\n/g, '<br>')}
            </div>

            ${orcamento.observacoes ? `
                <h3 style="color: #008060;">Observações</h3>
                <div class="descricao-completa">
                    ${orcamento.observacoes.replace(/\n/g, '<br>')}
                </div>
            ` : ''}

            <div style="margin-top: 2rem; text-align: center; display: flex; gap: 1rem; justify-content: center;">
                <button class="btn-purple" onclick="alterarStatus(${orcamento.id}, '${orcamento.status}')">
                    <i class="fas fa-edit"></i> Alterar Status
                </button>
                <a href="dashboard_precos.php" class="btn-purple" style="text-decoration: none; display: inline-block; padding: 10px 15px;">
                    <i class="fas fa-handshake"></i> Negociar Preço
                </a>
            </div>
        `;
        
        modals.modalDetalhes.style.display = 'block';
    }

    function alterarStatus(id, statusAtual) {
        document.getElementById('statusOrcamentoId').value = id;
        document.getElementById('statusSelect').value = statusAtual;
        modals.modalStatus.style.display = 'block';
    }

    function alterarStatusRapido(id, novoStatus) {
        if (confirm('Tem certeza que deseja alterar o status para ' + novoStatus + '?')) {
            // Criar um form temporário para submeter
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.name = 'action';
            actionInput.value = 'alterar_status';
            
            const idInput = document.createElement('input');
            idInput.name = 'id';
            idInput.value = id;
            
            const statusInput = document.createElement('input');
            statusInput.name = 'status';
            statusInput.value = novoStatus;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            
            form.submit();
        }
    }

    function getStatusInfo(status) {
        const statusMap = {
            'rascunho': ['Rascunho', '#6c757d'],
            'pendente': ['Pendente', '#ffc107'],
            'em_analise': ['Em Análise', '#17a2b8'],
            'aprovado': ['Aprovado', '#28a745'],
            'recusado': ['Recusado', '#dc3545'],
            'cancelado': ['Cancelado', '#6c757d']
        };
        
        return {
            text: statusMap[status]?.[0] || 'Desconhecido',
            color: statusMap[status]?.[1] || '#6c757d'
        };
    }

    // Adicionar animação de fade-in
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#tabela tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.5s ease-out';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, index * 100);
        });
    });
  </script>

</body>
</html>