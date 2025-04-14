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

// Obter todos os usuários - CORREÇÃO AQUI
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
        body { display: flex; background-color: #f8f9fa; }
        #sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }
        #sidebar .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        #content {
            margin-left: 270px;
            width: calc(100% - 270px);
            padding: 20px;
        }
        .stat-card {
            border-left: 5px solid #007bff;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .table-actions button, .table-actions a {
            margin: 0 2px;
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
    <a href="painel_admin.php" class="mb-2 d-block text-white text-decoration-none">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="gerenciar_usuarios.php" class="mb-2 d-block text-white text-decoration-none">
        <i class="fas fa-users"></i> Gerenciar Usuários
    </a>
    <?php if (($usuarioLogado['tipo_usuario'] ?? '') === 'super_admin'): ?>
        <a href="solicitacoes_pendentes.php" class="mb-2 d-block text-white text-decoration-none">
            <i class="fas fa-clock"></i> Solicitações Pendentes
        </a>
    <?php endif; ?>
    <a href="logout.php" class="mb-2 d-block text-white text-decoration-none">
        <i class="fas fa-sign-out-alt"></i> Sair
    </a>
</div>

<!-- Conteúdo Principal -->
<div id="content">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Usuários</h2>
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
                    <h5>Total de Usuários</h5>
                    <p class="fs-4 mb-0"><?= $totalUsuarios ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3">
                    <h5>Administradores</h5>
                    <p class="fs-4 mb-0"><?= $totalAdmins ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3">
                    <h5>Usuários</h5>
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
                <h5 class="modal-title">Cadastrar Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Usuário</label>
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
                    <button type="submit" name="cadastrar_usuario" class="btn btn-success w-100">
                        Cadastrar
                    </button>
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
        ]
    });
});
</script>

</body>
</html>