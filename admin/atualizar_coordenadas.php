<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/DestinoController.php';
require_once __DIR__ . '/../app/utils/GeocodingService.php';

use Config\Database;
use App\Controllers\DestinoController;
use App\Utils\GeocodingService;

echo "Iniciando atualização de coordenadas para destinos...\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    $destinoController = new DestinoController(); // Removido o parâmetro, já que o controller inicializa sua própria conexão
    
    // Buscar todos os destinos
    $query = "SELECT id, nome_destino, localizacao FROM destinos_turisticos";
    $result = $conn->query($query);
    
    // Contar os resultados com PDO
    $countQuery = "SELECT COUNT(*) FROM destinos_turisticos";
    $countStmt = $conn->query($countQuery);
    $total = $countStmt->fetchColumn();
    
    $atualizados = 0;
    
    echo "Total de destinos encontrados: $total\n";
    
    // Usar fetchAll para obter todos os resultados de uma vez
    $destinos = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($destinos as $destino) {
        echo "Processando destino: {$destino['nome_destino']} ({$destino['localizacao']})... ";
        
        $coordenadas = $destinoController->buscarEArmazenarCoordenadas($destino['id']);
        
        if ($coordenadas) {
            echo "SUCESSO! Lat: {$coordenadas['latitude']}, Lng: {$coordenadas['longitude']}\n";
            $atualizados++;
        } else {
            echo "FALHA! Não foi possível encontrar coordenadas.\n";
        }
        
        // Aguarda entre as requisições para respeitar limites de taxa da API
        sleep(1);
    }
    
    echo "\nProcessamento concluído!\n";
    echo "Total de destinos atualizados: $atualizados / $total\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}