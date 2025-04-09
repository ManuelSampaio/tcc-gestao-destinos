<?php
require_once '../app/controllers/DestinoController.php';
require_once '../app/controllers/CategoriaController.php';

use App\Controllers\DestinoController;
use App\Controllers\CategoriaController;

$message = "";

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $descricao = htmlspecialchars($_POST['descricao'] ?? '');
    $localizacao = htmlspecialchars($_POST['localizacao'] ?? '');
    $categoriaId = htmlspecialchars($_POST['categoria'] ?? '');
    $imagem = $_FILES['imagem'] ?? null;

    // Validações simples
    if (empty($nome) || empty($descricao) || empty($localizacao) || empty($categoriaId)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        $destinoController = new DestinoController();

        // Tratamento de upload da imagem
        if ($imagem && $imagem['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($imagem['type'], $allowedTypes)) {
                $uploadDir = __DIR__ . '/../../uploads/';
                $fileName = uniqid() . '_' . basename($imagem['name']);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($imagem['tmp_name'], $filePath)) {
                    // Chama o método para cadastrar o destino com a categoria
                    $result = $destinoController->cadastrarDestino($nome, $descricao, $localizacao, $filePath, $categoriaId);

                    if ($result) {
                        $message = "Destino cadastrado com sucesso!";
                    } else {
                        $message = "Ocorreu um erro ao cadastrar o destino.";
                    }
                } else {
                    $message = "Erro ao salvar a imagem.";
                }
            } else {
                $message = "Tipo de arquivo inválido. Envie uma imagem JPEG, PNG ou GIF.";
            }
        } else {
            $message = "Erro no upload da imagem.";
        }
    }
}

// Obtém categorias para exibição no formulário
$categoriaController = new CategoriaController();
$categorias = $categoriaController->listarCategorias();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Destino Turístico</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e63946;
            --secondary: #457b9d;
            --dark: #1d3557;
            --light: #f1faee;
            --success: #2ecc71;
            --error: #e74c3c;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            background-color: var(--dark);
            color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
        }
        
        .file-upload {
            position: relative;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover {
            border-color: var(--secondary);
        }
        
        .file-upload input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-upload-icon {
            font-size: 24px;
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .file-upload-text {
            font-size: 14px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #c1121f;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
        }
        
        .btn-secondary:hover {
            background-color: #2a6f97;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius);
            font-weight: 500;
        }
        
        .message.error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }
        
        .message.success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px;
        }
        
        .angola-flag {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        
        .angola-flag i {
            color: var(--primary);
        }
    </style>
</head>
<body>
    
          
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'sucesso') !== false ? 'success' : 'error' ?>">
                <?= strpos($message, 'sucesso') !== false ? '<i class="fas fa-check-circle"></i> ' : '<i class="fas fa-exclamation-circle"></i> ' ?>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Cadastrar Novo Destino</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome"><i class="fas fa-signature"></i> Nome do Destino:</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" placeholder="Ex: Parque Nacional da Quiçama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao"><i class="fas fa-align-left"></i> Descrição:</label>
                        <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva este destino turístico..." required><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="localizacao"><i class="fas fa-map-pin"></i> Localização:</label>
                        <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($_POST['localizacao'] ?? '') ?>" placeholder="Ex: Província de Luanda" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria"><i class="fas fa-tag"></i> Categoria:</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>">
                                    <?= htmlspecialchars($categoria['nome_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagem"><i class="fas fa-image"></i> Imagem do Destino:</label>
                        <div class="file-upload">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                Arraste uma imagem ou clique para selecionar
                            </div>
                            <input type="file" id="imagem" name="imagem" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="actions">
                        <a href="painel_admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Cadastrar Destino</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>