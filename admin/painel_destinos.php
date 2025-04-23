<?php
// Incluir configuração de banco de dados
require_once('../config/database.php');

// Usar a classe Database para obter a conexão
use Config\Database;
$conn = Database::getConnection();

// Inicializar variáveis de filtro
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$destaque = isset($_GET['destaque']) ? $_GET['destaque'] : '';

// Construir a consulta SQL base
$sql = "SELECT d.*, c.nome_categoria, l.nome_local 
        FROM destinos_turisticos d
        LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
        LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao
        WHERE 1=1";

// Adicionar condições de filtro se especificadas
if (!empty($categoria)) {
    $sql .= " AND d.id_categoria = :categoria";
}

if ($destaque == '1') {
    $sql .= " AND d.is_maravilha = 1";
} elseif ($destaque == '0') {
    $sql .= " AND d.is_maravilha = 0";
}

// Ordenar por data de cadastro (mais recente primeiro)
$sql .= " ORDER BY d.data_cadastro DESC";

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);

// Vincular parâmetros se necessário
if (!empty($categoria)) {
    $stmt->bindParam(':categoria', $categoria, PDO::PARAM_INT);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar todas as categorias para o filtro
$sql_categorias = "SELECT * FROM categorias ORDER BY nome_categoria";
$stmt_categorias = $conn->prepare($sql_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Destinos - Destinos Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
    <style>
        :root {
            --primary: #004d40;
            --primary-light: #006355;
            --primary-dark: #003b32;
            --secondary: #ff9800;
            --secondary-light: #ffb74d;
            --text-dark: #333333;
            --text-light: #6c757d;
            --background: #f5f5f5;
            --white: #ffffff;
            --border: #e0e0e0;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
        }

        .top-bar {
            background-color: var(--primary);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-section {
            display: flex;
            align-items: center;
        }

        .logo-section h1 {
            font-size: 20px;
            margin-left: 12px;
            font-weight: 500;
        }

        .logo-icon {
            background-color: var(--white);
            color: var(--primary);
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .user-info i {
            font-size: 18px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.15);
            border: none;
            color: var(--white);
            padding: 6px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .content-wrapper {
            padding: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 500;
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(0, 77, 64, 0.05);
        }

        .search-filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--white);
            padding: 16px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 24px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: var(--background);
            border-radius: 4px;
            padding: 8px 14px;
            width: 280px;
        }

        .search-box input {
            border: none;
            background: transparent;
            padding: 0 10px;
            width: 100%;
            outline: none;
            color: var(--text-dark);
        }

        .search-box i {
            color: var(--text-light);
        }

        .filters {
            display: flex;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            color: var(--text-light);
        }

        .filter-select {
            background-color: var(--background);
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            color: var(--text-dark);
            outline: none;
            min-width: 150px;
        }

        .data-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 16px;
            background-color: rgba(0, 77, 64, 0.03);
            font-weight: 500;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
        }

        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: rgba(0, 77, 64, 0.02);
        }

        .thumbnail {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 4px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-featured {
            background-color: rgba(255, 152, 0, 0.12);
            color: var(--secondary);
        }

        .badge i {
            margin-right: 4px;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            color: var(--white);
            transition: var(--transition);
        }

        .view-btn {
            background-color: var(--info);
        }

        .view-btn:hover {
            background-color: #1976d2;
        }

        .edit-btn {
            background-color: var(--secondary);
        }

        .edit-btn:hover {
            background-color: #f57c00;
        }

        .delete-btn {
            background-color: var(--danger);
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 24px;
            gap: 6px;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background-color: var(--white);
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .page-btn.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .page-btn:hover:not(.active) {
            border-color: var(--primary);
            color: var(--primary);
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .search-filter-bar {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .search-box {
                width: 100%;
            }

            .filters {
                width: 100%;
                flex-wrap: wrap;
            }

            .filter-group {
                flex: 1;
                min-width: 120px;
            }

            .data-table th:nth-child(3),
            .data-table td:nth-child(3),
            .data-table th:nth-child(5),
            .data-table td:nth-child(5) {
                display: none;
            }

            .action-buttons {
                flex-direction: column;
            }

            .user-actions {
                gap: 8px;
            }

            .user-info span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="top-bar">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h1>Destinos Angola</h1>
        </div>
        <div class="user-actions">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
            <a href="painel_admin.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Painel
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Gestão de Destinos Turísticos</h2>
            <div class="action-buttons">
                <a href="cadastrar_destino.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Novo Destino
                </a>
                
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <form method="GET" action="" id="filterForm" class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nome do destino...">
            </div>
            <div class="filters">
                <div class="filter-group">
                    <span class="filter-label">Categoria:</span>
                    <select class="filter-select" name="categoria" id="categoriaFilter">
                        <option value="">Todas</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <span class="filter-label">Destaque:</span>
                    <select class="filter-select" name="destaque" id="destaqueFilter">
                        <option value="">Todos</option>
                        <option value="1" <?php echo ($destaque == '1') ? 'selected' : ''; ?>>Sim</option>
                        <option value="0" <?php echo ($destaque == '0') ? 'selected' : ''; ?>>Não</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Data Table -->
        <div class="data-card">
            <table class="data-table" id="destinosTable">
                <thead>
                    <tr>
                        <th width="60">Imagem</th>
                        <th>Nome do Destino</th>
                        <th>Categoria</th>
                        <th>Destaque</th>
                        <th>Data Cadastro</th>
                        <th width="140">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($result) > 0): ?>
                        <?php foreach($result as $row): ?>
                            <tr>
                            <td>
    <?php 
    echo "<p style='font-size:10px;'>" . htmlspecialchars($row['imagem']) . "</p>";
    ?>
    <img src="../uploads/<?php echo htmlspecialchars($row['imagem']); ?>" alt="Foto" class="thumbnail">
</td>
                                <td><?php echo htmlspecialchars($row['nome_destino']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                                <td>
                                    <?php if($row['is_maravilha'] == 1): ?>
                                        <span class="badge badge-featured"><i class="fas fa-crown"></i> Maravilha</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['data_cadastro'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="visualizar_destino.php?id=<?php echo $row['id']; ?>" class="icon-btn view-btn" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="editar_destino.php?id=<?php echo $row['id']; ?>" class="icon-btn edit-btn" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmarExclusao(<?php echo $row['id']; ?>)" class="icon-btn delete-btn" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <p>Nenhum destino encontrado com os filtros selecionados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <script>
        // Aplicar filtros automaticamente quando mudar os selects
        document.getElementById('categoriaFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        
        document.getElementById('destaqueFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        
        // Função para busca rápida na tabela
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('destinosTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const destinoName = rows[i].getElementsByTagName('td')[1];
                if (destinoName) {
                    const textValue = destinoName.textContent || destinoName.innerText;
                    if (textValue.toLowerCase().indexOf(searchValue) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });
        
        // Função para confirmar exclusão
        function confirmarExclusao(id) {
            if (confirm("Tem certeza que deseja excluir este destino?")) {
                window.location.href = "excluir_destino.php?id=" + id;
            }
        }
    </script>
</body>
</html>
<?php
// Não é necessário fechar a conexão com PDO, ele faz isso automaticamente ao final do script
?>