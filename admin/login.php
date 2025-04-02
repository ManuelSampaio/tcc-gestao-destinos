<?php
session_start();
require_once '../app/controllers/UsuarioController.php';
use App\Controllers\UsuarioController;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $usuarioController = new UsuarioController();
            $resultado = $usuarioController->autenticarUsuario($email, $senha);

            if ($resultado['success']) {
                $_SESSION['usuario'] = [
                    'id_usuario' => $resultado['usuario']['id_usuario'],
                    'email' => $resultado['usuario']['email'],
                    'nome' => $resultado['usuario']['nome'],
                    'tipo_usuario' => $resultado['usuario']['tipo_usuario']
                ];

                // Redirecionamento baseado no tipo de usuário
                if ($resultado['usuario']['tipo_usuario'] === 'comum') {
                    header('Location: index.php');
                } else {
                    header('Location: painel_admin.php');
                }
                exit;
            } else {
                $erro = 'Email ou senha inválidos.';
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $erro = 'Ocorreu um erro ao processar o login. Tente novamente mais tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
        }
        
        .container {
            width: 380px;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(180deg, #1a73e8 0%, #1557b0 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .header p {
            font-size: 15px;
            opacity: 0.9;
        }
        
        .header .logo {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .form-container {
            padding: 30px 25px;
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
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-compass"></i>
            </div>
            <h1>Gestão de Destinos Turísticos</h1>
            <p>Acesse sua conta para gerenciar destinos em Angola</p>
        </div>
        
        <div class="form-container">
            <!-- Exibe erro, caso haja -->
            <?php if (!empty($erro)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required autofocus>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="senha" placeholder="Senha" required>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <div class="footer">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>