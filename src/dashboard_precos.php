<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Processar negociação de preço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'negociar_preco') {
        $orcamento_id = $_POST['orcamento_id'];
        $valor_negociado = $_POST['valor_negociado'];
        $status = $_POST['status'];
        
        $sql = "UPDATE orcamentos SET orcamento_negociado = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dsi", $valor_negociado, $status, $orcamento_id);
        
        if ($stmt->execute()) {
            $success_message = "✅ Preço negociado e status atualizado com sucesso!";
        } else {
            $error_message = "❌ Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Aprovação rápida
    if (isset($_POST['action']) && $_POST['action'] === 'aprovar_rapido') {
        $orcamento_id = $_POST['orcamento_id'];
        
        $sql = "UPDATE orcamentos SET status = 'aprovado', updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orcamento_id);
        $stmt->execute();
        $stmt->close();
        
        $success_message = "✅ Orçamento aprovado com sucesso!";
    }
}

// Buscar orçamentos para negociação
$sql = "SELECT o.*, u.nome as usuario_nome, u.email, u.telefone, p.nome as plano_nome, p.preco_base 
        FROM orcamentos o
        JOIN usuarios u ON o.usuario_id = u.id
        JOIN planos p ON o.plano_id = p.id
        WHERE o.status IN ('pendente', 'em_analise')
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);
$orcamentos = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas
$totalPendentes = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'pendente'")->fetch_assoc()['total'];
$totalAnalise = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'em_analise'")->fetch_assoc()['total'];
$totalAprovados = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'aprovado'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negociação de Preços - InnovaMeta</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .negociacao-container {
            background: var(--secondary-color-dark);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .sidebar a:hover, .sidebar a.active {
        color: var(--primary-color);
    }
        
        .orcamento-wpp {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .orcamento-wpp:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .orcamento-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .cliente-info h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .cliente-contato {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .wpp-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-wpp {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-wpp-negociar {
            background: #25D366;
            color: white;
        }
        
        .btn-wpp-contato {
            background: #128C7E;
            color: white;
        }
        
        .btn-wpp-aprovar {
            background: #28a745;
            color: white;
        }
        
        .btn-wpp-salvar {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-wpp:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .preco-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .preco-item {
            background: rgba(255,255,255,0.02);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .preco-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .preco-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .preco-negociado {
            color: #25D366 !important;
        }
        
        .descricao-projeto {
            background: rgba(255,255,255,0.02);
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            line-height: 1.6;
        }
        
        .negociacao-form {
            background: rgba(37, 211, 102, 0.1);
            border: 1px solid #25D366;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .form-negociacao {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-negociacao input, .form-negociacao select {
            padding: 0.8rem;
            border-radius: 6px;
            border: 1px solid #444;
            background: rgba(255,255,255,0.1);
            color: white;
            width: 100%;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-block;
            margin-left: 1rem;
        }
        
        .status-pendente { background: #ffc107; color: #000; }
        .status-analise { background: #17a2b8; color: #fff; }
        .status-aprovado { background: #28a745; color: #fff; }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .modelos-msg {
            background: rgba(37, 211, 102, 0.1);
            border: 1px solid #25D366;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .msg-item {
            background: rgba(255,255,255,0.05);
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .msg-item:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .msg-copiar {
            float: right;
            color: #25D366;
            font-size: 0.8rem;
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
      <li><a href="dashboard_precos.php" class="active"><i class="fas fa-tags"></i> Gerenciar Preços</a></li>
      <li><a href="dashboard_orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a></li>
      <li><a href="dashboard_configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
      <li><a href="../index.php"><i class="fas fa-home"></i> Voltar ao Início</a></li>
    </ul>
  </div> 

<div class="main-content" id="main-content">
    <div class="header">
        <h1><i class="fas fa-handshake"></i> Negociação de Preços</h1>
        <div class="buttons">
            <a href="dashboard.php"><button class="btn-purple"><i class="fas fa-arrow-left"></i> Voltar</button></a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div class="card">
            <i class="fas fa-clock"></i>
            <h2><?= $totalPendentes ?></h2>
            <p>Pendentes</p>
        </div>
        <div class="card">
            <i class="fas fa-comments"></i>
            <h2><?= $totalAnalise ?></h2>
            <p>Em Análise</p>
        </div>
        <div class="card">
            <i class="fas fa-check-circle"></i>
            <h2><?= $totalAprovados ?></h2>
            <p>Aprovados</p>
        </div>
        <div class="card">
            <i class="fas fa-handshake"></i>
            <h2><?= count($orcamentos) ?></h2>
            <p>Para Negociar</p>
        </div>
    </div>

    <div class="modelos-msg">
        <h4><i class="fas fa-copy"></i> Modelos de Mensagem para WhatsApp</h4>
        
        <div class="msg-item" onclick="copiarMensagem(this)">
            <strong>💰 Proposta de Valor</strong>
            <span class="msg-copiar">Copiar</span>
            <div style="font-size: 0.9rem; color: #ccc; margin-top: 0.3rem;">
                Olá! Analisamos seu projeto e temos uma proposta personalizada. Podemos oferecer [SERVIÇO] por R$ [VALOR_NEGOCIADO]. O que achou?
            </div>
        </div>
        
        <div class="msg-item" onclick="copiarMensagem(this)">
            <strong>🎯 Negociação</strong>
            <span class="msg-copiar">Copiar</span>
            <div style="font-size: 0.9rem; color: #ccc; margin-top: 0.3rem;">
                Entendemos seu orçamento. Podemos ajustar o escopo para R$ [VALOR_NEGOCIADO] incluindo [ITENS]. Fechamos?
            </div>
        </div>
        
        <div class="msg-item" onclick="copiarMensagem(this)">
            <strong>✅ Confirmação</strong>
            <span class="msg-copiar">Copiar</span>
            <div style="font-size: 0.9rem; color: #ccc; margin-top: 0.3rem;">
                Perfeito! Orçamento aprovado no valor de R$ [VALOR_FINAL]. Iniciaremos o projeto em breve. Obrigado pela confiança!
            </div>
        </div>
    </div>

    <div class="negociacao-container">
        <h2><i class="fas fa-list"></i> Orçamentos para Negociar (<?= count($orcamentos) ?>)</h2>

        <?php if (empty($orcamentos)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>Todos os orçamentos estão em dia!</h3>
                <p>Quando surgirem novos orçamentos pendentes, eles aparecerão aqui para negociação.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orcamentos as $orcamento): 
                $telefone_wpp = preg_replace('/[^0-9]/', '', $orcamento['telefone']);
                $telefone_wpp = '55' . $telefone_wpp;
                $valor_sugerido = $orcamento['orcamento_negociado'] ?: $orcamento['orcamento_base'];
            ?>
                <div class="orcamento-wpp">
                    <div class="orcamento-header">
                        <div class="cliente-info">
                            <h3>
                                <?= htmlspecialchars($orcamento['usuario_nome']) ?>
                                <span class="status-badge status-<?= $orcamento['status'] === 'pendente' ? 'pendente' : 'analise' ?>">
                                    <?= $orcamento['status'] === 'pendente' ? 'Pendente' : 'Em Análise' ?>
                                </span>
                            </h3>
                            <div class="cliente-contato">
                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($orcamento['email']) ?> | 
                                <i class="fas fa-phone"></i> <?= htmlspecialchars($orcamento['telefone']) ?> |
                                <i class="fas fa-cube"></i> <?= htmlspecialchars($orcamento['plano_nome']) ?>
                            </div>
                            <small><i class="fas fa-hashtag"></i> Código: <?= htmlspecialchars($orcamento['codigo']) ?></small>
                        </div>
                        <div class="wpp-actions">
                            <a href="https://wa.me/<?= $telefone_wpp ?>?text=<?= urlencode("Olá " . $orcamento['usuario_nome'] . "! Sou da InnovaMeta. Analisei seu projeto e temos uma proposta de R$ " . number_format($valor_sugerido, 2, ',', '.') . ". Podemos conversar?") ?>" 
                               target="_blank" class="btn-wpp btn-wpp-negociar">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="aprovar_rapido">
                                <input type="hidden" name="orcamento_id" value="<?= $orcamento['id'] ?>">
                                <button type="submit" class="btn-wpp btn-wpp-aprovar" onclick="return confirm('Aprovar este orçamento?')">
                                    <i class="fas fa-check"></i> Aprovar
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="preco-info">
                        <div class="preco-item">
                            <div class="preco-label">Valor Base</div>
                            <div class="preco-value">R$ <?= number_format($orcamento['preco_base'], 2, ',', '.') ?></div>
                        </div>
                        
                        <div class="preco-item">
                            <div class="preco-label">Valor Negociado</div>
                            <div class="preco-value preco-negociado">
                                <?= $orcamento['orcamento_negociado'] ? 
                                    'R$ ' . number_format($orcamento['orcamento_negociado'], 2, ',', '.') : 
                                    'A negociar' ?>
                            </div>
                        </div>
                        
                        <?php if ($orcamento['orcamento_maximo']): ?>
                        <div class="preco-item">
                            <div class="preco-label">Orçamento Máximo</div>
                            <div class="preco-value">R$ <?= number_format($orcamento['orcamento_maximo'], 2, ',', '.') ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="descricao-projeto">
                        <strong><i class="fas fa-file-alt"></i> Descrição do Projeto:</strong><br>
                        <?= nl2br(htmlspecialchars($orcamento['descricao_projeto'])) ?>
                    </div>

                    <div class="negociacao-form">
                        <h4><i class="fas fa-handshake"></i> Negociar Preço</h4>
                        <form method="POST" class="form-negociacao">
                            <input type="hidden" name="action" value="negociar_preco">
                            <input type="hidden" name="orcamento_id" value="<?= $orcamento['id'] ?>">
                            
                            <div>
                                <label>Valor Negociado:</label>
                                <input type="number" name="valor_negociado" step="0.01" 
                                       value="<?= $valor_sugerido ?>" required
                                       min="0" max="100000">
                            </div>
                            
                            <div>
                                <label>Status:</label>
                                <select name="status">
                                    <option value="em_analise" <?= $orcamento['status'] === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                                    <option value="aprovado">Aprovar</option>
                                </select>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn-wpp btn-wpp-salvar">
                                    <i class="fas fa-save"></i> Salvar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-calendar"></i> Solicitado em: <?= date('d/m/Y H:i', strtotime($orcamento['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
        document.querySelector(".hamburger").classList.toggle("active");
    }

    // Copiar modelos de mensagem
    function copiarMensagem(element) {
        const texto = element.querySelector('div').textContent.trim();
        navigator.clipboard.writeText(texto).then(() => {
            const copiarBtn = element.querySelector('.msg-copiar');
            const originalText = copiarBtn.textContent;
            copiarBtn.textContent = '✓ Copiado!';
            copiarBtn.style.color = '#25D366';
            
            setTimeout(() => {
                copiarBtn.textContent = originalText;
                copiarBtn.style.color = '#25D366';
            }, 2000);
        });
    }

    // Gerar link WhatsApp com valor negociado
    function gerarLinkWhatsApp(telefone, nome, valor, descricao) {
        const mensagem = `Olá ${nome}! 

Analisei seu projeto:
"${descricao.substring(0, 100)}..."

💰 *Proposta:* R$ ${parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}

Vamos conversar sobre os detalhes?`;

        return `https://wa.me/${telefone}?text=${encodeURIComponent(mensagem)}`;
    }
</script>

</body>
</html>