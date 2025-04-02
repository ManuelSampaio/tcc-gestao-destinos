/**
 * Script para interatividade do sistema de avaliações com estrelas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Selecionar elementos
    const estrelas = document.querySelectorAll('.selecao-estrelas .estrela');
    const notaInput = document.getElementById('nota-input');
    const formulario = document.getElementById('formulario-avaliacao');
    const mensagemSucesso = document.getElementById('mensagem-sucesso');
    const mensagemErro = document.getElementById('mensagem-erro');

    // Configurar seleção de estrelas
    estrelas.forEach(estrela => {
        // Evento ao passar o mouse por cima da estrela
        estrela.addEventListener('mouseover', function() {
            const valor = this.getAttribute('data-valor');
            
            // Destaca estrelas até a atual
            estrelas.forEach(e => {
                const valorEstrela = e.getAttribute('data-valor');
                if (valorEstrela <= valor) {
                    e.classList.remove('far', 'estrela-vazia');
                    e.classList.add('fas');
                } else {
                    e.classList.remove('fas');
                    e.classList.add('far', 'estrela-vazia');
                }
            });
        });

        // Evento ao tirar o mouse de cima da estrela
        estrela.addEventListener('mouseout', function() {
            const valorSelecionado = notaInput.value;
            
            // Restaura ao estado selecionado
            estrelas.forEach(e => {
                const valorEstrela = e.getAttribute('data-valor');
                if (valorEstrela <= valorSelecionado) {
                    e.classList.remove('far', 'estrela-vazia');
                    e.classList.add('fas');
                } else {
                    e.classList.remove('fas');
                    e.classList.add('far', 'estrela-vazia');
                }
            });
        });

        // Evento ao clicar na estrela
        estrela.addEventListener('click', function() {
            const valor = this.getAttribute('data-valor');
            notaInput.value = valor;
            
            // Atualiza visuais
            estrelas.forEach(e => {
                const valorEstrela = e.getAttribute('data-valor');
                if (valorEstrela <= valor) {
                    e.classList.remove('far', 'estrela-vazia');
                    e.classList.add('fas');
                } else {
                    e.classList.remove('fas');
                    e.classList.add('far', 'estrela-vazia');
                }
            });
        });
    });

    // Configurar envio do formulário
    formulario?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar se uma nota foi selecionada
        if (!notaInput.value) {
            mensagemErro.textContent = 'Por favor, selecione uma classificação por estrelas.';
            mensagemErro.style.display = 'block';
            mensagemSucesso.style.display = 'none';
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('../app/controllers/processar_avaliacao.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                mensagemSucesso.textContent = data.mensagem;
                mensagemSucesso.style.display = 'block';
                mensagemErro.style.display = 'none';
                
                // Recarrega a página após 2 segundos para mostrar a avaliação atualizada
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                mensagemErro.textContent = data.mensagem;
                mensagemErro.style.display = 'block';
                mensagemSucesso.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mensagemErro.textContent = 'Erro ao enviar avaliação. Tente novamente.';
            mensagemErro.style.display = 'block';
            mensagemSucesso.style.display = 'none';
        });
    });
});