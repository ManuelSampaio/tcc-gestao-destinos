<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/SolicitacaoAcesso.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\SolicitacaoAcesso;
use App\Models\Usuario;
use config\Database;
use PDO;
use Exception;

class SolicitacaoAcessoController {
    private $db;
    private $solicitacaoModel;
    private $usuarioModel;

    public function __construct() {
        try {
            $this->db = Database::getConnection();
            $this->solicitacaoModel = new SolicitacaoAcesso($this->db);
            $this->usuarioModel = new Usuario($this->db);
        } catch (Exception $e) {
            error_log("Erro na inicialização do controller: " . $e->getMessage());
            throw new Exception("Erro ao inicializar o controlador de solicitações.");
        }
    }

    /**
     * Lista todas as solicitações de acesso com opção de filtro por status
     * 
     * @param string|null $status O status para filtrar (pendente, aprovado, rejeitado)
     * @return array Lista de solicitações
     */
    public function listarSolicitacoes($status = null) {
        try {
            $query = "SELECT s.*, u.nome, u.email, u.tipo_usuario as tipo_usuario_atual, 
                      admin.nome as aprovador_nome
                      FROM solicitacoes_acesso s
                      LEFT JOIN usuarios u ON s.id_usuario = u.id_usuario
                      LEFT JOIN usuarios admin ON s.aprovado_por = admin.id_usuario";
            
            $params = [];
            
            if ($status) {
                $query .= " WHERE s.status = :status";
                $params[':status'] = $status;
            }
            
            $query .= " ORDER BY s.data_solicitacao DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar solicitações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Processa uma solicitação (aprovar ou rejeitar)
     * 
     * @param int $idSolicitacao ID da solicitação
     * @param string $status Novo status (aprovado/rejeitado)
     * @param int $idAprovador ID do usuário aprovador
     * @return array Resultado da operação
     */
    public function processarSolicitacao($idSolicitacao, $status, $idAprovador) {
        try {
            // Inicia transação para garantir consistência dos dados
            $this->db->beginTransaction();
            
            // Obter detalhes da solicitação
            $solicitacao = $this->solicitacaoModel->buscarPorId($idSolicitacao);
            
            if (!$solicitacao) {
                throw new Exception("Solicitação não encontrada");
            }
            
            if ($solicitacao['status'] !== 'pendente') {
                throw new Exception("Esta solicitação já foi processada anteriormente");
            }
            
            // Atualizar status da solicitação
            $resultado = $this->solicitacaoModel->atualizarStatus($idSolicitacao, $status, $idAprovador);
            
            if (!$resultado) {
                throw new Exception("Não foi possível atualizar o status da solicitação");
            }
            
            // Se aprovado, atualizar nível de acesso do usuário
            if ($status === 'aprovado') {
                $atualizacaoUsuario = $this->usuarioModel->atualizarTipo(
                    $solicitacao['id_usuario'], 
                    $solicitacao['novo_nivel']
                );
                
                if (!$atualizacaoUsuario) {
                    throw new Exception("Erro ao atualizar o nível de acesso do usuário");
                }
            }
            
            // Confirmar transação
            $this->db->commit();
            
            $statusMsg = $status === 'aprovado' ? 'aprovada' : 'rejeitada';
            return [
                'success' => true,
                'message' => "Solicitação {$statusMsg} com sucesso."
            ];
            
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log("Erro ao processar solicitação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Erro ao processar solicitação: " . $e->getMessage()
            ];
        }
    }

    /**
     * Exclui uma solicitação de acesso (apenas para super_admin)
     * 
     * @param int $idSolicitacao ID da solicitação
     * @return array Resultado da operação
     */
    public function excluirSolicitacao($idSolicitacao) {
        try {
            $solicitacao = $this->solicitacaoModel->buscarPorId($idSolicitacao);
            
            if (!$solicitacao) {
                throw new Exception("Solicitação não encontrada");
            }
            
            $resultado = $this->solicitacaoModel->excluir($idSolicitacao);
            
            if (!$resultado) {
                throw new Exception("Não foi possível excluir a solicitação");
            }
            
            return [
                'success' => true,
                'message' => "Solicitação excluída com sucesso."
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao excluir solicitação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Erro ao excluir solicitação: " . $e->getMessage()
            ];
        }
    }

    /**
     * Cria uma nova solicitação de acesso
     * 
     * @param int $idUsuario ID do usuário solicitante
     * @param string $novoNivel Nível solicitado
     * @param string $justificativa Justificativa para a solicitação
     * @return array Resultado da operação
     */
    public function criarSolicitacao($idUsuario, $novoNivel, $justificativa = '') {
        try {
            // Verificar se já existe uma solicitação pendente
            $solicitacaoPendente = $this->solicitacaoModel->buscarPendentePorUsuario($idUsuario);
            
            if ($solicitacaoPendente) {
                throw new Exception("Você já possui uma solicitação pendente. Aguarde sua aprovação.");
            }
            
            // Verificar se o usuário já tem o nível solicitado
            $usuario = $this->usuarioModel->buscarPorId($idUsuario);
            if ($usuario && $usuario['tipo_usuario'] === $novoNivel) {
                throw new Exception("Você já possui o nível de acesso solicitado.");
            }
            
            // Criar a solicitação
            $resultado = $this->solicitacaoModel->criar($idUsuario, $novoNivel, $justificativa);
            
            if (!$resultado) {
                throw new Exception("Não foi possível criar a solicitação");
            }
            
            return [
                'success' => true,
                'message' => "Solicitação criada com sucesso. Aguarde a aprovação de um administrador."
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao criar solicitação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Erro ao criar solicitação: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtém estatísticas das solicitações
     * 
     * @return array Estatísticas por status
     */
    public function obterEstatisticas() {
        try {
            $query = "SELECT 
                        SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendente,
                        SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as aprovado,
                        SUM(CASE WHEN status = 'rejeitado' THEN 1 ELSE 0 END) as rejeitado
                      FROM solicitacoes_acesso";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Garantir que não retorne NULL para nenhum status
            return [
                'pendente' => (int)($resultado['pendente'] ?? 0),
                'aprovado' => (int)($resultado['aprovado'] ?? 0),
                'rejeitado' => (int)($resultado['rejeitado'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'pendente' => 0,
                'aprovado' => 0,
                'rejeitado' => 0
            ];
        }
    }

    /**
     * Obtém solicitações pendentes para um usuário específico
     * 
     * @param int $idUsuario ID do usuário
     * @return array|null Solicitações pendentes
     */
    public function obterSolicitacoesPendentesUsuario($idUsuario) {
        try {
            return $this->solicitacaoModel->buscarPendentePorUsuario($idUsuario);
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações pendentes: " . $e->getMessage());
            return null;
        }
    }
}