<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Verificar se o usuário está logado e é um administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] != 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

use Config\Database;

// Inicializa conexão com o banco de dados
$database = new Database();
$conn = $database->getConnection();

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gerenciar_destinos.php');
    exit;
}

// Obtém os IDs necessários
$id_imagem = $_POST['id_imagem'] ?? null;
$id_destino = $_POST['id_destino'] ?? null;

if (!$id_imagem || !$id_destino) {
    header('Location: gerenciar_destinos.php?erro=' . urlencode('Parâmetros inválidos'));
    exit;
}

// Busca informações da imagem
$sql = "SELECT caminho_imagem FROM imagens WHERE id_imagem = ? AND id_destino = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_imagem, $id_destino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&erro=" . urlencode('Imagem não encontrada'));
    exit;
}

$imagem = $result->fetch_assoc();
$caminho_arquivo = '../uploads/' . $imagem['caminho_imagem'];

// Exclui a imagem do banco de dados
$sql_delete = "DELETE FROM imagens WHERE id_imagem = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id_imagem);

if ($stmt_delete->execute()) {
    // Remove o arquivo físico, se existir
    if (file_exists($caminho_arquivo)) {
        @unlink($caminho_arquivo);
    }
    
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&sucesso=" . urlencode('Imagem excluída com sucesso'));
} else {
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&erro=" . urlencode('Erro ao excluir imagem: ' . $stmt_delete->error));
}

$stmt_delete->close();
$conn->close();
exit;