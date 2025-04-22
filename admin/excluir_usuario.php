<?php
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/controllers/UsuarioController.php';

use App\Controllers\UsuarioController;

session_start();

// Verificação de autenticação e permissão
if (!isset($_SESSION['usuario']) || 
    !isset($_SESSION['usuario']['tipo_usuario']) || 
    !in_array($_SESSION['usuario']['tipo_usuario'], ['admin', 'super_admin'])) {
    header('Location: index.php');
    exit;
}

// Inicializar variáveis
$tipoUsuario = $_SESSION['usuario']['tipo_usuario'];
$usuarioController = new UsuarioController();
$message = "";
$alertType = "";

// Verificar se a requisição é POST e tem o ID do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario']) && is_numeric($_POST['id_usuario'])) {
    $idUsuario = (int)$_POST['id_usuario'];
    
    // Buscar o usuário a ser excluído para verificar seu tipo
    $usuarioParaExcluir = $usuarioController->buscarUsuarioPorId($idUsuario);
    
    if (!$usuarioParaExcluir) {
        // Usuário não encontrado
        $message = "Usuário não encontrado.";
        $alertType = "danger";
    } else {
        $tipoUsuarioParaExcluir = $usuarioParaExcluir['tipo_usuario'] ?? '';
        
        // Verificar permissões de exclusão
        $podeExcluir = false;
        
        // Super admin pode excluir admins e usuários comuns, mas não outros super_admins
        if ($tipoUsuario === 'super_admin' && $tipoUsuarioParaExcluir !== 'super_admin') {
            $podeExcluir = true;
        }
        // Admin só pode excluir usuários comuns
        else if ($tipoUsuario === 'admin' && $tipoUsuarioParaExcluir === 'comum') {
            $podeExcluir = true;
        }
        
        if ($podeExcluir) {
            // Tentar excluir o usuário
            try {
                if ($usuarioController->removerUsuario($idUsuario)) {
                    $message = "Usuário excluído com sucesso!";
                    $alertType = "success";
                } else {
                    $message = "Erro ao excluir usuário.";
                    $alertType = "danger";
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $alertType = "danger";
            }
        } else {
            // Sem permissão para excluir
            $message = "Você não tem permissão para excluir este usuário.";
            $alertType = "warning";
            
            // Registrar tentativa não autorizada (opcional)
            error_log("Tentativa não autorizada de exclusão de usuário. ID Usuário: {$idUsuario}, Tipo: {$tipoUsuarioParaExcluir}, Por: {$_SESSION['usuario']['id_usuario']} ({$tipoUsuario})");
        }
    }
} else {
    // Requisição inválida
    $message = "Requisição inválida.";
    $alertType = "danger";
}

// Armazenar mensagem na sessão para exibição após redirecionamento
$_SESSION['message'] = $message;
$_SESSION['alertType'] = $alertType;

// Redirecionar de volta para a página de gerenciamento
header('Location: gerenciar_usuarios.php');
exit;
?>