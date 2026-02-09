<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Consulta carrinhos com detalhes dos usuários
$sql = "SELECT c.id as carrinho_id, u.nome as usuario_nome, p.nome as produto_nome, p.preco, c.quantidade, (p.preco * c.quantidade) as subtotal
        FROM carrinho c
        JOIN usuarios u ON c.usuario_id = u.id
        JOIN produtos p ON c.produto_id = p.id
        ORDER BY u.nome ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Carrinhos</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="header">
        <h1>Gerenciar Carrinhos</h1>
        <a href="dashboard.php"><button class="btn-purple">Voltar</button></a>
    </div>

    <div class="filters">
        <p>Veja e gerencie os itens nos carrinhos dos clientes.</p>
    </div>

    <table id="tabela">
        <tr>
            <th>Usuário</th>
            <th>Produto</th>
            <th>Preço</th>
            <th>Quantidade</th>
            <th>Subtotal</th>
            <th>Ação</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr data-id="<?= $row['carrinho_id'] ?>">
                    <td><?= htmlspecialchars($row['usuario_nome']) ?></td>
                    <td><?= htmlspecialchars($row['produto_nome']) ?></td>
                    <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
                    <td><input type="number" class="qtd" value="<?= $row['quantidade'] ?>" min="1"></td>
                    <td class="subtotal">R$ <?= number_format($row['subtotal'], 2, ',', '.') ?></td>
                    <td><button class="remover">Remover</button></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Nenhum item encontrado.</td></tr>
        <?php endif; ?>
    </table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Atualizar quantidade
$(document).on('change', '.qtd', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    let qtd = $(this).val();
    $.post('update_carrinho_admin.php', {id, qtd}, function(data) {
        if (data.success) {
            linha.find('.subtotal').fadeOut(200, function() {
                $(this).text('R$ ' + data.subtotal).fadeIn(200);
            });
        }
    }, 'json');
});

// Remover item
$(document).on('click', '.remover', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    $.post('remove_carrinho_admin.php', {id}, function(data) {
        if (data.success) {
            linha.fadeOut(300, function() { $(this).remove(); });
        }
    }, 'json');
});
</script>
</body>
</html>
