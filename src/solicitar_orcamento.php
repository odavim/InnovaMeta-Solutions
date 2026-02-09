<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$plano_id = $_GET['plano_id'] ?? null;

// Buscar informações do plano
$sql_plano = "SELECT id, nome, preco_base, descricao FROM planos WHERE id = ?";
$stmt = $conn->prepare($sql_plano);
$stmt->bind_param("i", $plano_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../index.php");
    exit;
}

$plano = $result->fetch_assoc();
$stmt->close();

$erro = '';
$sucesso = '';

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao']);
    $prazo = trim($_POST['prazo']);
    $orcamento_maximo = $_POST['orcamento_maximo'] ? floatval($_POST['orcamento_maximo']) : null;
    
    if (empty($descricao)) {
        $erro = "Por favor, descreva seu projeto.";
    } else {
        // Gerar código único para o orçamento
        $codigo = 'ORC' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Definir título base
        $titulo = "Orçamento - " . $plano['nome'];
        
        // Inserir no banco de dados
        $sql = "INSERT INTO orcamentos (codigo, usuario_id, plano_id, titulo, descricao_projeto, prazo_desejado, orcamento_maximo, orcamento_base, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("siisssdd", 
                $codigo,
                $_SESSION['usuario_id'], 
                $plano_id, 
                $titulo,
                $descricao, 
                $prazo, 
                $orcamento_maximo, 
                $plano['preco_base']
            );
            
            if ($stmt->execute()) {
                $sucesso = "Orçamento solicitado com sucesso! Entraremos em contato em breve.";
            } else {
                $erro = "Erro ao enviar solicitação: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $erro = "Erro ao preparar a consulta: " . $conn->error;
        }
    }
}

