<?php
require_once '../config/database.php';
require_once '../app/models/DestinoModel.php';

use Config\Database;
use App\Models\DestinoModel;

try {
    $database = new Database();
    $conn = $database->getConnection();
    $destinoModel = new DestinoModel($conn);
    $destinos = $destinoModel->obterDestinos();
} catch (Exception $e) {
    die("Erro ao carregar os destinos: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Turísticos de Angola</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <h1>Descubra os Destinos Turísticos de Angola</h1>
        <p>Explore a fauna, flora e a rica cultura do nosso país!</p>
    </header>
    <main class="destinos-container">
        <?php if (!empty($destinos)): ?>
            <?php foreach ($destinos as $destino): ?>
                <div class="destino-card">
                    <img src="../uploads/<?= htmlspecialchars($destino['imagem'] ?: 'default.jpg') ?>" alt="Imagem de <?= htmlspecialchars($destino['nome_destino']) ?>">
                    <h2><?= htmlspecialchars($destino['nome_destino']) ?></h2>
                    <p><?= htmlspecialchars($destino['descricao']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-message">Nenhum destino disponível no momento.</p>
        <?php endif; ?>
    </main>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Descubra Angola. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
