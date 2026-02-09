<?php
require_once 'conexao.php';
$erro = '';

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $dia = $_POST['dia'] ?? '';
    $mes = $_POST['mes'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $genero = $_POST['genero'] ?? '';

    $data_nascimento = "$ano-$mes-$dia";

    if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
        $erro = 'Data de nascimento inválida!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } elseif (!validarCPF($cpf)) {
        $erro = 'CPF inválido!';
    } elseif (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
        $erro = 'Senha deve ter no mínimo 8 caracteres, incluindo letras e números.';
    } elseif (empty($nome) || empty($telefone) || empty($cpf) || empty($dia) || empty($mes) || empty($ano) || empty($genero)) {
        $erro = 'Preencha todos os campos!';
    } else {
        $verifica = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $verifica->bind_param("s", $email);
        $verifica->execute();
        $verifica->store_result();

        if ($verifica->num_rows > 0) {
            $erro = 'Esse e-mail já está cadastrado!';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_ARGON2ID);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, cpf, senha, data_nascimento, genero) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nome, $email, $telefone, $cpf, $senhaHash, $data_nascimento, $genero);

            if ($stmt->execute()) {
                header("Location: login.php?cadastro=sucesso");
                exit;
            } else {
                $erro = 'Erro interno ao cadastrar. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cadastro - InnovaMeta</title>
<link rel="stylesheet" href="./css/cadastro.css" />
</head>
<body>
<div id="container">
    <div id="barra"><div></div></div>
    <form id="form" method="POST">
        <div class="step active" data-step="1">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <p class="aviso" id="aviso-email"></p>
        </div>

        <div class="step" data-step="2">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required placeholder="8+ chars, letras + números">
            <p class="aviso" id="aviso-senha"></p>
        </div>

        <div class="step" data-step="3">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">

            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" required placeholder="(99) 99999-9999" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">

            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">

            <label>Data de nascimento</label>
            <div class="data-nascimento-group"> 
                <input type="text" name="dia" id="dia" placeholder="dd" maxlength="2" required value="<?= htmlspecialchars($_POST['dia'] ?? '') ?>">
                <select name="mes" id="mes" required>
                    <option value="">Mês</option>
                    <?php
                    $meses = ['01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril', '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'];
                    foreach ($meses as $num => $nome) {
                        $selected = (($_POST['mes'] ?? '') == $num) ? 'selected' : '';
                        echo "<option value=\"$num\" $selected>$nome</option>";
                    }
                    ?>
                </select>
                <input type="text" name="ano" id="ano" placeholder="aaaa" maxlength="4" required value="<?= htmlspecialchars($_POST['ano'] ?? '') ?>">
            </div>

            <label>Gênero</label>
            <div class="genero-opcoes"> 
                <?php
                $opcoes_genero = ['homem' => 'Homem', 'mulher' => 'Mulher', 'nao-binario' => 'Não binário', 'outro' => 'Outro', 'prefiro-nao-dizer' => 'Prefiro não dizer'];
                $genero_selecionado = $_POST['genero'] ?? '';
                foreach ($opcoes_genero as $valor => $texto) {
                    $checked = ($genero_selecionado == $valor) ? 'checked' : '';
                    // Envolve o texto em <span> para melhor estilização no CSS
                    echo "<label><input type=\"radio\" name=\"genero\" value=\"$valor\" $checked><span>$texto</span></label>";
                }
                ?>
            </div>

            <p class="aviso" id="aviso-final"></p>
        </div>

        <?php if($erro): ?><p class="erro" style="text-align:center;"><?=htmlspecialchars($erro)?></p><?php endif; ?>

        <div class="nav">
            <button type="button" class="voltar" id="btn-voltar">Voltar</button>
            <button type="button" id="btn-prox">Avançar</button>
        </div>
    </form>

    <div class="signup">Já tem conta? <a href="login.php">Entrar</a></div>
</div>

<script>
const steps = [...document.querySelectorAll('.step')];
const barra = document.querySelector('#barra div');
let cur = 0;
const botao = document.getElementById('btn-prox');
const voltar = document.getElementById('btn-voltar');
const senhaInput = document.getElementById('senha');
const avisoSenha = document.getElementById('aviso-senha');

function showStep(i){
    steps.forEach((s, idx)=>s.classList.toggle('active', idx===i));
    barra.style.transform = `scaleX(${(i+1)/steps.length})`;
    voltar.style.visibility = i ? 'visible' : 'hidden';
    botao.textContent = i < steps.length - 1 ? 'Avançar' : 'Cadastrar';
}
showStep(cur);

botao.addEventListener('click', () => {
    // Pegando os valores dos campos
    const email = document.getElementById('email').value;
    const senha = senhaInput.value;
    const nome = document.getElementById('nome').value;
    const telefone = document.getElementById('telefone').value;
    const cpf = document.getElementById('cpf').value;
    const dia = document.getElementById('dia')?.value;
    const mes = document.getElementById('mes')?.value;
    const ano = document.getElementById('ano')?.value;
    const genero = document.querySelector('input[name="genero"]:checked')?.value;

    if(cur===0){
        // Validação de E-mail
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const avisoEmail = document.getElementById('aviso-email');
        if(!email.match(emailRegex)) {
             avisoEmail.textContent = 'Formato de e-mail inválido.';
             return;
        }
        avisoEmail.textContent = '';
    }
    else if(cur===1){
        // Validação de Senha (8+ chars, letras + números)
        if(!(senha.length >= 8 && /[A-Za-z]/.test(senha) && /\d/.test(senha))) {
            return;
        }
    }
    else if(cur===2){
        // Validação dos Campos Finais
        if(!nome || !telefone || !cpf || !dia || !mes || !ano || !genero){
            document.getElementById('aviso-final').textContent='Todos os campos são obrigatórios.';
            return;
        }
        document.getElementById('form').submit();
        return;
    }
    cur++; showStep(cur);
});

voltar.addEventListener('click',()=>{if(cur>0){cur--; showStep(cur);}});

// Força da senha em tempo real
senhaInput.addEventListener('input', () => {
    const senha = senhaInput.value;
    let forca = 0;
    if (senha.length >= 8) forca++;
    if (/[A-Z]/.test(senha)) forca++;
    if (/[0-9]/.test(senha)) forca++;
    if (/[^A-Za-z0-9]/.test(senha)) forca++;
    const mensagens = ['Muito fraca', 'Fraca', 'Forte', 'Muito forte'];
    const cores = ['red', 'orange', '#1E90FF', 'green']; // Azul mais escuro para Forte
    // Usando forca - 1 pois o array é 0-based
    avisoSenha.textContent = mensagens[forca - 1] || (senha.length > 0 ? 'Muito fraca' : '');
    avisoSenha.style.color = cores[forca - 1] || 'red';
});

// Máscara de telefone e CPF
function mascaraTelefone(valor) {
    valor = valor.replace(/\D/g, "");
    valor = valor.replace(/^(\d{2})(\d)/g, "($1) $2");
    valor = valor.replace(/(\d{5})(\d)/, "$1-$2");
    return valor.slice(0, 15);
}
function mascaraCPF(valor) {
    valor = valor.replace(/\D/g, "");
    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return valor.slice(0, 14);
}
document.getElementById('telefone').addEventListener('input', function() { this.value = mascaraTelefone(this.value); });
document.getElementById('cpf').addEventListener('input', function() { this.value = mascaraCPF(this.value); });
</script>
</body>
</html>