<?php
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/controllers/SolicitacaoAcessoController.php';

use App\Controllers\SolicitacaoAcessoController;

session_start();

// Verificação de autenticação e permissão (apenas admin e super_admin podem acessar)
if (!isset($_SESSION['usuario']) || 
    !isset($_SESSION['usuario']['tipo_usuario']) || 
    ($_SESSION['usuario']['tipo_usuario'] !== 'admin' && $_SESSION['usuario']['tipo_usuario'] !== 'super_admin')) {
    header('Location: index.php');
    exit;
}

$usuarioLogado = $_SESSION['usuario'];
$solicitacaoController = new SolicitacaoAcessoController();

// Filtro de status
$statusFiltro = isset($_GET['status']) ? $_GET['status'] : null;

// Mensagem de feedback
$message = "";
$alertType = "info";

// Processar solicitação (aprovar/rejeitar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprovar_solicitacao']) || isset($_POST['rejeitar_solicitacao'])) {
        $idSolicitacao = (int)$_POST['id_solicitacao'];
        $idAprovador = (int)$_SESSION['usuario']['id_usuario'];
        
        // Definir o status baseado na ação
        $status = isset($_POST['aprovar_solicitacao']) ? 'aprovado' : 'rejeitado';
        
        // Processar a solicitação
        $resultado = $solicitacaoController->processarSolicitacao($idSolicitacao, $status, $idAprovador);
        
        $message = $resultado['message'];
        $alertType = $resultado['success'] ? 'success' : 'danger';
    }
    
    // Excluir solicitação
    if (isset($_POST['excluir_solicitacao']) && $_SESSION['usuario']['tipo_usuario'] === 'super_admin') {
        $idSolicitacao = (int)$_POST['id_solicitacao'];
        
        // Excluir a solicitação
        $resultado = $solicitacaoController->excluirSolicitacao($idSolicitacao);
        
        $message = $resultado['message'];
        $alertType = $resultado['success'] ? 'success' : 'danger';
    }
}

// Obter solicitações com base no filtro
$solicitacoes = $solicitacaoController->listarSolicitacoes($statusFiltro);