$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Orçamento - InnovaMeta</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--primary-color-dark);
            color: var(--white);
            padding-top: 100px;
        }

        .orcamento-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 3rem 2rem;
            background: var(--secondary-color-dark);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 30px rgba(21, 181, 112, 0.1);
        }

        .orcamento-container h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .plano-info {
            background: rgba(21, 181, 112, 0.1);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
            border: 1px solid rgba(21, 181, 112, 0.2);
        }

        .plano-info h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .plano-info p {
            color: var(--text-light);
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .plano-info strong {
            color: var(--white);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--white);
            font-size: 1.1rem;
        }

        .form-group textarea,
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
        }

        .form-group textarea:focus,
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(21, 181, 112, 0.2);
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .form-group textarea::placeholder {
            color: var(--text-light);
        }

        .btn-enviar {
            background: linear-gradient(to right, var(--primary-color), #1fffb0);
            color: #000;
            padding: 1rem 2rem;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-enviar:hover {
            filter: brightness(1.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(21, 181, 112, 0.4);
        }

        .alert {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .alert-success {
            background: rgba(21, 181, 112, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(21, 181, 112, 0.3);
        }

        .alert-error {
            background: rgba(255, 59, 59, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(255, 59, 59, 0.3);
        }

        .alert .btn {
            display: inline-block;
            margin: 0.5rem;
            text-decoration: none;
        }

        /* Ajustes para os botões dentro do alerta */
        .alert .btn {
            background: var(--primary-color);
            color: #000;
            padding: 0.7rem 1.5rem;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .alert .btn:hover {
            filter: brightness(1.2);
            transform: translateY(-2px);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .orcamento-container {
                margin: 20px auto;
                padding: 2rem 1rem;
            }

            .orcamento-container h1 {
                font-size: 2rem;
            }

            .plano-info {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR COMPLETA -->
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
    <div class="orcamento-container">
        <h1>Solicitar Orçamento</h1>
        
        <div class="plano-info">
            <h3>Plano: <?= htmlspecialchars($plano['nome']) ?></h3>
            <p><strong>Valor Base:</strong> R$ <?= number_format($plano['preco_base'], 2, ',', '.') ?></p>
            <p><strong>Descrição:</strong> <?= htmlspecialchars($plano['descricao']) ?></p>
            <p><small style="color: var(--text-light);">* O valor final pode variar conforme as especificações do seu projeto</small></p>
        </div>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= $sucesso ?>
                <br><br>
                <a href="../index.php" class="btn">Voltar para Home</a>
                <a href="meus_orcamentos.php" class="btn">Ver Meus Orçamentos</a>
            </div>
        <?php else: ?>
            <?php if ($erro): ?>
                <div class="alert alert-error"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="descricao">Descreva seu projeto:*</label>
                    <textarea 
                        id="descricao" 
                        name="descricao" 
                        placeholder="Ex: Preciso de um site institucional para minha empresa de consultoria...
- Quantas páginas?
- Funcionalidades especiais?
- Design específico?
- Integrações necessárias?
- Conteúdo que já possui?" 
                        required><?= $_POST['descricao'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="prazo">Prazo desejado:</label>
                    <select id="prazo" name="prazo">
                        <option value="">Sem preferência</option>
                        <option value="1 semana" <?= ($_POST['prazo'] ?? '') === '1 semana' ? 'selected' : '' ?>>1 semana</option>
                        <option value="2 semanas" <?= ($_POST['prazo'] ?? '') === '2 semanas' ? 'selected' : '' ?>>2 semanas</option>
                        <option value="1 mês" <?= ($_POST['prazo'] ?? '') === '1 mês' ? 'selected' : '' ?>>1 mês</option>
                        <option value="2 meses" <?= ($_POST['prazo'] ?? '') === '2 meses' ? 'selected' : '' ?>>2 meses</option>
                        <option value="3 meses" <?= ($_POST['prazo'] ?? '') === '3 meses' ? 'selected' : '' ?>>3 meses</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="orcamento_maximo">Orçamento máximo (opcional):</label>
                    <input 
                        type="number" 
                        id="orcamento_maximo" 
                        name="orcamento_maximo" 
                        placeholder="R$"
                        min="0"
                        step="0.01"
                        value="<?= $_POST['orcamento_maximo'] ?? '' ?>">
                </div>

                <button type="submit" class="btn-enviar">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitação de Orçamento
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- FOOTER COMPLETO -->
    <footer class="footer dark-style">
        <div class="footer-main-content">
            
            <div class="footer-col footer-logo-area">
                <h4>INNOVAMETA SOLUTIONS</h4>
                
                <div class="contact-info">
                    <p><i class="fas fa-map-marker-alt"></i> Rua Marechal Soares de Andreia 90, Realengo RJ</p>
                    <p><i class="fas fa-phone"></i> (21) 98773-6603</p>
                    <p><i class="fas fa-envelope"></i> contato@innovameta.com</p>
                </div>
                
                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fas fa-money-bill-alt"></i>
                </div>
                
                <form class="newsletter-box">
                    <input type="email" placeholder="Insira seu e-mail" required>
                    <button type="submit" class="dark-btn"><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>

            <div class="footer-col link-col">
                <h4>EMPRESA</h4>
                <ul>
                    <li><a href="../index.php">Início</a></li>
                    <li><a href="Sobre.php">Sobre Nós</a></li>
                    <li><a href="Equipe.php">Equipe</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </div>
            
            <div class="footer-col link-col">
                <h4>REDES SOCIAIS</h4>
                <ul>
                    <li><a href="#" target="_blank"><i class="fab fa-linkedin"></i> linkedin</a></li>
                    <li><a href="https://www.instagram.com/innovametasolution/" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
                    <li><a href="#" target="_blank"><i class="fab fa-github"></i> github</a></li>
                    <li><a href="#" target="_blank"><i class="fab fa-twitter-square"></i> Twitter</a></li>
                </ul>
            </div>
            
        </div>

        <div class="footer-bottom">
            <p>© 2025 InnovaMeta Solutions. Todos os direitos reservados.</p>
            <div class="social-icons">
                <a href="#" target="_blank" aria-label="Facebook"><i class="fab fa-instagram"></i></a>
                <a href="#" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" target="_blank" aria-label="LinkedIn"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </footer>

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
            const container = document.querySelector('.orcamento-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.8s ease-out';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>

</body>
</html>