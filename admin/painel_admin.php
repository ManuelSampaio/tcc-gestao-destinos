<?php
session_start();

// Verificação de autenticação e permissão
if (!isset($_SESSION['usuario']) || 
    !isset($_SESSION['usuario']['tipo_usuario']) || 
    !in_array($_SESSION['usuario']['tipo_usuario'], ['admin', 'super_admin'])) {
    header('Location: index.php');
    exit;
}

$tipoUsuario = $_SESSION['usuario']['tipo_usuario'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angola Tours - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --secondary: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --success: #28a745;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a73e8 0%, #1557b0 100%);
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1rem 0 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .menu-item i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        /* Main Content Styles */
        .main-container {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .welcome-text h1 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: var(--text-light);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: var(--text-light);
        }

        .actions-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .btn-action i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }

        .btn-action:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-container {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        .search-bar {
            background: white;
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            display: flex;
            align-items: center;
            max-width: 300px;
            margin-left: auto;
        }

        .search-bar input {
            border: none;
            outline: none;
            padding: 0.5rem;
            width: 100%;
            font-size: 0.9rem;
        }

        .search-bar i {
            color: var(--text-light);
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Angola Tours</h2>
            <p>Painel Administrativo</p>
        </div>
        
        <a href="cadastrar_destino.php" class="menu-item">
            <i class="fas fa-plus-circle"></i>
            Cadastrar Destino
        </a>
        <a href="painel_destinos.php" class="menu-item">
            <i class="fas fa-map-marked-alt"></i>
            Painel de Destinos
        </a>
        <a href="gerenciar_usuarios.php" class="menu-item">
            <i class="fas fa-users"></i>
            Gerenciar Usuários
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            Sair
        </a>
    </div>

    <div class="main-container">
        <div class="header">
            <div class="welcome-text">
                <h1>Bem-vindo ao Painel Administrativo</h1>
                <p>Gerencie os destinos turísticos de Angola</p>
            </div>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Pesquisar...">
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Destinos Ativos</h3>
                <p>Gerencie seus destinos</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3>Atividade</h3>
                <p>Monitore o sistema</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell"></i>
                <h3>Notificações</h3>
                <p>Atualizações do sistema</p>
            </div>
        </div>

        <div class="actions-container">
            <a href="cadastrar_destino.php" class="btn-action">
                <i class="fas fa-plus"></i>
                Novo Destino
            </a>
        </div>
    </div>

    <script>
        // Ativar menu item atual
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            if (item.getAttribute('href') === currentPath) {
                item.classList.add('active');
            }
            
            item.addEventListener('click', function() {
                menuItems.forEach(link => link.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Responsividade do menu para mobile
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>