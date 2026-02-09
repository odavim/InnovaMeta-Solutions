<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

// Buscar orçamentos do usuário
$sql_orcamentos = "
    SELECT o.*, p.nome as plano_nome, p.preco_base 
    FROM orcamentos o 
    INNER JOIN planos p ON o.plano_id = p.id 
    WHERE o.usuario_id = ? 
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql_orcamentos);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$orcamentos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
    <title>Meus Orçamentos - InnovaMeta</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--primary-color-dark);
            color: var(--white);
            padding-top: 100px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .orcamentos-grid {
            display: grid;
            gap: 2rem;
        }

        .orcamento-card {
            background: var(--secondary-color-dark);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px rgba(21, 181, 112, 0.1);
            transition: all 0.3s ease;
        }

        .orcamento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(21, 181, 112, 0.2);
        }

        .orcamento-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .orcamento-info h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .orcamento-codigo {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .orcamento-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .detail-value {
            color: var(--white);
            font-weight: 500;
        }

        .orcamento-descricao {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .orcamento-descricao h4 {
            color: var(--primary-color);
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .orcamento-descricao p {
            color: var(--text-light);
            line-height: 1.6;
        }

        .orcamento-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #000;
        }

        .btn-primary:hover {
            filter: brightness(1.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .empty-state h3 {
            color: var(--white);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .empty-state p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn-novo-orcamento {
            background: linear-gradient(to right, var(--primary-color), #1fffb0);
            color: #000;
            padding: 1rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-novo-orcamento:hover {
            filter: brightness(1.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .container {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .orcamento-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .orcamento-details {
                grid-template-columns: 1fr;
            }

            .orcamento-actions {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav>
        <div id="logo">
            <img src="images/ims3.0-removebg-preview.png" alt="Logo" id="img-logo" />
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
                        <li><a href="pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="login.php" id="login" class="btn">Já tem uma conta?</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container">
        <div class="page-header">
            <h1>Meus Orçamentos</h1>
            <p>Acompanhe todos os seus orçamentos solicitados</p>
        </div>

        <?php if (empty($orcamentos)): ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3>Nenhum orçamento encontrado</h3>
                <p>Você ainda não solicitou nenhum orçamento. Que tal começar um projeto conosco?</p>
                <a href="../index.php" class="btn-novo-orcamento">
                    <i class="fas fa-plus"></i> Solicitar Primeiro Orçamento
                </a>
            </div>
        <?php else: ?>
            <div class="orcamentos-grid">
                <?php foreach ($orcamentos as $orcamento): 
                    list($status_text, $status_color) = formatarStatus($orcamento['status']);
                    
                    // Determinar valor final (negociado ou base)
                    $valor_final = !empty($orcamento['orcamento_negociado']) && $orcamento['orcamento_negociado'] > 0 
                        ? $orcamento['orcamento_negociado'] 
                        : $orcamento['orcamento_base'];
                ?>
                    <div class="orcamento-card">
                        <div class="orcamento-header">
                            <div class="orcamento-info">
                                <h3><?= htmlspecialchars($orcamento['plano_nome']) ?></h3>
                                <div class="orcamento-codigo">Código: <?= htmlspecialchars($orcamento['codigo']) ?></div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <?php if (!empty($orcamento['orcamento_negociado']) && $orcamento['orcamento_negociado'] > 0): ?>
                                    <div class="status-badge" style="background-color: #28a745; color: #fff;">
                                        <i class="fas fa-tag"></i> Valor Negociado
                                    </div>
                                <?php endif; ?>
                                <div class="status-badge" style="background-color: <?= $status_color ?>; color: #000;">
                                    <?= $status_text ?>
                                </div>
                            </div>
                        </div>

                        <div class="orcamento-details">
                            <div class="detail-item">
                                <span class="detail-label">Valor Base</span>
                                <span class="detail-value">R$ <?= number_format($orcamento['orcamento_base'], 2, ',', '.') ?></span>
                            </div>
                            
                            <?php if (!empty($orcamento['orcamento_negociado']) && $orcamento['orcamento_negociado'] > 0): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Valor Negociado</span>
                                    <span class="detail-value" style="color: var(--primary-color); font-weight: bold;">
                                        R$ <?= number_format($orcamento['orcamento_negociado'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Economia</span>
                                    <span class="detail-value" style="color: #28a745; font-weight: bold;">
                                        R$ <?= number_format($orcamento['orcamento_base'] - $orcamento['orcamento_negociado'], 2, ',', '.') ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <span class="detail-label">Orçamento Máximo</span>
                                <span class="detail-value">
                                    <?= $orcamento['orcamento_maximo'] ? 'R$ ' . number_format($orcamento['orcamento_maximo'], 2, ',', '.') : 'Não informado' ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Prazo Desejado</span>
                                <span class="detail-value">
                                    <?= $orcamento['prazo_desejado'] ?: 'Não informado' ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Data de Solicitação</span>
                                <span class="detail-value">
                                    <?= date('d/m/Y H:i', strtotime($orcamento['created_at'])) ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($orcamento['descricao_projeto'])): ?>
                            <div class="orcamento-descricao">
                                <h4>Descrição do Projeto</h4>
                                <p><?= nl2br(htmlspecialchars($orcamento['descricao_projeto'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="orcamento-actions">
                            <?php if ($orcamento['status'] === 'aprovado'): ?>
                                <a href="pages/finalizar_pagamento.php?id=<?= $orcamento['id'] ?>" class="btn-action btn-primary">
                                    <i class="fas fa-credit-card"></i> Finalizar Pagamento
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Menu Mobile
        document.getElementById('menu-toggle')?.addEventListener('click', function() {
            document.getElementById('menu').classList.toggle('show');
            this.classList.toggle('active');
        });

        // Dropdown
        document.querySelector('.dropdown-toggle')?.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            this.nextElementSibling.classList.toggle('show');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
                document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                    toggle.classList.remove('active');
                });
            }
        });

        // Adicionar animação de fade-in
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.orcamento-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>

</body>
</html>