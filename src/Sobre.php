<?php
session_start();
$logado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sobre - InnovaMeta Solutions</title>
    <link rel="stylesheet" href="./css/fonts.css"/>
    <link rel="stylesheet" href="./css/style.css"/>
    <link rel="stylesheet" href="./css/sobre.css"/>
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }
        /* Estilo rápido para os botões do chatbot */
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }
        .quick-replies button {
            background: var(--primary-color); 
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.3s;
        }
        .quick-replies button:hover {
            background: var(--primary-dark);
        }
    </style>
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

<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Nossa História</h1>
        <p class="hero-subtitle">Da ideia à inovação: nossa jornada em transformar negócios</p>
    </div>
</section>

<section class="timeline-section">
    <div class="container">
        <h2 class="section-title center">Nossa Jornada</h2>
        <p class="section-subtitle">Conheça os marcos que definiram a Innovameta ao longo dos anos.</p>
        
        <div class="timeline">
            
            <div class="left">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>2023: O Nascimento da InnovaMeta</h2>
                        <p>A InnovaMeta Solutions nasceu da união de mentes criativas e apaixonadas por tecnologia. Começamos com foco em sistemas de login e cadastro robustos e seguros.</p>
                        </div>
                </div>
            </div>

            <div class="right">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>2024: Primeiros Projetos e Crescimento</h2>
                        <p>Lançamento dos primeiros projetos de portfólio, como o "Traffic Racer" e a "Eleven Solutions", marcando nossa entrada no mercado com projetos em JavaScript e HTML. A equipe começa a crescer.</p>
                        </div>
                </div>
            </div>

            <div class="left">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>2025: Foco em UI/UX e Personalização</h2>
                        <p>Aprimoramos o design para ser mais responsivo e unificado, introduzindo o novo visual Dark Mode. Iniciamos o desenvolvimento do sistema de recuperação de senha por e-mail e melhorias no sistema de compras.</p>
                        </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<section class="legacy-sites-section">
    <div class="container">
        <h2 class="section-title center">Outros Projetos Marcantes</h2>
        <p class="section-subtitle">Projetos que pavimentaram nosso caminho para o sucesso atual.</p>
        
        <div class="legacy-grid">
            <div class="legacy-card">
                <div class="legacy-image">
                    <img src="../src/images/jogo de carro.png" alt="Traffic Racer" class="rounded-image">
                    <div class="legacy-overlay">
                        <a href="./portfolios/C future/Traffic Racer" target="_blank" class="legacy-link">Visitar Jogo</a>
                    </div>
                </div>
                <div class="legacy-content">
                    <h3>Traffic racer</h3>
                    <p>Jogo de Corrida, desenvolvido com Javascript e focado em Diversão e inovação. Uma jornada que começou com muita determinação e aprendizado.</p>
                    <div class="legacy-tech">
                        <span class="tech-tag">Javascript</span>
                        <span class="tech-tag">Html</span>
                        <span class="tech-tag">CSS3</span>
                    </div>
                </div>
            </div>

            <div class="legacy-card">
                <div class="legacy-image">
                    <img src="../src/images/Eleven.png" alt="Eleven solutions" class="rounded-image">
                    <div class="legacy-overlay">
                        <a href="./portfolios/Eleven-solutions-main/eleven solutions.html" target="_blank" class="legacy-link">Visitar Site</a>
                    </div>
                </div>
                <div class="legacy-content">
                    <h3>Eleven solutions</h3>
                    <p>Evoluímos para um site customizado com Html, com mais funcionalidades e um design mais moderno e com mais jogos a seu dispor. Um marco importante no nosso crescimento como profissionais.</p>
                    <div class="legacy-tech">
                        <span class="tech-tag">html</span>
                        <span class="tech-tag">JavaScript</span>
                        <span class="tech-tag">CSS3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="values-section">
    <div class="container">
        <h2 class="section-title center">Nossos Pilares</h2>
        <div class="values-grid">
            <div class="value-card"><div class="value-icon"><i class="fas fa-bullseye"></i></div><h3>Missão</h3><p>Oferecer soluções tecnológicas inovadoras que transformem ideias em realidade digital, com excelência e comprometimento.</p></div>
            <div class="value-card"><div class="value-icon"><i class="fas fa-eye"></i></div><h3>Visão</h3><p>Ser referência em desenvolvimento de soluções digitais personalizadas, reconhecida pela qualidade e inovação.</p></div>
            <div class="value-card"><div class="value-icon"><i class="fas fa-gem"></i></div><h3>Valores</h3><p>Inovação, Transparência, Qualidade, Comprometimento e Foco no Cliente são os pilares do nosso trabalho.</p></div>
        </div>
    </div>
</section>

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

<script>
// --- Script para Dropdown Funcionar ---
const dropdownToggle = document.getElementById('dropdownToggle');
const dropdownMenu = document.getElementById('dropdownMenu');

dropdownToggle?.addEventListener('click', function(e) {
    e.preventDefault();
    dropdownMenu.classList.toggle('show');
});

// Fecha ao clicar fora
document.addEventListener('click', function(e) {
    if (dropdownToggle && dropdownMenu && !dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});

// --- Script do Chatbot (Mantido) ---
const chatbotToggle = document.getElementById('chatbotToggle');
const chatbotContainer = document.getElementById('chatbotContainer');
const closeChatbot = document.getElementById('closeChatbot');
const chatbotMessages = document.getElementById('chatbotMessages');
const chatbotInput = document.getElementById('chatbotInput');
const sendMessage = document.getElementById('sendMessage');

chatbotToggle.addEventListener('click', () => chatbotContainer.classList.toggle('active'));
closeChatbot.addEventListener('click', () => chatbotContainer.classList.remove('active'));

const whatsappConfig = {
    phoneNumber: '552198736603',
    defaultMessage: 'Olá! Gostaria de mais informações sobre os serviços da InnovaMeta.'
};

const respostas = {
    ola: "Olá! Sou o assistente virtual da InnovaMeta. Como posso ajudar?",
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

const inputContainer = chatbotInput.parentNode;
const sendButton = document.getElementById('sendMessage');
inputContainer.insertBefore(whatsappButton, sendButton);


sendMessage.addEventListener('click', () => enviarMensagem());
chatbotInput.addEventListener('keypress', e => { 
    if (e.key === 'Enter') enviarMensagem(); 
});


const style = document.createElement('style');
style.textContent = `
    .whatsapp-btn {
        background: var(--primary-color);
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
        background: var(--primary-dark);
    }
    
    #whatsappButton {
        background: var(--primary-color) !important;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        color: white;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    #whatsappButton:hover {
        background: var(--primary-dark) !important;
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>