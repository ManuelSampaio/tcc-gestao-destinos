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

    public function cadastrarDestino($nome, $descricao, $localizacao, $imagem, $categoriaId) {
        try {
            if (empty($nome) || empty($descricao) || empty($localizacao) || empty($categoriaId)) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
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
            $uploadedFile = 'imagem_padrao.jpg'; // Valor padrão caso não seja enviada imagem

            if (is_array($imagem) && isset($imagem['tmp_name']) && $imagem['tmp_name']) {
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception("Tipo de arquivo inválido. Somente imagens (jpg, jpeg, png, gif) são permitidas.");
                }

                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Erro ao criar diretório de uploads.");
                }

                $uniqueFileName = uniqid() . '.' . $extension;
                $filePath = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($imagem['tmp_name'], $filePath)) {
                    throw new Exception("Erro ao mover o arquivo para o diretório de uploads.");
                }

                $uploadedFile = $uniqueFileName;
            } else if (is_string($imagem) && !empty($imagem)) {
                // Se $imagem já for um caminho de arquivo (string)
                $uploadedFile = basename($imagem);
            }

            $sql = "INSERT INTO destinos_turisticos (nome_destino, descricao, localizacao, imagem, id_categoria) 
                    VALUES (:nome, :descricao, :localizacao, :imagem, :categoria)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->bindParam(':imagem', $uploadedFile);
            $stmt->bindParam(':categoria', $categoriaId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao cadastrar destino.", $e);
            return false;
        }
    }

    public function listarDestinos() {
        try {
            $sql = "SELECT d.*, c.nome_categoria 
                    FROM destinos_turisticos d
                    LEFT JOIN categorias c ON d.id_categoria = c.id
                    ORDER BY d.nome_destino ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar destinos.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar destinos.", $e);
            return [];
        }
    }

    public function listarCategorias() {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nome_categoria ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar categorias.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar categorias.", $e);
            return [];
        }
    }

    public function obterDetalhes($id) {
        return $this->getDestinoById($id);
    }

    public function getDestinoById($id) {
        try {
            if (!is_numeric($id)) {
                throw new Exception("ID inválido.");
            }

            $sql = "SELECT d.*, c.nome_categoria 
                    FROM destinos_turisticos d
                    LEFT JOIN categorias c ON d.id_categoria = c.id
                    WHERE d.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $destino = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$destino) {
                throw new Exception("Destino com o ID especificado não encontrado.");
            }

            return $destino;
        } catch (Exception $e) {
            $this->handleError("Erro ao obter detalhes do destino.", $e);
            return null;
        }
    }

    public function atualizarDestino($id, $nome, $descricao, $localizacao, $imagem = null, $categoriaId = null) {
        try {
            if (empty($nome) || empty($descricao) || empty($localizacao) || empty($categoriaId)) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            $sql = "UPDATE destinos_turisticos 
                    SET nome_destino = :nome, descricao = :descricao, localizacao = :localizacao, 
                        id_categoria = :categoria";

            $params = [
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':localizacao' => $localizacao,
                ':categoria' => $categoriaId,
                ':id' => $id
            ];

            if ($imagem && is_array($imagem) && $imagem['tmp_name']) {
                $uploadDir = __DIR__ . '/../../uploads/';
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception("Tipo de arquivo inválido.");
                }

                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Erro ao criar diretório de uploads.");
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
            
            if (!$stmt->execute($params)) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao atualizar destino.", $e);
            return false;
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
            return false;
        }
    }

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
        // Removido o die() para permitir que o script continue mesmo com erros
        return false;
    }
}