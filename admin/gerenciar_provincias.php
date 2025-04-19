<?php
// Conexão com o banco de dados
require_once 'includes/conexao.php';

// Título da página
$titulo = "Gerenciar Províncias";

// Incluir cabeçalho
include_once 'includes/header.php';

// Consulta SQL para listar províncias
$sql = "SELECT * FROM provincias ORDER BY nome_provincia";
$resultado = $conexao->query($sql);
?>

<div class="content-container">
    <div class="content-header">
        <div class="content-title">
            <i class="fas fa-map"></i>
            Gerenciar Províncias
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addProvinceModal">
                <i class="fas fa-plus"></i> Nova Província
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="destinos-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Província</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($provincia = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $provincia['id_provincia']; ?></td>
                            <td><?php echo $provincia['nome_provincia']; ?></td>
                            <td>
                                <button class="action-btn edit-btn" data-id="<?php echo $provincia['id_provincia']; ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $provincia['id_provincia']; ?>">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">Nenhuma província cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para adicionar província -->
<div class="modal fade" id="addProvinceModal" tabindex="-1" role="dialog">
    <!-- Conteúdo do modal aqui -->
</div>

<?php
// Incluir rodapé
include_once 'includes/footer.php';
?>