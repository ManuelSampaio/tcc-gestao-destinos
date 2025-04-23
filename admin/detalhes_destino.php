<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once '../app/controllers/DestinoController.php'; 
require_once '../app/controllers/AvaliacaoController.php';

use Config\Database;
use App\Controllers\DestinoController;
use App\Controllers\AvaliacaoController;

// Inicializa conexão com o banco de dados
$database = new Database();
$conn = $database->getConnection();

// Inicializa o controlador de destinos
$destinoController = new DestinoController($conn);
$avaliacaoController = new AvaliacaoController();

try {
    // Inicializa conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();

  // Obtém o ID do destino da URL
$destinoId = $_GET['id'] ?? null;
$destino = $destinoController->obterDetalhes($destinoId);

if (!$destino) {
    die("Destino não encontrado.");
}

// Obter informações completas incluindo a província
$infoCompleta = $destinoController->obterCoordenadas($destinoId);

// Adiciona a informação de localização ao array de destino
if ($infoCompleta && isset($infoCompleta['nome_provincia'])) {
    $destino['localizacao'] = $infoCompleta['nome_provincia'];
}

// Obtém ou busca as coordenadas do destino
$coordenadas = $destinoController->buscarEArmazenarCoordenadas($destinoId);

    // Obtém imagens do destino
    $imagens = $destinoController->obterImagensDestino($destinoId);
    
    // Se não houver imagens adicionais, usar pelo menos a imagem principal
    if (empty($imagens)) {
        $imagens = [['caminho' => $destino['imagem'] ?? 'default.jpg', 'descricao' => $destino['nome_destino']]];
    }

    // Obtém dados de avaliações
    $dadosAvaliacoes = $avaliacaoController->obterAvaliacoesDestino($destinoId);
    $avaliacoes = $dadosAvaliacoes['avaliacoes'];
    $mediaAvaliacoes = $dadosAvaliacoes['media'];
    $totalAvaliacoes = $dadosAvaliacoes['total'];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
    <style>
        :root {
            --primary-color: #004d40;
            --secondary-color: #ff9800;
            --accent-color: #f44336;
            --light-bg: #f5f5f5;
            --dark-bg: #212121;
            --text-dark: #333333;
            --text-light: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background-color: var(--primary-color);
            color: var(--text-light);
            text-align: center;
            padding: 40px 20px;
            position: relative;
            box-shadow: var(--shadow);
            background-image: linear-gradient(rgba(0, 77, 64, 0.9), rgba(0, 77, 64, 0.8)), url('../assets/images/banner1.jpg');
            background-size: cover;
            background-position: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease;
        }

        /* Main content */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Galeria de Imagens */
        .galeria-container {
            position: relative;
            margin-bottom: 30px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .galeria-imagens {
            position: relative;
            height: 400px;
            background-color: #000;
        }

        .galeria-imagem {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .galeria-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 2;
            transition: var(--transition);
        }

        .galeria-nav:hover {
            background-color: var(--secondary-color);
        }

        .galeria-prev {
            left: 15px;
        }

        .galeria-next {
            right: 15px;
        }

        .galeria-miniaturas {
            display: flex;
            gap: 10px;
            padding: 15px;
            background-color: #fff;
            overflow-x: auto;
        }

        .galeria-miniatura {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            opacity: 0.6;
            transition: var(--transition);
        }

        .galeria-miniatura:hover, .galeria-miniatura.active {
            opacity: 1;
            box-shadow: 0 0 0 2px var(--secondary-color);
        }

        /* Informações do Destino */
        .destino-info {
            background-color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .destino-info h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .destino-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .destino-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .destino-meta-item i {
            color: var(--secondary-color);
        }

        /* Abas de informação */
        .info-adicional {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .info-tabs {
            display: flex;
            background-color: #f2f2f2;
        }

        .info-tab {
            padding: 15px 20px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .info-tab:hover, .info-tab.active {
            background-color: var(--primary-color);
            color: white;
        }

        .info-content {
            padding: 25px;
            display: none;
        }

        .info-content.active {
            display: block;
        }

        .location-container {
            text-align: center;
        }

        .location-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .location-name {
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .location-coords {
            margin-top: 10px;
            color: #666;
        }

        /* Avaliações */
        .reviews {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .avaliacoes-cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .avaliacoes-cabecalho h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .media-avaliacoes {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .estrelas {
            color: #FFD700;
        }

        .avaliacoes-acoes {
            margin-bottom: 30px;
        }

        .nova-avaliacao {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .nova-avaliacao h4 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .avaliacao-nota {
            margin-bottom: 15px;
        }

        .selecao-estrelas {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }

        .estrela {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .estrela-vazia:hover, .estrela:hover {
            color: #FFD700;
        }

        .avaliacao-comentario textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-top: 5px;
        }

        .nova-avaliacao button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            transition: var(--transition);
        }

        .nova-avaliacao button:hover {
            background-color: var(--secondary-color);
        }

        .login-avaliacao {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
        }

        .login-avaliacao a {
            color: var(--primary-color);
            font-weight: bold;
            text-decoration: none;
        }

        .login-avaliacao a:hover {
            color: var(--secondary-color);
        }

        .mensagem-sucesso, .mensagem-erro {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: none;
        }

        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
        }

        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
        }

        .lista-avaliacoes {
            margin-top: 20px;
        }

        .review {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .review:last-child {
            border-bottom: none;
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
            color: #888;
            font-size: 0.9rem;
        }

        .review-comentario {
            margin-top: 10px;
        }

        .sem-avaliacoes {
            text-align: center;
            padding: 20px;
            color: #888;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .lightbox-close:hover {
            color: var(--secondary-color);
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .lightbox-nav:hover {
            background-color: var(--secondary-color);
        }

        .lightbox-prev {
            left: -60px;
        }

        .lightbox-next {
            right: -60px;
        }

        /* Botão voltar fixo */
        .fixed-back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: var(--hover-shadow);
            transition: var(--transition);
            z-index: 100;
            text-decoration: none;
        }

        .fixed-back-btn:hover {
            background-color: var(--secondary-color);
            transform: scale(1.1);
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .galeria-imagens {
                height: 300px;
            }
            
            .destino-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .lightbox-prev {
                left: 10px;
            }
            
            .lightbox-next {
                right: 10px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            .galeria-imagens {
                height: 250px;
            }
            
            .galeria-miniatura {
                width: 60px;
                height: 45px;
            }
            
            .fixed-back-btn {
                width: 45px;
                height: 45px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Detalhes do Destino</h1>
    </header>
    <main>
        <section class="destino-detalhes">
            <!-- Galeria de imagens -->
            <div class="galeria-container">
                <div class="galeria-imagens">
                    <?php foreach ($imagens as $index => $imagem): ?>
                        <img src="../uploads/<?= htmlspecialchars($imagem['caminho']) ?>" 
                             alt="<?= htmlspecialchars($imagem['descricao'] ?? 'Imagem de ' . $destino['nome_destino']) ?>" 
                             data-index="<?= $index ?>"
                             class="galeria-imagem <?= $index === 0 ? 'active' : 'hidden' ?>"
                             style="<?= $index === 0 ? '' : 'display: none;' ?>"
                             onclick="openLightbox(<?= $index ?>)">
                    <?php endforeach; ?>
                </div>
                <div class="galeria-nav galeria-prev" onclick="navegarGaleria(-1)">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="galeria-nav galeria-next" onclick="navegarGaleria(1)">
                    <i class="fas fa-chevron-right"></i>
                </div>
                <div class="galeria-miniaturas">
                    <?php foreach ($imagens as $index => $imagem): ?>
                        <img src="../uploads/<?= htmlspecialchars($imagem['caminho']) ?>" 
                             alt="Miniatura" 
                             class="galeria-miniatura <?= $index === 0 ? 'active' : '' ?>"
                             onclick="mostrarImagem(<?= $index ?>)">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="destino-info">
                <h2><?= htmlspecialchars($destino['nome_destino']) ?></h2>
                
                <div class="destino-meta">
                    <div class="destino-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($destino['nome_provincia'] ?? 'Localização não disponível') ?></span>
                    </div>
                    <div class="destino-meta-item">
                        <i class="fas fa-star"></i>
                        <span><?= number_format($mediaAvaliacoes, 1) ?> (<?= $totalAvaliacoes ?> avaliações)</span>
                    </div>
                </div>
                
                <p><strong>Descrição:</strong> <?= htmlspecialchars($destino['descricao'] ?? 'Descrição não disponível') ?></p>
            </div>
            
            <!-- Informações adicionais com abas -->
            <div class="info-adicional">
                <div class="info-tabs">
                    <div class="info-tab active" data-tab="localizacao">Localização</div>
                </div>
                
                <div class="info-content active" id="localizacao">
                    <div class="location-container">
                        <div class="location-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Localização</h3>
                        <p class="location-name"><?= htmlspecialchars($destino['localizacao'] ?? 'Localização não disponível') ?></p>
                        
                        <!-- Contêiner para o mapa -->
                        <div id="location-map" style="height: 350px; margin-top: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"></div>
                        
                        <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                            <div class="location-coords">
                                <small>
                                    <i class="fas fa-location-arrow"></i> 
                                    Coordenadas: <?= $destino['latitude'] ?>, <?= $destino['longitude'] ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                    <?php if(isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id_usuario'])): ?>
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
        </section>
        
        <!-- Lightbox para visualização de imagens -->
        <div class="lightbox" id="lightbox">
            <div class="lightbox-content">
                <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
                <img src="" alt="Imagem ampliada" class="lightbox-img" id="lightbox-img">
                <div class="lightbox-nav lightbox-prev" onclick="mudarImagemLightbox(-1)">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="lightbox-nav lightbox-next" onclick="mudarImagemLightbox(1)">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <a href="listar_destino.php" class="fixed-back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <script>
    // Variáveis para controle da galeria de imagens
    let imagemAtual = 0;
    const totalImagens = <?= count($imagens) ?>;

    // Funções para navegação na galeria principal
    function mostrarImagem(index) {
        // Esconde todas as imagens
        document.querySelectorAll('.galeria-imagem').forEach(img => {
            img.style.display = 'none';
            img.classList.remove('active');
        });
        
        // Mostra a imagem selecionada
        const imagemSelecionada = document.querySelector(`.galeria-imagem[data-index="${index}"]`);
        if (imagemSelecionada) {
            imagemSelecionada.style.display = 'block';
            imagemSelecionada.classList.add('active');
        }
        
        // Atualiza miniaturas
        document.querySelectorAll('.galeria-miniatura').forEach((miniatura, idx) => {
            if (idx === index) {
                miniatura.classList.add('active');
            } else {
                miniatura.classList.remove('active');
            }
        });
        
        imagemAtual = index;
    }

    function navegarGaleria(direcao) {
        let novoIndex = imagemAtual + direcao;
        
        // Verifica limites
        if (novoIndex < 0) {
            novoIndex = totalImagens - 1;
        } else if (novoIndex >= totalImagens) {
            novoIndex = 0;
        }
        
        mostrarImagem(novoIndex);
    }

    // Funções para controle do lightbox
    let lightboxImagemAtual = 0;

    function openLightbox(index) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const imagemSelecionada = document.querySelector(`.galeria-imagem[data-index="${index}"]`);
        
        if (lightbox && lightboxImg && imagemSelecionada) {
            lightboxImg.src = imagemSelecionada.src;
            lightbox.style.display = 'flex';
            lightboxImagemAtual = index;
            
            // Desabilita o scroll da página
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        if (lightbox) {
            lightbox.style.display = 'none';
            
            // Habilita o scroll da página novamente
            document.body.style.overflow = 'auto';
        }
    }

    function mudarImagemLightbox(direcao) {
    let novoIndex = lightboxImagemAtual + direcao;
    
    // Verifica limites
    if (novoIndex < 0) {
        novoIndex = totalImagens - 1;
    } else if (novoIndex >= totalImagens) {
        novoIndex = 0;
    }
    
    const lightboxImg = document.getElementById('lightbox-img');
    const imagemSelecionada = document.querySelector(`.galeria-imagem[data-index="${novoIndex}"]`);
    
    if (lightboxImg && imagemSelecionada) {
        lightboxImg.src = imagemSelecionada.src;
        lightboxImagemAtual = novoIndex;
    }
}

// Funções para avaliações
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização do mapa se houver coordenadas
    const latitude = <?= !empty($destino['latitude']) ? $destino['latitude'] : 'null' ?>;
    const longitude = <?= !empty($destino['longitude']) ? $destino['longitude'] : 'null' ?>;
    
    if (latitude && longitude) {
        const map = L.map('location-map').setView([latitude, longitude], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Adiciona um marcador na localização
        L.marker([latitude, longitude])
            .addTo(map)
            .bindPopup("<?= htmlspecialchars($destino['nome_destino']) ?>")
            .openPopup();
    } else {
        // Se não houver coordenadas, mostra uma mensagem
        document.getElementById('location-map').innerHTML = '<div style="text-align: center; padding: 20px;">Coordenadas não disponíveis para este destino.</div>';
    }
    
    // Sistema de avaliação com estrelas
    const estrelas = document.querySelectorAll('#selecao-estrelas .estrela');
    const notaInput = document.getElementById('nota-input');
    
    if (estrelas.length > 0 && notaInput) {
        estrelas.forEach(estrela => {
            estrela.addEventListener('click', function() {
                const valor = parseInt(this.getAttribute('data-valor'));
                
                // Atualiza o valor do input hidden
                notaInput.value = valor;
                
                // Atualiza o visual das estrelas
                estrelas.forEach((e, index) => {
                    if (index < valor) {
                        e.className = 'fas fa-star estrela';
                    } else {
                        e.className = 'far fa-star estrela estrela-vazia';
                    }
                });
            });
            
            // Efeito hover
            estrela.addEventListener('mouseover', function() {
                const valor = parseInt(this.getAttribute('data-valor'));
                
                estrelas.forEach((e, index) => {
                    if (index < valor) {
                        e.classList.add('hover');
                    }
                });
            });
            
            estrela.addEventListener('mouseout', function() {
                estrelas.forEach(e => {
                    e.classList.remove('hover');
                });
            });
        });
    }
    
    // Submissão do formulário de avaliação
    const formularioAvaliacao = document.getElementById('formulario-avaliacao');
    
    if (formularioAvaliacao) {
        formularioAvaliacao.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            
            // Verifica se o usuário selecionou uma nota
            const nota = formData.get('nota');
            if (!nota || nota == '0') {
                mostrarMensagem('Por favor, selecione uma nota para o destino.', false);
                return;
            }
            
            // Envia a avaliação via Ajax
            fetch('../app/actions/salvar_avaliacao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensagem(data.message, true);
                    // Recarrega a página após um curto período para mostrar a avaliação atualizada
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    mostrarMensagem(data.message, false);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarMensagem('Ocorreu um erro ao processar sua avaliação. Tente novamente.', false);
            });
        });
    }
    
    // Função para mostrar mensagens de sucesso ou erro
    function mostrarMensagem(mensagem, sucesso) {
        const mensagemSucesso = document.getElementById('mensagem-sucesso');
        const mensagemErro = document.getElementById('mensagem-erro');
        
        if (sucesso) {
            mensagemSucesso.innerText = mensagem;
            mensagemSucesso.style.display = 'block';
            mensagemErro.style.display = 'none';
            
            setTimeout(() => {
                mensagemSucesso.style.display = 'none';
            }, 5000);
        } else {
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemSucesso.style.display = 'none';
            
            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 5000);
        }
    }
    
    // Event listeners para o lightbox
    document.addEventListener('keydown', function(event) {
        if (document.getElementById('lightbox').style.display === 'flex') {
            if (event.key === 'Escape') {
                closeLightbox();
            } else if (event.key === 'ArrowLeft') {
                mudarImagemLightbox(-1);
            } else if (event.key === 'ArrowRight') {
                mudarImagemLightbox(1);
            }
        }
    });
    
    // Fecha o lightbox se clicar fora da imagem
    document.getElementById('lightbox').addEventListener('click', function(event) {
        if (event.target === this) {
            closeLightbox();
        }
    });
    
    // Gerenciamento de abas
    const infoPanels = document.querySelectorAll('.info-tab');
    
    infoPanels.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove classe active de todas as abas
            document.querySelectorAll('.info-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.info-content').forEach(c => c.classList.remove('active'));
            
            // Adiciona classe active na aba clicada
            this.classList.add('active');
            
            // Mostra o conteúdo correspondente
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    
});


</script>
</body>
</html>