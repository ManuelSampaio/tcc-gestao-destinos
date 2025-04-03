<?php
require_once '../app/controllers/DestinoController.php';
require_once '../app/controllers/CategoriaController.php';

use App\Controllers\DestinoController;
use App\Controllers\CategoriaController;

$message = "";
$messageType = "error"; // Pode ser "error" ou "success"

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $descricao = htmlspecialchars($_POST['descricao'] ?? '');
    $localizacao = htmlspecialchars($_POST['localizacao'] ?? '');
    $categoriaId = htmlspecialchars($_POST['categoria'] ?? '');
    $imagem = $_FILES['imagem'] ?? null;

    // Inicializa o caminho da imagem como null
    $filePath = null;

    // Validações simples
    if (empty($nome) || empty($descricao) || empty($localizacao) || empty($categoriaId)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        $destinoController = new DestinoController();

        // Tratamento de upload da imagem (se enviada)
        if ($imagem && $imagem['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($imagem['type'], $allowedTypes)) {
                // Verifica se o arquivo é realmente uma imagem
                $imageSize = getimagesize($imagem['tmp_name']);
                
                if ($imageSize) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    
                    // Verifica e cria o diretório se não existir
                    if (!is_dir($uploadDir)) {
                        if (!mkdir($uploadDir, 0755, true)) {
                            $message = "Erro ao criar diretório de uploads.";
                        }
                    }
                    
                    if (empty($message)) {
                        $fileName = uniqid() . '_' . basename($imagem['name']);
                        $filePath = $uploadDir . $fileName;
                        
                        if (!move_uploaded_file($imagem['tmp_name'], $filePath)) {
                            $message = "Erro ao mover o arquivo para a pasta de uploads. Verifique as permissões.";
                            // Registrar detalhes do erro para depuração
                            error_log("Erro ao mover arquivo: " . print_r($imagem, true));
                        }
                    }
                } else {
                    $message = "O arquivo selecionado não é uma imagem válida.";
                }
            } else {
                $message = "Tipo de arquivo inválido. Envie uma imagem JPEG, PNG ou GIF.";
            }
        }

        // Se não tem mensagem de erro, continua com o cadastro
        if (empty($message)) {
            // Chama o método para cadastrar o destino
            // O controller lidará com a situação em que $filePath é null
            $result = $destinoController->cadastrarDestino($nome, $descricao, $localizacao, $filePath, $categoriaId);
            
            if ($result) {
                $message = "Destino cadastrado com sucesso!";
                $messageType = "success";
                
                // Limpar os dados do formulário após sucesso
                $_POST = [];
            } else {
                $message = "Ocorreu um erro ao cadastrar o destino. Verifique os logs para mais informações.";
            }
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
            --primary: #1a73e8;
            --primary-dark: #155db1;
            --secondary: #4285f4;
            --dark: #202124;
            --light: #f8f9fa;
            --light-gray: #e8eaed;
            --medium-gray: #dadce0;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --success: #0f9d58;
            --error: #d93025;
            --warning: #f29900;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
            --shadow-md: 0 2px 6px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 4px 8px rgba(0, 0, 0, 0.2);
            --radius: 8px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px 0;
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
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
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid var(--medium-gray);
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 18px 24px;
            display: flex;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 20px;
            font-weight: 500;
            margin-left: 8px;
        }
        
        .card-header i {
            font-size: 18px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 14px;
        }
        
        label i {
            color: var(--primary);
            margin-right: 6px;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
            background-color: white;
            color: var(--text-primary);
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }
        
        input::placeholder, textarea::placeholder {
            color: var(--text-secondary);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
            border: 2px dashed var(--medium-gray);
            border-radius: var(--radius);
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            height: 150px;
        }
        
        .file-upload:hover {
            border-color: var(--primary);
            background-color: rgba(26, 115, 232, 0.05);
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
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            font-size: 14px;
            color: var(--text-secondary);
            max-width: 80%;
            line-height: 1.4;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 13px;
            color: var(--primary);
            font-weight: 500;
            display: none;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
            height: 46px;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-secondary {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--medium-gray);
        }
        
        .btn-secondary:hover {
            background-color: var(--light);
            color: var(--primary-dark);
            border-color: var(--primary);
        }
        
        .message {
            padding: 16px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            font-weight: 500;
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .message i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .message.error {
            background-color: rgba(217, 48, 37, 0.08);
            color: var(--error);
            border-left: 4px solid var(--error);
        }
        
        .message.success {
            background-color: rgba(15, 157, 88, 0.08);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        @media (max-width: 576px) {
            .actions {
                flex-direction: column-reverse;
            }
            
            .actions .btn {
                width: 100%;
            }
        }
        
        /* Estilos responsivos adicionais */
        @media (max-width: 992px) {
            .container {
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .card-header h2 {
                font-size: 18px;
            }
            
            .header {
                padding: 20px 0;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
        
        @media (max-width: 480px) {
            .file-upload {
                padding: 20px 15px;
            }
            
            .file-upload-icon {
                font-size: 30px;
                margin-bottom: 10px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
        }
        
        /* Animações e transições */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Estilo para select */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235f6368' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><i class="fas fa-globe-africa"></i> Sistema de Gestão Turística</h1>
            <p>Painel de Administração - Cadastro de Destinos</p>
        </header>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'sucesso') !== false ? 'success' : 'error' ?>">
                <?= strpos($message, 'sucesso') !== false ? '<i class="fas fa-check-circle"></i> ' : '<i class="fas fa-exclamation-circle"></i> ' ?>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i>
                <h2>Cadastrar Novo Destino</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome"><i class="fas fa-signature"></i> Nome do Destino</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" placeholder="Ex: Parque Nacional da Quiçama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                        <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva este destino turístico..." required><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="localizacao"><i class="fas fa-map-pin"></i> Localização</label>
                        <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($_POST['localizacao'] ?? '') ?>" placeholder="Ex: Província de Luanda" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria"><i class="fas fa-tag"></i> Categoria</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>" <?= (isset($_POST['categoria']) && $_POST['categoria'] == $categoria['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagem"><i class="fas fa-image"></i> Imagem do Destino</label>
                        <div class="file-upload">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                Arraste uma imagem ou clique para selecionar
                                <p class="file-name" id="fileName"></p>
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

    <script>
        // Script para mostrar o nome do arquivo selecionado
        document.getElementById('imagem').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            const fileNameElement = document.getElementById('fileName');
            
            if (fileName) {
                fileNameElement.textContent = 'Arquivo selecionado: ' + fileName;
                fileNameElement.style.display = 'block';
            } else {
                fileNameElement.style.display = 'none';
            }
        });
    </script>
</body>
</html>