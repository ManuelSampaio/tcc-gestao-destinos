<?php
session_start();
ob_start(); // Evita erros de redireção por saída prematura
require_once '../app/controllers/UsuarioController.php';
require_once '../config/Database.php'; // Arquivo de conexão com o banco

use App\Controllers\UsuarioController;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    // Definir tipo_usuario como "comum" por padrão, conforme o fluxo definido
    $tipo_usuario = 'comum';

    // Validação dos campos
    if (empty($nome) || empty($email) || empty($senha)) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos.';
        header('Location: cadastro.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Email inválido.';
        header('Location: cadastro.php');
        exit;
    }

    if (strlen($senha) < 4) {
        $_SESSION['error_message'] = 'A senha deve ter pelo menos 4 caracteres.';
        header('Location: cadastro.php');
        exit;
    }

    try {
        $usuarioController = new UsuarioController();

        // Verifica se o email já está cadastrado
        if ($usuarioController->verificarEmailExiste($email)) {
            $_SESSION['error_message'] = 'Este email já está cadastrado.';
            header('Location: cadastro.php');
            exit;
        }

        // Criar array com os dados do usuário
        $dadosUsuario = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha, // Senha será criptografada dentro do Controller
            'tipo_usuario' => $tipo_usuario
        ];

        // Chamar o método de cadastro no controller
        $usuarioId = $usuarioController->adicionarUsuario($dadosUsuario);

        if ($usuarioId) {
            // Autenticar o usuário após o cadastro
            $resultado = $usuarioController->autenticarUsuario($email, $senha);

            if ($resultado['success']) {
                $_SESSION['usuario'] = [
                    'id_usuario' => $resultado['usuario']['id_usuario'],
                    'email' => $resultado['usuario']['email'],
                    'nome' => $resultado['usuario']['nome'],
                    'tipo_usuario' => $resultado['usuario']['tipo_usuario']
                ];
                
                // Redirecionar conforme o tipo de usuário
                if ($resultado['usuario']['tipo_usuario'] === 'admin') {
                    header('Location: painel_admin.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                // Se houver algum problema com o login automático
                $_SESSION['success_message'] = 'Usuário cadastrado com sucesso! Faça login.';
                header('Location: login.php');
                exit;
            }
        } else {
            $_SESSION['error_message'] = 'Erro ao cadastrar usuário.';
            header('Location: cadastro.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        $_SESSION['error_message'] = 'Erro ao processar cadastro.';
        header('Location: cadastro.php');
        exit;
    }
}

ob_end_flush(); // Libera o buffer de saída
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --text-color: #333;
            --text-light: #6b7280;
            --background: #f8f9fa;
            --white: #ffffff;
            --error: #e53935;
            --success: #4caf50;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            padding: 20px;
        }
        
        .container {
            width: 420px;
            max-width: 100%;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(180deg, #1a73e8 0%, #1557b0 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .header p {
            font-size: 15px;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 25px;
        }
        
        .error {
            background-color: rgba(229, 57, 53, 0.1);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .input-group {
            margin-bottom: 18px;
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 12px;
            top: 14px;
            color: var(--text-light);
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.2s ease;
            background-color: var(--white);
        }
        
        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(180deg, #1a73e8 0%, #1557b0 100%);
            color: white;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        button:hover {
            box-shadow: 0 2px 8px rgba(26, 115, 232, 0.4);
        }
        
        .footer {
            text-align: center;
            padding-top: 5px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* Mensagem de sucesso - usada na página de login após cadastro bem-sucedido */
        .success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Melhorias de responsividade */
        @media (max-width: 480px) {
            .container {
                width: 100%;
            }
            
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cadastro</h1>
            <p>Crie sua conta para descobrir destinos turísticos em Angola</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($erro)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="cadastro.php">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nome" placeholder="Nome Completo" value="<?= htmlspecialchars($nome ?? '') ?>" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="senha" placeholder="Senha (mínimo 8 caracteres)" required>
                </div>
                
                <button type="submit" name="cadastrar_usuario">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </button>
            </form>
            
            <div class="footer">
                Já tem uma conta? <a href="login.php">Faça login</a>
            </div>
        </div>
    </div>
    
    <!-- Adicionado: JavaScript para validação do formulário do lado do cliente -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function(event) {
            let valid = true;
            const nome = form.querySelector('input[name="nome"]');
            const email = form.querySelector('input[name="email"]');
            const senha = form.querySelector('input[name="senha"]');
            
            // Validação básica dos campos
            if (nome.value.trim() === '') {
                showError(nome, 'Nome é obrigatório');
                valid = false;
            }
            
            if (email.value.trim() === '') {
                showError(email, 'Email é obrigatório');
                valid = false;
            } else if (!isValidEmail(email.value)) {
                showError(email, 'Email inválido');
                valid = false;
            }
            
            if (senha.value.trim() === '') {
                showError(senha, 'Senha é obrigatória');
                valid = false;
            } else if (senha.value.length < 8) {
                showError(senha, 'Senha deve ter pelo menos 8 caracteres');
                valid = false;
            }
            
            if (!valid) {
                event.preventDefault();
            }
        });
        
        // Limpar mensagens de erro ao digitar
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
                if (this.nextElementSibling && this.nextElementSibling.className === 'error-message') {
                    this.nextElementSibling.remove();
                }
            });
        });
        
        // Funções auxiliares
        function showError(input, message) {
            input.style.borderColor = 'var(--error)';
            
            // Verificar se já existe uma mensagem de erro
            if (input.nextElementSibling && input.nextElementSibling.className === 'error-message') {
                input.nextElementSibling.textContent = message;
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.color = 'var(--error)';
                errorDiv.style.fontSize = '12px';
                errorDiv.style.marginTop = '5px';
                errorDiv.style.marginBottom = '5px';
                errorDiv.textContent = message;
                
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        }
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
    </script>
</body>
</html>