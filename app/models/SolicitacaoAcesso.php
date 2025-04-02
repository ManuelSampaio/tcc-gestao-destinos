<?php

namespace App\Models;

use PDO;
use Exception;
use Config\Database;

class SolicitacaoAcesso
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = (new Database())->getConnection();
        } catch (Exception $e) {
            error_log("[Erro] Conexão com o banco falhou: " . $e->getMessage());
            throw new Exception("Erro ao conectar ao banco de dados.");
        }
    }

    /**
     * Registrar uma nova solicitação de mudança de nível
     */
    public function solicitarAcesso(int $id_usuario, string $novo_nivel): bool
    {
        try {
            $sql = "INSERT INTO solicitacoes_acesso (id_usuario, novo_nivel, status, data_solicitacao) 
                    VALUES (:id_usuario, :novo_nivel, 'pendente', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':novo_nivel', $novo_nivel, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[Erro] Falha ao registrar solicitação de acesso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas as solicitações pendentes
     */
    public function listarSolicitacoesPendentes(): array
    {
        try {
            $sql = "SELECT s.id_solicitacao, u.id_usuario, u.email, s.novo_nivel, s.status, s.data_solicitacao
                    FROM solicitacoes_acesso s
                    JOIN usuarios u ON s.id_usuario = u.id_usuario
                    WHERE s.status = 'pendente'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[Erro] Falha ao listar solicitações pendentes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualizar status da solicitação (Aprovar ou Rejeitar)
     */
    public function atualizarStatus(int $id_solicitacao, string $status, int $id_aprovador): bool
    {
        $status_permitidos = ['aprovado', 'rejeitado'];

        if (!in_array($status, $status_permitidos, true)) {
            error_log("[Erro] Status inválido: {$status}");
            return false;
        }

        try {
            $sql = "UPDATE solicitacoes_acesso 
        SET status = :status, aprovado_por = :aprovado_por, data_aprovacao = NOW() 
        WHERE id = :id_solicitacao";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':aprovado_por', $id_aprovador, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[Erro] Falha ao atualizar status da solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar o ID do usuário a partir de uma solicitação específica
     */
    public function buscarIdUsuarioPorSolicitacao(int $id_solicitacao): ?int
    {
        try {
            $sql = "SELECT id_usuario FROM solicitacoes_acesso WHERE id_solicitacao = :id_solicitacao";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetchColumn();
            return $resultado !== false ? (int) $resultado : null;
        } catch (Exception $e) {
            error_log("[Erro] Falha ao buscar ID do usuário na solicitação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Contar solicitações pendentes
     */
    public function contarPendentes(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM solicitacoes_acesso WHERE status = 'pendente'";
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[Erro] Falha ao contar solicitações pendentes: " . $e->getMessage());
            return 0;
        }
    }
}
