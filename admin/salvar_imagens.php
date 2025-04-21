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

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gerenciar_destinos.php');
    exit;
}

// Obtém o ID do destino
$id_destino = $_POST['id_destino'] ?? null;

if (!$id_destino) {
    header('Location: gerenciar_destinos.php?erro=' . urlencode('ID do destino não fornecido'));
    exit;
}

// Verifica se foram enviadas imagens
if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'][0])) {
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&erro=" . urlencode('Nenhuma imagem selecionada'));
    exit;
}

// Define diretório de upload
$diretorio_upload = '../uploads/';

// Verifica se o diretório existe, se não, tenta criar
if (!is_dir($diretorio_upload)) {
    if (!mkdir($diretorio_upload, 0755, true)) {
        header("Location: gerenciar_imagens_destino.php?id=$id_destino&erro=" . urlencode('Erro ao criar diretório de upload'));
        exit;
    }
}

// Obtém a ordem máxima existente para este destino
$sql = "SELECT MAX(ordem) as max_ordem FROM imagens WHERE id_destino = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_destino);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$ordem_atual = ($row['max_ordem'] !== null) ? (int)$row['max_ordem'] : 0;

// Prepara a consulta para inserção
$sql_insert = "INSERT INTO imagens (id_destino, caminho_imagem, ordem) VALUES (?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// Extensões permitidas
$extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

// Tamanho máximo (2MB)
$tamanho_maximo = 2 * 1024 * 1024;

$sucessos = 0;
$erros = [];

// Processa cada arquivo
foreach ($_FILES['imagens']['name'] as $key => $nome) {
    // Verifica se houve erro no upload
    if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_OK) {
        $erros[] = "Erro no upload do arquivo $nome";
        continue;
    }
    
    // Verifica o tamanho do arquivo
    if ($_FILES['imagens']['size'][$key] > $tamanho_maximo) {
        $erros[] = "O arquivo $nome excede o tamanho máximo permitido (2MB)";
        continue;
    }
    
    // Verifica a extensão
    $extensao = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoes_permitidas)) {
        $erros[] = "O arquivo $nome tem uma extensão não permitida. Use: " . implode(', ', $extensoes_permitidas);
        continue;
    }
    
    // Gera um nome único para o arquivo
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_completo = $diretorio_upload . $nome_arquivo;
    
    // Move o arquivo para o diretório de upload
    if (move_uploaded_file($_FILES['imagens']['tmp_name'][$key], $caminho_completo)) {
        // Incrementa a ordem
        $ordem_atual++;
        
        // Insere no banco de dados
        $stmt_insert->bind_param("isi", $id_destino, $nome_arquivo, $ordem_atual);
        if ($stmt_insert->execute()) {
            $sucessos++;
        } else {
            $erros[] = "Erro ao salvar o arquivo $nome no banco de dados: " . $stmt_insert->error;
            // Remove o arquivo se não conseguiu inserir no banco
            @unlink($caminho_completo);
        }
    } else {
        $erros[] = "Erro ao mover o arquivo $nome para o diretório de upload";
    }
}

$stmt_insert->close();
$conn->close();

// Redireciona com mensagens de sucesso ou erro
if ($sucessos > 0) {
    $mensagem = ($sucessos == 1) ? "1 imagem adicionada com sucesso" : "$sucessos imagens adicionadas com sucesso";
    
    if (!empty($erros)) {
        $mensagem .= ", mas ocorreram os seguintes erros: " . implode("; ", $erros);
    }
    
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&sucesso=" . urlencode($mensagem));
} else {
    header("Location: gerenciar_imagens_destino.php?id=$id_destino&erro=" . urlencode(implode("; ", $erros)));
}
exit;