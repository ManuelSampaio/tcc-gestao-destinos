<?php
// Conexão com o banco de dados
require_once 'includes/conexao.php';

// Título da página
$titulo = "Gerenciar Localizações";

// Incluir cabeçalho
include_once 'includes/header.php';

// Consulta SQL para listar localizações
$sql = "SELECT l.*, p.nome_provincia 
        FROM localizacoes l
        LEFT JOIN provincias p ON l.id_provincia = p.id_provincia 
        ORDER BY l.nome_local";
$resultado = $conexao->query($sql);
?>

<div class="content-container">
    <div class="content-header">
        <div class="content-title">
            <i class="fas fa-map-pin"></i>
            Gerenciar Localizações
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addLocationModal">
                <i class="fas fa-plus"></i> Nova Localização
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="destinos-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Localização</th>
                    <th>Província</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($local = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $local['id_localizacao']; ?></td>
                            <td><?php echo $local['nome_local']; ?></td>
                            <td><?php echo $local['nome_provincia'] ?? 'Não definida'; ?></td>
                            <td><?php echo $local['latitude'] ?? 'N/A'; ?></td>
                            <td><?php echo $local['longitude'] ?? 'N/A'; ?></td>
                            <td>
                                <button class="action-btn edit-btn" data-id="<?php echo $local['id_localizacao']; ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $local['id_localizacao']; ?>">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhuma localização cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para adicionar localização -->
<div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog">
    <!-- Conteúdo do modal aqui -->
</div>

<?php
// Incluir rodapé
include_once 'includes/footer.php';
?>