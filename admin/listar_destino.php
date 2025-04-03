<?php 
require_once __DIR__ . '/../config/database.php';
require_once '../app/models/DestinoModel.php'; 
require_once '../app/models/CategoriaModel.php'; // Adicionado modelo de categorias

use Config\Database;
use App\Models\DestinoModel;
use App\Models\CategoriaModel; // Importação do modelo de categorias

try {
    // Inicializa conexão com o banco de dados
    $database = new Database();
    $conn = $database->getConnection();
    
    // Inicializa o modelo de destinos
    $destinoModel = new DestinoModel($conn);
    
    // Inicializa o modelo de categorias
    $categoriaModel = new CategoriaModel($conn);

    // Obter os destinos existentes
    $destinos = $destinoModel->obterDestinos();
    
    // Obter todas as categorias
    $categorias = $categoriaModel->obterCategorias();
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            position: relative;
            background-image: linear-gradient(135deg, #1e90ff, #4682b4);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        /* Botão Voltar Fixo */
        .back-button-fixed {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: #1e90ff;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-decoration: none;
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        .back-button-fixed:hover {
            background-color: #4682b4;
            transform: scale(1.1);
            opacity: 1;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .back-button-fixed i {
            font-size: 1.2rem;
        }

        .search-filter {
            max-width: 1200px;
            margin: 1.5rem auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box, .filter-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box i, .filter-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .search-filter input, .search-filter select {
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 30px;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .search-filter input:focus, .search-filter select:focus {
            outline: none;
            border-color: #1e90ff;
            box-shadow: 0 2px 10px rgba(30, 144, 255, 0.2);
        }
        
        /* Botão Limpar Filtros */
        .clear-filter {
            background-color: #f0f0f0;
            color: #666;
            border: none;
            border-radius: 30px;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .clear-filter:hover {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .clear-filter i {
            font-size: 0.8rem;
        }

        .destinos-container {
            max-width: 1200px;
            margin: 2rem auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 0 1rem;
        }

        .destino-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 550px; /* Altura fixa para todos os cards */
            border: 2px solid transparent; /* Borda transparente por padrão */
            position: relative; /* Para posicionar a categoria */
        }

        .destino-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
            border-color: #1e90ff; /* Borda azul ao passar o mouse */
        }

        .destino-card a {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .destino-card .categoria-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(30, 144, 255, 0.9);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .imagem-container {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .destino-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease, filter 0.5s ease;
        }

        .destino-card:hover img {
            transform: scale(1.05);
            filter: brightness(1.1) saturate(1.2); /* Aumenta brilho e saturação ao passar o mouse */
        }

        .card-click-indicator {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .card-click-indicator i {
            color: #1e90ff;
            font-size: 1.2rem;
        }

        .destino-card:hover .card-click-indicator {
            opacity: 1;
            transform: scale(1.1);
        }

        .destino-card-content {
            padding: 1.5rem;
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .destino-card-content h3 {
            font-size: 1.5rem;
            margin: 0 0 0.8rem;
            color: #1e90ff;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.8rem;
        }

        .destino-card-content p {
            margin: 0;
            font-size: 0.95rem;
            color: #555;
            display: -webkit-box;
            -webkit-line-clamp: 6; /* Limita a 6 linhas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis; /* Adiciona "..." ao final do texto truncado */
            line-height: 1.6;
        }

        .destino-card-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            background: linear-gradient(to right, #f5f5f5, #e0f0ff);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            border-top: 1px solid #eee;
        }

        .destino-card-footer .localizacao {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .destino-card-footer .localizacao i {
            color: #1e90ff;
        }

        .destino-card:hover .destino-card-footer {
            background: linear-gradient(to right, #e0f0ff, #c9e3ff);
        }

        .empty-message {
            text-align: center;
            font-size: 1.2rem;
            color: #999;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            grid-column: 1 / -1;
            display: none; /* Inicialmente oculto */
        }

        .footer {
            text-align: center;
            background: linear-gradient(135deg, #1e90ff, #4682b4);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 5px solid rgba(255, 255, 255, 0.2);
        }

        .footer p {
            margin: 0;
        }

        /* Mensagem de contagem de resultados */
        .result-counter {
            text-align: center;
            margin: 1rem auto;
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Indicador de filtro ativo */
        .filter-active {
            background-color: #e0f0ff;
            border: 1px solid #1e90ff;
        }
        
        /* Indicador visual para categoria selecionada */
        .filter-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background-color: #1e90ff;
            border-radius: 50%;
            display: none;
        }

        /* Destaque para destinos populares */
        .destino-card.popular::before {
            content: "Popular";
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Animação de carregamento da página */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .destino-card {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Animação para quando não há resultados */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .no-results-shake {
            animation: shake 0.8s ease;
        }
        
        /* Tags de categoria */
        .categoria-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .categoria-tag {
            background-color: #f0f8ff;
            color: #1e90ff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            border: 1px solid #c9e3ff;
        }

        /* Responsividade para telas muito pequenas */
        @media (max-width: 768px) {
            .destinos-container {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                padding: 0 0.8rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .back-button-fixed {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
            
            .search-filter {
                flex-direction: column;
            }
        }

        @media (max-width: 350px) {
            .destinos-container {
                grid-template-columns: 1fr;
            }
            
            .destino-card {
                height: auto;
                min-height: 500px;
            }
            
            .search-box, .filter-box {
                width: 100%;
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
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>">
                                <?= htmlspecialchars($categoria['nome_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback para categorias estáticas caso o modelo não funcione -->
                        <option value="1">Praias e Litoral</option>
                        <option value="2">Parques Nacionais e Reservas</option>
                        <option value="3">Montanhas e Formações Rochosas</option>
                        <option value="4">Patrimônio Histórico-Cultural</option>
                        <option value="5">Ecoturismo e Natureza</option>
                        <option value="6">Desertos e Savanas</option>
                        <option value="7">Turismo Urbano</option>
                        <option value="8">Turismo Rural e Comunitário</option>
                        <option value="9">Cultura e Festivais</option>
                        <option value="10">Turismo Religioso</option>
                        <option value="11">Roteiros Etnográficos</option>
                    <?php endif; ?>
                </select>
                <div class="filter-indicator"></div>
            </div>
            <button class="clear-filter" id="clearFilters">
                <i class="fas fa-times"></i> Limpar Filtros
            </button>
        </div>

        <div class="result-counter" id="resultCounter"></div>

        <section class="destinos-container" id="destinosContainer">
            <?php if (!empty($destinos)): ?>
                <?php foreach ($destinos as $destino): ?>
                    <div class="destino-card" data-category="<?= htmlspecialchars($destino['id_categoria'] ?? '') ?>" 
                         data-category-name="<?= htmlspecialchars($destino['categoria'] ?? '') ?>">
                        <!-- Exibição da categoria -->
                        <?php if (!empty($destino['categoria'])): ?>
                            <div class="categoria-badge"><?= htmlspecialchars($destino['categoria']) ?></div>
                        <?php endif; ?>
                        
                        <!-- A imagem e todo o conteúdo estão dentro do link -->
                        <a href="detalhes_destino.php?id=<?= htmlspecialchars($destino['id']) ?>">
                            <div class="imagem-container">
                                <img src="../uploads/<?= !empty($destino['imagem']) ? htmlspecialchars($destino['imagem']) : 'default.jpg' ?>" 
                                    alt="Imagem de <?= htmlspecialchars($destino['nome_destino']) ?>"
                                    loading="lazy">
                                <!-- Indicador visual de clique -->
                                <div class="card-click-indicator">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                            <div class="destino-card-content">
                                <h3><?= htmlspecialchars($destino['nome_destino']) ?></h3>
                                <p><?= htmlspecialchars($destino['descricao'] ?? 'Descrição não disponível') ?></p>
                                
                                <!-- Tags de categoria - para casos de múltiplas categorias no futuro -->
                                <?php if (!empty($destino['categoria'])): ?>
                                <div class="categoria-tags">
                                    <span class="categoria-tag"><?= htmlspecialchars($destino['categoria']) ?></span>
                                </div>
                                <?php endif; ?>
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
            <?php endif; ?>
            
            <!-- Mensagem para quando não houver resultados -->
            <div class="empty-message" id="emptyMessage">
                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; color: #ccc;"></i><br>
                Nenhum destino encontrado com os critérios selecionados.
                <p style="margin-top: 1rem;">
                    <button id="resetSearch" class="clear-filter">
                        <i class="fas fa-redo"></i> Redefinir Busca
                    </button>
                </p>
            </div>
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
            const visibleCards = document.querySelectorAll('.destino-card:not([style*="display: none"])');
            const totalCards = document.querySelectorAll('.destino-card');
            const counterElement = document.getElementById('resultCounter');
            const emptyMessage = document.getElementById('emptyMessage');
            const destinosContainer = document.getElementById('destinosContainer');
            
            if (visibleCards.length === 0) {
                // Nenhum resultado encontrado
                emptyMessage.style.display = 'block';
                emptyMessage.classList.add('no-results-shake');
                setTimeout(() => {
                    emptyMessage.classList.remove('no-results-shake');
                }, 800);
                counterElement.textContent = 'Nenhum destino encontrado';
            } else {
                // Resultados encontrados
                emptyMessage.style.display = 'none';
                
                if (visibleCards.length === totalCards.length) {
                    counterElement.textContent = `Mostrando todos os ${totalCards.length} destinos`;
                } else {
                    counterElement.textContent = `Mostrando ${visibleCards.length} de ${totalCards.length} destinos`;
                }
            }
        }

        // Efeito de animação para os cards
        document.querySelectorAll('.destino-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Função para filtrar destinos
        function filterDestinos() {
            const searchValue = document.getElementById('search').value.toLowerCase();
            const filterValue = document.getElementById('filter').value;
            const filterBox = document.querySelector('.filter-box');
            const cards = document.querySelectorAll('.destino-card');
            let hasResults = false;
            
            // Adicionar indicador visual para filtro ativo
            if (filterValue) {
                filterBox.classList.add('filter-active');
                document.querySelector('.filter-indicator').style.display = 'block';
            } else {
                filterBox.classList.remove('filter-active');
                document.querySelector('.filter-indicator').style.display = 'none';
            }
            
            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                const location = card.querySelector('.localizacao').textContent.toLowerCase();
                const category = card.getAttribute('data-category');
                const categoryName = card.getAttribute('data-category-name').toLowerCase();
                
                const matchesSearch = title.includes(searchValue) || 
                                    description.includes(searchValue) ||
                                    location.includes(searchValue) ||
                                    categoryName.includes(searchValue);
                
                const matchesFilter = filterValue === '' || category === filterValue;
                
                if (matchesSearch && matchesFilter) {
                    card.style.display = 'flex';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            updateResultCounter();
        }

        // Busca por texto
        document.getElementById('search').addEventListener('input', filterDestinos);

        // Filtro por categoria
        document.getElementById('filter').addEventListener('change', filterDestinos);
        
        // Limpar filtros
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('search').value = '';
            document.getElementById('filter').value = '';
            
            const filterBox = document.querySelector('.filter-box');
            filterBox.classList.remove('filter-active');
            document.querySelector('.filter-indicator').style.display = 'none';
            
            filterDestinos();
        });
        
        // Resetar busca do botão de mensagem vazia
        document.getElementById('resetSearch').addEventListener('click', function() {
            document.getElementById('search').value = '';
            document.getElementById('filter').value = '';
            
            const filterBox = document.querySelector('.filter-box');
            filterBox.classList.remove('filter-active');
            document.querySelector('.filter-indicator').style.display = 'none';
            
            filterDestinos();
        });

        // Inicializa o contador de resultados
        document.addEventListener('DOMContentLoaded', function() {
            updateResultCounter();
        });
    </script>
</body>
</html>