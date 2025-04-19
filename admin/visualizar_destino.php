<?php
// Incluir configuração de banco de dados
require_once('../config/database.php');
require_once('../app/controllers/DestinoController.php');

use Config\Database;
use App\Controllers\DestinoController;

// Verificar se foi enviado um ID válido
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirecionar para a página de listagem com mensagem de erro
    header('Location: gerenciar_destinos.php?erro=id_invalido');
    exit;
}

$id = (int)$_GET['id'];

// Inicializa o controlador de destinos
$destinoController = new DestinoController();

// Obter informações detalhadas do destino
$destino = $destinoController->obterDestinoDetalhado($id);

if (!$destino) {
    // Destino não encontrado
    header('Location: gerenciar_destinos.php?erro=destino_nao_encontrado');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Destino - Destinos Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #004d40;
            --primary-light: #006355;
            --primary-dark: #003b32;
            --secondary: #ff9800;
            --secondary-light: #ffb74d;
            --text-dark: #333333;
            --text-light: #6c757d;
            --background: #f5f5f5;
            --white: #ffffff;
            --border: #e0e0e0;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
        }

        .top-bar {
            background-color: var(--primary);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            background-color: var(--white);
            color: var(--primary);
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
            color: var(--white);
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

        .content-wrapper {
            padding: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 500;
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-edit {
            background-color: var(--secondary);
            color: var(--white);
        }

        .btn-edit:hover {
            background-color: #f57c00;
        }

        .btn-delete {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-delete:hover {
            background-color: #d32f2f;
        }

        .destino-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .destino-image {
            position: relative;
            height: 100%;
            min-height: 400px;
        }

        .destino-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .destino-details {
            padding: 30px;
        }

        .destino-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .destino-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-light);
        }

        .meta-item i {
            color: var(--secondary);
        }

        .destino-description {
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .destino-location {
            margin-bottom: 24px;
        }

        .location-title {
            font-size: 18px;
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: var(--text-dark);
        }

        .coordenadas {
            background-color: rgba(0, 77, 64, 0.05);
            padding: 6px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 500;
        }

        .badge-featured {
            background-color: rgba(255, 152, 0, 0.12);
            color: var(--secondary);
        }

        .badge i {
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .destino-container {
                grid-template-columns: 1fr;
            }

            .destino-image {
                height: 300px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .destino-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="top-bar">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h1>Destinos Angola</h1>
        </div>
        <div class="user-actions">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
            <a href="../admin/painel_destinos.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Detalhes do Destino</h2>
            <div class="action-buttons">
                <a href="editar_destino.php?id=<?php echo $destino['id']; ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i>
                    Editar Destino
                </a>
                <a href="javascript:void(0);" onclick="confirmarExclusao(<?php echo $destino['id']; ?>)" class="btn btn-delete">
                    <i class="fas fa-trash"></i>
                    Excluir Destino
                </a>
            </div>
        </div>

        <!-- Destino Details -->
        <div class="destino-image">
    <?php if (!empty($destino['imagem'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($destino['imagem']); ?>" 
             alt="<?php echo htmlspecialchars($destino['nome_destino']); ?>">
    <?php else: ?>
        <img src="../assets/img/sem-imagem.jpg" alt="Imagem não disponível">
    <?php endif; ?>
</div>
    <div class="destino-details">
        <h1 class="destino-title">
            <?php echo htmlspecialchars($destino['nome_destino']); ?>
            <?php if(isset($destino['is_maravilha']) && $destino['is_maravilha'] == 1): ?>
                <span class="badge badge-featured"><i class="fas fa-crown"></i> Maravilha de Angola</span>
            <?php endif; ?>
        </h1>
        
        <div class="destino-meta">
            <div class="meta-item">
                <i class="fas fa-tag"></i>
                <span><?php echo isset($destino['nome_categoria']) ? htmlspecialchars($destino['nome_categoria']) : 'Não categorizado'; ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Cadastrado em <?php echo isset($destino['data_cadastro']) && !empty($destino['data_cadastro']) ? date('d/m/Y', strtotime($destino['data_cadastro'])) : 'Data desconhecida'; ?></span>
            </div>
        </div>
        
        <div class="destino-description">
            <p><?php echo nl2br(htmlspecialchars($destino['descricao'])); ?></p>
        </div>
        
        <div class="destino-location">
            <h3 class="location-title">
                <i class="fas fa-map-marker-alt"></i> Localização
            </h3>
            <div class="location-info">
                <span>
                    <?php echo isset($destino['nome_local']) ? htmlspecialchars($destino['nome_local']) : 'Local não especificado'; ?>
                    <?php if(isset($destino['nome_provincia'])): ?>, <?php echo htmlspecialchars($destino['nome_provincia']); ?><?php endif; ?>
                </span>
            </div>
            
            <?php if(isset($destino['latitude']) && isset($destino['longitude']) && !empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                <div class="meta-item" style="margin-top: 10px;">
                    <i class="fas fa-location-arrow"></i>
                    <span class="coordenadas">
                        Lat: <?php echo htmlspecialchars($destino['latitude']); ?>, 
                        Long: <?php echo htmlspecialchars($destino['longitude']); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id) {
            if (confirm("Tem certeza que deseja excluir este destino? Esta ação não pode ser desfeita.")) {
                window.location.href = "excluir_destino.php?id=" + id;
            }
        }
    </script>
</body>
</html>