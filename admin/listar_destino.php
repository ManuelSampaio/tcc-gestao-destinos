<?php 
require_once __DIR__ . '/../config/database.php';
require_once '../app/models/DestinoModel.php'; 

use Config\Database;
use App\Models\DestinoModel;

try {
    // Inicializa conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();
    
    // Inicializa o modelo de destinos
    $destinoModel = new DestinoModel($conn);

    // Obter os destinos existentes
    $destinos = $destinoModel->obterDestinos();
    $categorias = $destinoModel->obterCategorias();
} catch (Exception $e) {
    die("Erro ao carregar os dados: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Turísticos de Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylekk.css">
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
            padding: 60px 20px;
            position: relative;
            box-shadow: var(--shadow);
            background-image: linear-gradient(rgba(0, 77, 64, 0.9), rgba(0, 77, 64, 0.8)), url('../assets/images/banner1.jpg');
            background-size: cover;
            background-position: center;
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            animation: fadeInUp 1s ease;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            animation: fadeInUp 1.3s ease;
            opacity: 0.9;
        }

        /* Main content */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: space-between;
            background-color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            animation: fadeInUp 1s ease;
        }

        .search-box, .filter-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box i, .filter-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .search-box input, .filter-box select {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 25px;
            color: var(--text-dark);
            background-color: white;
            transition: var(--transition);
            font-size: 1rem;
        }

        .search-box input:focus, .filter-box select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.2);
        }

        /* Result Counter */
        .result-counter {
            text-align: center;
            margin-bottom: 30px;
            padding: 10px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border-radius: var(--border-radius);
            font-weight: 500;
            box-shadow: var(--shadow);
            animation: fadeIn 1.5s ease;
        }

        /* Destinos Container */
        .destinos-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        /* Destino Card */
        .destino-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .destino-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .destino-card a {
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .imagem-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .imagem-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .destino-card:hover .imagem-container img {
            transform: scale(1.1);
        }

        .card-click-indicator {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background-color: var(--secondary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition);
        }

        .destino-card:hover .card-click-indicator {
            opacity: 1;
            transform: translateY(0);
        }

        .categoria-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
            box-shadow: var(--shadow);
        }

        .destino-card-content {
            padding: 20px;
            flex-grow: 1;
        }

        .destino-card-content h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
        }

        .destino-card-content p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .destino-card-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }

        .destino-card-footer i {
            color: var(--primary-color);
            margin-right: 5px;
        }

        .destino-card-footer span:last-child {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .empty-message {
            text-align: center;
            padding: 50px 20px;
            grid-column: 1/-1;
            color: #666;
        }

        /* Back Button */
        .back-button-fixed {
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
        }

        .back-button-fixed:hover {
            background-color: var(--secondary-color);
            transform: scale(1.1);
        }

        /* Footer */
        .footer {
            background-color: var(--dark-bg);
            color: var(--text-light);
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }

        /* Animations */
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

        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }
            
            .search-filter {
                flex-direction: column;
                gap: 15px;
            }

            .destinos-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .destinos-container {
                grid-template-columns: 1fr;
            }
            
            .back-button-fixed {
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
        <h1>Descubra Angola</h1>
        <p>Explore os destinos mais incríveis do nosso país</p>
    </header>
    <main>
        <div class="search-filter">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search" placeholder="Buscar destinos...">
            </div>
            <div class="filter-box">
    <i class="fas fa-filter"></i>
    <select id="filter">
        <option value="">Todas as Categorias</option>
        <?php foreach ($categorias as $categoria): ?>
            <option value="<?= htmlspecialchars(strtolower($categoria['nome_categoria'])) ?>">
                <?= htmlspecialchars($categoria['nome_categoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
        <div class="result-counter" id="resultCounter"></div>

        <section class="destinos-container">
            <?php if (!empty($destinos)): ?>
                <?php foreach ($destinos as $destino): ?>
                    <div class="destino-card" data-category="<?= htmlspecialchars($destino['categoria'] ?? '') ?>">
                        <!-- Exibição da categoria -->
                        <?php if (!empty($destino['categoria'])): ?>
                            <div class="categoria-badge"><?= htmlspecialchars($destino['categoria']) ?></div>
                        <?php endif; ?>
                        
                        <!-- A imagem e todo o conteúdo estão dentro do link -->
                        <a href="detalhes_destino.php?id=<?= htmlspecialchars($destino['id']) ?>">
                            <div class="imagem-container">
                                <img src="../uploads/<?= !empty($destino['imagem']) ? htmlspecialchars($destino['imagem']) : 'default.jpg' ?>" 
                                    alt="Imagem de <?= htmlspecialchars($destino['nome_destino']) ?>">
                                <!-- Indicador visual de clique -->
                                <div class="card-click-indicator">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                            <div class="destino-card-content">
                                <h3><?= htmlspecialchars($destino['nome_destino']) ?></h3>
                                <p><?= htmlspecialchars($destino['descricao'] ?? 'Descrição não disponível') ?></p>
                            </div>
                            <div class="destino-card-footer">
                                <span class="localizacao">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($destino['localizacao'] ?? 'Não disponível') ?>
                                </span>
                                <span><i class="fas fa-info-circle"></i> Ver detalhes</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">
                    <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; color: #ccc;"></i><br>
                    Nenhum destino cadastrado.
                </p>
            <?php endif; ?>
        </section>

        <!-- Botão voltar fixo -->
        <a href="../admin/index.php" class="back-button-fixed" title="Voltar para Página Principal">
            <i class="fas fa-home"></i>
        </a>
    </main>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Descubra Angola. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Função para atualizar o contador de resultados
        function updateResultCounter() {
            const visibleCards = document.querySelectorAll('.destino-card[style*="display: flex"], .destino-card:not([style*="display"])');
            const totalCards = document.querySelectorAll('.destino-card');
            const counterElement = document.getElementById('resultCounter');
            
            if (visibleCards.length === totalCards.length) {
                counterElement.textContent = `Mostrando todos os ${totalCards.length} destinos`;
            } else {
                counterElement.textContent = `Mostrando ${visibleCards.length} de ${totalCards.length} destinos`;
            }
        }

        // Efeito de animação para os cards
        document.querySelectorAll('.destino-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Busca por texto
        document.getElementById('search').addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.destino-card');

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                const location = card.querySelector('.localizacao').textContent.toLowerCase();
                
                const matchesSearch = title.includes(searchValue) || 
                                    description.includes(searchValue) ||
                                    location.includes(searchValue);
                
                card.style.display = matchesSearch ? 'flex' : 'none';
            });
            
            updateResultCounter();
        });

        // Filtro por categoria
        document.getElementById('filter').addEventListener('change', function () {
            const filterValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.destino-card');

            cards.forEach(card => {
                const category = card.getAttribute('data-category').toLowerCase();
                card.style.display = filterValue === '' || category === filterValue ? 'flex' : 'none';
            });
            
            updateResultCounter();
        });

        // Inicializa o contador de resultados
        document.addEventListener('DOMContentLoaded', function() {
            updateResultCounter();
        });
    </script>
</body>
</html>