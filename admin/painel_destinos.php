<?php
require_once '../config/database.php'; 
use Config\Database;

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Configuração da paginação
    $por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $offset = ($pagina - 1) * $por_pagina;
    
    // Processamento dos filtros
    $where = [];
    $params = [];
    
    if (!empty($_GET['busca'])) {
        $where[] = "(d.nome_destino LIKE ? OR d.localizacao LIKE ?)";
        $params[] = "%{$_GET['busca']}%";
        $params[] = "%{$_GET['busca']}%";
    }
    
    if (!empty($_GET['categoria'])) {
        $where[] = "d.id_categoria = ?";
        $params[] = $_GET['categoria'];
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Queries principais
    $sqlTotal = "SELECT COUNT(*) as total FROM destinos_turisticos d $whereClause";
    $sqlCategorias = "SELECT id_categoria, nome_categoria, 
                      (SELECT COUNT(*) FROM destinos_turisticos 
                       WHERE id_categoria = c.id_categoria) as total 
                      FROM categorias c";
    $sqlDestinos = "
        SELECT d.id, d.nome_destino, COALESCE(c.nome_categoria, 'Sem Categoria') as categoria,
               d.localizacao
        FROM destinos_turisticos d
        LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
        $whereClause
        ORDER BY d.id DESC
        LIMIT $por_pagina OFFSET $offset";
    
    // Execução das queries
    $stmtTotal = !empty($params) ? $conn->prepare($sqlTotal) : $conn->query($sqlTotal);
    $stmtCategorias = $conn->query($sqlCategorias);
    $stmtDestinos = !empty($params) ? $conn->prepare($sqlDestinos) : $conn->query($sqlDestinos);
    
    if (!empty($params)) {
        $stmtTotal->execute($params);
        $stmtDestinos->execute($params);
    }
    
    $total = !empty($params) ? $stmtTotal->fetch()['total'] : $stmtTotal->fetch()['total'];
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
    $destinos = $stmtDestinos->fetchAll(PDO::FETCH_ASSOC);
    $total_paginas = ceil($total / $por_pagina);
    
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Destinos - Angola Tours</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #0d47a1;
            --accent: #2962ff;
            --background: #f5f6fa;
            --surface: #ffffff;
            --text: #333333;
            --error: #d32f2f;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--surface);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .header h1 {
            color: var(--primary);
            font-size: 24px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--surface);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 24px;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .tools {
            background: var(--surface);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            min-width: 200px;
        }

        .table-container {
            background: var(--surface);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: var(--text);
            background: var(--surface);
            transition: all 0.3s ease;
        }

        .page-link.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .page-link:hover:not(.active) {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .tools {
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
            }
            
            select {
                width: 100%;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestão de Destinos</h1>
            <a href="cadastrar_destino.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Destino
            </a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $total; ?></h3>
                <p>Total de Destinos</p>
            </div>
            <?php foreach ($categorias as $categoria): ?>
                <div class="stat-card">
                    <h3><?php echo $categoria['total']; ?></h3>
                    <p><?php echo htmlspecialchars($categoria['nome_categoria']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="tools">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="busca" placeholder="Buscar destinos..." 
                       value="<?php echo htmlspecialchars($_GET['busca'] ?? ''); ?>">
            </div>
            
            <select id="categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id_categoria']; ?>"
                            <?php echo ($_GET['categoria'] ?? '') == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Destino</th>
                        <th>Categoria</th>
                        <th>Localização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($destinos)): ?>
                        <?php foreach ($destinos as $destino): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($destino['nome_destino']); ?></td>
                                <td><?php echo htmlspecialchars($destino['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($destino['localizacao']); ?></td>
                                <td class="actions">
                                    <a href="editar_destino.php?id=<?php echo $destino['id']; ?>" 
                                       class="btn btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0)" 
                                       onclick="confirmarExclusao(<?php echo $destino['id']; ?>)"
                                       class="btn btn-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Nenhum destino encontrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?><?php echo isset($_GET['busca']) ? '&busca=' . htmlspecialchars($_GET['busca']) : ''; ?><?php echo isset($_GET['categoria']) ? '&categoria=' . htmlspecialchars($_GET['categoria']) : ''; ?>" 
                       class="page-link <?php echo $pagina == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const busca = document.getElementById('busca');
        const categoria = document.getElementById('categoria');

        function atualizarFiltros() {
            const params = new URLSearchParams(window.location.search);
            
            if (busca.value) params.set('busca', busca.value);
            else params.delete('busca');
            
            if (categoria.value) params.set('categoria', categoria.value);
            else params.delete('categoria');
            
            window.location.href = '?' + params.toString();
        }

        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este destino?')) {
                window.location.href = 'excluir_destino.php?id=' + id;
            }
        }

        busca.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') atualizarFiltros();
        });
        
        categoria.addEventListener('change', atualizarFiltros);
    </script>
</body>
</html>