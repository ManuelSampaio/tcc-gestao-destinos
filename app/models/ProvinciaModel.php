<?php
namespace App\Models;

require_once __DIR__ . '/../../config/Database.php';
use Config\Database;
use PDO;
use Exception;

class ProvinciaModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = Database::getConnection();
    }

    /**
     * Lista todas as províncias
     *
     * @return array|bool Lista de províncias ou false em caso de erro
     */
    public function listarTodas() {
        try {
            $query = "SELECT * FROM provincias ORDER BY nome_provincia";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao listar províncias: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca uma província pelo ID
     *
     * @param int $id ID da província
     * @return array|bool Dados da província ou false em caso de erro
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM provincias WHERE id_provincia = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao buscar província: ' . $e->getMessage());
            throw $e;
        }
    }
}