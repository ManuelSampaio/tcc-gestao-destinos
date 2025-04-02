<?php
session_start();

// Se estiver logado como admin ou super_admin, redireciona para o painel
if (isset($_SESSION['usuario']) && 
    isset($_SESSION['usuario']['tipo_usuario']) && 
    in_array($_SESSION['usuario']['tipo_usuario'], ['admin', 'super_admin'])) {
    header('Location: painel_admin.php');
    exit;
}

// Verifica se o usuário está logado (para exibir informações adequadas no menu)
$usuarioLogado = isset($_SESSION['usuario']) && isset($_SESSION['usuario']['nome']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Turísticos em Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!--<link rel="stylesheet" href="../assets/css/styles.css"> -- Ajuste para o CSS -->
    <style>
        /* Estilos modernos para melhorar a interface */
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --accent: #ff6b6b;
            --text-color: #333;
            --text-light: #6b7280;
            --background: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        /* Header e navegação modernos */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            margin-right: 15px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        nav {
            display: flex;
            align-items: center;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 0;
        }
        
        nav ul li a:hover {
            color: var(--primary);
        }
        
        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        nav ul li a:hover::after {
            width: 100%;
        }
        
        .search-box {
            position: relative;
            margin-left: 20px;
        }
        
        .search-box input {
            padding: 10px 15px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 50px;
            width: 200px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            width: 250px;
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
        }
        
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        
        .search-box button {
            display: none; /* Ocultamos o botão e usamos o ícone */
        }
        
        /* Botões de Autenticação */
        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 20px;
        }
        
        .btn-login, .btn-register, .btn-logout, .btn-profile {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-login {
            background-color: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        
        .btn-login:hover {
            background-color: rgba(26, 115, 232, 0.1);
        }
        
        .btn-register, .btn-profile {
            background: linear-gradient(180deg, #1a73e8 0%, #1557b0 100%);
            color: white;
            border: none;
        }
        
        .btn-register:hover, .btn-profile:hover {
            box-shadow: 0 2px 8px rgba(26, 115, 232, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background-color: transparent;
            color: var(--text-light);
            border: 1.5px solid #ddd;
        }
        
        .btn-logout:hover {
            background-color: #f0f0f0;
            color: var(--text-color);
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 45px;
            min-width: 200px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 8px;
            padding: 12px 0;
            z-index: 1100;
        }
        
        .dropdown-content a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s ease;
        }
        
        .dropdown-content a:hover {
            background-color: #f0f0f0;
            color: var(--primary);
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 8px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .user-role {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 2px;
        }
        
        /* Banner moderno e envolvente */
        .banner {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../assets/images/angola-banner.jpg') no-repeat center center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
            margin-top: 80px;
        }
        
        .banner h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease;
        }
        
        .banner p {
            font-size: 1.2rem;
            max-width: 800px;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.3s both;
        }
        
        .banner .btn {
            background: var(--primary);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease 0.6s both;
            border: none;
            display: inline-block;
        }
        
        .banner .btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }
        
        /* Melhorias no carrossel */
        .carousel {
            padding: 60px 0;
            background-color: white;
            overflow: hidden;
        }
        
        .carousel h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.2rem;
            color: var(--text-color);
        }
        
        .carousel-inner {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Para Firefox */
            gap: 25px;
            padding: 20px 5%;
        }
        
        .carousel-inner::-webkit-scrollbar {
            display: none; /* Para Chrome e Safari */
        }
        
        .carousel-item {
            min-width: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .carousel-item:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }
        
        .carousel-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .carousel-item h2 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 20px;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: left;
        }
        
        .carousel-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .carousel-controls button {
            background-color: var(--primary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .carousel-controls button:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--hover-shadow);
        }
        
        /* Cards de destinos modernos */
        .destinos {
            padding: 80px 5%;
            background-color: var(--background);
        }
        
        .destinos h2 {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            color: var(--text-color);
            position: relative;
            padding-bottom: 15px;
        }
        
        .destinos h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .destinos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .destino-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .destino-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }
        
        .destino-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .destino-content {
            padding: 20px;
        }
        
        .destino-card h3 {
            margin: 10px 0;
            font-size: 1.3rem;
            color: var(--text-color);
        }
        
        .destino-card p {
            color: var(--text-light);
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .destino-card .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--accent);
            color: white;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .destino-card .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .destino-card .btn:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 3px 8px rgba(26, 115, 232, 0.3);
        }
        
        /* Seção de fatos interessantes */
        .fatos {
            padding: 80px 5%;
            background-color: white;
        }
        
        .fatos h2 {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            color: var(--text-color);
            position: relative;
            padding-bottom: 15px;
        }
        
        .fatos h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .fatos-carousel {
            display: flex;
            overflow-x: hidden;
            position: relative;
            scroll-behavior: smooth;
        }
        
        .fato-item {
            min-width: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .fato-item img {
            width: 100%;
            max-width: 500px;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        
        .fato-item h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .fato-item p {
            max-width: 600px;
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .fatos-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .fatos-buttons .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: var(--shadow);
        }
        
        .fatos-buttons .btn:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--hover-shadow);
        }
        
        /* Seção Sobre */
        #sobre {
            padding: 80px 5%;
            background-color: var(--background);
            text-align: center;
        }
        
        #sobre h2 {
            margin-bottom: 30px;
            font-size: 2.5rem;
            color: var(--text-color);
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }
        
        #sobre h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary);
        }
        
        #sobre p {
            max-width: 800px;
            margin: 0 auto;
            color: var(--text-color);
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        /* Footer moderno */
        footer {
            background-color: #2c3e50;
            color: rgba(255, 255, 255, 0.8);
            padding: 60px 5% 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary);
        }
        
        .footer-column p {
            margin-bottom: 15px;
            line-height: 1.7;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-column ul li a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Animações e responsividade */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Media queries para responsividade */
        @media (max-width: 991px) {
            header {
                padding: 15px 3%;
            }
            
            .logo-text {
                display: none;
            }
            
            nav ul {
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .banner h1 {
                font-size: 2.5rem;
            }
            
            nav {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 80%;
                height: calc(100vh - 80px);
                background-color: white;
                flex-direction: column;
                align-items: flex-start;
                padding: 40px 20px;
                transition: all 0.4s ease;
                box-shadow: var(--shadow);
            }
            
            nav.active {
                left: 0;
            }
            
            nav ul {
                flex-direction: column;
                width: 100%;
            }
            
            nav ul li {
                margin-bottom: 20px;
            }
            
            .hamburger {
                display: block;
                cursor: pointer;
                z-index: 1001;
            }
            
            .search-box {
                width: 100%;
                margin: 20px 0;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .auth-buttons {
                margin-left: 0;
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-login, .btn-register {
                width: 100%;
                justify-content: center;
            }
            
            .destinos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .banner h1 {
                font-size: 2rem;
            }
            
            .banner p {
                font-size: 1rem;
            }
            
            .banner .btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo da Aplicação" />
            <div class="logo-text">Destinos Angola</div>
        </div>
        
        <nav>
            <ul>
                <li><a href="#destinos"><i class="fas fa-map-marked-alt"></i> Destinos</a></li>
                <li><a href="#fatos"><i class="fas fa-lightbulb"></i> Fatos Interessantes</a></li>
                <li><a href="#sobre"><i class="fas fa-info-circle"></i> Sobre Nós</a></li>
            </ul>
            
            <div class="search-box">
                <form action="listar_destino.php" method="GET">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Pesquisar destinos...">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            
            <!-- Botões de autenticação -->
            <div class="auth-buttons">
                <?php if ($usuarioLogado): ?>
                    <!-- Usuário logado - Mostra dropdown com opções -->
                    <div class="dropdown">
                        <a href="#" class="btn-profile">
                            <i class="fas fa-user-circle"></i> 
                            <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>
                        </a>
                        <div class="dropdown-content">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($_SESSION['usuario']['nome'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <div class="user-name"><?= htmlspecialchars($_SESSION['usuario']['nome']) ?></div>
                                    <div class="user-role">
                                        <?php 
                                        $tipoUsuario = $_SESSION['usuario']['tipo_usuario'];
                                        echo $tipoUsuario === 'super_admin' ? 'Super Administrador' : 
                                             ($tipoUsuario === 'admin' ? 'Administrador' : 'Usuário');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a>
                            <a href="favoritos.php"><i class="fas fa-heart"></i> Destinos Favoritos</a>
                            <a href="avaliacoes.php"><i class="fas fa-star"></i> Minhas Avaliações</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado - Mostra botões de login e cadastro -->
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                    <a href="cadastro.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Cadastrar
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <!-- Hambúrguer para menu mobile (será controlado via JavaScript) -->
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </header>

    <section class="banner">
        <h1>Descubra a Magia de Angola</h1>
        <p>Venha conhecer as paisagens deslumbrantes, a cultura vibrante e a história fascinante de um dos mais belos países da África.</p>
        <a href="#destinos" class="btn"><i class="fas fa-compass"></i> Explore Nossos Destinos</a>
    </section>

    <section class="carousel">
        <h2>Destinos Populares</h2>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../assets/images/tundavala.jpg" alt="Fenda da Tundavala" />
                <h2>Fenda da Tundavala</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/maiombe.jpg" alt="Floresta do Mayombe" />
                <h2>Floresta do Mayombe</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/moco.jpg" alt="Morro do Môco" />
                <h2>Morro do Môco</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/nzenzo.jpg" alt="Grutas do Nzenzo" />
                <h2>Grutas do Nzenzo</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/carumbo.jpg" alt="Lagoa Carumbo" />
                <h2>Lagoa Carumbo</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/calandula.jpg" alt="Quedas de Kalandula" />
                <h2>Quedas de Kalandula</h2>
            </div>
            <div class="carousel-item">
                <img src="../assets/images/chiumbe.jpg" alt="Quedas do Rio Chiumbe" />
                <h2>Quedas do Rio Chiumbe</h2>
            </div>
        </div>
        <div class="carousel-controls">
            <button onclick="scrollCarousel(-1)"><i class="fas fa-chevron-left"></i></button>
            <button onclick="scrollCarousel(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>

    <section id="destinos" class="destinos">
        <h2>Destinos em Destaque</h2>
        <div class="destinos-grid">
        <div class="destino-card">
                <span class="badge">Mais Visitado</span>
                <img src="../assets/images/tundavala.jpg" alt="Fenda da Tundavala">
                <div class="destino-content">
                    <h3>Fenda da Tundavala</h3>
                    <p>Um impressionante precipício natural localizado na província de Huíla. Com mais de 1000 metros de altura, oferece uma vista deslumbrante da planície abaixo.</p>
                    <a href="listar_destino.php?id=1" class="btn">Saiba Mais</a>
                </div>
            </div>
            
            <div class="destino-card">
                <img src="../assets/images/calandula.jpg" alt="Quedas de Kalandula">
                <div class="destino-content">
                    <h3>Quedas de Kalandula</h3>
                    <p>Uma das maiores quedas d'água da África, com 105 metros de altura e 400 metros de largura. Um espetáculo natural localizado na província de Malanje.</p>
                    <a href="listar_destino.php?id=2" class="btn">Saiba Mais</a>
                </div>
            </div>
            
            <div class="destino-card">
                <span class="badge">Popular</span>
                <img src="../assets/images/carumbo.jpg" alt="Lagoa Carumbo">
                <div class="destino-content">
                    <h3>Lagoa Carumbo</h3>
                    <p>Localizada na província da Lunda Norte, é um magnífico espelho d'água rodeado por uma rica diversidade de fauna e flora.</p>
                    <a href="listar_destino.php?id=3" class="btn">Saiba Mais</a>
                </div>
            </div>
            
            <div class="destino-card">
                <img src="../assets/images/maiombe.jpg" alt="Floresta do Mayombe">
                <div class="destino-content">
                    <h3>Floresta do Mayombe</h3>
                    <p>Uma floresta tropical densa localizada em Cabinda. Lar de uma impressionante biodiversidade, incluindo espécies raras de plantas e animais.</p>
                    <a href="listar_destino.php?id=4" class="btn">Saiba Mais</a>
                </div>
            </div>
            
            <div class="destino-card">
                <img src="../assets/images/moco.jpg" alt="Morro do Môco">
                <div class="destino-content">
                    <h3>Morro do Môco</h3>
                    <p>O ponto mais alto de Angola, com 2.620 metros de altitude. Localizado na província do Huambo, oferece trilhas desafiadoras e vistas panorâmicas.</p>
                    <a href="listar_destino.php?id=5" class="btn">Saiba Mais</a>
                </div>
            </div>
            
            <div class="destino-card">
                <span class="badge">Recomendado</span>
                <img src="../assets/images/nzenzo.jpg" alt="Grutas do Nzenzo">
                <div class="destino-content">
                    <h3>Grutas do Nzenzo</h3>
                    <p>Um complexo de cavernas subterrâneas na província do Uíge. Formações rochosas impressionantes e um rio subterrâneo fazem deste lugar uma experiência única.</p>
                    <a href="listar_destino.php?id=6" class="btn">Saiba Mais</a>
                </div>
            </div>
        </div>
    </section>

    <section id="fatos" class="fatos">
        <h2>Fatos Interessantes sobre Angola</h2>
        <div class="fatos-carousel">
            <div class="fato-item active">
                <img src="../assets/images/cultura-angola.jpg" alt="Cultura Angolana">
                <h3>Rica Herança Cultural</h3>
                <p>Angola é lar de mais de 90 grupos étnicos diferentes, cada um com suas próprias tradições, línguas e expressões culturais. A dança e a música, especialmente o Semba (precursor da Samba brasileira), são parte integrante da identidade cultural angolana.</p>
            </div>
            <div class="fato-item">
                <img src="../assets/images/diamantes-angola.jpg" alt="Diamantes de Angola">
                <h3>Riquezas Naturais</h3>
                <p>Angola é um dos maiores produtores de diamantes do mundo e possui vastas reservas de petróleo. O país também é rico em outros recursos naturais como ouro, ferro, cobre e muito mais.</p>
            </div>
            <div class="fato-item">
                <img src="../assets/images/biodiversidade-angola.jpg" alt="Biodiversidade de Angola">
                <h3>Incrível Biodiversidade</h3>
                <p>O país abriga seis principais biomas e é considerado um dos hotspots de biodiversidade da África. A Palanca Negra Gigante, símbolo nacional, é uma espécie de antílope encontrada apenas em Angola.</p>
            </div>
        </div>
        <div class="fatos-buttons">
            <button class="btn" onclick="navegarFatos(-1)"><i class="fas fa-arrow-left"></i> Anterior</button>
            <button class="btn" onclick="navegarFatos(1)">Próximo <i class="fas fa-arrow-right"></i></button>
        </div>
    </section>

    <section id="sobre" class="sobre">
        <h2>Sobre Nós</h2>
        <p>Somos uma plataforma dedicada a promover o turismo em Angola, destacando os locais mais bonitos e interessantes deste país incrível. Nossa missão é conectar viajantes a experiências autênticas, contribuindo para o desenvolvimento sustentável do turismo angolano e ajudando a preservar o patrimônio natural e cultural do país.</p>
        <p>Todas as informações que compartilhamos são cuidadosamente pesquisadas e atualizadas regularmente. Acreditamos no potencial turístico de Angola e estamos comprometidos em apresentar o melhor que este país tem a oferecer para o mundo.</p>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Destinos Angola</h3>
                <p>Descubra as maravilhas naturais, a rica cultura e a história fascinante de Angola com a nossa plataforma dedicada ao turismo responsável e sustentável.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Links Rápidos</h3>
                <ul>
                    <li><a href="#destinos">Destinos</a></li>
                    <li><a href="#fatos">Fatos Interessantes</a></li>
                    <li><a href="#sobre">Sobre Nós</a></li>
                    <li><a href="contato.php">Contato</a></li>
                    <li><a href="termos.php">Termos de Uso</a></li>
                    <li><a href="privacidade.php">Política de Privacidade</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contate-nos</h3>
                <p><i class="fas fa-map-marker-alt"></i> Luanda, Angola</p>
                <p><i class="fas fa-phone"></i> +244 923 456 789</p>
                <p><i class="fas fa-envelope"></i> info@destinosangola.co.ao</p>
            </div>
            
            <div class="footer-column">
                <h3>Newsletter</h3>
                <p>Inscreva-se para receber as últimas novidades sobre destinos turísticos em Angola.</p>
                <form action="newsletter.php" method="POST" class="newsletter-form">
                    <input type="email" name="email" placeholder="Seu e-mail" required>
                    <button type="submit" class="btn">Inscrever-se</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Destinos Angola. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Funcionalidade para o carrossel de imagens
        function scrollCarousel(direction) {
            const carousel = document.querySelector('.carousel-inner');
            const itemWidth = document.querySelector('.carousel-item').offsetWidth + 25; // item width + gap
            carousel.scrollBy({ left: itemWidth * direction, behavior: 'smooth' });
        }
        
        // Funcionalidade para a navegação de fatos interessantes
        let currentFactIndex = 0;
        const facts = document.querySelectorAll('.fato-item');
        
        function showFact(index) {
            facts.forEach(fact => fact.classList.remove('active'));
            
            currentFactIndex = (index + facts.length) % facts.length;
            facts[currentFactIndex].classList.add('active');
        }
        
        function navegarFatos(direction) {
            showFact(currentFactIndex + direction);
        }
        
        // Menu hambúrguer para mobile
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const nav = document.querySelector('nav');
            
            hamburger.addEventListener('click', function() {
                nav.classList.toggle('active');
                
                // Alterna o ícone do hambúrguer
                const icon = hamburger.querySelector('i');
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Adiciona estilos de hover para destinos
            const destinoCards = document.querySelectorAll('.destino-card');
            destinoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                    this.style.boxShadow = 'var(--hover-shadow)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--shadow)';
                });
            });
        });
    </script>
</body>
</html>