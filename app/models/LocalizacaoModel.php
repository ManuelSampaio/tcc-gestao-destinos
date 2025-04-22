<?php 

namespace App\Models;

use PDO;
use Exception;
// Importando a classe Database do namespace Config
use Config\Database;

class LocalizacaoModel {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    /**
     * Insere uma nova localização
     *
     * @param string $nomeLocal Nome do local
     * @param string|null $latitude Latitude (opcional)
     * @param string|null $longitude Longitude (opcional)
     * @param int $provinciaId ID da província
     * @return int|bool ID da localização inserida ou false em caso de erro
     */
    public function inserir($nomeLocal, $latitude, $longitude, $provinciaId) {
        try {
            $query = "INSERT INTO localizacoes (nome_local, latitude, longitude, id_provincia)
                      VALUES (:nome_local, :latitude, :longitude, :id_provincia)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome_local', $nomeLocal);
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':longitude', $longitude);
            $stmt->bindParam(':id_provincia', $provinciaId);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log('Erro ao inserir localização: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Atualiza uma localização existente
     * 
     * @param int $idLocalizacao ID da localização a ser atualizada
     * @param string $nomeLocal Nome do local
     * @param int|null $idProvincia ID da província (opcional)
     * @param float|null $latitude Latitude (opcional)
     * @param float|null $longitude Longitude (opcional)
     * @return bool Sucesso ou falha da operação
     */
    public function atualizar($idLocalizacao, $nomeLocal, $idProvincia = null, $latitude = null, $longitude = null) {
        try {
            $sql = "UPDATE localizacoes 
                    SET nome_local = :nome_local";
            
            $params = [
                ':nome_local' => $nomeLocal,
                ':id_localizacao' => $idLocalizacao
            ];
            
            // Adiciona campos opcionais se fornecidos
            if ($idProvincia !== null) {
                $sql .= ", id_provincia = :id_provincia";
                $params[':id_provincia'] = $idProvincia;
            }
            
            if ($latitude !== null) {
                $sql .= ", latitude = :latitude";
                $params[':latitude'] = $latitude;
            }
            
            if ($longitude !== null) {
                $sql .= ", longitude = :longitude";
                $params[':longitude'] = $longitude;
            }
            
            $sql .= " WHERE id_localizacao = :id_localizacao";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception("Erro ao atualizar localização: " . implode(", ", $stmt->errorInfo()));
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Erro ao atualizar localização: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Retorna a conexão com o banco de dados
     *
     * @return PDO Conexão com o banco de dados
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Busca uma localização pelo ID
     *
     * @param int $id ID da localização
     * @return array|bool Dados da localização ou false em caso de erro
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM localizacoes WHERE id_localizacao = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao buscar localização: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca uma localização pelo nome e ID da província
     *
     * @param int $provinciaId ID da província
     * @param string $nomeLocal Nome do local
     * @return array|bool Dados da localização ou false em caso de erro
     */
    public function buscarPorProvinciaENome($provinciaId, $nomeLocal) {
        try {
            $query = "SELECT * FROM localizacoes 
                     WHERE id_provincia = :provincia_id 
                     AND nome_local = :nome_local 
                     LIMIT 1";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':provincia_id', $provinciaId, PDO::PARAM_INT);
            $stmt->bindParam(':nome_local', $nomeLocal, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao buscar localização por província e nome: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lista todas as localizações
     *
     * @return array|bool Lista de localizações ou false em caso de erro
     */
    public function listarTodos() {
        try {
            $query = "SELECT l.*, p.nome_provincia
                      FROM localizacoes l
                     JOIN provincias p ON l.id_provincia = p.id_provincia
                     ORDER BY l.nome_local";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao listar localizações: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Exclui uma localização pelo ID
     *
     * @param int $id ID da localização a ser excluída
     * @return bool True se for excluída com sucesso, False caso contrário
     */
    public function excluir($id) {
        try {
            $query = "DELETE FROM localizacoes WHERE id_localizacao = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Erro ao excluir localização: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca localizações por província
     *
     * @param int $provinciaId ID da província
     * @return array Lista de localizações da província
     */
    public function buscarPorProvincia($provinciaId) {
        try {
            $query = "SELECT * FROM localizacoes WHERE id_provincia = :provincia_id ORDER BY nome_local";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':provincia_id', $provinciaId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao buscar localizações por província: ' . $e->getMessage());
            throw $e;
        }
    }
}