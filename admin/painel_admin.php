<?php
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/controllers/UsuarioController.php';
require_once __DIR__ . '/../app/models/SolicitacaoAcesso.php';

use App\Controllers\UsuarioController;

session_start();

// Verificação de autenticação e permissão
if (!isset($_SESSION['usuario']) || 
    !isset($_SESSION['usuario']['tipo_usuario']) || 
    !in_array($_SESSION['usuario']['tipo_usuario'], ['admin', 'super_admin'])) {
    header('Location: index.php');
    exit;
}

// Definir o usuário logado para exibição correta no painel
$usuarioLogado = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Angola - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #004d40;
            --primary-light: #006355;
            --primary-dark: #003b32;
            --secondary: #ff9800;
            --secondary-light: #ffb74d;
            --text-dark: #333333;
            --text-light: #6c757d;
            --background: #f5f5f5;
            --white: #ffffff;
            --border: #e0e0e0;
            --sidebar-width: 250px;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --angola-red: #ce1126;
            --angola-yellow: #f7d618;
            --angola-black: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            padding: 1.5rem 1rem;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 0.5rem 0 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .sidebar-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, var(--angola-red), var(--angola-yellow), var(--angola-black));
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
            position: relative;
            display: inline-block;
        }

        .sidebar-header h2::after {
            content: "🇦🇴";
            position: absolute;
            top: -10px;
            right: -20px;
            font-size: 0.9rem;
        }

        .sidebar-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
        }

        .angola-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-dark);
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--angola-yellow);
        }

        .angola-logo::before {
            content: "🇦🇴";
            font-size: 1.8rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            color: white;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, rgba(0, 77, 64, 0.7), rgba(0, 99, 85, 0.7));
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .user-info::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 70%, var(--angola-yellow) 150%);
            opacity: 0.1;
        }

        .user-info i {
            font-size: 2rem;
            margin-bottom: 0.8rem;
            color: var(--angola-yellow);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .user-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .user-email {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .user-type {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background: var(--angola-yellow);
            color: var(--primary-dark);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.5rem;
            text-transform: uppercase;
        }

        .menu-section {
            margin-bottom: 1rem;
        }

        .menu-section-title {
            color: var(--angola-yellow);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            padding: 0.5rem 1rem;
            letter-spacing: 0.5px;
            position: relative;
        }

        .menu-section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 1rem;
            width: 20px;
            height: 2px;
            background: var(--angola-red);
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-item i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .menu-item::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--angola-yellow);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .menu-item:hover, .menu-item.active {
            background: var(--primary-light);
        }

        .menu-item:hover::before, .menu-item.active::before {
            opacity: 1;
        }

        .menu-item.active {
            background: linear-gradient(to right, var(--primary-light), var(--primary));
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Animação dos ícones no hover */
        .menu-item:hover i {
            transform: translateX(3px);
            color: var(--angola-yellow);
        }

        /* Main Content Styles */
        .main-container {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .header {
            background: var(--white);
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--angola-red), var(--angola-yellow), var(--angola-black));
        }

        .welcome-user {
            font-size: 1.1rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .welcome-user i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .welcome-message {
            display: flex;
            flex-direction: column;
        }

        .welcome-message span:first-child {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .welcome-message span:last-child {
            font-weight: 600;
            color: var(--primary);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: var(--primary);
        }

        .card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .angola-feature {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .feature-image {
            width: 35%;
            background-image: url('assets/images/angola-landscape.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .feature-image::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(0,0,0,0.2), transparent);
        }

        .feature-content {
            flex: 1;
            padding: 2rem;
            position: relative;
        }

        .feature-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .feature-title::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--angola-red);
        }

        .angola-flag-colors {
            display: flex;
            margin-top: 1.5rem;
        }

        .flag-stripe {
            height: 5px;
            flex: 1;
        }

        .flag-red {
            background-color: var(--angola-red);
        }

        .flag-yellow {
            background-color: var(--angola-yellow);
        }

        .flag-black {
            background-color: var(--angola-black);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            transform: scaleX(0);
            transition: transform 0.3s ease;
            transform-origin: left;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .action-card:hover::before {
            transform: scaleX(1);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(0, 77, 64, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .action-card:hover .action-icon {
            background: var(--primary);
            color: white;
            transform: rotateY(180deg);
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .action-desc {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .recent-activity {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--angola-yellow);
        }

        .view-all {
            color: var(--primary);
            font-size: 0.8rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            color: var(--primary-dark);
            transform: translateX(3px);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: rgba(0,0,0,0.01);
            transform: translateX(5px);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0, 77, 64, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 0.2rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .activity-action {
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .activity-action:hover {
            color: var(--primary);
            transform: scale(1.1);
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-top: 1px solid var(--border);
            margin-top: 2rem;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .copyright {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .copyright img {
            height: 20px;
        }

        .footer-links {
            display: flex;
            gap: 1rem;
        }

        .footer-link {
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: var(--primary);
        }

        .collapse-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            cursor: pointer;
            position: absolute;
            top: 1rem;
            right: -14px;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .collapse-btn:hover {
            background: var(--angola-yellow);
            color: var(--primary-dark);
            transform: rotate(180deg);
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 77, 64, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 77, 64, 0.3);
        }

        .btn-secondary {
            background: var(--angola-yellow);
            color: var(--text-dark);
            box-shadow: 0 2px 8px rgba(247, 214, 24, 0.2);
        }

        .btn-secondary:hover {
            background: #e6c518;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 214, 24, 0.3);
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .angola-feature {
                flex-direction: column;
            }

            .feature-image {
                width: 100%;
                height: 150px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1010;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-container {
                margin-left: 0;
                padding: 1rem;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }

            .mobile-menu-toggle {
                display: block;
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--primary);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
                z-index: 1005;
                cursor: pointer;
                border: none;
            }
        }

        @media (max-width: 480px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="angola-logo"></div>
            </div>
            <h2>Destinos Angola</h2>
          
        </div>
        
        
        
        
        <div class="menu-section">
            <div class="menu-section-title">Gestão de Conteúdo</div>
            <a href="painel_destinos.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                Destinos
            </a>
            <a href="cadastrar_destino.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
             Novo Destino
            </a>
            
        </div>
        
        <div class="menu-section">
            <div class="menu-section-title">Administração</div>
            <a href="gerenciar_usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                Usuários
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                Sair
            </a>
        </div>
        
        <button class="collapse-btn" id="collapseBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="main-container">
        <div class="header">
            <div class="welcome-user">
                <i class="fas fa-user-circle"></i>
                <div class="welcome-message">
                    <span>Bem-vindo,</span>
                    <span><?= htmlspecialchars($usuarioLogado['nome'] ?? 'Administrador') ?></span>
                </div>
            </div>
            <button class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Atualizar Painel
            </button>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Destinos Cadastrados
                </div>
                <div class="card-content">
                    <h2>37</h2>
                    <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="card-title">
                    <i class="fas fa-users"></i>
                    Usuários Ativos
                </div>
                <div class="card-content">
                    <h2>32</h2>
                    <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                </div>
            </div>
           
        </div>

        <div class="angola-feature">
            <div class="feature-image"></div>
            <div class="feature-content">
                <h2 class="feature-title">Descubra Angola</h2>
                <p>Bem-vindo ao sistema administrativo do portal Destinos Angola. Aqui você pode gerenciar todos os conteúdos relacionados ao turismo e destinos de Angola.</p>
                
                <div class="angola-flag-colors">
                    <div class="flag-stripe flag-red"></div>
                    <div class="flag-stripe flag-yellow"></div>
                    <div class="flag-stripe flag-black"></div>
                </div>
            </div>
        </div>

        
        
        
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <h3 class="action-title">Novo Destino</h3>
                <p class="action-desc">Adicionar um novo destino turístico</p>
            </div>
           
        <div class="footer">
            <div class="copyright">
                <span>© 2025 Destinos Angola</span>
                <span>|</span> 
                <span>🇦🇴 Promovendo o turismo angolano</span>
            </div>
            <div class="footer-links">
                <a href="#" class="footer-link">Suporte</a>
                <a href="#" class="footer-link">Política de Privacidade</a>
                <a href="#" class="footer-link">Termos de Uso</a>
            </div>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

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

        // Toggle do menu mobile
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('active');
                this.querySelector('i').classList.toggle('fa-bars');
                this.querySelector('i').classList.toggle('fa-times');
            });
        }
        
        // Colapsar/expandir menu lateral
        const collapseBtn = document.getElementById('collapseBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContainer = document.querySelector('.main-container');
        
        collapseBtn.addEventListener('click', function() {
            const sidebarCollapsed = sidebar.classList.toggle('collapsed');
            
            if (sidebarCollapsed) {
                sidebar.style.width = '60px';
                mainContainer.style.marginLeft = '60px';
                this.innerHTML = '<i class="fas fa-chevron-right"></i>';
                
                // Ocultar textos
                document.querySelectorAll('.menu-item span, .menu-section-title, .sidebar-header p, .user-info div, .user-info span').forEach(el => {
                    el.style.display = 'none';
                });
                
                // Centralizar ícones
                document.querySelectorAll('.menu-item i').forEach(icon => {
                    icon.style.margin = '0 auto';
                });
                
                // Ajustar logo
                document.querySelector('.sidebar-header h2').style.display = 'none';
            } else {
                sidebar.style.width = 'var(--sidebar-width)';
                mainContainer.style.marginLeft = 'var(--sidebar-width)';
                this.innerHTML = '<i class="fas fa-chevron-left"></i>';
                
                // Mostrar textos
                setTimeout(() => {
                    document.querySelectorAll('.menu-item span, .menu-section-title, .sidebar-header p, .user-info div, .user-info span').forEach(el => {
                        el.style.display = 'block';
                    });
                    
                    // Restaurar margens dos ícones
                    document.querySelectorAll('.menu-item i').forEach(icon => {
                        icon.style.margin = '0 0.8rem 0 0';
                    });
                    
                    // Restaurar logo
                    document.querySelector('.sidebar-header h2').style.display = 'block';
                }, 300);
            }
        });

        // Adicionar funcionalidade ao botão de atualizar
        document.querySelector('.btn-primary').addEventListener('click', function() {
            // Simular atualização
            const btn = this;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
            btn.disabled = true;
            
            setTimeout(function() {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                
                // Mostrar mensagem de sucesso
                const message = document.createElement('div');
                message.innerHTML = '<i class="fas fa-check-circle"></i> Painel atualizado com sucesso!';
                message.style.position = 'fixed';
                message.style.top = '20px';
                message.style.right = '20px';
                message.style.background = '#4caf50';
                message.style.color = 'white';
                message.style.padding = '12px 20px';
                message.style.borderRadius = '8px';
                message.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
                message.style.zIndex = '9999';
                message.style.display = 'flex';
                message.style.alignItems = 'center';
                message.style.gap = '8px';
                message.style.fontSize = '14px';
                
                document.body.appendChild(message);
                
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => message.remove(), 500);
                }, 3000);
            }, 1500);
        });

        // Animação nas action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
                
                // Simulando redirecionamento
                const actionTitle = this.querySelector('.action-title').textContent;
                let redirectUrl = '';
                
                switch(actionTitle) {
                    case 'Novo Destino':
                        redirectUrl = 'cadastrar_destino.php';
                        break;
                    case 'Novo Usuário':
                        redirectUrl = 'cadastrar_usuario.php';
                        break;
                    case 'Relatórios':
                        redirectUrl = 'relatorios.php';
                        break;
                    case 'Configurações':
                        redirectUrl = 'configuracoes.php';
                        break;
                }
                
                if (redirectUrl) {
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 300);
                }
            });
        });

        // Efeito de hover nos itens de atividade
        document.querySelectorAll('.activity-item').forEach(item => {
            item.addEventListener('click', function() {
                // Aqui pode adicionar ação ao clicar em uma atividade
                console.log('Atividade clicada:', this.querySelector('.activity-title').textContent);
            });
        });
    </script>
</body>
</html>