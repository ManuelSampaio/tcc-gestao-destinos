<?php
require_once '../app/controllers/DestinoController.php';
require_once '../app/controllers/CategoriaController.php';
require_once '../app/controllers/ProvinciaController.php';
require_once '../app/controllers/localizacao_controller.php';

use App\Controllers\DestinoController;
use App\Controllers\CategoriaController;
use App\Controllers\ProvinciaController;
use App\Controllers\LocalizacaoController;

$message = "";

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $descricao = htmlspecialchars($_POST['descricao'] ?? '');
    $localizacaoTexto = htmlspecialchars($_POST['localizacao'] ?? '');
    $categoriaId = htmlspecialchars($_POST['categoria'] ?? '');
    $provinciaId = htmlspecialchars($_POST['provincia'] ?? '');
    $latitude = htmlspecialchars($_POST['latitude'] ?? '');
    $longitude = htmlspecialchars($_POST['longitude'] ?? '');
    $imagem = $_FILES['imagem'] ?? null;

    // Validações simples
    if (empty($nome) || empty($descricao) || empty($localizacaoTexto) || empty($categoriaId) || empty($provinciaId)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!isset($imagem) || $imagem['error'] !== UPLOAD_ERR_OK) {
        // Verificação específica para a imagem
        $message = "Por favor, selecione uma imagem válida.";
    } else {
        try {
            // 1. Primeiro criamos a localização
            $localizacaoController = new LocalizacaoController();
            $idLocalizacao = $localizacaoController->cadastrarLocalizacao($localizacaoTexto, $latitude, $longitude, $provinciaId);
            
            if (!$idLocalizacao) {
                throw new Exception("Erro ao cadastrar a localização do destino.");
            }
            
            // 2. Depois criamos o destino turístico
            $destinoController = new DestinoController();

            // Tratamento de upload da imagem
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($imagem['type'], $allowedTypes)) {
                // Corrigindo o caminho para a pasta uploads (usando caminho relativo)
                $uploadDir = '../uploads/';
                
                // Verifica se o diretório de upload existe, senão cria
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($imagem['name']);
                $filePath = $uploadDir . $fileName;
                $dbFilePath = 'uploads/' . $fileName; // Caminho para armazenar no banco de dados

                if (move_uploaded_file($imagem['tmp_name'], $filePath)) {
                    // Chama o método para cadastrar o destino com a categoria e localização
                    $result = $destinoController->cadastrarDestino($nome, $descricao, $idLocalizacao, $dbFilePath, $categoriaId);

                    if ($result) {
                        $message = "Destino cadastrado com sucesso!";
                    } else {
                        $message = "Ocorreu um erro ao cadastrar o destino.";
                    }
                } else {
                    $message = "Erro ao salvar a imagem. Verifique as permissões da pasta de upload.";
                }
            } else {
                $message = "Tipo de arquivo inválido. Envie uma imagem JPEG, PNG ou GIF.";
            }
        } catch (Exception $e) {
            $message = "Erro ao cadastrar destino. Detalhes: " . $e->getMessage();
        }
    }
}

// Obtém categorias para exibição no formulário
$categoriaController = new CategoriaController();
$categorias = $categoriaController->listarCategorias();

