// ScrollReveal config
const sr = ScrollReveal({
  distance: '50px',
  duration: 800,
  easing: 'ease-out',
  origin: 'bottom',
  reset: true // mantém animando ao rolar

});

sr.reveal('.section-container', { interval: 100 });
sr.reveal('.header__content', { origin: 'top' });
sr.reveal('.banner-card', { interval: 200 });
sr.reveal('.experiencia-container', { interval: 150 });
sr.reveal('.preco-card-dark', { interval: 200 });
sr.reveal('nav', { origin: 'top' });

// Menu hambúrguer
const menuToggle = document.getElementById('menu-toggle');
const menu = document.getElementById('menu');

menuToggle?.addEventListener('click', () => {
  menu.classList.toggle('show');
});

// Adicionar produto ao carrinho
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
                alert("Erro ao adicionar produto.");
            }
        })
        .catch(() => alert("Erro na comunicação com o servidor."));
    });
});

