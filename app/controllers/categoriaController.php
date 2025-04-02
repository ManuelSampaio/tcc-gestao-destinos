<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/database.php';

use Config\Database;
use PDO;
use Exception;

class CategoriaController {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();

            if (!$this->db) {
                throw new Exception("Falha ao conectar com o banco de dados.");
            }
        } catch (Exception $e) {
            error_log("Erro ao inicializar CategoriaController: " . $e->getMessage());
            throw new Exception("Erro ao inicializar o controlador de categorias.");
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
            error_log("Erro ao listar categorias: " . $e->getMessage());
            return [];
        }
    }

    public function cadastrarCategoria($nome) {
        try {
            if (empty($nome)) {
                throw new Exception("O nome da categoria deve ser preenchido.");
            }

            $sqlCheck = "SELECT COUNT(*) FROM categorias WHERE nome_categoria = :nome";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':nome', $nome);
            $stmtCheck->execute();

            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Já existe uma categoria com este nome.");
            }

            $sql = "INSERT INTO categorias (nome_categoria) VALUES (:nome)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao cadastrar categoria: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarCategoria($id, $nome) {
        try {
            if (empty($nome)) {
                throw new Exception("O nome da categoria deve ser preenchido.");
            }

            $sql = "UPDATE categorias SET nome_categoria = :nome WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            return false;
        }
    }

    public function excluirCategoria($id) {
        try {
            $sql = "DELETE FROM categorias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao excluir categoria.");
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            return false;
        }
    }
}