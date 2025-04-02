<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/database.php';

use Config\Database;
use PDO;
use Exception;

class DestinoController {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();

            if (!$this->db) {
                throw new Exception("Falha ao conectar com o banco de dados.");
            }
        } catch (Exception $e) {
            $this->handleError("Erro ao inicializar o controlador de destinos.", $e);
        }
    }

    public function cadastrarDestino($nome, $descricao, $localizacao, $imagem) {
        try {
            if (empty($nome) || empty($descricao) || empty($localizacao)) {
                throw new Exception("Todos os campos devem ser preenchidos.");
            }

            $sqlCheck = "SELECT COUNT(*) FROM destinos_turisticos WHERE nome_destino = :nome";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':nome', $nome);
            $stmtCheck->execute();

            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Já existe um destino com este nome.");
            }

            $uploadDir = __DIR__ . '/../../uploads/';
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $uploadedFile = null;

            if (is_array($imagem) && isset($imagem['tmp_name']) && $imagem['tmp_name']) {
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception("Tipo de arquivo inválido. Somente imagens (jpg, jpeg, png, gif) são permitidas.");
                }

                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Erro ao criar diretório de uploads.");
                }

                $uniqueFileName = uniqid() . '.' . $extension;
                $uploadedFile = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($imagem['tmp_name'], $uploadedFile)) {
                    throw new Exception("Erro ao mover o arquivo para o diretório de uploads.");
                }

                $uploadedFile = $uniqueFileName;
            }

            $sql = "INSERT INTO destinos_turisticos (nome_destino, descricao, localizacao, imagem) 
                    VALUES (:nome, :descricao, :localizacao, :imagem)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->bindParam(':imagem', $uploadedFile);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao cadastrar destino.", $e);
        }
    }

    public function listarDestinos() {
        try {
            $sql = "SELECT * FROM destinos_turisticos ORDER BY nome_destino ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar destinos.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar destinos.", $e);
        }
    }

    public function listarCategorias() {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nome_categoria ASC"; // Supondo que você tenha uma tabela 'categorias'
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar categorias.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar categorias.", $e);
        }
    }

    // Método para obter detalhes de um destino específico
    public function obterDetalhes($id) {
        return $this->getDestinoById($id); // Chama o método existente
    }

    public function getDestinoById($id) {
        try {
            if (!is_numeric($id)) {
                throw new Exception("ID inválido.");
            }

            $sql = "SELECT * FROM destinos_turisticos WHERE id = :id";            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $destino = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$destino) {
                throw new Exception("Destino com o ID especificado não encontrado.");
            }

            return $destino;
        } catch (Exception $e) {
            $this->handleError("Erro ao obter detalhes do destino.", $e);
        }
    }

    public function atualizarDestino($id, $nome, $descricao, $localizacao, $imagem = null) {
        try {
            if (empty($nome) || empty($descricao) || empty($localizacao)) {
                throw new Exception("Todos os campos devem ser preenchidos.");
            }

            $sql = "UPDATE destinos_turisticos 
                    SET nome_destino = :nome, descricao = :descricao, localizacao = :localizacao";

            $params = [
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':localizacao' => $localizacao,
                ':id' => $id
            ];

            if ($imagem) {
                $uploadDir = __DIR__ . '/../../uploads/';
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception("Tipo de arquivo inválido.");
                }

                $uniqueFileName = uniqid() . '.' . $extension;
                $uploadedFile = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($imagem['tmp_name'], $uploadedFile)) {
                    throw new Exception("Erro ao mover o arquivo para o diretório de uploads.");
                }

                $sql .= ", imagem = :imagem";
                $params[':imagem'] = $uniqueFileName;
            }

            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao atualizar destino.", $e);
        }
    }

    public function excluirDestino($id) {
        try {
            $sql = "DELETE FROM destinos_turisticos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao excluir destino.");
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao excluir destino.", $e);
        }
    }

    // Adicione estes métodos dentro da classe DestinoController

public function buscarAvaliacoesPorDestino($idDestino) {
    try {
        $sql = "SELECT a.*, u.nome as nome_usuario 
                FROM avaliacoes a
                JOIN usuarios u ON a.id_usuario = u.id_usuario
                WHERE a.id_destino = :id_destino
                ORDER BY a.data_avaliacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_destino', $idDestino, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $this->handleError("Erro ao buscar avaliações do destino.", $e);
        return [];
    }
}

public function calcularMediaAvaliacoes($idDestino) {
    try {
        $sql = "SELECT ROUND(AVG(nota), 1) as media_nota 
                FROM avaliacoes 
                WHERE id_destino = :id_destino";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_destino', $idDestino, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['media_nota'] ?? 0;
    } catch (Exception $e) {
        $this->handleError("Erro ao calcular média de avaliações.", $e);
        return 0;
    }
}

    private function handleError($message, Exception $e) {
        $fullMessage = $message . " Detalhes: " . $e->getMessage();
        echo $fullMessage;
        error_log($fullMessage);
        die(); // Opcional, interrompe a execução em caso de erro grave
    }
}

