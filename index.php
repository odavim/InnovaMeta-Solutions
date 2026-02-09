<?php
session_start();

try {
    require_once 'src/conexao.php';
} catch (Exception $e) {
    // Ignora erro de conexão
    $conn = null;
}

$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;

// Buscar planos do banco com tratamento robusto
$planos = [];
if (isset($conn) && $conn) {
    try {
        $sql_planos = "SELECT id, nome, preco_base, descricao FROM planos WHERE id IN (1,2,3)";
        $result = $conn->query($sql_planos);
        
        if ($result && $result->num_rows > 0) {
            $planos = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Nenhum plano encontrado no banco");
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar planos: " . $e->getMessage());
        // Usar planos padrão em caso de erro
        $planos = [];
    }
}

// Se não conseguiu buscar do banco ou não há planos, usar padrão
if (empty($planos)) {
    $planos = [
        ['id' => 1, 'nome' => 'solutions', 'preco_base' => 750.00, 'descricao' => 'Ideal para quem está começando e precisa de presença online profissional.'],
        ['id' => 2, 'nome' => 'Innova', 'preco_base' => 2500.00, 'descricao' => 'Solução perfeita para Inovar com Melhores Resultados.'],
        ['id' => 3, 'nome' => 'Meta', 'preco_base' => 1500.00, 'descricao' => 'Um site para empresas em desenvolvimento e que necessita de ajuda.']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>InnovaMeta Solutions</title>

    <link rel="stylesheet" href="./src/css/style.css" />
    <link rel="stylesheet" href="./src/css/media.css" />
    <link rel="stylesheet" href="./src/css/fonts.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <nav>
    <div id="logo">
        <img src="./src/images/ims3.0-removebg-preview.png" alt="Logo" id="img-logo" />
    </div>
    
    <div class="menu-toggle" id="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <ul id="menu">
        <li><a href="index.php">Início</a></li>
        <li><a href="./src/Equipe.php">Equipe</a></li>
        <li><a href="./src/Sobre.php">Sobre</a></li>
        <li><a href="./src/contato.php">Contato</a></li>

        <?php if ($logado): ?>
            <li class="dropdown">
                <a href="#" class="btn dropdown-toggle" id="dropdownToggle">
                    Olá, <?= htmlspecialchars($nomeUsuario) ?> <i class="fas fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu" id="dropdownMenu">
                    <?php if (isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === 'admin'): ?>
                        <li><a href="./src/dashboard.php"><i class="fas fa-cogs"></i> Painel Administrativo</a></li>
                    <?php endif; ?>
                    <li><a href="./src/meus_orcamentos.php"><i class="fas fa-file-invoice"></i> Meus Orçamentos</a></li>
                    <li><a href="./src/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li><a href="./src/login.php" id="login" class="btn">Já tem uma conta?</a></li>
        <?php endif; ?>
    </ul>
</nav>

    <header class="header">
        <div class="section-container header__content">
            <h1>Sempre <span>Inovando</span> no mercado de trabalho</h1>
            <button class="btn" onclick="scrollToSection('.planos-container')">Saiba Mais</button>
        </div>
    </header>

    <section class="banner-container">
        <div class="banner-card">
            <h4>Soluções digitais sob medida para o seu sucesso.</h4>
        </div>
        <div class="banner-card">
            <h4>Transforme ideias em projetos incríveis conosco.</h4>
        </div>
        <div class="banner-card">
            <h4>Mantenha estabilidade e sucesso com incrível qualidade.</h4>
        </div>
    </section>

    <section class="header-mid"> 
        <div class="section-container header__content">
            <h1>Como a <span>InnovaMeta</span> te ajuda no mercado de trabalho</h1>
        </div>
    </section>

    <section class="experiencia-container">
        <div class="section-container experiencia-texto">
            <p class="section-subheader">Por que nós?</p>
            <h2 class="section-header">Temos 3 anos de experiência na área!</h2>
            <p class="section-description">
                Atualmente a <strong>InnovaMeta Solutions</strong> está se preparando para o projeto de conclusão de curso (TCC), que tem como objetivo a création de um site para uma empresa cliente. Esse site permitirá a divulgação de seus serviços com uma presença digital moderna e eficiente, melhorando sua visibilidade e apoiando o crescimento do negócio.
            </p>
        </div>
    </section>

    <section class="planos-container">
        <div class="section-container">
            <h2 class="section-header-light">Veja os Nossos Planos</h2>
            <p class="planos-descricao">
                Na <strong>InnovaMeta</strong>, cada projeto é único. Utilizamos tecnologias de forma precisa para entregar
                soluções digitais sob medida para o seu sucesso.
            </p>

            <div class="planos-grid">
                <?php 
                $planos_data = [
                    1 => [
                        'nome' => 'solutions',
                        'preco' => 750,
                        'parcelas' => 3,
                        'descricao' => 'Ideal para quem está começando e precisa de presença online profissional.',
                        'caracteristicas' => ['Site One Page', 'Formulário de Contato', 'SEO Básico', 'Design elaborado', 'Integração com redes sociais']
                    ],
                    2 => [
                        'nome' => 'Innova', 
                        'preco' => 2500,
                        'parcelas' => 5,
                        'descricao' => 'Solução perfeita para Inovar com Melhores Resultados.',
                        'caracteristicas' => ['Paginas innovadoras estilizadas', 'Blog ou Portfólio', 'Uso de linguagens de back e front end completas(sql,php)', 'Suporte técnico 1 ano']
                    ],
                    3 => [
                        'nome' => 'Meta',
                        'preco' => 1500, 
                        'parcelas' => 3,
                        'descricao' => 'Um site para empresas em desenvolvimento e que necessita de ajuda.',
                        'caracteristicas' => ['Até 5 páginas', 'dashboard Autoral', 'Hospedagem 1 ano', 'Integração com WhatsApp e Google Maps']
                    ]
                ];
                
                foreach ([1, 2, 3] as $plano_id): 
                    $plano = $planos_data[$plano_id];
                    $destaque = $plano_id === 2 ? 'destaque' : '';
                ?>
                    <div class="plano-card <?= $destaque ?>">
                        <h3><?= $plano['nome'] ?></h3>
                        <p class="preco">R$ <?= number_format($plano['preco'], 0, ',', '.') ?></p>
                        <span class="parcelamento">ou <?= $plano['parcelas'] ?>x de R$ <?= number_format($plano['preco'] / $plano['parcelas'], 0, ',', '.') ?>,00</span>
                        <p class="descricao"><?= $plano['descricao'] ?></p>
                        
                        <?php if ($logado): ?>
                            <button class="btn-comprar" onclick="solicitarOrcamento(<?= $plano_id ?>)">Solicitar Orçamento</button>
                        <?php else: ?>
                            <a href="./src/login.php" class="btn-comprar">Solicitar Orçamento</a>
                        <?php endif; ?>
                        
                        <ul>
                            <?php foreach ($plano['caracteristicas'] as $caracteristica): ?>
                                <li><?= $caracteristica ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer dark-style">
    <div class="footer-main-content">
        
        <div class="footer-col footer-logo-area">
            <h4>INNOVAMETA SOLUTIONS</h4>
            
            <div class="contact-info">
                <p><i class="fas fa-map-marker-alt"></i> Rua Marechal Soares de Andreia 90, Realengo RJ</p>
                <p><i class="fas fa-phone"></i> (21) 99914-2907</p>
                <p><i class="fas fa-envelope"></i> innovametasolutions@gmail.com</p>
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
                <li><a href="index.php">Início</a></li>
                <li><a href="./src/Sobre.php">Sobre Nós</a></li>
                <li><a href="./src/Equipe.php">Equipe</a></li>
                <li><a href="./src/contato.php">Contato</a></li>
            </ul>
        </div>
        
        <div class="footer-col link-col">
            <h4>REDES SOCIAIS</h4>
            <ul>
                <li><a href="https://www.linkedin.com/in/innovameta-solutions-6314a2397/" target="_blank"><i class="fab fa-linkedin"></i> linkedin</a></li>
                <li><a href="https://www.instagram.com/innovametasolution/" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
                <li><a href="https://github.com/InnovaMetaSolutions" target="_blank"><i class="fab fa-github"></i> github</a></li>
                <li><a href="https://x.com/InnovaMeta2025" target="_blank"><i class="fab fa-twitter-square"></i> Twitter</a></li>
            </ul>
        </div>
        
        </div>

    <div class="footer-bottom">
        <p>© 2025 InnovaMeta Solutions. Todos os direitos reservados.</p>
        <div class="social-icons">
            <a href="https://www.instagram.com/innovametasolution/" target="_blank" aria-label="Facebook"><i class="fab fa-instagram"></i></a>
            <a href="https://x.com/InnovaMeta2025" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="https://www.linkedin.com/in/innovameta-solutions-6314a2397/" target="_blank" aria-label="LinkedIn"><i class="fab fa-github"></i></a>
        </div>
    </div>
    
</footer>

    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header">
            <h4>💬 Suporte InnovaMeta</h4>
            <button id="closeChatbot"><i class="fas fa-times"></i></button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-message bot">
                <p>👋 Olá! Sou o assistente virtual da InnovaMeta. Como posso ajudar?</p>
                <div class="quick-replies">
                    <button data-msg="Quais serviços vocês oferecem?">Ver Serviços</button>
                    <button data-msg="Quero entrar em contato">Contato</button>
                    <button data-msg="Quais são os preços?">Ver Preços</button>
                    <button data-msg="Falar com humano">WhatsApp</button>
                </div>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatbotInput" placeholder="Digite sua mensagem...">
            <button id="sendMessage"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <button class="chatbot-toggle" id="chatbotToggle"><i class="fas fa-comment-dots"></i></button>

    <script src="./src/js/script.js"></script>
  <script>
    function solicitarOrcamento(plano_id) {
        const isLoggedIn = <?= $logado ? 'true' : 'false' ?>;
        
        if (!isLoggedIn) {
            showToast("Você precisa estar logado para solicitar orçamentos.", 'error');
            setTimeout(() => {
                window.location.href = 'src/login.php';
            }, 1500);
            return;
        }
        
        window.location.href = `src/solicitar_orcamento.php?plano_id=${plano_id}`;
    }

    function showToast(msg, type = 'info') {
        let toast = document.getElementById('toast-global');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast-global';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#008060' : '#ff3b3b'};
                color: white;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s;
                z-index: 10000;
                max-width: 300px;
            `;
            document.body.appendChild(toast);
        }
        
        toast.textContent = msg;
        toast.style.background = type === 'success' ? '#008060' : '#ff3b3b';
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
        }, 3000);
    }

    function scrollToSection(selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.getElementById('dropdownToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (dropdownToggle && dropdownMenu) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
        }

        const menuToggle = document.getElementById('menu-toggle');
        const menu = document.getElementById('menu');
        
        if (menuToggle && menu) {
            menuToggle.addEventListener('click', function() {
                menu.classList.toggle('active');
            });
        }

        const botoesOrcamento = document.querySelectorAll('.btn-comprar');
        botoesOrcamento.forEach(botao => {
            const novoBotao = botao.cloneNode(true);
            botao.parentNode.replaceChild(novoBotao, botao);
        });
    });

    ScrollReveal().reveal('.plano-card', {
        duration: 800,
        distance: '40px',
        easing: 'ease-out',
        origin: 'bottom',
        interval: 200
    });

    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotContainer = document.getElementById('chatbotContainer');
    const closeChatbot = document.getElementById('closeChatbot');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotInput = document.getElementById('chatbotInput');
    const sendMessage = document.getElementById('sendMessage');

    if (chatbotToggle) {
        chatbotToggle.addEventListener('click', () => chatbotContainer.classList.toggle('active'));
    }
    if (closeChatbot) {
        closeChatbot.addEventListener('click', () => chatbotContainer.classList.remove('active'));
    }

    const whatsappConfig = {
        phoneNumber: '552198736603', 
        defaultMessage: 'Olá! Gostaria de mais informações sobre os serviços da InnovaMeta.'
    };

    const respostas = {
        ola: "Olá! Como posso te ajudar hoje?",
        serviços: "Oferecemos desenvolvimento web, sistemas personalizados e consultoria em TI.",
        contato: `📞 Telefone: (21) 98773-6603<br>📧 Email: contato@innovameta.com<br><br>Quer falar direto pelo WhatsApp? <button class="whatsapp-btn" onclick="abrirWhatsApp()">Clique aqui</button>`,
        portfólio: "Confira nossos projetos na seção de Portfólio!",
        preços: "Temos diferentes planos. Veja na página inicial ou solicite um orçamento personalizado.",
        whatsapp: `💚 Prefere falar direto pelo WhatsApp? <button class="whatsapp-btn" onclick="abrirWhatsApp()">Clique aqui para conversar</button><br><br>Ou se preferir, me diga sua dúvida que eu te ajudo!`,
        default: "Desculpe, não entendi. Pode reformular? Se preferir, posso te conectar com nosso time pelo WhatsApp."
    };

    function enviarMensagem(mensagem = null) {
        const msg = mensagem || chatbotInput.value.trim();
        if (!msg) return;
        addMensagem(msg, 'user');
        chatbotInput.value = '';

        setTimeout(() => {
            let resposta = respostas.default;
            
            if (msg.toLowerCase().includes('serviço') || msg.toLowerCase().includes('servicos')) {
                resposta = respostas.serviços;
            } else if (msg.toLowerCase().includes('contato') || msg.toLowerCase().includes('email') || msg.toLowerCase().includes('telefone')) {
                resposta = respostas.contato;
            } else if (msg.toLowerCase().includes('preço') || msg.toLowerCase().includes('valor') || msg.toLowerCase().includes('custo')) {
                resposta = respostas.preços;
            } else if (msg.toLowerCase().includes('whatsapp') || msg.toLowerCase().includes('humano') || msg.toLowerCase().includes('atendente')) {
                resposta = respostas.whatsapp;
            } else if (msg.toLowerCase().includes('ola') || msg.toLowerCase().includes('oi') || msg.toLowerCase().includes('olá')) {
                resposta = respostas.ola;
            } else if (msg.toLowerCase().includes('portfólio') || msg.toLowerCase().includes('projeto')) {
                resposta = respostas.portfólio;
            }
            
            addMensagem(resposta, 'bot', true);
        }, 600);
    }

    function addMensagem(texto, tipo, quick = false) {
        const div = document.createElement('div');
        div.classList.add('chatbot-message', tipo);
        div.innerHTML = `<p>${texto}</p><small>${new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</small>`;
        
        if (tipo === 'bot' && quick) {
            const quickDiv = document.createElement('div');
            quickDiv.classList.add('quick-replies');
            quickDiv.innerHTML = `
                <button data-msg="Quais serviços vocês oferecem?">Ver Serviços</button>
                <button data-msg="Quero entrar em contato">Contato</button>
                <button data-msg="Quais são os preços?">Ver Preços</button>
                <button data-msg="Falar com humano">WhatsApp</button>
            `;
            div.appendChild(quickDiv);
        }
        
        chatbotMessages.appendChild(div);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

        div.querySelectorAll('.quick-replies button').forEach(btn => {
            btn.addEventListener('click', () => enviarMensagem(btn.dataset.msg));
        });
    }

    function abrirWhatsApp(mensagemPersonalizada = null) {
        const mensagem = mensagemPersonalizada || whatsappConfig.defaultMessage;
        const url = `https://wa.me/${whatsappConfig.phoneNumber}?text=${encodeURIComponent(mensagem)}`;
        window.open(url, '_blank');
    }

    function enviarParaWhatsApp() {
        const mensagem = chatbotInput.value.trim();
        if (mensagem) {
            abrirWhatsApp(mensagem);
        } else {
            abrirWhatsApp();
        }
    }

    const whatsappButton = document.createElement('button');
    whatsappButton.innerHTML = '<i class="fab fa-whatsapp"></i>';
    whatsappButton.id = 'whatsappButton';
    whatsappButton.style.background = '#25D366';
    whatsappButton.style.marginLeft = '5px';
    whatsappButton.title = 'Enviar mensagem para WhatsApp';
    whatsappButton.addEventListener('click', enviarParaWhatsApp);

    if (!document.getElementById('whatsappButton')) {
        chatbotInput.parentNode.appendChild(whatsappButton);
    }

    if (sendMessage) {
        sendMessage.addEventListener('click', () => enviarMensagem());
    }
    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', e => { 
            if (e.key === 'Enter') enviarMensagem(); 
        });
    }

    const style = document.createElement('style');
    style.textContent = `
        .whatsapp-btn {
            background: #25D366;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
        }
        
        #whatsappButton {
            background: #25D366 !important;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        #whatsappButton:hover {
            background: #128C7E !important;
        }
    `;
    document.head.appendChild(style);
</script>

</body>
</html>