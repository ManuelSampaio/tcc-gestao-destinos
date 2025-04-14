<?php

namespace App\Models;

use PDO;
use PDOException;

class DestinoModel {
    private $conn;

    public function __construct($conn) {
        if (!$conn instanceof PDO) {
            throw new PDOException("Conexão inválida fornecida para DestinoModel.");
        }
        $this->conn = $conn;
    }

    /**
     * Obtém todos os destinos turísticos.
     * @return array Lista de destinos.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function obterDestinoPorId($id) {
        try {
            $sql = "SELECT d.*, l.nome_local as localizacao, c.nome_categoria as categoria
                    FROM destinos_turisticos d
                    LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao
                    LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
                    WHERE d.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao obter destino: " . $e->getMessage());
        }
    }

    /**
     * Obtém todas as categorias de destinos.
     * @return array Lista de categorias.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function obterCategorias() {
        try {
            $sql = "SELECT * FROM categorias"; // Nome da tabela
            $query = $this->conn->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao obter categorias: " . $e->getMessage());
        }
    }

    /**
     * Obtém um destino específico pelo ID.
     * @param int $id ID do destino.
     * @return array Detalhes do destino.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function obterDestinos() {
        try {
            $sql = "SELECT d.*, c.nome_categoria as categoria, l.nome_local as localizacao 
                    FROM destinos_turisticos d
                    LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
                    LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao";
            $query = $this->conn->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao obter destinos: " . $e->getMessage());
        }
    }

    /**
     * Cadastra um novo destino.
     * @param string $nome Nome do destino.
     * @param string $descricao Descrição do destino.
     * @param string $localizacao Localização do destino.
     * @param string $imagem Nome do arquivo da imagem.
     * @return bool Resultado da operação.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function cadastrarDestino($nome, $descricao, $id_localizacao, $imagem, $id_categoria = null) {
        try {
            $sql = "INSERT INTO destinos_turisticos (nome_destino, descricao, id_localizacao, imagem, id_categoria) 
                    VALUES (:nome, :descricao, :id_localizacao, :imagem, :id_categoria)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':id_localizacao', $id_localizacao, PDO::PARAM_INT);
            $stmt->bindParam(':imagem', $imagem);
            $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao cadastrar destino: " . $e->getMessage());
        }
    }

    /**
     * Atualiza um destino existente.
     * @param int $id ID do destino.
     * @param string $nome Nome do destino.
     * @param string $descricao Descrição do destino.
     * @param string $localizacao Localização do destino.
     * @param string $imagem Nome do arquivo da imagem.
     * @return bool Resultado da operação.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function atualizarDestino($id, $nome, $descricao, $id_localizacao, $imagem, $id_categoria = null) {
        try {
            $sql = "UPDATE destinos_turisticos 
                    SET nome_destino = :nome, descricao = :descricao, id_localizacao = :id_localizacao, 
                    imagem = :imagem, id_categoria = :id_categoria
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':id_localizacao', $id_localizacao, PDO::PARAM_INT);
            $stmt->bindParam(':imagem', $imagem);
            $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao atualizar destino: " . $e->getMessage());
        }
    }

    /**
     * Exclui um destino pelo ID.
     * @param int $id ID do destino.
     * @return bool Resultado da operação.
     * @throws PDOException Caso ocorra um erro na consulta.
     */
    public function excluirDestino($id) {
        try {
            $sql = "DELETE FROM destinos_turisticos WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao excluir destino: " . $e->getMessage());
        }
    }

    /**
 * Obtém todas as localizações.
 * @return array Lista de localizações.
 * @throws PDOException Caso ocorra um erro na consulta.
 */
public function obterLocalizacoes() {
    try {
        $sql = "SELECT * FROM localizacoes";
        $query = $this->conn->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new PDOException("Erro ao obter localizações: " . $e->getMessage());
    }
}

/**
 * Obtém uma localização pelo ID.
 * @param int $id ID da localização.
 * @return array Detalhes da localização.
 * @throws PDOException Caso ocorra um erro na consulta.
 */
public function obterLocalizacaoPorId($id) {
    try {
        $sql = "SELECT * FROM localizacoes WHERE id_localizacao = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new PDOException("Erro ao obter localização: " . $e->getMessage());
    }
}
}