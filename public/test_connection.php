<?php
require_once '../config/database.php'; // Caminho corrigido

use Config\Database; // Importando a classe Database do namespace Config

function verificarTabela($conn, $tabela) {
    $query = "SHOW TABLES LIKE '$tabela'"; // Corrigido: sem placeholders
    $stmt = $conn->query($query);
    return $stmt->rowCount() > 0;
}

function obterDestinos($conn) {
    $query = "SELECT DISTINCT nome_destino, descricao FROM destinos_turisticos"; // Nome da tabela corrigido
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os resultados como array associativo
}

try {
    // Instancia a classe Database e obtém a conexão
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "<p style='color: green; font-weight: bold;'>✅ Conexão com o banco de dados bem-sucedida!</p>";
    } else {
        throw new Exception("❌ Erro ao estabelecer conexão com o banco de dados.");
    }

    // Verifica se a tabela 'destino_turisticos' existe
    if (!verificarTabela($conn, 'destinos_turisticos')) {
        throw new Exception("⚠️ A tabela 'destinos_turisticos' não existe no banco de dados.");
    }

    // Recupera os destinos turísticos
    $destinos = obterDestinos($conn);

    // Apresentação dos dados
    echo "<div style='font-family: Arial, sans-serif; margin: 20px;'>";
    echo "<h1 style='color: #003366;'>🌍 Destinos Turísticos</h1>";

    if (!empty($destinos)) {
        echo "<ul style='list-style-type: none; padding: 0;'>";
        foreach ($destinos as $destino) {
            echo "<li style='margin-bottom: 20px;'>";
            echo "<h2 style='color: #007BFF;'>" . htmlspecialchars($destino['nome_destino']) . "</h2>";
            echo "<p style='color: #555;'>" . htmlspecialchars($destino['descricao']) . "</p>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum destino encontrado no banco de dados.</p>";
    }
    echo "</div>";
} catch (PDOException $e) {
    // Tratamento de erros relacionados ao banco
    echo "<p style='color: red;'>❌ Erro no banco de dados: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    // Tratamento de erros genéricos
    echo "<p style='color: red;'>⚠️ " . htmlspecialchars($e->getMessage()) . "</p>";
} finally {
    // Fecha a conexão para evitar desperdício de recursos
    $conn = null;
    echo "<p style='font-family: Arial, sans-serif;'>🔒 Conexão encerrada.</p>";
}
?>
