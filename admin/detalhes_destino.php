<?php 
session_start();
require_once __DIR__ . '/../config/database.php';
require_once '../app/controllers/DestinoController.php'; 
require_once '../app/controllers/AvaliacaoController.php';

use Config\Database;
use App\Controllers\DestinoController;
use App\Controllers\AvaliacaoController;

try {
    // Inicializa conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();

    // Inicializa o controlador de destinos
    $destinoController = new DestinoController($conn);
    $avaliacaoController = new AvaliacaoController();

    // Obtém o ID do destino da URL
    $destinoId = $_GET['id'] ?? null;
    $destino = $destinoController->obterDetalhes($destinoId);

    if (!$destino) {
        die("Destino não encontrado.");
    }

    // Obtém dados de avaliações
    $dadosAvaliacoes = $avaliacaoController->obterAvaliacoesDestino($destinoId);
    $avaliacoes = $dadosAvaliacoes['avaliacoes'];
    $mediaAvaliacoes = $dadosAvaliacoes['media'];
    $totalAvaliacoes = $dadosAvaliacoes['total'];
    $estatisticas = $dadosAvaliacoes['estatisticas'];
    $avaliacaoUsuario = $dadosAvaliacoes['avaliacao_usuario'];
} catch (Exception $e) {
    die("Erro ao carregar os detalhes do destino: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Destino - <?= htmlspecialchars($destino['nome_destino'] ?? 'Destino') ?></title>
    <link rel="stylesheet" href="../assets/css/avaliacao.css">
    <script src="../assets/js/avaliacao.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .header {
            background-color: #1e90ff;
            color: white;
            text-align: center;
            padding: 2rem 0;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .destino-detalhes {
            max-width: 1200px;
            margin: 2rem auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 1.5rem;
        }

        .destino-detalhes img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .destino-info {
            padding: 1.5rem;
        }

        .destino-info h2 {
            font-size: 2rem;
            margin: 0 0 1rem;
        }

        .destino-info p {
            margin: 0.5rem 0;
            line-height: 1.6;
            color: #555;
        }

        .map-container {
            margin-top: 2rem;
        }

        iframe {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 10px;
        }

        /* Estilos para avaliações */
        .reviews {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .reviews-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .reviews h3 {
            font-size: 1.5rem;
            margin-bottom: 0;
        }

        .media-avaliacoes {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .estatisticas-avaliacoes {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .barra-estatistica {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .barra-label {
            min-width: 50px;
        }

        .barra-container {
            flex-grow: 1;
            background-color: #e9ecef;
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
        }

        .barra-preenchida {
            height: 100%;
            background-color: #1e90ff;
        }

        .barra-percentual {
            min-width: 40px;
            text-align: right;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .review {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .review:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .review-autor {
            font-weight: bold;
            color: #333;
        }

        .review-data {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .estrelas {
            color: #FFD700;
            margin-bottom: 0.5rem;
        }

        .estrela {
            cursor: pointer;
        }

        .estrela-vazia {
            color: #ddd;
        }

        .review p {
            margin: 0;
            color: #555;
            line-height: 1.5;
        }

        /* Estilos para o formulário de avaliação */
        .avaliacoes-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.avaliacoes-cabecalho {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.avaliacoes-cabecalho h3 {
    margin: 0;
    font-size: 1.5rem;
}

.media-avaliacoes {
    display: flex;
    align-items: center;
}

.estrelas {
    color: #FFD700;
    margin-right: 10px;
}

.pontuacao-media {
    font-size: 1rem;
}

.avaliacoes-acoes {
    margin-bottom: 30px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 5px;
}

.login-avaliacao {
    text-align: center;
    padding: 10px;
}

.nova-avaliacao h4 {
    margin-top: 0;
}

.avaliacao-nota {
    margin-bottom: 15px;
}

.selecao-estrelas {
    display: inline-block;
    margin-left: 10px;
    cursor: pointer;
}

.avaliacao-comentario textarea {
    width: 100%;
    padding: 10px;
    height: 100px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.nova-avaliacao button {
    margin-top: 10px;
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.lista-avaliacoes h4 {
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.review {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 5px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.review-autor {
    font-weight: bold;
}

.review-data {
    color: #777;
    font-size: 0.9rem;
}

.review-comentario {
    margin-top: 10px;
    line-height: 1.5;
}

.sem-avaliacoes {
    font-style: italic;
    color: #777;
    text-align: center;
    padding: 20px;
}

        textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        button[type="submit"] {
            padding: 0.75rem 1.5rem;
            background-color: #1e90ff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            align-self: flex-start;
        }

        button[type="submit"]:hover {
            background-color: #1c7ed6;
        }

        .back-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.5rem 1rem;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: none;
        }

        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Detalhes do Destino</h1>
    </header>
    <main>
        <section class="destino-detalhes">
            <img src="../uploads/<?= htmlspecialchars($destino['imagem'] ?? 'default.jpg') ?>" alt="Imagem de <?= htmlspecialchars($destino['nome_destino']) ?>">
            <div class="destino-info">
                <h2><?= htmlspecialchars($destino['nome_destino']) ?></h2>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($destino['descricao'] ?? 'Descrição não disponível') ?></p>
                <p><strong>Localização:</strong> <?= htmlspecialchars($destino['localizacao'] ?? 'Não disponível') ?></p>
                <p><strong>Atividades:</strong> <?= htmlspecialchars($destino['atividades'] ?? 'Informação não disponível') ?></p>
                <a href="listar_destino.php" class="back-btn">Voltar</a>
            </div>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed/v1/place?key=SUA_CHAVE_API&q=<?= urlencode($destino['localizacao'] ?? 'Angola') ?>"
                    allowfullscreen>
                </iframe>
            </div>
        </section>
        <section class="reviews">
        <div class="avaliacoes-container">
    <div class="avaliacoes-cabecalho">
        <h3>Avaliações</h3>
        <div class="media-avaliacoes">
            <div class="estrelas">
                <?php 
                $mediaArredondada = round($mediaAvaliacoes);
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $mediaArredondada) {
                        echo '<i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
            </div>
            <div class="pontuacao-media"><strong><?= number_format($mediaAvaliacoes, 1) ?></strong> de 5 (<?= $totalAvaliacoes ?> avaliações)</div>
        </div>
    </div>

    <div class="avaliacoes-acoes">
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <div class="nova-avaliacao">
                <h4><?= $avaliacaoUsuario ? 'Editar sua avaliação' : 'Avaliar este destino' ?></h4>
                <div class="mensagem-sucesso" id="mensagem-sucesso"></div>
                <div class="mensagem-erro" id="mensagem-erro"></div>
                <form id="formulario-avaliacao">
                    <input type="hidden" name="id_destino" value="<?= $destinoId ?>">
                    <div class="avaliacao-nota">
                        <label>Sua avaliação:</label>
                        <div class="selecao-estrelas" id="selecao-estrelas">
                            <?php 
                            $notaUsuario = $avaliacaoUsuario ? $avaliacaoUsuario['nota'] : 0;
                            for ($i = 1; $i <= 5; $i++) {
                                $classe = $i <= $notaUsuario ? 'fas fa-star estrela' : 'far fa-star estrela estrela-vazia';
                                echo "<i class=\"$classe\" data-valor=\"$i\"></i>";
                            }
                            ?>
                        </div>
                        <input type="hidden" name="nota" id="nota-input" value="<?= $notaUsuario ?>">
                    </div>
                    <div class="avaliacao-comentario">
                        <label>Comentário:</label>
                        <textarea name="comentario" maxlength="500" placeholder="Conte sua experiência..."><?= htmlspecialchars($avaliacaoUsuario['comentario'] ?? '') ?></textarea>
                    </div>
                    <button type="submit"><?= $avaliacaoUsuario ? 'Atualizar Avaliação' : 'Enviar Avaliação' ?></button>
                </form>
            </div>
        <?php else: ?>
            <div class="login-avaliacao">
                <p>Faça <a href="login.php">login</a> para avaliar este destino.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="lista-avaliacoes">
        <?php if (!empty($avaliacoes)): ?>
            <h4>Comentários dos usuários</h4>
            <?php foreach ($avaliacoes as $avaliacao): ?>
                <div class="review">
                    <div class="review-header">
                        <div class="review-autor"><?= htmlspecialchars($avaliacao['nome_usuario']) ?></div>
                        <div class="review-data"><?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?></div>
                    </div>
                    <div class="estrelas">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avaliacao['nota']) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <p class="review-comentario"><?= htmlspecialchars($avaliacao['comentario'] ?? 'Sem comentário.') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="sem-avaliacoes">Nenhuma avaliação disponível para este destino.</p>
        <?php endif; ?>
    </div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecionar elementos
        const estrelas = document.querySelectorAll('.selecao-estrelas .estrela');
        const notaInput = document.getElementById('nota-input');
        const formulario = document.getElementById('formulario-avaliacao');
        const mensagemSucesso = document.getElementById('mensagem-sucesso');
        const mensagemErro = document.getElementById('mensagem-erro');

        // Configurar seleção de estrelas
        estrelas.forEach(estrela => {
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
    </script>
</body>
</html>