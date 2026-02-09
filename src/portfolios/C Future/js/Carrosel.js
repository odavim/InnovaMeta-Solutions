var currentIndex = 0;
var totalSlides = document.querySelectorAll('.carousel-item').length;
var intervalId;

function showSlides() {
  var container = document.querySelector('.carousel-container');
  var itemWidth = document.querySelector('.carousel-item').offsetWidth;

  container.style.transform = 'translateX(' + (-currentIndex * itemWidth) + 'px)';
}

function nextSlide() {
  currentIndex = (currentIndex + 1) % totalSlides;
  showSlides();
}

function prevSlide() {
  currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
  showSlides();
}

function startAutoSlide() {
  intervalId = setInterval(function() {
    nextSlide();
  }, 5000); // 10 segundos
}

function stopAutoSlide() {
  clearInterval(intervalId);
}

// Inicia o carrossel automático ao carregar a página
startAutoSlide();

// Pausa o carrossel automático quando o mouse entra no carrossel
document.getElementById('carousel').addEventListener('mouseenter', stopAutoSlide);

// Reinicia o carrossel automático quando o mouse sai do carrossel
document.getElementById('carousel').addEventListener('mouseleave', startAutoSlide);

document.addEventListener("DOMContentLoaded", function() {
  var carrossel = document.querySelector('.carrossel');
  var slides = document.querySelector('.slides');
  var setaEsquerda = document.querySelector('.seta-esquerda');
  var setaDireita = document.querySelector('.seta-direita');
  var larguraSlide = document.querySelector('.slide').offsetWidth;

  setaDireita.addEventListener('click', function() {
      slides.style.transform = 'translateX(' + (-larguraSlide) + 'px)';
      setaEsquerda.style.display = 'block';
      setaDireita.style.display = 'none';
  });

  setaEsquerda.addEventListener('click', function() {
      slides.style.transform = 'translateX(0)';
      setaEsquerda.style.display = 'none';
      setaDireita.style.display = 'block';
  });
});