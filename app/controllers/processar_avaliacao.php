<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Avaliacao.php';

use Config\Database;
use App\Models\Avaliacao;

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_usuario'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para avaliar.'
    ]);
    exit;
}

// Verificar se os dados necessários foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_destino']) || !isset($_POST['nota'])) {
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
        
        if ($resultado) {
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Avaliação atualizada com sucesso!'
            ]);
        } else {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao atualizar avaliação.'
            ]);
        }
    } else {
        // Adicionar nova avaliação
        $resultado = $avaliacaoModel->adicionarAvaliacao($dados);
        
        if ($resultado) {
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Avaliação enviada com sucesso!'
            ]);
        } else {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao enviar avaliação.'
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar avaliação: ' . $e->getMessage()
    ]);
}