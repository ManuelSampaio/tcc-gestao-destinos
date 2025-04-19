<?php
// Conexão com o banco de dados
require_once '../public/test_connection.php';

// Título da página
$titulo = "Gerenciar Categorias";

// Incluir cabeçalho
include_once 'includes/header.php';

// Consulta SQL para listar categorias
$sql = "SELECT * FROM categorias ORDER BY nome_categoria";
$resultado = $conexao->query($sql);
?>

<div class="content-container">
    <div class="content-header">
        <div class="content-title">
            <i class="fas fa-tags"></i>
            Gerenciar Categorias
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Nova Categoria
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="destinos-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($categoria = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $categoria['id_categoria']; ?></td>
                            <td><?php echo $categoria['nome_categoria']; ?></td>
                            <td>
                                <button class="action-btn edit-btn" data-id="<?php echo $categoria['id_categoria']; ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $categoria['id_categoria']; ?>">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">Nenhuma categoria cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para adicionar categoria -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
    <!-- Conteúdo do modal aqui -->
</div>

<?php
// Incluir rodapé
include_once 'includes/footer.php';
?>