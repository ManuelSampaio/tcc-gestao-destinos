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
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 3600); // Sessão dura 1 hora
ini_set('session.cookie_lifetime', 3600); // Cookie dura 1 hora
session_start();

    session_start();
}
        error_log('Sessão antes da verificação: ' . print_r($_SESSION, true));

        // Verifica se usuário está logado corretamente
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_usuario'])) { 
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => 'Você precisa estar logado para avaliar.'
            ]);
            exit;
        }        

        // Valida dados de entrada
        if (empty($dados['id_destino']) || empty($dados['nota'])) {
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => 'Dados inválidos para avaliação.'
            ]);
            exit;
        }

        $idUsuario = $_SESSION['usuario']['id_usuario'];
        $idDestino = (int) $dados['id_destino'];

        // Prepara dados da avaliação
        $dadosAvaliacao = [
            'id_usuario' => $idUsuario,
            'id_destino' => $idDestino,
            'nota' => (int) $dados['nota'],
            'comentario' => $dados['comentario'] ?? ''
        ];

        // Verifica se já avaliou e atualiza ao invés de adicionar
        if ($this->avaliacaoModel->verificarSeJaAvaliou($idUsuario, $idDestino)) {
            $resultado = $this->avaliacaoModel->atualizarAvaliacao($idUsuario, $idDestino, $dadosAvaliacao);
            $mensagem = 'Avaliação atualizada com sucesso!';
        } else {
            $resultado = $this->avaliacaoModel->adicionarAvaliacao($dadosAvaliacao);
            $mensagem = 'Avaliação enviada com sucesso!';
        }

        echo json_encode([
            'sucesso' => $resultado, 
            'mensagem' => $resultado ? $mensagem : 'Erro ao processar avaliação.'
        ]);
        exit;
    }

    public function obterAvaliacoesDestino($idDestino) {
        if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
        $idDestino = (int) $idDestino;
        
        // Busca avaliações do destino
        $avaliacoes = $this->avaliacaoModel->buscarAvaliacoesPorDestino($idDestino);
        $mediaAvaliacoes = $this->avaliacaoModel->calcularMediaAvaliacoes($idDestino);
        $totalAvaliacoes = $this->avaliacaoModel->contarAvaliacoes($idDestino);
        $estatisticas = $this->avaliacaoModel->obterEstatisticasAvaliacoes($idDestino);
        
        // Verifica se o usuário atual já avaliou
        $avaliacaoUsuario = null;
        if (isset($_SESSION['usuario']['id_usuario'])) {
            $avaliacaoUsuario = $this->avaliacaoModel->buscarAvaliacaoUsuario($_SESSION['usuario']['id_usuario'], $idDestino);
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
