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
    <title>Cadastro - Gestão de Destinos Turísticos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #004d40;
            --secondary-color: #ff9800;
            --accent-color: #f44336;
            --light-bg: #f5f5f5;
            --dark-bg: #212121;
            --text-dark: #333333;
            --text-light: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
            --border-radius: 8px;
            --success: #00c853;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            padding: 20px;
        }
        
        .container {
            width: 440px;
            max-width: 100%;
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .container:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #00695c 100%);
            color: var(--text-light);
            padding: 30px 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
        }
        
        .header h1 {
            font-size: 26px;
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .header h1:before {
            content: '\f234';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: var(--secondary-color);
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--accent-color);
            padding: 14px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid var(--accent-color);
        }
        
        .success {
            background-color: rgba(0, 200, 83, 0.1);
            color: var(--success);
            padding: 14px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid var(--success);
        }
        
        .input-group {
            margin-bottom: 22px;
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #757575;
            transition: var(--transition);
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: #fafafa;
        }
        
        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 77, 64, 0.2);
            outline: none;
            background-color: #ffffff;
        }
        
        .input-group input:focus + i {
            color: var(--primary-color);
        }
        
        .error-message {
            color: var(--accent-color);
            font-size: 13px;
            margin-top: 6px;
            margin-left: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .error-message::before {
            content: '\f071';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 11px;
        }
        
        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: var(--border-radius);
            background: linear-gradient(to right, var(--primary-color), #00796b);
            color: var(--text-light);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        button:hover::before {
            left: 100%;
        }
        
        button:hover {
            background: linear-gradient(to right, #00695c, var(--primary-color));
            box-shadow: 0 4px 8px rgba(0, 77, 64, 0.3);
            transform: translateY(-2px);
        }
        
        .footer {
            text-align: center;
            padding-top: 10px;
            color: #757575;
            font-size: 15px;
        }
        
        .footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
        }
        
        .footer a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--secondary-color);
            transition: var(--transition);
        }
        
        .footer a:hover {
            color: var(--secondary-color);
        }
        
        .footer a:hover::after {
            width: 100%;
        }
        
        @media (max-width: 480px) {
            .container {
                width: 100%;
            }
            
            body {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
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
            <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <form method="POST" action="cadastro.php">
                <div class="input-group">
                    <input type="text" name="nome" placeholder="Nome Completo" value="<?= htmlspecialchars($nome ?? '') ?>" required>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <i class="fas fa-envelope"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" name="senha" placeholder="Senha (mínimo 4 caracteres)" required>
                    <i class="fas fa-lock"></i>
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
    
    <!-- Script para validação do formulário do lado do cliente -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function(event) {
            let valid = true;
            const nome = form.querySelector('input[name="nome"]');
            const email = form.querySelector('input[name="email"]');
            const senha = form.querySelector('input[name="senha"]');
            
            // Limpar mensagens de erro anteriores
            clearErrors();
            
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
            } else if (senha.value.length < 4) {
                showError(senha, 'Senha deve ter pelo menos 4 caracteres');
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
                const errorElement = this.parentNode.querySelector('.error-message');
                if (errorElement) {
                    errorElement.remove();
                }
            });
        });
        
        // Funções auxiliares
        function showError(input, message) {
            input.style.borderColor = 'var(--accent-color)';
            
            // Verificar se já existe uma mensagem de erro
            const existingError = input.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.textContent = message;
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                
                input.parentNode.appendChild(errorDiv);
            }
        }
        
        function clearErrors() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(error => error.remove());
            
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.style.borderColor = '';
            });
        }
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
    </script>
</body>
</html>