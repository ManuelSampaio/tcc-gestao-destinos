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

        .header p {
            font-size: 1.2rem;
        }

        .search-filter {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .search-filter input, .search-filter select {
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
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
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .destino-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .destino-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .destino-card-content {
            padding: 1rem;
            flex: 1;
        }

        .destino-card-content h3 {
            font-size: 1.5rem;
            margin: 0 0 0.5rem;
        }

        .destino-card-content p {
            margin: 0;
            font-size: 0.9rem;
            color: #555;
        }

        .destino-card-footer {
            margin-top: auto;
            padding: 1rem;
            background: #f1f1f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .empty-message {
            text-align: center;
            font-size: 1.2rem;
            color: #999;
        }

        .footer {
            text-align: center;
            background: #1e90ff;
            color: white;
            padding: 1rem 0;
            margin-top: 2rem;
        }

        .back-button {
            display: inline-block;
            margin: 1rem auto;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: white;
            background-color: #1e90ff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #4682b4;
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
            <input type="text" id="search" placeholder="Buscar destinos...">
            <select id="filter">
                <option value="">Todas as Categorias</option>
                <option value="praias">Praias</option>
                <option value="montanhas">Montanhas</option>
                <option value="cidades">Cidades Históricas</option>
            </select>
        </div>

        <section class="destinos-container">
            <?php if (!empty($destinos)): ?>
                <?php foreach ($destinos as $destino): ?>
                    <div class="destino-card" data-category="<?= htmlspecialchars($destino['categoria'] ?? '') ?>">
                        <img src="../uploads/<?= !empty($destino['imagem']) ? htmlspecialchars($destino['imagem']) : 'default.jpg' ?>" 
                             alt="Imagem de <?= htmlspecialchars($destino['nome_destino']) ?>">
                        <div class="destino-card-content">
                            <h3><?= htmlspecialchars($destino['nome_destino']) ?></h3>
                            <p><?= htmlspecialchars($destino['descricao'] ?? 'Descrição não disponível') ?></p>
                        </div>
                        <div class="destino-card-footer">
                            <span><strong>Localização:</strong> <?= htmlspecialchars($destino['localizacao'] ?? 'Não disponível') ?></span>
                            <a href="detalhes_destino.php?id=<?= htmlspecialchars($destino['id']) ?>" class="btn">Saiba mais</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">Nenhum destino cadastrado.</p>
            <?php endif; ?>
        </section>
        <a href="../admin/index.php" class="back-button">Voltar para Página Principal</a>
    </main>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Descubra Angola. Todos os direitos reservados.</p>
    </footer>

    <script>
        document.getElementById('search').addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.destino-card');

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = title.includes(searchValue) ? 'block' : 'none';
            });
        });

        document.getElementById('filter').addEventListener('change', function () {
            const filterValue = this.value;
            const cards = document.querySelectorAll('.destino-card');

            cards.forEach(card => {
                const category = card.getAttribute('data-category');
                card.style.display = filterValue === '' || category === filterValue ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
