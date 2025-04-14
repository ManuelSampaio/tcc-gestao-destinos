<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Aqui você deve buscar as avaliações do usuário no banco de dados
$avaliacoes = []; // Exemplo: array com as avaliações

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Avaliações</title>
    <link rel="stylesheet" href="../assets/css/stylea.css">
</head>
<body>
    <div class="container">
        <h1>Minhas Avaliações</h1>
        <div class="avaliacoes-container">
            <?php if (empty($avaliacoes)): ?>
                <p class="mensagem-vazio">Você ainda não fez nenhuma avaliação.</p>
            <?php else: ?>
                <ul class="lista-avaliacoes">
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <li class="avaliacao-item">
                            <div class="destino-info">
                                <span class="destino-nome"><?= htmlspecialchars($avaliacao['destino']) ?></span>
                                <span class="avaliacao-nota">Nota: <?= htmlspecialchars($avaliacao['nota']) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="links-navegacao">
            <a href="index.php" class="botao-secundario">Voltar ao Home</a>
            <a href="listar_destinos.php" class="botao-primario">Explorar Destinos</a>
        </div>
    </div>
</body>
</html>