<?php
// Incluir configuração de banco de dados
require_once('../config/database.php');
require_once('../app/controllers/DestinoController.php');

use Config\Database;
use App\Controllers\DestinoController;

// Verificar se foi enviado um ID válido
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirecionar para a página de listagem com mensagem de erro
    header('Location: gerenciar_destinos.php?erro=id_invalido');
    exit;
}

$id = (int)$_GET['id'];

// Inicializa o controlador de destinos
$destinoController = new DestinoController();

// Obter informações do destino antes de excluir (para excluir a imagem também)
$destino = $destinoController->obterDestinoPorId($id);

if (!$destino) {
    // Destino não encontrado
    header('Location: gerenciar_destinos.php?erro=destino_nao_encontrado');
    exit;
}

// Excluir o destino
$resultado = $destinoController->excluirDestino($id);

if ($resultado) {
    // Se houver imagem, excluí-la do sistema de arquivos
    if (isset($destino['imagem']) && !empty($destino['imagem'])) {
        $caminhoImagem = '../' . $destino['imagem'];
        if (file_exists($caminhoImagem)) {
            unlink($caminhoImagem);
        }
    }
    
    // Redirecionar para a página de listagem com mensagem de sucesso
    header('Location: gerenciar_destinos.php?sucesso=destino_excluido');
} else {
    // Redirecionar para a página de listagem com mensagem de erro
    header('Location: gerenciar_destinos.php?erro=falha_ao_excluir');
}
exit;