// Obter estatísticas
$estatisticas = $solicitacaoController->obterEstatisticas();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Solicitações de Acesso</title>
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
            background-color: var(--light-bg);
            color: var(--text-dark);
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
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-success:hover {
            background-color: #28a745;
            filter: brightness(1.1);
            box-shadow: var(--hover-shadow);
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
        
        .table-actions form {
            display: inline;
        }
        
        .table-actions button {
            margin: 0 2px;
        }
        
        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: none;
            margin-bottom: 20px;
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
        }
        
        table.dataTable thead th {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-bottom: none;
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
        
        .badge.bg-success {
            background-color: #28a745 !important;
        }
        
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: var(--text-dark);
        }
        
        .stat-card {
            border-left: 5px solid var(--primary-color);
            transition: var(--transition);
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .stat-card.pendentes {
            border-left-color: var(--secondary-color);
        }
        
        .stat-card.aprovadas {
            border-left-color: #28a745;
        }
        
        .stat-card.rejeitadas {
            border-left-color: var(--accent-color);
        }
        
        .filters {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-shield"></i> Gerenciar Solicitações de Acesso</h2>
        <div>
            <a href="painel_admin.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Voltar para o Painel
            </a>
        </div>
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
            <div class="card stat-card pendentes">
                <h5><i class="fas fa-clock"></i> Solicitações Pendentes</h5>
                <p class="fs-4 mb-0"><?= $estatisticas['pendente'] ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card aprovadas">
                <h5><i class="fas fa-check-circle"></i> Solicitações Aprovadas</h5>
                <p class="fs-4 mb-0"><?= $estatisticas['aprovado'] ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card rejeitadas">
                <h5><i class="fas fa-times-circle"></i> Solicitações Rejeitadas</h5>
                <p class="fs-4 mb-0"><?= $estatisticas['rejeitado'] ?></p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card filters">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Filtrar por Status</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="pendente" <?= $statusFiltro === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="aprovado" <?= $statusFiltro === 'aprovado' ? 'selected' : '' ?>>Aprovados</option>
                        <option value="rejeitado" <?= $statusFiltro === 'rejeitado' ? 'selected' : '' ?>>Rejeitados</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <?php if (!empty($statusFiltro)): ?>
                        <a href="gerenciar_solicitacoes.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i> Limpar Filtros
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Solicitações -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($solicitacoes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Não há solicitações <?= $statusFiltro ? "com status '$statusFiltro'" : "" ?> no momento.
                </div>
            <?php else: ?>
                <table id="solicitacoesTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Nível Solicitado</th>
                            <th>Status</th>
                            <th>Data da Solicitação</th>
                            <th>Aprovado Por</th>
                            <th>Data de Aprovação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td><?= htmlspecialchars($solicitacao['id'] ?? 'N/A') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($solicitacao['nome'] ?? 'Nome não disponível') ?></strong><br>
                                    <small><?= htmlspecialchars($solicitacao['email'] ?? 'Email não disponível') ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $nivelSolicitado = $solicitacao['novo_nivel'] ?? 'comum';
                                    
                                    switch($nivelSolicitado) {
                                        case 'admin':
                                            $nivelExibicao = 'Administrador';
                                            $badgeClass = 'bg-primary';
                                            break;
                                        case 'super_admin':
                                            $nivelExibicao = 'Super Administrador';
                                            $badgeClass = 'bg-danger';
                                            break;
                                        default:
                                            $nivelExibicao = 'Usuário Comum';
                                            $badgeClass = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($nivelExibicao) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = $solicitacao['status'] ?? 'pendente';
                                    
                                    switch($status) {
                                        case 'aprovado':
                                            $statusBadge = '<span class="badge bg-success">Aprovado</span>';
                                            break;
                                        case 'rejeitado':
                                            $statusBadge = '<span class="badge bg-danger">Rejeitado</span>';
                                            break;
                                        default:
                                            $statusBadge = '<span class="badge bg-warning">Pendente</span>';
                                    }
                                    
                                    echo $statusBadge;
                                    ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'] ?? 'now'))) ?>
                                </td>
                                <td>
                                    <?php
                                    if ($solicitacao['aprovado_por']) {
                                        echo htmlspecialchars($solicitacao['aprovador_nome'] ?? 'ID: ' . $solicitacao['aprovado_por']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($solicitacao['data_aprovacao']) {
                                        echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitacao['data_aprovacao'])));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="table-actions">
                                    <?php if ($solicitacao['status'] === 'pendente'): ?>
                                        <form method="post" onsubmit="return confirm('Tem certeza que deseja aprovar esta solicitação?');">
                                            <input type="hidden" name="id_solicitacao" value="<?= $solicitacao['id'] ?? '' ?>">
                                            <button type="submit" name="aprovar_solicitacao" class="btn btn-sm btn-success" title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Tem certeza que deseja rejeitar esta solicitação?');">
                                            <input type="hidden" name="id_solicitacao" value="<?= $solicitacao['id'] ?? '' ?>">
                                            <button type="submit" name="rejeitar_solicitacao" class="btn btn-sm btn-danger" title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($_SESSION['usuario']['tipo_usuario'] === 'super_admin'): ?>
                                        <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir esta solicitação? Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="id_solicitacao" value="<?= $solicitacao['id'] ?? '' ?>">
                                            <button type="submit" name="excluir_solicitacao" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-sm btn-info" title="Detalhes" 
                                            data-bs-toggle="modal" data-bs-target="#detalhesModal<?= $solicitacao['id'] ?>">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    
                                    <!-- Modal de Detalhes -->
                                    <div class="modal fade" id="detalhesModal<?= $solicitacao['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalhes da Solicitação #<?= htmlspecialchars($solicitacao['id'] ?? '') ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <h6>Informações do Usuário</h6>
                                                        <p><strong>Nome:</strong> <?= htmlspecialchars($solicitacao['nome'] ?? 'N/A') ?></p>
                                                        <p><strong>Email:</strong> <?= htmlspecialchars($solicitacao['email'] ?? 'N/A') ?></p>
                                                        <p><strong>Nível Atual:</strong> <?= htmlspecialchars($solicitacao['tipo_usuario_atual'] ?? 'N/A') ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Informações da Solicitação</h6>
                                                        <p><strong>Nível Solicitado:</strong> <?= htmlspecialchars($nivelExibicao) ?></p>
                                                        <p><strong>Justificativa:</strong> <?= htmlspecialchars($solicitacao['justificativa'] ?? 'Nenhuma justificativa fornecida') ?></p>
                                                        <p><strong>Data da Solicitação:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'] ?? 'now'))) ?></p>
                                                    </div>
                                                    <div>
                                                        <h6>Status</h6>
                                                        <p><strong>Status Atual:</strong> <?= $statusBadge ?></p>
                                                        <?php if ($solicitacao['status'] !== 'pendente'): ?>
                                                            <p><strong>Aprovado/Rejeitado por:</strong> <?= htmlspecialchars($solicitacao['aprovador_nome'] ?? 'N/A') ?></p>
                                                            <p><strong>Data:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($solicitacao['data_aprovacao'] ?? 'now'))) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                    <?php if ($solicitacao['status'] === 'pendente'): ?>
                                                        <form method="post">
                                                            <input type="hidden" name="id_solicitacao" value="<?= $solicitacao['id'] ?? '' ?>">
                                                            <button type="submit" name="aprovar_solicitacao" class="btn btn-success">Aprovar</button>
                                                            <button type="submit" name="rejeitar_solicitacao" class="btn btn-danger">Rejeitar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#solicitacoesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            order: [[4, 'desc']], // Ordenar por data de solicitação (decrescente)
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [7] } // Desabilitar ordenação na coluna de ações
            ]
        });
        
        // Fechar alert automaticamente após 5 segundos
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 5000);
    });
</script>
</body>
</html>