<?php
namespace App\Models;

use PDO;
use PDOException;

class Avaliacao {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function adicionarAvaliacao(array $dados): bool {
        try {
            $sql = "INSERT INTO avaliacoes (id_usuario, id_destino, nota, comentario, data_avaliacao) 
                    VALUES (:id_usuario, :id_destino, :nota, :comentario, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $dados['id_usuario'],
                ':id_destino' => $dados['id_destino'],
                ':nota' => $dados['nota'],
                ':comentario' => $dados['comentario'] ?? null
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao adicionar avaliação: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarAvaliacao(int $idUsuario, int $idDestino, array $dados): bool {
        try {
            $sql = "UPDATE avaliacoes 
                    SET nota = :nota, comentario = :comentario, data_avaliacao = NOW() 
                    WHERE id_usuario = :id_usuario AND id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nota' => $dados['nota'],
                ':comentario' => $dados['comentario'] ?? null,
                ':id_usuario' => $idUsuario,
                ':id_destino' => $idDestino
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar avaliação: " . $e->getMessage());
            return false;
        }
    }

    public function buscarAvaliacoesPorDestino(int $idDestino): array {
        try {
            $sql = "SELECT a.*, u.nome AS nome_usuario 
                    FROM avaliacoes a
                    JOIN usuarios u ON a.id_usuario = u.id_usuario
                    WHERE a.id_destino = :id_destino
                    ORDER BY a.data_avaliacao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_destino' => $idDestino]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar avaliações: " . $e->getMessage());
            return [];
        }
    }

    public function verificarSeJaAvaliou(int $idUsuario, int $idDestino): bool {
        try {
            $sql = "SELECT COUNT(*) AS total 
                    FROM avaliacoes 
                    WHERE id_usuario = :id_usuario AND id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':id_destino' => $idDestino
            ]);
            
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao verificar avaliação: " . $e->getMessage());
            return false;
        }
    }

    public function calcularMediaAvaliacoes(int $idDestino): float {
        try {
            $sql = "SELECT ROUND(AVG(nota), 1) AS media_nota 
                    FROM avaliacoes 
                    WHERE id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_destino' => $idDestino]);
            
            return (float) ($stmt->fetchColumn() ?: 0);
        } catch (PDOException $e) {
            error_log("Erro ao calcular média de avaliações: " . $e->getMessage());
            return 0.0;
        }
    }

    public function contarAvaliacoes(int $idDestino): int {
        try {
            $sql = "SELECT COUNT(*) AS total 
                    FROM avaliacoes 
                    WHERE id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_destino' => $idDestino]);
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar avaliações: " . $e->getMessage());
            return 0;
        }
    }

    public function obterEstatisticasAvaliacoes(int $idDestino): array {
        try {
            $sql = "SELECT nota, COUNT(*) AS quantidade 
                    FROM avaliacoes 
                    WHERE id_destino = :id_destino
                    GROUP BY nota
                    ORDER BY nota DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_destino' => $idDestino]);
            
            $estatisticas = array_fill(1, 5, 0);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $estatisticas[$row['nota']] = $row['quantidade'];
            }
            
            return $estatisticas;
        } catch (PDOException $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return array_fill(1, 5, 0);
        }
    }

    public function buscarAvaliacaoUsuario(int $idUsuario, int $idDestino): array|false {
        try {
            $sql = "SELECT * FROM avaliacoes 
                    WHERE id_usuario = :id_usuario AND id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':id_destino' => $idDestino
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log("Erro ao buscar avaliação do usuário: " . $e->getMessage());
            return false;
        }
    }
}
