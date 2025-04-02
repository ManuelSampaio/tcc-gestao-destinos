<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Avaliacao.php';

use Config\Database;
use App\Models\Avaliacao;

header('Content-Type: application/json');

// Depuração: Verificar se a sessão contém os dados esperados
error_log('Sessão Atual: ' . print_r($_SESSION, true));

// Verificar se o usuário está logado corretamente
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_usuario'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para avaliar.'
    ]);
    exit;
}

// Verificar se os dados necessários foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_destino']) || empty($_POST['nota'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $database = new Database();
    $conn = $database->getConnection();
    
    // Inicializar o modelo de avaliação
    $avaliacaoModel = new Avaliacao($conn);
    
    // Obter dados do formulário
    $idUsuario = $_SESSION['usuario']['id_usuario'];
    $idDestino = (int)$_POST['id_destino'];
    $nota = (int)$_POST['nota'];
    $comentario = $_POST['comentario'] ?? '';
    
    $dados = [
        'id_usuario' => $idUsuario,
        'id_destino' => $idDestino,
        'nota' => $nota,
        'comentario' => $comentario
    ];
    
    // Verificar se o usuário já avaliou este destino
    $jaAvaliou = $avaliacaoModel->verificarSeJaAvaliou($idUsuario, $idDestino);
    
    if ($jaAvaliou) {
        // Atualizar avaliação existente
        $resultado = $avaliacaoModel->atualizarAvaliacao($idUsuario, $idDestino, [
            'nota' => $nota,
            'comentario' => $comentario
        ]);
        
        echo json_encode([
            'sucesso' => $resultado,
            'mensagem' => $resultado ? 'Avaliação atualizada com sucesso!' : 'Erro ao atualizar avaliação.'
        ]);
    } else {
        // Adicionar nova avaliação
        $resultado = $avaliacaoModel->adicionarAvaliacao($dados);
        
        echo json_encode([
            'sucesso' => $resultado,
            'mensagem' => $resultado ? 'Avaliação enviada com sucesso!' : 'Erro ao enviar avaliação.'
        ]);
    }
} catch (Exception $e) {
    error_log('Erro ao processar avaliação: ' . $e->getMessage());
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar avaliação. Tente novamente mais tarde.'
    ]);
}