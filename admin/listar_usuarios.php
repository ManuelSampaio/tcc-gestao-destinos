<?php
require_once __DIR__ . '/../app/controllers/UsuarioController.php';

use App\Controllers\UsuarioController;

session_start();

$usuarioController = new UsuarioController();

// Paginação
$pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$usuariosPorPagina = 10;

// Obtendo a lista de usuários com estatísticas
$resultado = $usuarioController->listarUsuarios($pagina, $usuariosPorPagina);
$usuarios = $resultado['usuarios'] ?? [];
$estatisticas = $resultado['estatisticas'] ?? [];

// Calculando total de páginas
$totalPaginas = max(1, ceil($estatisticas['total'] / $usuariosPorPagina));

// Verificar tipo do usuário atual
$tipoUsuarioAtual = $_SESSION['usuario']['tipo_usuario'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gestão de Usuários</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <div class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg p-4">
            <div class="flex items-center gap-2 mb-8">
                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                <h1 class="text-xl font-bold">Gestão de Usuários</h1>
            </div>
            
            <nav class="space-y-2">
                <a href="#" class="block p-2 rounded hover:bg-gray-100">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Dashboard
                </a>
                <a href="#" class="block p-2 rounded bg-blue-50 text-blue-600">
                    <i class="fas fa-users mr-2"></i>
                    Usuários
                </a>
                <?php if ($tipoUsuarioAtual === 'super_admin'): ?>
                <a href="solicitacoes.php" class="block p-2 rounded hover:bg-gray-100">
                    <i class="fas fa-bell mr-2"></i>
                    Solicitações
                </a>
                <?php endif; ?>
                <a href="#" class="block p-2 rounded hover:bg-gray-100">
                    <i class="fas fa-cog mr-2"></i>
                    Configurações
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 p-8">
            <!-- Header Stats -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-500">Total Usuários</h3>
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-bold mt-2"><?= $estatisticas['total'] ?? 0 ?></p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-500">Administradores</h3>
                        <i class="fas fa-user-shield text-purple-600"></i>
                    </div>
                    <p class="text-2xl font-bold mt-2"><?= $estatisticas['admins'] ?? 0 ?></p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-500">Usuários Comuns</h3>
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <p class="text-2xl font-bold mt-2"><?= $estatisticas['comuns'] ?? 0 ?></p>
                </div>

                <?php if ($tipoUsuarioAtual === 'super_admin'): ?>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-500">Super Admins</h3>
                        <i class="fas fa-crown text-yellow-600"></i>
                    </div>
                    <p class="text-2xl font-bold mt-2"><?= $estatisticas['super_admins'] ?? 0 ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           placeholder="Buscar usuários..." 
                           class="pl-10 pr-4 py-2 border rounded-lg w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-2">
                    <?php if ($tipoUsuarioAtual === 'comum'): ?>
                    <a href="solicitar_acesso.php" 
                       class="flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-shield-alt"></i>
                        Solicitar Acesso Admin
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($tipoUsuarioAtual, ['admin', 'super_admin'])): ?>
                    <a href="adicionar_usuario.php" 
                       class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                        Novo Usuário
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow">
                <?php if (!empty($usuarios)): ?>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4 font-medium text-gray-500">ID</th>
                            <th class="text-left p-4 font-medium text-gray-500">Nome</th>
                            <th class="text-left p-4 font-medium text-gray-500">Email</th>
                            <th class="text-left p-4 font-medium text-gray-500">Tipo</th>
                            <th class="text-left p-4 font-medium text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr class="border-t">
                            <td class="p-4"><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($usuario['nome'] ?? 'N/A') ?></td>
                            <td class="p-4"><?= htmlspecialchars($usuario['email']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    <?php
                                    switch($usuario['tipo_usuario']) {
                                        case 'super_admin':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'admin':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        default:
                                            echo 'bg-blue-100 text-blue-800';
                                    }
                                    ?>">
                                    <?= htmlspecialchars($usuario['tipo_usuario']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <?php if ($tipoUsuarioAtual !== 'comum'): ?>
                                    <a href="editar_usuario.php?id=<?= urlencode($usuario['id_usuario']) ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="remover_usuario.php?id=<?= urlencode($usuario['id_usuario']) ?>" 
                                       class="text-red-600 hover:text-red-800"
                                       onclick="return confirm('Tem certeza que deseja remover este usuário?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <div class="flex justify-between items-center p-4 border-t">
                    <p class="text-sm text-gray-500">
                        Mostrando <?= count($usuarios) ?> de <?= $estatisticas['total'] ?? 0 ?> usuários
                    </p>
                    <div class="flex gap-2">
                        <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-50">
                            Anterior
                        </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                        <a href="?pagina=<?= $i ?>" 
                           class="px-3 py-1 border rounded <?= $i === $pagina ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-50">
                            Próxima
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="p-4 text-center text-gray-500">Nenhum usuário cadastrado.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mensagens de Feedback -->
    <?php if (isset($_SESSION['mensagem'])): ?>
    <div id="feedback-message" 
         class="fixed bottom-4 right-4 bg-white p-4 rounded-lg shadow-lg border-l-4 border-blue-500">
        <?= htmlspecialchars($_SESSION['mensagem']) ?>
        <button onclick="this.parentElement.remove()" 
                class="ml-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const message = document.getElementById('feedback-message');
            if (message) message.remove();
        }, 5000);
    </script>
    <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>
</body>
</html>