// Obtém províncias para exibição no formulário
$provinciaController = new ProvinciaController();
$provincias = $provinciaController->listarProvincias();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Destinos - Turismo Angola</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-image: linear-gradient(to bottom, rgba(245, 245, 245, 0.95), rgba(245, 245, 245, 0.85)), 
                              url('../assets/images/angola-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .main-container {
            max-width: 800px;
            width: 100%;
            margin: 40px auto;
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }
        
        .form-title {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
        }
        
        .form-title i {
            color: var(--secondary-color);
        }
        
        .form-container {
            padding: 30px;
        }
        
        .angola-flag {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--primary-color);
        }
        
        .flag-colors {
            display: inline-block;
            width: 30px;
            height: 15px;
            background: linear-gradient(to bottom, 
                #ce1126 33.33%, /* Red */
                #000000 33.33%, 66.66%, /* Black */
                #ffce00 66.66% /* Yellow */
            );
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--accent-color);
            border-left: 4px solid var(--accent-color);
        }
        
        .message.success {
            background-color: rgba(0, 77, 64, 0.1);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        label i {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
            background-color: #fafafa;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 77, 64, 0.2);
            background-color: #ffffff;
        }
        
        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #fafafa;
            border: 2px dashed #b0bec5;
            border-radius: var(--border-radius);
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            min-height: 150px;
        }
        
        .file-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(0, 77, 64, 0.05);
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
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            font-size: 14px;
            color: #546e7a;
            margin-bottom: 5px;
        }
        
        .file-upload-subtext {
            font-size: 12px;
            color: #90a4ae;
        }
        
        .file-selected {
            padding: 5px 10px;
            background-color: rgba(0, 77, 64, 0.1);
            border-radius: 4px;
            color: var(--primary-color);
            font-size: 12px;
            margin-top: 10px;
            display: none;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 15px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-width: 150px;
        }
        
        .btn:hover {
            background-color: #003d33;
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #eceff1;
            color: var(--dark-bg);
        }
        
        .btn-secondary:hover {
            background-color: #cfd8dc;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            min-width: 180px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 25px;
            font-size: 0.8rem;
            color: #78909c;
        }
        
        .footer-note a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-note a:hover {
            text-decoration: underline;
        }
        
        .required-field::after {
            content: '*';
            color: var(--accent-color);
            margin-left: 3px;
        }
        
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: var(--border-radius);
            font-size: 12px;
            color: #666;
            display: none;
        }
        
        /* Estilo para o grid de 2 colunas */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        /* Estilos para o modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            max-width: 600px;
            width: 90%;
            animation: slideIn 0.4s;
        }
        
        @keyframes slideIn {
            from {transform: translateY(-50px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-title {
            color: var(--primary-color);
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .close-btn:hover {
            color: var(--accent-color);
        }
        
        .modal-body {
            margin-bottom: 25px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 20px auto;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-title">
            <i class="fas fa-map-marked-alt"></i> Cadastro de Destino Turístico
        </div>
        
        <div class="form-container">
            <div class="angola-flag">
                <span class="flag-colors"></span> Sistema de Gestão de Destinos Turísticos de Angola
            </div>
            
            <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'sucesso') !== false ? 'success' : 'error' ?>">
                <?= strpos($message, 'sucesso') !== false ? '<i class="fas fa-check-circle"></i> ' : '<i class="fas fa-exclamation-circle"></i> ' ?>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="destino-form">
                <div class="form-group">
                    <label for="nome" class="required-field">
                        <i class="fas fa-signature"></i> Nome do Destino
                    </label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" placeholder="Ex: Parque Nacional da Quiçama" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="required-field">
                        <i class="fas fa-align-left"></i> Descrição
                    </label>
                    <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva as atrações e características deste destino turístico..." required><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="provincia" class="required-field">
                            <i class="fas fa-map"></i> Província
                        </label>
                        <select id="provincia" name="provincia" required>
                            <option value="">Selecione uma província</option>
                            <?php foreach ($provincias as $provincia): ?>
                                <option value="<?= htmlspecialchars($provincia['id_provincia']) ?>" <?= isset($_POST['provincia']) && $_POST['provincia'] == $provincia['id_provincia'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($provincia['nome_provincia']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria" class="required-field">
                            <i class="fas fa-tag"></i> Categoria
                        </label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>" <?= isset($_POST['categoria']) && $_POST['categoria'] == $categoria['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="localizacao" class="required-field">
                        <i class="fas fa-map-pin"></i> Nome do Local
                    </label>
                    <div class="input-group">
                        <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($_POST['localizacao'] ?? '') ?>" placeholder="Ex: Praia do Mussulo, Município de Belas" required>
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="latitude">
                            <i class="fas fa-location-arrow"></i> Latitude (opcional)
                        </label>
                        <input type="text" id="latitude" name="latitude" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>" placeholder="Ex: -8.838333">
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude">
                            <i class="fas fa-location-arrow"></i> Longitude (opcional)
                        </label>
                        <input type="text" id="longitude" name="longitude" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>" placeholder="Ex: 13.235278">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="imagem" class="required-field">
                        <i class="fas fa-image"></i> Imagem do Destino
                    </label>
                    <div class="file-upload" id="file-upload-container">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">
                            Arraste uma imagem ou clique para selecionar
                        </div>
                        <div class="file-upload-subtext">
                            Formatos aceitos: JPG, PNG, GIF
                        </div>
                        <div class="file-selected" id="file-selected"></div>
                        <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif" required>
                    </div>
                </div>
                
                <div class="btn-group">
                    <a href="painel_admin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Painel
                    </a>
                    <button type="submit" class="btn btn-submit" id="submit-btn">
                        <i class="fas fa-save"></i> Cadastrar Destino
                    </button>
                </div>
            </form>
            
            <div class="footer-note">
                Sistema de Gestão de Destinos Turísticos &copy; <?= date('Y') ?> | <a href="#">Suporte Técnico</a>
            </div>
        </div>
    </div>
    
    <!-- Modal para inserção de coordenadas GPS (opcional) -->
    <div id="mapaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-map-marker-alt"></i> Adicionar Coordenadas</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <p>Selecione um ponto no mapa ou insira as coordenadas manualmente.</p>
                <div class="form-group">
                    <label for="modal-latitude">Latitude</label>
                    <input type="text" id="modal-latitude" placeholder="Ex: -8.838333">
                </div>
                <div class="form-group">
                    <label for="modal-longitude">Longitude</label>
                    <input type="text" id="modal-longitude" placeholder="Ex: 13.235278">
                </div>
                <!-- Aqui poderíamos adicionar um mapa interativo com alguma API (Google Maps, Leaflet, etc) -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-coords">Cancelar</button>
                <button class="btn btn-submit" id="confirm-coords">Confirmar</button>
            </div>
        </div>
    </div>
    
    <script>
        // Preview da imagem quando selecionada
        document.getElementById('imagem').addEventListener('change', function(e) {
            const fileUpload = document.querySelector('.file-upload');
            const fileSelected = document.getElementById('file-selected');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Adiciona imagem de fundo com overlay para manter a legibilidade do texto
                    fileUpload.style.backgroundImage = `linear-gradient(rgba(0, 77, 64, 0.7), rgba(0, 77, 64, 0.7)), url('${event.target.result}')`;
                    fileUpload.style.backgroundSize = 'cover';
                    fileUpload.style.backgroundPosition = 'center';
                    fileUpload.style.color = '#fff';
                    fileUpload.style.textShadow = '0 0 5px rgba(0,0,0,0.7)';
                    
                    const icon = fileUpload.querySelector('.file-upload-icon');
                    const text = fileUpload.querySelector('.file-upload-text');
                    const subtext = fileUpload.querySelector('.file-upload-subtext');
                    
                    icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    text.textContent = 'Imagem selecionada';
                    subtext.textContent = 'Clique para alterar a imagem';
                    
                    // Exibe o nome do arquivo selecionado
                    fileSelected.style.display = 'inline-block';
                    fileSelected.textContent = file.name;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Validação do formulário antes de enviar
        document.getElementById('destino-form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('imagem');
            const provinciaSelect = document.getElementById('provincia');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione uma imagem para o destino.');
                return false;
            }
            
            const file = fileInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Tipo de arquivo inválido. Por favor, envie uma imagem JPG, PNG ou GIF.');
                return false;
            }
            
            if (provinciaSelect.value === "") {
                e.preventDefault();
                alert('Por favor, selecione uma província.');
                provinciaSelect.focus();
                return false;
            }
            
            return true;
        });
        
        // Funcionalidade do modal
        const modal = document.getElementById("mapaModal");
        const closeBtn = document.querySelector(".close-btn");
        const cancelBtn = document.getElementById("cancel-coords");
        const confirmBtn = document.getElementById("confirm-coords");
        
        // Quando o usuário clica no botão "Adicionar Coordenadas"
        document.getElementById("add-coords").addEventListener("click", function() {
            modal.style.display = "block";
        });
        
        // Fechar o modal
        closeBtn.addEventListener("click", function() {
            modal.style.display = "none";
        });
        
        cancelBtn.addEventListener("click", function() {
            modal.style.display = "none";
        });
        
        // Quando o usuário clica fora do modal
        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
        
        // Confirmar coordenadas
        confirmBtn.addEventListener("click", function() {
            const lat = document.getElementById("modal-latitude").value;
            const lng = document.getElementById("modal-longitude").value;
            
            if (lat && lng) {
                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;
            }
            
            modal.style.display = "none";
        });
    </script>
</body>
</html>