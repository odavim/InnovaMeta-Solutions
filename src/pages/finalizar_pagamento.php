<?php
session_start();
require_once '../conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$orcamento_id = $_GET['id'] ?? null;

if (!$orcamento_id) {
    header("Location: ../meus_orcamentos.php");
    exit();
}

// Buscar dados do orçamento
$sql = "SELECT o.*, p.nome as plano_nome, p.descricao as plano_descricao
        FROM orcamentos o 
        JOIN planos p ON o.plano_id = p.id 
        WHERE o.id = ? AND o.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orcamento_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$orcamento = $result->fetch_assoc();

if (!$orcamento) {
    header("Location: ../meus_orcamentos.php");
    exit();
}

// Verificar se o orçamento está aprovado
if ($orcamento['status'] !== 'aprovado') {
    header("Location: ../meus_orcamentos.php");
    exit();
}

$logado = true;
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario'] ?? null;

// Determinar valor final (negociado ou base)
$valor_final = $orcamento['orcamento_negociado'] ?? $orcamento['orcamento_base'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pagamento - InnovaMeta</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/media.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--primary-color-dark);
            color: var(--white);
            padding-top: 100px;
            min-height: 100vh;
        }

        .pagamento-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 3rem 2rem;
            background: var(--secondary-color-dark);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 30px rgba(21, 181, 112, 0.1);
        }

        .pagamento-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .pagamento-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .pagamento-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .resumo-orcamento {
            background: rgba(21, 181, 112, 0.1);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }

        .resumo-orcamento h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .info-value {
            color: var(--white);
            font-weight: 500;
            font-size: 1.1rem;
        }

        .valor-total {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            margin: 2rem 0;
        }

        .valor-total .label {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .valor-total .valor {
            color: var(--primary-color);
            font-size: 3rem;
            font-weight: bold;
        }

        /* Abas de Pagamento */
        .tabs-pagamento {
            display: flex;
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            flex: 1;
            text-align: center;
            min-width: 120px;
        }

        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: bold;
        }

        .conteudo-pagamento {
            display: none;
            animation: fadeIn 0.5s ease-in;
        }

        .conteudo-pagamento.active {
            display: block;
        }

        .metodo-pagamento {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .metodo-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .metodo-header i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .metodo-header h3 {
            color: var(--white);
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--white);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--white);
            transition: all 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(21, 181, 112, 0.2);
        }

        .cartao-detalhes {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
        }

        .btn-pagar {
            background: linear-gradient(to right, var(--primary-color), #1fffb0);
            color: #000;
            padding: 1.2rem 3rem;
            border: none;
            border-radius: 30px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
        }

        .btn-pagar:hover {
            filter: brightness(1.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(21, 181, 112, 0.4);
        }

        .btn-pagar:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .qr-code {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            margin: 1.5rem 0;
        }

        .qr-code img {
            max-width: 250px;
            border-radius: 8px;
        }

        .codigo-pix {
            background: rgba(255,255,255,0.05);
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            word-break: break-all;
            font-family: monospace;
            text-align: center;
        }

        .btn-copiar {
            background: var(--primary-color);
            color: #000;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 1rem;
        }

        .boleto-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            margin: 1.5rem 0;
        }

        .codigo-barras {
            font-family: monospace;
            font-size: 1.1rem;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            color: #000;
        }

        .linhas-barras {
            background: #000;
            padding: 1rem;
            border-radius: 4px;
        }

        .linha-barra {
            height: 2px;
            background: #fff;
            margin: 0.5rem 0;
        }

        .btn-voltar {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .btn-voltar:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .pagamento-container {
                margin: 20px auto;
                padding: 2rem 1rem;
            }

            .pagamento-header h1 {
                font-size: 2rem;
            }

            .cartao-detalhes {
                grid-template-columns: 1fr;
            }

            .tab {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }

            .valor-total .valor {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav>
        <div id="logo">
            <img src="../images/ims3.0-removebg-preview.png" alt="Logo" id="img-logo" />
        </div>
        
        <div class="menu-toggle" id="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <ul id="menu">
            <li><a href="../../index.php">Início</a></li>
            <li><a href="../Equipe.php">Equipe</a></li>
            <li><a href="../Sobre.php">Sobre</a></li>
            <li><a href="../contato.php">Contato</a></li>

            <?php if ($logado): ?>
                <li class="dropdown">
                    <a href="#" class="btn dropdown-toggle" id="dropdownToggle">
                        Olá, <?= htmlspecialchars($nomeUsuario) ?> <i class="fas fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <li><a href="../meus_orcamentos.php"><i class="fas fa-file-invoice"></i> Meus Orçamentos</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="../login.php" id="login" class="btn">Já tem uma conta?</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="pagamento-container">
        <div class="pagamento-header">
            <h1><i class="fas fa-credit-card"></i> Finalizar Pagamento</h1>
            <p>Conclua o pagamento do seu orçamento aprovado</p>
        </div>

        <div class="resumo-orcamento">
            <h3><i class="fas fa-file-invoice"></i> Resumo do Orçamento</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Código do Orçamento</span>
                    <span class="info-value"><?= htmlspecialchars($orcamento['codigo']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Plano Contratado</span>
                    <span class="info-value"><?= htmlspecialchars($orcamento['plano_nome']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value" style="color: var(--primary-color); font-weight: bold;">
                        <?= htmlspecialchars(ucfirst($orcamento['status'])) ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($orcamento['descricao_projeto'])): ?>
                <div class="info-item">
                    <span class="info-label">Descrição do Projeto</span>
                    <span class="info-value"><?= nl2br(htmlspecialchars($orcamento['descricao_projeto'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="valor-total">
            <div class="label">Total a Pagar</div>
            <div class="valor">R$ <?= number_format($valor_final, 2, ',', '.') ?></div>
            <?php if ($orcamento['orcamento_negociado']): ?>
                <small style="color: var(--text-light);">
                    <s>De: R$ <?= number_format($orcamento['orcamento_base'], 2, ',', '.') ?></s> 
                    • Valor negociado
                </small>
            <?php else: ?>
                <small style="color: var(--text-light);">Valor referente ao serviço contratado</small>
            <?php endif; ?>
        </div>

        <div class="tabs-pagamento">
            <div class="tab active" onclick="mostrarMetodo('pix')">
                <i class="fas fa-qrcode"></i> PIX
            </div>
            <div class="tab" onclick="mostrarMetodo('cartao')">
                <i class="fas fa-credit-card"></i> Cartão
            </div>
            <div class="tab" onclick="mostrarMetodo('boleto')">
                <i class="fas fa-barcode"></i> Boleto
            </div>
        </div>

        <!-- PIX -->
        <div id="metodo-pix" class="conteudo-pagamento active">
            <div class="metodo-pagamento">
                <div class="metodo-header">
                    <i class="fas fa-qrcode"></i>
                    <h3>Pagamento via PIX</h3>
                </div>
                
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Pague instantaneamente usando o PIX. Escaneie o QR Code ou copie o código.
                </p>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode("PIX|InnovaMeta|{$orcamento['codigo']}|{$valor_final}") ?>" 
                         alt="QR Code PIX">
                </div>

                <div class="codigo-pix">
                    <strong>Chave PIX Copia e Cola:</strong><br>
                    <div id="codigoPix" style="margin: 1rem 0; padding: 1rem; background: rgba(0,0,0,0.3); border-radius: 6px;">
                        00020126580014br.gov.bcb.pix0136<?= $orcamento_id ?>26360054BR5925INNOVAMETA SOLUTIONS6008RIO DE JANEIRO62100506<?= $orcamento['codigo'] ?>6304
                    </div>
                    <button type="button" class="btn-copiar" onclick="copiarPix()">
                        <i class="fas fa-copy"></i> Copiar Código PIX
                    </button>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn-pagar" onclick="finalizarPagamento('pix')">
                        <i class="fas fa-check"></i> Confirmar Pagamento via PIX
                    </button>
                </div>
            </div>
        </div>

        <!-- CARTÃO -->
        <div id="metodo-cartao" class="conteudo-pagamento">
            <div class="metodo-pagamento">
                <div class="metodo-header">
                    <i class="fas fa-credit-card"></i>
                    <h3>Cartão de Crédito</h3>
                </div>
                    
                <div class="form-group">
                    <label>Número do Cartão</label>
                    <input type="text" id="numeroCartao" 
                           placeholder="0000 0000 0000 0000" required
                           oninput="formatarCartao(this)">
                </div>

                <div class="form-group">
                    <label>Nome no Cartão</label>
                    <input type="text" id="nomeCartao" 
                           placeholder="Seu nome completo" required>
                </div>

                <div class="cartao-detalhes">
                    <div class="form-group">
                        <label>Validade</label>
                        <input type="text" id="validadeCartao" 
                               placeholder="MM/AA" required
                               oninput="formatarValidade(this)">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="cvvCartao" 
                               placeholder="123" required
                               oninput="formatarCVV(this)">
                    </div>
                    <div class="form-group">
                        <label>Parcelas</label>
                        <select id="parcelasCartao">
                            <?php 
                            $max_parcelas = ($valor_final >= 100) ? 12 : 1;
                            for ($i = 1; $i <= $max_parcelas; $i++): 
                                $valor_parcela = $valor_final / $i;
                            ?>
                                <option value="<?= $i ?>">
                                    <?= $i ?>x de R$ <?= number_format($valor_parcela, 2, ',', '.') ?>
                                    <?= $i > 1 ? ' (sem juros)' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div style="text-align: center;">
                    <button type="button" class="btn-pagar" onclick="finalizarPagamento('cartao')">
                        <i class="fas fa-lock"></i> Pagar R$ <?= number_format($valor_final, 2, ',', '.') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- BOLETO -->
        <div id="metodo-boleto" class="conteudo-pagamento">
            <div class="metodo-pagamento">
                <div class="metodo-header">
                    <i class="fas fa-barcode"></i>
                    <h3>Boleto Bancário</h3>
                </div>
                
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    O boleto será gerado e poderá ser pago em qualquer banco ou lotérica.
                    <strong>Vencimento: <?= date('d/m/Y', strtotime('+3 days')) ?></strong>
                </p>

                <div class="boleto-container">
                    <div class="codigo-barras">
                        34191.79001 01043.510047 91020.150008 5 84410000015000
                    </div>
                    <div class="linhas-barras">
                        <div class="linha-barra"></div>
                        <div class="linha-barra"></div>
                        <div class="linha-barra"></div>
                        <div style="color: #fff; font-family: monospace; font-size: 0.9rem;">
                            CÓDIGO DE BARRAS
                        </div>
                    </div>
                </div>

                <div style="text-align: center;">
                    <button type="button" class="btn-pagar" onclick="finalizarPagamento('boleto')">
                        <i class="fas fa-download"></i> Gerar e Imprimir Boleto
                    </button>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="../meus_orcamentos.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para Meus Orçamentos
            </a>
        </div>
    </div>

    <script>
        // Menu Mobile
        document.getElementById('menu-toggle')?.addEventListener('click', function() {
            document.getElementById('menu').classList.toggle('show');
            this.classList.toggle('active');
        });

        // Dropdown
        const dropdownToggle = document.getElementById('dropdownToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (dropdownMenu) {
                    dropdownMenu.classList.toggle('show');
                }
            });
        }

        // Métodos de Pagamento
        function mostrarMetodo(metodo) {
            // Esconder todos os conteúdos
            document.querySelectorAll('.conteudo-pagamento').forEach(div => {
                div.classList.remove('active');
            });
            
            // Remover active de todas as tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostrar método selecionado
            document.getElementById('metodo-' + metodo).classList.add('active');
            
            // Ativar tab selecionada
            event.target.classList.add('active');
        }

        // Formatação de campos
        function formatarCartao(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = valor.replace(/(\d{4})(?=\d)/g, '$1 ');
            input.value = valor.trim().slice(0, 19);
        }

        function formatarValidade(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length > 2) {
                valor = valor.substring(0, 2) + '/' + valor.substring(2);
            }
            input.value = valor.slice(0, 5);
        }

        function formatarCVV(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 4);
        }

        // Copiar PIX
        function copiarPix() {
            const codigo = document.getElementById('codigoPix').textContent;
            navigator.clipboard.writeText(codigo).then(() => {
                const btn = document.querySelector('.btn-copiar');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Código Copiado!';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 3000);
            });
        }

        // Finalizar pagamento
        function finalizarPagamento(metodo) {
            if (confirm(`Confirmar pagamento de R$ <?= number_format($valor_final, 2, ',', '.') ?> via ${metodo.toUpperCase()}?`)) {
                // Simular processamento
                const btn = document.querySelector('.btn-pagar');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
                btn.disabled = true;
                
                setTimeout(() => {
                    alert('✅ Pagamento processado com sucesso!\\n\\nEm breve entraremos em contato para iniciar seu projeto.');
                    window.location.href = '../meus_orcamentos.php';
                }, 2000);
            }
        }

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.pagamento-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.8s ease-out';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                if (dropdownMenu) dropdownMenu.classList.remove('show');
                if (dropdownToggle) dropdownToggle.classList.remove('active');
            }
        });
    </script>

</body>
</html>