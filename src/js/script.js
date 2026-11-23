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


