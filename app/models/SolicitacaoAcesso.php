<?php
namespace App\Models;

use PDO;
use Exception;

class SolicitacaoAcesso {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Busca uma solicitação pelo ID
     * 
     * @param int $id ID da solicitação
     * @return array|null Dados da solicitação ou null se não encontrada
     */
    public function buscarPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM solicitacoes_acesso WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitação por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca solicitações pendentes de um usuário
     * 
     * @param int $idUsuario ID do usuário
     * @return array|null Solicitações pendentes
     */
    public function buscarPendentePorUsuario($idUsuario) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM solicitacoes_acesso 
                                        WHERE id_usuario = :id_usuario AND status = 'pendente'");
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações pendentes: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria uma nova solicitação de acesso
     * 
     * @param int $idUsuario ID do usuário solicitante
     * @param string $novoNivel Nível solicitado
     * @param string $justificativa Justificativa da solicitação
     * @return bool Resultado da operação
     */
    public function criar($idUsuario, $novoNivel, $justificativa = '') {
        try {
            $stmt = $this->db->prepare("INSERT INTO solicitacoes_acesso 
                                        (id_usuario, novo_nivel, justificativa, status) 
                                        VALUES (:id_usuario, :novo_nivel, :justificativa, 'pendente')");
            
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':novo_nivel', $novoNivel, PDO::PARAM_STR);
            $stmt->bindParam(':justificativa', $justificativa, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao criar solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de uma solicitação
     * 
     * @param int $idSolicitacao ID da solicitação
     * @param string $status Novo status (aprovado/rejeitado)
     * @param int $idAprovador ID do usuário aprovador
     * @return bool Resultado da operação
     */
    public function atualizarStatus($idSolicitacao, $status, $idAprovador) {
        try {
            $stmt = $this->db->prepare("UPDATE solicitacoes_acesso 
                                        SET status = :status, 
                                            aprovado_por = :aprovado_por, 
                                            data_aprovacao = CURRENT_TIMESTAMP 
                                        WHERE id = :id");
            
            $stmt->bindParam(':id', $idSolicitacao, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':aprovado_por', $idAprovador, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma solicitação de acesso
     * 
     * @param int $idSolicitacao ID da solicitação
     * @return bool Resultado da operação
     */
    public function excluir($idSolicitacao) {
        try {
            $stmt = $this->db->prepare("DELETE FROM solicitacoes_acesso WHERE id = :id");
            $stmt->bindParam(':id', $idSolicitacao, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao excluir solicitação: " . $e->getMessage());
            return false;
        }
    }
}