<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <title>Login - Gestão de Destinos Turísticos</title>
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
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
        }
        
        .container {
            width: 400px;
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
            padding: 35px 25px;
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
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .header .logo {
            font-size: 42px;
            margin-bottom: 15px;
            color: var(--secondary-color);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .form-container {
            padding: 35px 30px;
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
        
        .input-group {
            margin-bottom: 25px;
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
                width: 90%;
            }
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
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required autofocus>
                    <i class="fas fa-envelope"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" name="senha" placeholder="Senha" required>
                    <i class="fas fa-lock"></i>
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