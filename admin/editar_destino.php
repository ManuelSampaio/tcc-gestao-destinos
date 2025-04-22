<?php
require_once '../app/controllers/DestinoController.php';
require_once '../app/controllers/CategoriaController.php';
require_once '../app/controllers/ProvinciaController.php';
require_once '../app/controllers/localizacao_controller.php';

use App\Controllers\DestinoController;
use App\Controllers\CategoriaController;
use App\Controllers\ProvinciaController;
use App\Controllers\LocalizacaoController;

// Verificar se foi enviado um ID válido
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gerenciar_destinos.php?erro=id_invalido');
    exit;
}

$id = (int)$_GET['id'];
$message = "";
$destino = null;

// Inicializar controladores
$destinoController = new DestinoController();
$categoriaController = new CategoriaController();
$provinciaController = new ProvinciaController();
$localizacaoController = new LocalizacaoController();

// Obter dados do destino para edição
$destino = $destinoController->obterDestinoCompleto($id);

if (!$destino) {
    header('Location: gerenciar_destinos.php?erro=destino_nao_encontrado');
    exit;
}

// Listar categorias e províncias para os selects
$categorias = $categoriaController->listarCategorias();
$provincias = $provinciaController->listarProvincias();

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter apenas os campos que podem ser editados
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $descricao = htmlspecialchars($_POST['descricao'] ?? '');
    $provinciaId = htmlspecialchars($_POST['provincia'] ?? '');
    $imagem = $_FILES['imagem'] ?? null;
    
    // Manter os valores originais dos campos não editáveis
    $categoriaId = $destino['id_categoria'];
    $idLocalizacao = isset($destino['id_localizacao']) ? $destino['id_localizacao'] : null;
    $isMaravilha = $destino['is_maravilha'];
    $latitude = $destino['latitude'];
    $longitude = $destino['longitude'];
    $localizacaoTexto = isset($destino['localizacao_texto']) ? $destino['localizacao_texto'] : 
                   (isset($destino['nome_local']) ? $destino['nome_local'] : '');

    // Validações simples - apenas para os campos editáveis
    if (empty($nome) || empty($descricao) || empty($provinciaId)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            // 1. Atualizar apenas a província na localização
            if (!empty($idLocalizacao)) {
                $localizacaoController->atualizarProvincia($idLocalizacao, $provinciaId);
            } else {
                // Se não tivermos um ID de localização, isso precisa ser tratado de outra forma
                // Por exemplo, poderíamos criar uma nova localização
                $novaLocalizacaoId = $localizacaoController->cadastrarLocalizacao($localizacaoTexto, $latitude, $longitude, $provinciaId);
                if ($novaLocalizacaoId) {
                    // Atualize o destino para usar a nova localização
                    // Isto provavelmente requer um método adicional no DestinoController
                    // $destinoController->atualizarLocalizacaoDoDestino($id, $novaLocalizacaoId);
                    error_log("Nova localização criada com ID: $novaLocalizacaoId");
                } else {
                    error_log("Falha ao criar nova localização para o destino ID: $id");
                }
            }            
            // 2. Caminho da imagem atual
            $imagemPath = $destino['imagem'];
            
            // 3. Verificar se há uma nova imagem
            if (isset($imagem) && $imagem['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($imagem['type'], $allowedTypes)) {
                    // Corrigindo o caminho para a pasta uploads
                    $uploadDir = '../uploads/';
                    
                    // Verifica se o diretório de upload existe, senão cria
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($imagem['name']);
                    $filePath = $uploadDir . $fileName;
                    $imagemPath = $fileName; // Simplificando o caminho para armazenar
                    
                    if (move_uploaded_file($imagem['tmp_name'], $filePath)) {
                        // Se tiver uma imagem anterior, excluí-la
                        if (!empty($destino['imagem'])) {
                            $oldImagePath = $uploadDir . $destino['imagem'];
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    } else {
                        throw new Exception("Erro ao salvar a imagem. Verifique as permissões da pasta de upload.");
                    }
                } else {
                    throw new Exception("Tipo de arquivo inválido. Envie uma imagem JPEG, PNG ou GIF.");
                }
            }
            
            // 4. Atualizar apenas os campos editáveis do destino
            $result = $destinoController->atualizarDestinoSimplificado($id, $nome, $descricao, $imagemPath);
            
            if ($result) {
                $message = "Destino atualizado com sucesso!";
                // Recarregar dados do destino
                $destino = $destinoController->obterDestinoCompleto($id);
            } else {
                $message = "Ocorreu um erro ao atualizar o destino.";
            }
        } catch (Exception $e) {
            $message = "Erro ao atualizar destino. Detalhes: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Destino - Destinos Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            --disabled-bg: #f0f0f0;
            --disabled-text: #999999;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(to bottom, rgba(245, 245, 245, 0.95), rgba(245, 245, 245, 0.85)), 
                              url('../assets/images/angola-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .main-container {
            max-width: 900px;
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
        
        .top-bar {
            background-color: var(--primary-color);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-light);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
        }
        
        .logo-section h1 {
            font-size: 20px;
            margin-left: 12px;
            font-weight: 500;
        }
        
        .logo-icon {
            background-color: var(--text-light);
            color: var(--primary-color);
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .user-info i {
            font-size: 18px;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.15);
            border: none;
            color: var(--text-light);
            padding: 6px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
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
            font-family: 'Segoe UI', sans-serif;
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
        
        input:disabled, textarea:disabled, select:disabled {
            background-color: var(--disabled-bg);
            color: var(--disabled-text);
            cursor: not-allowed;
            border-color: #e0e0e0;
        }
        
        .current-image {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .image-preview {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: var(--border-radius);
            object-fit: cover;
            box-shadow: var(--shadow);
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
        }
        
        .file-upload:hover {
            background-color: #f0f0f0;
            border-color: var(--primary-color);
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .upload-text {
            color: #616161;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .upload-subtext {
            color: #9e9e9e;
            font-size: 0.8rem;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .form-column {
            flex: 1;
            min-width: 250px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-container input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .checkbox-label {
            cursor: pointer;
            font-weight: 500;
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .checkbox-label.disabled {
            color: var(--disabled-text);
            cursor: not-allowed;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        
        .btn-primary:hover {
            background-color: #00695c;
            box-shadow: var(--hover-shadow);
        }
        
        .btn-secondary {
            background-color: #e0e0e0;
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background-color: #d0d0d0;
        }
        
        .coords-container {
            display: flex;
            gap: 15px;
        }
        
        .coords-container .form-group {
            flex: 1;
        }
        
        .field-disabled {
            opacity: 0.7;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-column {
                width: 100%;
            }
            
            .btn-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h1>Destinos Angola</h1>
        </div>
        <div class="user-actions">
            <a href="../admin/painel_destinos.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="form-title">
            <i class="fas fa-edit"></i>
            Editar Destino
        </div>

        <div class="form-container">
            <div class="angola-flag">
                <span class="flag-colors"></span>
                <span>Sistema de Gerenciamento de Destinos Turísticos - Angola</span>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'sucesso') !== false ? 'success' : 'error'; ?>">
                    <i class="fas <?php echo strpos($message, 'sucesso') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="editar_destino.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="nome"><i class="fas fa-signature"></i> Nome do Destino</label>
                            <input type="text" id="nome" name="nome" value="<?php echo $destino['nome_destino'] ?? $destino['nome'] ?? ''; ?>" required>
                        </div>

                        <div class="form-group field-disabled">
                            <label for="categoria"><i class="fas fa-tags"></i> Categoria</label>
                            <select id="categoria" name="categoria" disabled>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria'] ?? $categoria['id']; ?>" 
                                        <?php echo (isset($destino['id_categoria']) && $destino['id_categoria'] == ($categoria['id_categoria'] ?? $categoria['id'])) ? 'selected' : ''; ?>>
                                        <?php echo $categoria['nome_categoria'] ?? $categoria['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Campo oculto para manter o valor original -->
                            <input type="hidden" name="categoria_original" value="<?php echo $destino['id_categoria']; ?>">
                        </div>

                        <div class="form-group">
                            <label for="provincia"><i class="fas fa-map-marker-alt"></i> Província</label>
                            <select id="provincia" name="provincia" required>
                                <option value="">Selecione uma província</option>
                                <?php foreach ($provincias as $provincia): ?>
                                    <option value="<?php echo $provincia['id_provincia'] ?? $provincia['id']; ?>" 
                                        <?php echo (isset($destino['id_provincia']) && $destino['id_provincia'] == ($provincia['id_provincia'] ?? $provincia['id'])) ? 'selected' : ''; ?>>
                                        <?php echo $provincia['nome_provincia'] ?? $provincia['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group field-disabled">
                            <label for="localizacao"><i class="fas fa-location-arrow"></i> Endereço/Localização</label>
                            <input type="text" id="localizacao" name="localizacao" value="<?php echo $destino['localizacao_texto'] ?? $destino['nome_local'] ?? ''; ?>" disabled>
                            <input type="hidden" name="localizacao_original" value="<?php echo $destino['localizacao_texto'] ?? $destino['nome_local'] ?? ''; ?>">
                        </div>

                        <div class="coords-container field-disabled">
                            <div class="form-group">
                                <label for="latitude"><i class="fas fa-map-pin"></i> Latitude</label>
                                <input type="text" id="latitude" name="latitude" value="<?php echo $destino['latitude'] ?? ''; ?>" disabled>
                                <input type="hidden" name="latitude_original" value="<?php echo $destino['latitude'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="longitude"><i class="fas fa-map-pin"></i> Longitude</label>
                                <input type="text" id="longitude" name="longitude" value="<?php echo $destino['longitude'] ?? ''; ?>" disabled>
                                <input type="hidden" name="longitude_original" value="<?php echo $destino['longitude'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-group">
                            <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                            <textarea id="descricao" name="descricao" required><?php echo $destino['descricao'] ?? ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Imagem</label>
                            <?php if (isset($destino['imagem']) && !empty($destino['imagem'])): ?>
                                <div class="current-image">
                                    <?php 
                                    // Garantir que o caminho da imagem esteja formatado corretamente
                                    $imagemPath = htmlspecialchars($destino['imagem']);
                                    ?>
                                    <img src="../uploads/<?php echo $imagemPath; ?>" alt="Imagem do destino" class="image-preview">
                                    <small>Imagem atual. Envie uma nova imagem para substituí-la.</small>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">Clique ou arraste uma imagem aqui</div>
                                <div class="upload-subtext">Formatos aceitos: JPG, PNG ou GIF</div>
                                <input type="file" id="imagem" name="imagem" accept="image/jpeg, image/png, image/gif">
                            </div>
                        </div>

                        <div class="checkbox-container field-disabled">
                            <input type="checkbox" id="is_maravilha" name="is_maravilha" <?php echo ($destino['is_maravilha'] == 1) ? 'checked' : ''; ?> disabled>
                            <label for="is_maravilha" class="checkbox-label disabled">
                                <i class="fas fa-star"></i> Marcar como Maravilha de Angola
                            </label>
                            <input type="hidden" name="is_maravilha_original" value="<?php echo $destino['is_maravilha']; ?>">
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="../admin/painel_destinos.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Preview da imagem ao selecionar arquivo
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Verificar se já existe uma preview
            let preview = document.querySelector('.image-preview');
            
            // Se não existir, criar uma nova
            if (!preview) {
                const currentImageDiv = document.createElement('div');
                currentImageDiv.className = 'current-image';
                
                preview = document.createElement('img');
                preview.className = 'image-preview';
                
                const caption = document.createElement('small');
                caption.textContent = 'Nova imagem selecionada';
                
                currentImageDiv.appendChild(preview);
                currentImageDiv.appendChild(caption);
                
                const fileUploadDiv = document.querySelector('.file-upload');
                fileUploadDiv.parentNode.insertBefore(currentImageDiv, fileUploadDiv);
            }
            
            // Atualizar a imagem preview
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.alt = file.name;
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>