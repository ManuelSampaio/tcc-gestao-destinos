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

$tipoUsuario = $_SESSION['usuario']['tipo_usuario'];
$usuarioController = new UsuarioController();
$usuarioLogado = $_SESSION['usuario'];

// Mensagem de feedback
$message = "";
$alertType = "info";

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gerenciar_usuarios.php');
    exit;
}

$idUsuario = (int)$_GET['id'];

// Buscar dados do usuário
$usuario = $usuarioController->buscarUsuarioPorId($idUsuario);

if (!$usuario) {
    header('Location: gerenciar_usuarios.php');
    exit;
}

// Atualização de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_usuario'])) {
    $nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $tipo_usuario = trim(htmlspecialchars($_POST['tipo_usuario'] ?? ''));
    
    // Verifica se a senha foi fornecida
    $dadosUsuario = [
        'nome' => $nome,
        'email' => $email,
        'tipo_usuario' => $tipo_usuario
    ];
    
    // Adiciona senha apenas se uma nova for fornecida
    if (!empty($_POST['senha'])) {
        $dadosUsuario['senha'] = $_POST['senha'];
    }
    
    if (empty($nome) || empty($email) || empty($tipo_usuario)) {
        $message = "Por favor, preencha os campos obrigatórios.";
        $alertType = "warning";
    } else {
        if ($usuarioController->atualizarUsuario($idUsuario, $dadosUsuario)) {
            $message = "Usuário atualizado com sucesso!";
            $alertType = "success";
            
            // Atualizar dados do usuário após sucesso
            $usuario = $usuarioController->buscarUsuarioPorId($idUsuario);
        } else {
            $message = "Erro ao atualizar o usuário.";
            $alertType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
    <style>
        :root {
            --primary-color: #004d40;
            --secondary-color: #ff9800;
            --accent-color: #f44336;
            --light-bg: #f5f5f5;
            --dark-bg: #212121;
            --text-dark: #333333;
            --text-light: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
            --border-radius: 8px;
        }
        
        body { 
            display: flex; 
            background-color: var(--light-bg); 
            color: var(--text-dark);
        }
        
        #sidebar {
            width: 250px;
            background: var(--primary-color);
            color: var(--text-light);
            height: 100vh;
            padding: 20px;
            position: fixed;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        #sidebar .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #sidebar a {
            color: var(--text-light);
            transition: var(--transition);
            padding: 10px;
            border-radius: var(--border-radius);
            display: block;
            margin-bottom: 8px;
        }
        
        #sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
            box-shadow: var(--shadow);
            text-decoration: none;
        }
        
        #content {
            margin-left: 270px;
            width: calc(100% - 270px);
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-color);
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
            color: var(--text-dark);
        }
        
        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 77, 64, 0.25);
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin-bottom: 0;
        }
        
        .alert {
            border-radius: var(--border-radius);
        }
    </style>
</head>
<body>

<!-- Sidebar com informações do usuário destacadas -->
<div id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-user-shield"></i> Painel Admin</h3>
    </div>
    <div class="user-info">
        <h5><i class="fas fa-user-circle"></i> Usuário Logado</h5>
        <p class="mb-0"><?= htmlspecialchars($usuarioLogado['nome'] ?? 'Usuário') ?></p>
        <small><?= htmlspecialchars($usuarioLogado['email'] ?? 'Email') ?></small>
    </div>
    <a href="gerenciar_usuarios.php" class="d-block text-decoration-none">
        <i class="fas fa-users"></i> Gerenciar Usuários
    </a>
    <?php if (($usuarioLogado['tipo_usuario'] ?? '') === 'super_admin'): ?>
        <a href="solicitacoes_pendentes.php" class="d-block text-decoration-none">
            <i class="fas fa-clock"></i> Solicitações Pendentes
        </a>
    <?php endif; ?>
</div>

<!-- Conteúdo Principal -->
<div id="content">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
            <a href="gerenciar_usuarios.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $alertType ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário de Edição -->
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> Nome</label>
                        <input type="text" class="form-control" name="nome" 
                               value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-key"></i> Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" class="form-control" name="senha">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Tipo de Usuário</label>
                        <select class="form-control" name="tipo_usuario">
                            <option value="comum" <?= ($usuario['tipo_usuario'] ?? '') == 'comum' ? 'selected' : '' ?>>
                                Usuário Comum
                            </option>
                            <?php if (in_array($tipoUsuario, ['admin', 'super_admin'])): ?>
                                <option value="admin" <?= ($usuario['tipo_usuario'] ?? '') == 'admin' ? 'selected' : '' ?>>
                                    Administrador
                                </option>
                            <?php endif; ?>
                            <?php if ($tipoUsuario === 'super_admin'): ?>
                                <option value="super_admin" <?= ($usuario['tipo_usuario'] ?? '') == 'super_admin' ? 'selected' : '' ?>>
                                    Super Administrador
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="atualizar_usuario" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>