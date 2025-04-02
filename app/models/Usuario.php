<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;
use PDOException;
use Exception;

class Usuario
{
    private PDO $db;
    private const TIPOS_USUARIO = ['comum', 'admin', 'super_admin'];

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (PDOException $e) {
            error_log("Erro ao conectar ao banco: " . $e->getMessage());
            throw new Exception("Erro interno ao conectar ao banco de dados.");
        }
    }

    private function executarQuery(string $sql, array $params = [], bool $fetchAll = false, bool $fetchColumn = false)
    {
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            if ($fetchColumn) {
                return $stmt->fetchColumn();
            }

            return $fetchAll ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro na query: " . $e->getMessage());
            return null;
        }
    }

    public function buscarPorEmail(string $email): ?array
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Formato de email inválido.");
    }

    $resultado = $this->executarQuery("SELECT * FROM usuarios WHERE email = :email LIMIT 1", [':email' => $email]);
    
    return $resultado ?: null; // Retorna null se a consulta não encontrar nenhum usuário
}

public function buscarPorId(int $id): ?array
{
    $resultado = $this->executarQuery("SELECT id_usuario, nome, email, tipo_usuario FROM usuarios WHERE id_usuario = :id LIMIT 1", [':id' => $id]);

    return $resultado ?: null; // Retorna null se a consulta não encontrar nenhum usuário
}

    public function contarTodos(): int
    {
        return (int) ($this->executarQuery("SELECT COUNT(*) FROM usuarios", [], false, true) ?: 0);
    }

    public function contarPorTipo(string $tipo): int
    {
        return (int) ($this->executarQuery("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = :tipo", [':tipo' => $tipo], false, true) ?: 0);
    }

    public function listarTodos(string $tipoUsuarioLogado, ?int $pagina = null, ?int $porPagina = null): array
    {
        if ($tipoUsuarioLogado === 'comum') {
            return [];
        }

        $where = $tipoUsuarioLogado === 'admin' ? "WHERE tipo_usuario != 'super_admin'" : "";
        $sql = "SELECT id_usuario, nome, email, tipo_usuario FROM usuarios $where ORDER BY nome";

        $params = [];
        if ($pagina !== null && $porPagina !== null) {
            $offset = ($pagina - 1) * $porPagina;
            $sql .= " LIMIT :limit OFFSET :offset";
            $params = [':limit' => $porPagina, ':offset' => $offset];
        }

        return $this->executarQuery($sql, $params, true) ?? [];
    }

    public function adicionar(array $dados, ?string $tipoUsuarioLogado): int
{
    // Garantir que os dados essenciais estão preenchidos
    if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['tipo_usuario'])) {
        throw new Exception("Dados incompletos para cadastro.");
    }

    // Validar o formato do e-mail
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("E-mail inválido.");
    }

    // Definir um tipo padrão caso $tipoUsuarioLogado seja null
    $tipoUsuarioLogado = $tipoUsuarioLogado ?? '';

    // Verificar permissões para criar o usuário
    if (!$this->validarPermissaoCriacao($tipoUsuarioLogado, $dados['tipo_usuario'])) {
        throw new Exception("Sem permissão para criar este tipo de usuário.");
    }

    try {
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario) 
                VALUES (:nome, :email, :senha, :tipo_usuario)";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nome'         => trim(htmlspecialchars($dados['nome'])),
            ':email'        => trim($dados['email']),
            ':senha'        => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':tipo_usuario' => trim($dados['tipo_usuario'])
        ]);

        return (int) $this->db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erro ao adicionar usuário: " . $e->getMessage());
        throw new Exception("Erro ao cadastrar usuário.");
    }
}

public function atualizarUsuario(int $id, array $dados, string $tipoUsuarioLogado): bool
{
    $usuarioAtual = $this->buscarPorId($id);
    if (!$usuarioAtual) {
        throw new Exception("Usuário não encontrado.");
    }

    // Verifica se o usuário tem permissão para editar
    if (!$this->validarPermissaoEdicao($tipoUsuarioLogado, $usuarioAtual['tipo_usuario'])) {
        throw new Exception("Sem permissão para editar este usuário.");
    }

    try {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email WHERE id_usuario = :id";
        $params = [
            ':nome' => trim(htmlspecialchars($dados['nome'])),
            ':email' => trim($dados['email']),
            ':id' => $id
        ];

        // Apenas super_admin pode alterar o tipo de usuário
        if ($tipoUsuarioLogado === 'super_admin' && isset($dados['tipo_usuario'])) {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, tipo_usuario = :tipo_usuario WHERE id_usuario = :id";
            $params[':tipo_usuario'] = trim($dados['tipo_usuario']);
        }

        return (bool) $this->executarQuery($sql, $params);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar usuário: " . $e->getMessage());
        throw new Exception("Erro ao atualizar o usuário.");
    }
}

    public function removerPorId(int $id, string $tipoUsuarioLogado): bool
    {
        $usuarioAlvo = $this->buscarPorId($id);
        if (!$usuarioAlvo) {
            throw new Exception("Usuário não encontrado.");
        }

        if (!$this->validarPermissaoRemocao($tipoUsuarioLogado, $usuarioAlvo['tipo_usuario'])) {
            throw new Exception("Sem permissão para remover este usuário.");
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao remover usuário: " . $e->getMessage());
            throw new Exception("Erro ao remover o usuário.");
        }
    }

    // Método para verificar se o email já está cadastrado
    public function emailJaCadastrado(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0; // Retorna true se o email já estiver cadastrado
    }

    private function validarPermissaoCriacao(string $tipoUsuarioLogado, string $tipoUsuarioNovo): bool {
        // Se não há usuário logado, permite apenas criar usuário comum
        if (empty($tipoUsuarioLogado)) {
            return $tipoUsuarioNovo === 'comum';
        }
        
        return match ($tipoUsuarioLogado) {
            'super_admin' => true,
            'admin' => $tipoUsuarioNovo === 'comum',
            default => false,
        };
    }
    private function validarPermissaoEdicao(string $tipoUsuarioLogado, string $tipoUsuarioAlvo): bool
    {
        return match ($tipoUsuarioLogado) {
            'super_admin' => true,
            'admin' => $tipoUsuarioAlvo === 'comum',
            default => false,
        };
    }

    private function validarPermissaoRemocao(string $tipoUsuarioLogado, string $tipoUsuarioAlvo): bool
    {
        return match ($tipoUsuarioLogado) {
            'super_admin' => true,
            'admin' => $tipoUsuarioAlvo === 'comum',
            default => false,
        };
    }
}