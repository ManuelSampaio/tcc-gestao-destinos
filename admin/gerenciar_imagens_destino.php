<?php
require_once '../config/database.php';
require_once '../app/controllers/DestinoController.php';
// Verificar autenticação e permissões

$id_destino = $_GET['id'] ?? 0;
if (!$id_destino) {
    header('Location: listar_destino.php');
    exit;
}

$destinoController = new DestinoController();
$destino = $destinoController->getDestinoById($id_destino);

// Buscar imagens existentes
$conn = Database::getConnection();
$sql = "SELECT * FROM imagens WHERE id_destino = ? ORDER BY ordem ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_destino);
$stmt->execute();
$result = $stmt->get_result();
$imagens = [];
while ($row = $result->fetch_assoc()) {
    $imagens[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imagens do Destino</title>
    <!-- Incluir CSS necessário -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include_once 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Gerenciar Imagens: <?php echo $destino['nome_destino']; ?></h2>
        
        <!-- Exibir imagens existentes -->
        <div class="row mt-4">
            <div class="col-12">
                <h4>Imagens Existentes</h4>
                <?php if (count($imagens) > 0): ?>
                    <div class="row">
                        <?php foreach ($imagens as $imagem): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="../uploads/<?php echo $imagem['caminho_imagem']; ?>" 
                                         class="card-img-top img-fluid" 
                                         alt="Imagem do destino">
                                    <div class="card-body">
                                        <p>Ordem: <?php echo $imagem['ordem']; ?></p>
                                        <a href="excluir_imagem.php?id=<?php echo $imagem['id_imagem']; ?>&destino=<?php echo $id_destino; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Tem certeza que deseja excluir esta imagem?');">
                                            Excluir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Não há imagens adicionais cadastradas para este destino.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Formulário para adicionar novas imagens -->
        <div class="row mt-4">
            <div class="col-12">
                <h4>Adicionar Novas Imagens</h4>
                <form action="salvar_imagens.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_destino" value="<?php echo $id_destino; ?>">
                    
                    <div class="form-group">
                        <label for="imagens">Selecione uma ou mais imagens:</label>
                        <input type="file" class="form-control-file" id="imagens" name="imagens[]" multiple required>
                        <small class="form-text text-muted">Você pode selecionar múltiplas imagens de uma vez.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Salvar Imagens</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="detalhes_destino.php?id=<?php echo $id_destino; ?>" class="btn btn-secondary">Voltar para Detalhes</a>
        </div>
    </div>
    
    <?php include_once 'includes/footer.php'; ?>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>