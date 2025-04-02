document.addEventListener('DOMContentLoaded', function() {
    // Função para iniciar o carrossel
    function startCarousel() {
        const items = document.querySelectorAll('.carousel-item');
        const prevButton = document.querySelector('.carousel-prev');
        const nextButton = document.querySelector('.carousel-next');
        let currentIndex = 0;
        let interval;

        // Oculta todos os itens
        function hideItems() {
            items.forEach(item => {
                item.style.display = 'none';
            });
        }

        // Mostra o item atual
        function showCurrentItem() {
            hideItems();
            items[currentIndex].style.display = 'flex'; // Usar flex para centralizar o conteúdo
        }

        // Avança para o próximo item
        function nextItem() {
            currentIndex = (currentIndex + 1) % items.length; // Loop de volta ao primeiro item
            showCurrentItem();
        }

        // Volta para o item anterior
        function prevItem() {
            currentIndex = (currentIndex - 1 + items.length) % items.length; // Loop de volta ao último item
            showCurrentItem();
        }

        // Inicia o carrossel automaticamente
        function startAutoSlide() {
            interval = setInterval(nextItem, 5000); // Muda de item a cada 5 segundos
        }

        // Pausa o carrossel automático
        function stopAutoSlide() {
            clearInterval(interval);
        }

        // Adiciona eventos aos botões
        if (prevButton && nextButton) {
            prevButton.addEventListener('click', function() {
                stopAutoSlide(); // Pausa o carrossel ao clicar no botão
                prevItem();
                startAutoSlide(); // Reinicia o carrossel automático
            });

            nextButton.addEventListener('click', function() {
                stopAutoSlide(); // Pausa o carrossel ao clicar no botão
                nextItem();
                startAutoSlide(); // Reinicia o carrossel automático
            });
        }

        // Configuração inicial
        showCurrentItem();
        startAutoSlide();
    }

    // Inicia o carrossel ao carregar a página
    startCarousel();

    // Adiciona um evento de clique aos botões
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            alert('Botão clicado: ' + this.textContent);
        });
    });
});
