<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Avaliacao.php';

use Config\Database;
use App\Models\Avaliacao;

class AvaliacaoController {
    private $avaliacaoModel;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->avaliacaoModel = new Avaliacao($this->db);
    }

    public function processarAvaliacao($dados) {
        error_log('Antes do session_start(): ' . session_status());
session_start();
error_log('Depois do session_start(): ' . session_status());


        // Verifica se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => 'Você precisa estar logado para avaliar.'
            ]);
            exit;
        }

        // Valida dados de entrada
        if (!isset($dados['id_destino']) || !isset($dados['nota'])) {
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => 'Dados inválidos para avaliação.'
            ]);
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $idDestino = $dados['id_destino'];

        // Prepara dados da avaliação
        $dadosAvaliacao = [
            'id_usuario' => $idUsuario,
            'id_destino' => $idDestino,
            'nota' => $dados['nota'],
            'comentario' => $dados['comentario'] ?? null
        ];

        // Verifica se já avaliou e atualiza ao invés de adicionar
        if ($this->avaliacaoModel->verificarSeJaAvaliou($idUsuario, $idDestino)) {
            $resultado = $this->avaliacaoModel->atualizarAvaliacao($idUsuario, $idDestino, $dadosAvaliacao);
            $mensagem = 'Avaliação atualizada com sucesso!';
        } else {
            $resultado = $this->avaliacaoModel->adicionarAvaliacao($dadosAvaliacao);
            $mensagem = 'Avaliação enviada com sucesso!';
        }

        if ($resultado) {
            echo json_encode([
                'sucesso' => true, 
                'mensagem' => $mensagem
            ]);
        } else {
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => 'Erro ao processar avaliação.'
            ]);
        }
        exit;
    }

    public function obterAvaliacoesDestino($idDestino) {
        // Busca avaliações do destino
        $avaliacoes = $this->avaliacaoModel->buscarAvaliacoesPorDestino($idDestino);
        $mediaAvaliacoes = $this->avaliacaoModel->calcularMediaAvaliacoes($idDestino);
        $totalAvaliacoes = $this->avaliacaoModel->contarAvaliacoes($idDestino);
        $estatisticas = $this->avaliacaoModel->obterEstatisticasAvaliacoes($idDestino);
        
        // Verifica se o usuário atual já avaliou
        $avaliacaoUsuario = null;
        if (isset($_SESSION['usuario_id'])) {
            $avaliacaoUsuario = $this->avaliacaoModel->buscarAvaliacaoUsuario($_SESSION['usuario_id'], $idDestino);
        }
        
        return [
            'avaliacoes' => $avaliacoes,
            'media' => $mediaAvaliacoes,
            'total' => $totalAvaliacoes,
            'estatisticas' => $estatisticas,
            'avaliacao_usuario' => $avaliacaoUsuario
        ];
    }
}

// Processa a requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AvaliacaoController();
    $controller->processarAvaliacao($_POST);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'obter_avaliacoes') {
    $controller = new AvaliacaoController();
    $idDestino = $_GET['id_destino'] ?? 0;
    
    header('Content-Type: application/json');
    echo json_encode($controller->obterAvaliacoesDestino($idDestino));
    exit;
}