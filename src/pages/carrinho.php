<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_carrinho'] = "Você precisa estar logado para acessar o carrinho.";
    header("Location: ../login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Busca os itens do carrinho
$sql = "SELECT c.id, p.nome, p.preco, p.imagem, c.quantidade 
        FROM carrinho c
        JOIN produtos p ON c.produto_id = p.id
        WHERE c.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Carrinho</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .carrinho-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .carrinho-table th, .carrinho-table td { padding: 10px; border-bottom: 1px solid #ccc; text-align: center; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-limpar { background: #ff3b3b; color: #fff; }
        .btn-finalizar { background: #15b570; color: #fff; }
        .toast { position: fixed; bottom: 20px; right: 20px; background: #15b570; color: white; padding: 10px 20px; border-radius: 5px; opacity: 0; transform: translateY(20px); transition: all 0.3s; }
        .toast.show { opacity: 1; transform: translateY(0); }
    </style>
    <script>
document.querySelectorAll('.btn-comprar').forEach(btn => {
    btn.addEventListener('click', function(){
        const produto_id = this.dataset.id;
        fetch('./src/adicionar_carrinho.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'produto_id=' + produto_id
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                alert("Produto adicionado ao carrinho!");
            } else if(data.redirect){
                window.location.href = data.redirect;
            } else {
                alert("Erro ao adicionar ao carrinho.");
            }
        });
    });
});
</script>

</head>
<body>

<nav>
    <div id="logo" class="navbar-animate">
        <img src="../images/ims-removebg-preview.png" alt="Logo da empresa" id="img-logo" />
    </div>

    <div class="menu-toggle" id="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <ul id="menu" class="navbar-animate">
        <li><a href="../../index.php">Início</a></li>
        <li><a href="../Equipe.php">Equipe</a></li>
        <li><a href="../Sobre.php">Sobre</a></li>
        <li><a href="../contato.php">Contato</a></li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
        <li><a href="./carrinho.php"><i class="fas fa-shopping-cart"></i></a></li>
        <li class="dropdown">
            <a href="#" class="btn dropdown-toggle" id="dropdownToggle">
                Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?> <i class="fas fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu" id="dropdownMenu">
                <?php if ($_SESSION['usuario_nivel'] === 'admin'): ?>
                    <li><a href="../dashboard.php"><i class="fas fa-cogs"></i> Painel Administrativo</a></li>
                <?php endif; ?>
                <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a></li>
            </ul>
        </li>
        <?php else: ?>
        <li><a href="../login.php" id="login" class="btn">Já tem uma conta?</a></li>
        <?php endif; ?>
    </ul>
</nav>


<div class="section-container fade-in">
    <h1>Meu Carrinho</h1>
    <?php if ($result->num_rows > 0): ?>
        <table class="carrinho-table">
            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ação</th>
            </tr>
            <?php while ($item = $result->fetch_assoc()): 
                $subtotal = $item['preco'] * $item['quantidade'];
                $total += $subtotal;
            ?>
            <tr data-id="<?= $item['id'] ?>">
                <td>
                    <img src="<?= $item['imagem'] ?>" alt="<?= $item['nome'] ?>" width="60">
                    <?= $item['nome'] ?>
                </td>
                <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                <td><input type="number" class="qtd" value="<?= $item['quantidade'] ?>" min="1"></td>
                <td class="subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                <td><button class="remover btn">Remover</button></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <h2>Total: <span id="total">R$ <?= number_format($total, 2, ',', '.') ?></span></h2>
        <div class="actions">
            <button id="limpar" class="btn btn-limpar">Limpar Carrinho</button>
            <a href="../finalizar_compra.php" class="btn">Finalizar Compra</a>
        </div>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</div>

<div id="toast" class="toast"></div>

<script>
function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

$(document).on('change', '.qtd', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    let qtd = $(this).val();
    $.post('../update_carrinho.php', {id, qtd}, function(data) {
        if (data.success) {
            linha.find('.subtotal').text('R$ ' + data.subtotal);
            $('#total').text('R$ ' + data.total);
            showToast("Quantidade atualizada!");
        }
    }, 'json');
});

$(document).on('click', '.remover', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    $.post('../remove_item.php', {id}, function(data) {
        if (data.success) {
            linha.remove();
            $('#total').text('R$ ' + data.total);
            showToast("Item removido!");
        }
    }, 'json');
});

$('#limpar').on('click', function(){
    $.post('../limpar_carrinho.php', {}, function(data){
        if(data.success){
            location.reload();
        }
    }, 'json');
});

$('#finalizar').on('click', function(){
    $.post('../finalizar_compra.php', {}, function(data){
        if(data.success){
            showToast("Compra finalizada!");
            setTimeout(()=>location.href='../../index.php',2000);
        }
    }, 'json');
});

const dropdownToggle = document.getElementById('dropdownToggle');
const dropdownMenu = document.getElementById('dropdownMenu');
dropdownToggle?.addEventListener('click', function(e) {
    e.preventDefault();
    dropdownMenu.classList.toggle('show');
});
document.addEventListener('click', function(e) {
    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});


</script>

</body>
</html>
