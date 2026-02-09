$(document).on("click", ".mais", function () {
    let id = $(this).data("id");
    $.post("src/ajax/alterar_quantidade.php", { id: id, acao: "mais" }, function () {
        location.reload();
    });
});

$(document).on("click", ".menos", function () {
    let id = $(this).data("id");
    $.post("src/ajax/alterar_quantidade.php", { id: id, acao: "menos" }, function () {
        location.reload();
    });
});

$(document).on("click", ".remover", function () {
    let id = $(this).data("id");
    if (confirm("Deseja remover este produto do carrinho?")) {
        $.post("src/ajax/remover_item.php", { id: id }, function () {
            location.reload();
        });
    }
});

// Ao alterar quantidade
$(document).on('change', '.qtd', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    let qtd = $(this).val();
    $.post('./src/update_carrinho.php', {id, qtd}, function(data) {
        if (data.success) {
            linha.find('.subtotal').fadeOut(200, function() {
                $(this).text('R$ ' + data.subtotal).fadeIn(200);
            });
            $('#total').fadeOut(200, function() {
                $(this).text('R$ ' + data.total).fadeIn(200);
            });
        }
    }, 'json');
});

// Ao remover item
$(document).on('click', '.remover', function() {
    let linha = $(this).closest('tr');
    let id = linha.data('id');
    $.post('./src/remove_item.php', {id}, function(data) {
        if (data.success) {
            linha.fadeOut(300, function() { $(this).remove(); });
            $('#total').fadeOut(200, function() {
                $(this).text('R$ ' + data.total).fadeIn(200);
            });
        }
    }, 'json');
});

document.querySelectorAll('.btn-comprar').forEach(botao => {
    botao.addEventListener('click', function() {
        const produto_id = this.dataset.id;
        fetch('./src/pages/adicionar_carrinho.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'produto_id=' + produto_id
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Produto adicionado ao carrinho!");
            } else if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert("Erro ao adicionar ao carrinho.");
            }
        })
        .catch(() => alert("Erro na comunicação com o servidor."));
    });
});