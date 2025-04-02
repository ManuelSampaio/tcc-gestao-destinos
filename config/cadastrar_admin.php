<?php
require_once 'database.php'; // Certifique-se de que o caminho está correto

use Config\Database;

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Criando o usuário super_admin
try {
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (:nome, :email, :senha, :tipo_usuario)";
    $stmt = $conn->prepare($sql);
    
    $nome = "Manuel Afonso";
    $email = "manuelafonso@gmail.com";
    $senha = password_hash("123", PASSWORD_DEFAULT); // Hash seguro da senha
    $tipo_usuario = "super_admin";

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':tipo_usuario', $tipo_usuario);
    
    $stmt->execute();
    
    echo "Usuário super_admin cadastrado com sucesso!";
} catch (Exception $e) {
    echo "Erro ao cadastrar usuário: " . $e->getMessage();
}
?>
