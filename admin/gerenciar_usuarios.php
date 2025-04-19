<?php
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/controllers/UsuarioController.php';
require_once __DIR__ . '/../app/models/SolicitacaoAcesso.php';

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

// Exclusão de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_usuario'])) {
    $idUsuario = (int)$_POST['id_usuario'];
    
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
}

// Cadastro de novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_usuario'])) {
    $nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $tipo_usuario = trim(htmlspecialchars($_POST['tipo_usuario'] ?? ''));

    if (empty($nome) || empty($email) || empty($senha) || empty($tipo_usuario)) {
        $message = "Por favor, preencha todos os campos.";
        $alertType = "warning";
    } else {
        $dadosUsuario = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'tipo_usuario' => $tipo_usuario
        ];

        if ($usuarioController->adicionarUsuario($dadosUsuario)) {
            $message = "Usuário cadastrado com sucesso!";
            $alertType = "success";
        } else {
            $message = "Erro ao cadastrar o usuário.";
            $alertType = "danger";
        }
    }
}

// Obter todos os usuários
$resultado = $usuarioController->listarUsuarios();

// Extrair usuários e estatísticas corretamente
if (is_array($resultado) && isset($resultado['usuarios'])) {
    $usuarios = $resultado['usuarios'];
    $estatisticas = $resultado['estatisticas'] ?? [
        'total' => count($usuarios),
        'admins' => 0,
        'comuns' => 0
    ];
} else {
    // Fallback caso o retorno não seja o esperado
    $usuarios = [];
    $estatisticas = [
        'total' => 0,
        'admins' => 0,
        'comuns' => 0
    ];
}

$totalUsuarios = $estatisticas['total'] ?? 0;
$totalAdmins = ($estatisticas['admins'] ?? 0) + ($estatisticas['super_admins'] ?? 0);
$totalUsuariosComuns = $estatisticas['comuns'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        .btn-success {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success:hover {
            background-color: var(--primary-color);
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
        }
        
        .btn-info {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-dark);
        }
        
        .btn-info:hover {
            background-color: var(--secondary-color);
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
            color: var(--text-dark);
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-danger:hover {
            background-color: var(--accent-color);
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
        }
        
        .stat-card {
            border-left: 5px solid var(--primary-color);
            transition: var(--transition);
            background-color: var(--text-light);
            border-radius: var(--border-radius);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .table-actions button, .table-actions a {
            margin: 0 2px;
        }
        
        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: none;
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .modal-content {
            border-radius: var(--border-radius);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-radius: calc(var(--border-radius) - 1px) calc(var(--border-radius) - 1px) 0 0;
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
        
        table.dataTable thead th {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-bottom: none;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
        }
        
        .page-link:hover {
            color: var(--text-light);
            background-color: var(--primary-color);
        }
        
        .badge.bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .badge.bg-secondary {
            background-color: var(--secondary-color) !important;
            color: var(--text-dark);
        }
        
        .badge.bg-danger {
            background-color: var(--accent-color) !important;
        }
    </style>
</head>
<body>

<!-- Sidebar com informações do usuário destacadas -->
<div id="sidebar">
    
    <div class="user-info">
        <h5><i class="fas fa-user-circle"></i> Usuário Logado</h5>
        <p class="mb-0"><?= htmlspecialchars($usuarioLogado['nome'] ?? 'Usuário') ?></p>
        <small><?= htmlspecialchars($usuarioLogado['email'] ?? 'Email') ?></small>
    </div>
    <a href="painel_admin.php" class="d-block text-decoration-none">
        <i class="fas fa-users"></i> Painel Admin
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
            <h2><i class="fas fa-users-cog"></i> Gerenciar Usuários</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal">
                <i class="fas fa-user-plus"></i> Novo Usuário
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $alertType ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3">
                    <h5><i class="fas fa-users"></i> Total de Usuários</h5>
                    <p class="fs-4 mb-0"><?= $totalUsuarios ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3">
                    <h5><i class="fas fa-user-shield"></i> Administradores</h5>
                    <p class="fs-4 mb-0"><?= $totalAdmins ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3">
                    <h5><i class="fas fa-user"></i> Usuários Comuns</h5>
                    <p class="fs-4 mb-0"><?= $totalUsuariosComuns ?></p>
                </div>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="usuariosTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Nível de Acesso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($usuario['nome'] ?? 'Nome não disponível') ?></td>
                                <td><?= htmlspecialchars($usuario['email'] ?? 'Email não disponível') ?></td>
                                <td>
                                    <?php 
                                    // Verificar se o tipo_usuario existe
                                    $tipoExibicao = 'Usuário';
                                    $tipoUsuario = $usuario['tipo_usuario'] ?? 'comum';
                                    
                                    // Mapear valores do banco para exibição
                                    switch($tipoUsuario) {
                                        case 'admin':
                                            $tipoExibicao = 'Administrador';
                                            $badgeClass = 'bg-primary';
                                            break;
                                        case 'super_admin':
                                            $tipoExibicao = 'Super Administrador';
                                            $badgeClass = 'bg-danger';
                                            break;
                                        default:
                                            $tipoExibicao = 'Usuário Comum';
                                            $badgeClass = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($tipoExibicao) ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="editar_usuario.php?id=<?= $usuario['id_usuario'] ?? '' ?>" class="btn btn-sm btn-info" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($tipoUsuario === 'super_admin' || 
                                        ($tipoUsuario === 'admin' && ($usuario['tipo_usuario'] ?? '') === 'comum')): ?>
                                        <form method="post" style="display: inline;" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?? '' ?>">
                                            <button type="submit" name="excluir_usuario" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro -->
<div class="modal fade" id="cadastroModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Cadastrar Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> Nome</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-key"></i> Senha</label>
                        <input type="password" class="form-control" name="senha" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Tipo de Usuário</label>
                        <select class="form-control" name="tipo_usuario">
                            <option value="comum">Usuário Comum</option>
                            <?php if (in_array($tipoUsuario, ['admin', 'super_admin'])): ?>
                                <option value="admin">Administrador</option>
                            <?php endif; ?>
                            <?php if ($tipoUsuario === 'super_admin'): ?>
                                <option value="super_admin">Super Administrador</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="cadastrar_usuario" class="btn btn-success">
                            <i class="fas fa-save"></i> Cadastrar
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
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#usuariosTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        },
        pageLength: 10,
        order: [[0, 'asc']],
        columns: [
            { width: '10%' },
            { width: '25%' },
            { width: '30%' },
            { width: '15%' },
            { width: '20%', orderable: false }
        ],
        initComplete: function() {
            // Aplicar estilos personalizados após a inicialização
            $('.dataTables_wrapper .pagination .page-item.active .page-link').css({
                'background-color': 'var(--primary-color)',
                'border-color': 'var(--primary-color)'
            });
        }
    });
});
</script>

</body>
</html>