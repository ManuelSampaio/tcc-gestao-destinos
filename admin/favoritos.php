<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Aqui você deve buscar os destinos favoritos do usuário no banco de dados
$favoritos = []; // Exemplo: array com os destinos favoritos

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Favoritos</title>
    <link rel="stylesheet" href="../assets/css/stylef.css">
</head>
<body>
    <div class="container">
        <h1>Destinos Favoritos</h1>
        <div class="favoritos-container">
            <?php if (empty($favoritos)): ?>
                <p class="mensagem-vazio">Nenhum destino favoritado ainda.</p>
            <?php else: ?>
                <ul class="lista-favoritos">
                    <?php foreach ($favoritos as $destino): ?>
                        <li class="destino-item"><?= htmlspecialchars($destino['nome']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <a href="index.php" class="botao-voltar">Voltar ao Home</a>
    </div>
</body>
</html>