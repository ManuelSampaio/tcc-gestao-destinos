<?php
// Evitar qualquer saída antes dos headers
// Incluir configuração de banco de dados
require_once('../config/database.php');
require_once('../app/controllers/DestinoController.php');

use Config\Database;
use App\Controllers\DestinoController;

// Iniciar sessão se necessário
session_start();

try {
    // Verificar se foi enviado um ID válido
    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['mensagem'] = "ID inválido.";
        $_SESSION['tipo_mensagem'] = "erro";
        header('Location: painel_destinos.php');
        exit;
    }

    $id = (int)$_GET['id'];

    // Inicializa o controlador de destinos
    $destinoController = new DestinoController();

    // Obter informações do destino antes de excluir (para excluir a imagem também)
    $destino = $destinoController->obterDestinoPorId($id);

    if (!$destino) {
        $_SESSION['mensagem'] = "Destino não encontrado.";
        $_SESSION['tipo_mensagem'] = "erro";
        header('Location: ../admin/gerenciar_destinos.php');
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
        
        $_SESSION['mensagem'] = "Destino excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
    } else {
        $_SESSION['mensagem'] = "Falha ao excluir o destino.";
        $_SESSION['tipo_mensagem'] = "erro";
    }

} catch (Exception $e) {
    $_SESSION['mensagem'] = "Erro ao processar a solicitação: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "erro";
}

// Garantir que o redirecionamento ocorra sempre
header('Location: painel_destinos.php');
exit;
?>