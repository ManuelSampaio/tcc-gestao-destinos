<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/DestinoController.php';
require_once __DIR__ . '/../app/utils/GeocodingService.php';

use Config\Database;
use App\Controllers\DestinoController;
use App\Utils\GeocodingService;

// Verificar se o usuário está logado e é administrador
// [adicione sua lógica de autenticação aqui]

$mensagem = '';
$tipo = '';

try {
    $database = new Database();
    $conn = $database->getConnection();
    $destinoController = new DestinoController(); // Removido o parâmetro
    
    // Buscar todas as coordenadas
    $query = "SELECT dt.id, dt.nome_destino, dt.localizacao, 
                    l.latitude, l.longitude, l.id_localizacao
             FROM destinos_turisticos dt
             LEFT JOIN localizacoes l ON dt.id_localizacao = l.id_localizacao
             ORDER BY dt.nome_destino";
             
    $result = $conn->query($query);
    $destinos = [];
    
    if ($result) {
        $destinos = $result->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Processar atualização manual de coordenadas
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $destinoId = $_POST['destino_id'] ?? null;
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        
        if ($destinoId && $latitude !== null && $longitude !== null) {
            $destino = $destinoController->obterCoordenadas($destinoId);
            $idLocalizacao = $destino['id_localizacao'];
            
            if ($idLocalizacao) {
                // Atualiza localização existente
                $query = "UPDATE localizacoes 
                         SET latitude = ?, longitude = ? 
                         WHERE id_localizacao = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$latitude, $longitude, $idLocalizacao]);
            } else {
                // Cria nova localização
                $query = "INSERT INTO localizacoes (provincia, latitude, longitude) 
                         VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([$destino['localizacao'], $latitude, $longitude]);
                
                $idLocalizacao = $conn->lastInsertId();
                
                // Atualiza o destino com o id da localização
                $query = "UPDATE destinos_turisticos SET id_localizacao = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$idLocalizacao, $destinoId]);
            }
            
            $mensagem = "Coordenadas atualizadas com sucesso!";
            $tipo = "success";
            
            // Recarregar dados
            header("Location: coordenadas.php");
            exit;
        }
    }
    
    // Processar busca automática de coordenadas
    if (isset($_GET['atualizar']) && $_GET['atualizar'] === 'todos') {
        $geocoder = new GeocodingService();
        $atualizados = 0;
        
        foreach ($destinos as $destino) {
            if (empty($destino['latitude']) || empty($destino['longitude'])) {
                $coordenadas = $destinoController->buscarEArmazenarCoordenadas($destino['id']);
                if ($coordenadas) {
                    $atualizados++;
                }
            }
        }
        
        $mensagem = "Foram atualizadas coordenadas de $atualizados destinos.";
        $tipo = "success";
        
        // Recarregar dados
        header("Location: coordenadas.php");
        exit;
    }
    
    // Processar busca individual
    if (isset($_GET['atualizar']) && $_GET['atualizar'] === 'destino' && isset($_GET['id'])) {
        $destinoId = $_GET['id'];
        $coordenadas = $destinoController->buscarEArmazenarCoordenadas($destinoId);
        
        if ($coordenadas) {
            $mensagem = "Coordenadas atualizadas com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Não foi possível encontrar coordenadas para este destino.";
            $tipo = "danger";
        }
        
        // Recarregar dados
        header("Location: coordenadas.php");
        exit;
    }
} catch (Exception $e) {
    $mensagem = "Erro: " . $e->getMessage();
    $tipo = "danger";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Coordenadas - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        .mapa-preview {
            height: 250px;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .coordenadas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .destino-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
        }
        
        .destino-card h3 {
            margin-top: 0;
            color: #004d40;
        }
        
        .coordenadas-form {
            margin-top: 10px;
        }
        
        .coordenadas-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .coordenadas-form button {
            background: #004d40;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .coordenadas-form button:hover {
            background: #00352c;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-ok {
            background-color: #4CAF50;
        }
        
        .status-missing {
            background-color: #F44336;
        }
        
        .acoes-gerais {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <!-- Cabeçalho do painel admin aqui -->
    </header>
    
    <main>
        <h1>Gerenciar Coordenadas de Destinos</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>
        
        <div class="acoes-gerais">
            <a href="?atualizar=todos" class="btn btn-primary">
                <i class="fas fa-sync"></i> Buscar Coordenadas para Todos os Destinos
            </a>
        </div>
        
        <div class="coordenadas-grid">
            <?php foreach ($destinos as $destino): ?>
                <div class="destino-card">
                    <h3>
                        <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                            <span class="status-indicator status-ok" title="Coordenadas disponíveis"></span>
                        <?php else: ?>
                            <span class="status-indicator status-missing" title="Coordenadas não disponíveis"></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($destino['nome_destino']) ?>
                    </h3>
                    <p><strong>Localização:</strong> <?= htmlspecialchars($destino['localizacao']) ?></p>
                    
                    <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                        <p>
                            <strong>Coordenadas:</strong> 
                            <?= $destino['latitude'] ?>, <?= $destino['longitude'] ?>
                        </p>
                        <div id="mapa-<?= $destino['id'] ?>" class="mapa-preview"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const map = L.map('mapa-<?= $destino['id'] ?>').setView([<?= $destino['latitude'] ?>, <?= $destino['longitude'] ?>], 13);
                                
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                                }).addTo(map);
                                
                                L.marker([<?= $destino['latitude'] ?>, <?= $destino['longitude'] ?>]).addTo(map)
                                    .bindPopup("<?= htmlspecialchars($destino['nome_destino']) ?>")
                                    .openPopup();
                            });
                        </script>
                    <?php else: ?>
                        <p><em>Coordenadas não disponíveis</em></p>
                    <?php endif; ?>
                    
                    <form class="coordenadas-form" method="post">
                        <input type="hidden" name="destino_id" value="<?= $destino['id'] ?>">
                        <input type="text" name="latitude" placeholder="Latitude" value="<?= htmlspecialchars($destino['latitude'] ?? '') ?>">
                        <input type="text" name="longitude" placeholder="Longitude" value="<?= htmlspecialchars($destino['longitude'] ?? '') ?>">
                        <button type="submit">Atualizar</button>
                    </form>
                    
                    <p>
                        <a href="?atualizar=destino&id=<?= $destino['id'] ?>" class="btn btn-sm">
                            <i class="fas fa-search"></i> Buscar coordenadas
                        </a>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>