<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/SolicitacaoAcesso.php';

use App\Models\Usuario;
use App\Models\SolicitacaoAcesso;
use Exception;

class UsuarioController
{
    protected $usuarioModel;
    protected $solicitacaoModel;
    protected $db;

    public function __construct()
    {
        try {
            $this->db = \Config\Database::getConnection();
            $this->usuarioModel = new Usuario();
            $this->solicitacaoModel = new SolicitacaoAcesso($this->db);
        } catch (Exception $e) {
            error_log("Erro ao inicializar UsuarioController: " . $e->getMessage());
            throw new Exception("Erro interno ao carregar o controlador de usuários.");
        }
    }

    private function iniciarSessao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }        
    }

    private function obterTipoUsuarioLogado(): string
    {
        $this->iniciarSessao();
        
        return $_SESSION['usuario']['tipo_usuario'] ?? ''; // Retorna string vazia se não estiver definido
    }

    private function verificarPermissao(string $nivelMinimo): bool
    {
        $tipoUsuario = $this->obterTipoUsuarioLogado();
        
        switch ($nivelMinimo) {
            case 'super_admin':
                return $tipoUsuario === 'super_admin';
            case 'admin':
                return in_array($tipoUsuario, ['admin', 'super_admin']);
            case 'comum':
                return in_array($tipoUsuario, ['comum', 'admin', 'super_admin']);
            default:
                return false;
        }
    }

    public function verificarEmailExiste($email): bool
    {
        return $this->usuarioModel->emailJaCadastrado($email);
    }

    public function autenticarUsuario($email, $senha): array {
        $usuario = $this->usuarioModel->buscarPorEmail($email);
    
        if (!$usuario || !isset($usuario['senha']) || !password_verify($senha, $usuario['senha'])) {
            return ['success' => false, 'message' => 'Credenciais inválidas.'];
        }

        $this->iniciarSessao();
    
        $_SESSION['usuario'] = [
            'id_usuario' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo_usuario' => $usuario['tipo_usuario']
        ];
    
        return ['success' => true, 'usuario' => $_SESSION['usuario']];
    }
    
    public function listarUsuarios($pagina = 1, $usuariosPorPagina = 10) {
        try {
            $tipoUsuario = $this->obterTipoUsuarioLogado();
            
            if ($tipoUsuario === 'comum') {
                throw new Exception("Acesso negado.");
            }

            $usuarios = $this->usuarioModel->listarTodos($tipoUsuario, $pagina, $usuariosPorPagina);
            
            // Contagem por tipo
            $estatisticas = [
                'total' => $this->usuarioModel->contarTodos(),
                'admins' => $this->usuarioModel->contarPorTipo('admin'),
                'comuns' => $this->usuarioModel->contarPorTipo('comum')
            ];
            
            if ($tipoUsuario === 'super_admin') {
                $estatisticas['super_admins'] = $this->usuarioModel->contarPorTipo('super_admin');
            }

            return ['usuarios' => $usuarios, 'estatisticas' => $estatisticas];
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }

    public function buscarUsuarioPorId(int $id): ?array
    {
        return $this->usuarioModel->buscarPorId($id);
    }

    public function atualizarUsuario(int $id, array $dados): bool
    {
        return $this->usuarioModel->atualizarUsuario($id, $dados, $this->obterTipoUsuarioLogado());
    }

    public function adicionarUsuario(array $dados)
    {
        if (empty($dados['email']) || empty($dados['senha']) || empty($dados['tipo_usuario'])) {
            return false; // Campos obrigatórios não preenchidos
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return false; // E-mail inválido
        }

        // Obter tipo do usuário logado (se existir)
        $tipoUsuarioLogado = $this->obterTipoUsuarioLogado();

        // Permitir que usuários comuns se cadastrem sozinhos
        if ($tipoUsuarioLogado === null || $tipoUsuarioLogado === 'comum') {
            $tipoUsuarioLogado = 'comum'; // Define automaticamente o novo usuário como comum
        }

        try {
            $id = $this->usuarioModel->adicionar($dados, $tipoUsuarioLogado);
            return $id > 0; // Retorna true se um ID válido foi obtido
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function removerUsuario($id)
    {
        $tipoUsuarioLogado = $this->obterTipoUsuarioLogado();
        
        try {
            $resultado = $this->usuarioModel->removerPorId($id, $tipoUsuarioLogado);
            return $resultado; // Retorna true ou false dependendo do resultado da remoção
        } catch (Exception $e) {
            error_log("Erro ao remover usuário: " . $e->getMessage());
            return false;
        }
    }

    public function solicitarMudancaAcesso($nivelSolicitado, $justificativa = '')
    {
        $this->iniciarSessao();
        $usuarioId = $_SESSION['usuario']['id_usuario'] ?? null;
        
        if (!$usuarioId) {
            return ['success' => false, 'message' => "Usuário não autenticado."];
        }

        $tipoUsuarioLogado = $this->obterTipoUsuarioLogado();
        
        // Apenas usuários comuns podem solicitar elevação para admin
        if ($tipoUsuarioLogado !== 'comum' || $nivelSolicitado !== 'admin') {
            return ['success' => false, 'message' => "Solicitação inválida."];
        }

        try {
            // Usar o método criar da classe SolicitacaoAcesso
            $resultado = $this->solicitacaoModel->criar($usuarioId, $nivelSolicitado, $justificativa);
            
            return ['success' => $resultado, 'message' => $resultado ? "Solicitação enviada com sucesso!" : "Erro ao enviar solicitação."];
        } catch (Exception $e) {
            error_log("Erro ao solicitar mudança de acesso: " . $e->getMessage());
            return ['success' => false, 'message' => "Erro ao solicitar mudança de acesso."];
        }
    }

    public function atualizarSolicitacaoAcesso($idSolicitacao, $status)
    {
        if (!$this->verificarPermissao('super_admin')) {
            return ['success' => false, 'message' => "Apenas super administradores podem aprovar solicitações."];
        }

        try {
            $this->iniciarSessao();
            $idAprovador = $_SESSION['usuario']['id_usuario'];

            // Buscar solicitação para obter o ID do usuário
            $solicitacao = $this->solicitacaoModel->buscarPorId($idSolicitacao);
            
            if (!$solicitacao) {
                return ['success' => false, 'message' => "Solicitação não encontrada."];
            }
            
            $idUsuario = $solicitacao['id_usuario'];
            
            // Usar o método atualizarStatus em vez de atualizar
            $resultado = $this->solicitacaoModel->atualizarStatus($idSolicitacao, $status, $idAprovador);

            if ($resultado && $status === 'aprovado') {
                $this->usuarioModel->atualizarTipo($idUsuario, 'admin');
            }

            return ['success' => $resultado, 'message' => $resultado ? "Solicitação atualizada com sucesso." : "Erro ao atualizar solicitação."];
        } catch (Exception $e) {
            error_log("Erro ao atualizar solicitação: " . $e->getMessage());
            return ['success' => false, 'message' => "Erro ao atualizar solicitação."];
        }
    }

    public function listarSolicitacoesPendentes()
    {
        if (!$this->verificarPermissao('super_admin')) {
            return ['success' => false, 'message' => "Acesso negado."];
        }

        try {
            // Utilizar o método listarSolicitacoes com o filtro 'pendente'
            $solicitacoes = $this->solicitacaoModel->listarSolicitacoes('pendente');
            return ['success' => true, 'solicitacoes' => $solicitacoes];
        } catch (Exception $e) {
            error_log("Erro ao listar solicitações: " . $e->getMessage());
            return ['success' => false, 'message' => "Erro ao listar solicitações."];
        }
    }

    /**
     * Atualiza o tipo de usuário
     * 
     * @param int $idUsuario ID do usuário
     * @param string $novoTipo Novo tipo de usuário ('comum', 'admin', 'super_admin')
     * @return bool Resultado da operação
     */
    public function atualizarTipoUsuario(int $idUsuario, string $novoTipo): bool
    {
        try {
            $tiposPermitidos = ['comum', 'admin', 'super_admin'];
            
            if (!in_array($novoTipo, $tiposPermitidos)) {
                throw new Exception("Tipo de usuário inválido.");
            }
            
            return $this->usuarioModel->atualizarTipo($idUsuario, $novoTipo);
        } catch (Exception $e) {
            error_log("[Erro] Falha ao atualizar tipo de usuário: " . $e->getMessage());
            return false;
        }
    }
}