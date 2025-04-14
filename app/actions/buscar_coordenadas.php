<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/controllers/DestinoController.php';

use Config\Database;
use App\Controllers\DestinoController;

header('Content-Type: application/json');

try {
    $destinoId = $_GET['id_destino'] ?? null;
    
    if (!$destinoId) {
        throw new Exception("ID do destino não fornecido");
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $destinoController = new DestinoController($conn);
    $coordenadas = $destinoController->buscarEArmazenarCoordenadas($destinoId);
    
    if ($coordenadas) {
        echo json_encode([
            'success' => true,
            'coordenadas' => $coordenadas
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensagem' => 'Não foi possível encontrar coordenadas para este destino'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mensagem' => $e->getMessage()
    ]);
}