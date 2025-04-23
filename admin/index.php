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

// Conexão com o banco de dados
$conexao = new mysqli("localhost", "root", "", "turismo_angola");

// Verificação de erros de conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Consulta para as 7 maravilhas de Angola
// Consulta para as 7 maravilhas de Angola
$sql_maravilhas = "SELECT d.id, d.nome_destino, d.descricao, d.imagem, l.nome_local as localizacao 
                  FROM destinos_turisticos d
                  LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao
                  WHERE d.is_maravilha = 1 
                  ORDER BY d.data_cadastro DESC 
                  LIMIT 7";
$resultado_maravilhas = $conexao->query($sql_maravilhas);

// Adicione esta linha para depuração
if (!$resultado_maravilhas) {
    error_log("Erro na consulta de maravilhas: " . $conexao->error);
}

// Consulta para todos os destinos
// Consulta para todos os destinos
$sql_destinos = "SELECT d.id, d.nome_destino, d.descricao, d.imagem, l.nome_local as localizacao 
                FROM destinos_turisticos d
                LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao
                ORDER BY d.nome_destino ASC 
                LIMIT 15";
$resultado_destinos = $conexao->query($sql_destinos);

// Adicione esta linha para depuração
if (!$resultado_destinos) {
    error_log("Erro na consulta de destinos: " . $conexao->error);
}
// Consulta para os destinos adicionados recentemente
// Consulta para os destinos adicionados recentemente
$sql_recentes = "SELECT d.id, d.nome_destino, d.descricao, d.imagem, d.data_cadastro, l.nome_local as localizacao 
                FROM destinos_turisticos d
                LEFT JOIN localizacoes l ON d.id_localizacao = l.id_localizacao
                ORDER BY d.data_cadastro DESC 
                LIMIT 6";
$resultado_recentes = $conexao->query($sql_recentes);

// Adicione esta linha para depuração
if (!$resultado_recentes) {
    error_log("Erro na consulta de recentes: " . $conexao->error);
}

// Função para encurtar texto
function encurtarTexto($texto, $limite = 150) {
    if (strlen($texto) <= $limite) return $texto;
    $texto = substr($texto, 0, $limite);
    return substr($texto, 0, strrpos($texto, ' ')) . '...';
}

// Função para formatar a data
function formatarData($data) {
    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <!-- link:favicon -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinos Turísticos em Angola</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styleij.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
    <style>
        /* Estilos adicionais para o redesign */
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
        /* Header Principal */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    background-color: var(--primary-color);
    color: var(--text-light);
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 100;
    transition: var(--transition);
}

/* Logo */
header .logo {
    display: flex;
    align-items: center;
    gap: 8px; /* Espaçamento reduzido para melhor proporção */
    margin-right: 20px; /* Adiciona espaço entre o logo e os itens de navegação */
}

header .logo img {
    height: 60px; /* Tamanho ligeiramente menor para melhor proporção */
    width: auto;
    border-radius: 50%; /* Torna o logo circular, se combinar com seu design */
    /* Se o fundo do seu logo não combinar, considere adicionar: */
    background-color: transparent; /* Ou uma cor que combine com seu tema */
}

header .logo-text {
    font-size: 1.5rem; /* Tamanho ligeiramente menor para melhor equilíbrio */
    font-weight: bold;
    color: white; /* Usando branco para melhor contraste com a barra de navegação verde escura */
    letter-spacing: 0.5px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3); /* Adiciona sombra sutil para melhor legibilidade */
}

/* Navegação */
header nav {
    display: flex;
    align-items: center;
    gap: 25px;
    flex: 1;
    justify-content: flex-end;
}

header nav ul {
    display: flex;
    list-style-type: none;
    gap: 20px;
    margin: 0;
    padding: 0;
}

header nav ul li a {
    color: var(--text-light);
    text-decoration: none;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: var(--border-radius);
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
}

header nav ul li a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

header nav ul li a i {
    font-size: 1rem;
}

/* Caixa de Pesquisa */
header .search-box {
    position: relative;
    flex: 0 1 300px;
    margin: 0 20px;
}

header .search-box form {
    display: flex;
    align-items: center;
}

header .search-box input {
    width: 100%;
    padding: 10px 15px 10px 35px;
    border: none;
    border-radius: 20px;
    background-color: rgba(255, 255, 255, 0.15);
    color: var(--text-light);
    transition: var(--transition);
}

header .search-box input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

header .search-box input:focus {
    outline: none;
    background-color: rgba(255, 255, 255, 0.25);
}

header .search-box i.fa-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
    pointer-events: none;
}

header .search-box button {
    background-color: var(--secondary-color);
    color: var(--text-dark);
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    margin-left: -40px;
    cursor: pointer;
    font-weight: bold;
    transition: var(--transition);
    z-index: 2;
}

header .search-box button:hover {
    background-color: #ffb74d;
    transform: translateY(-2px);
}

/* Botões de Autenticação */
header .auth-buttons {
    display: flex;
    gap: 15px;
}

header .btn-login,
header .btn-register,
header .btn-profile {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

header .btn-login {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
}

header .btn-register {
    background-color: var(--secondary-color);
    color: var(--text-dark);
}

header .btn-profile {
    background-color: var(--secondary-color); /* Alterar de rgba(255, 255, 255, 0.1) para var(--secondary-color) */
    color: var(--text-dark); /* Alterar de var(--text-light) para var(--text-dark) */
    cursor: pointer;
}

header .btn-profile:hover {
    background-color: #ffb74d; /* Alterar de rgba(255, 255, 255, 0.2) para #ffb74d */
    transform: translateY(-2px);
}
header .btn-register:hover {
    background-color: #ffb74d;
    transform: translateY(-2px);
}

/* Dropdown Menu */
header .dropdown {
    position: relative;
}

header .dropdown-content {
    position: absolute;
    top: 100%;
    right: 0;
    width: 250px;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 0;
    margin-top: 10px;
    display: none;
    z-index: 101;
}

header .dropdown:hover .dropdown-content {
    display: block;
    animation: fadeIn 0.3s ease;
}

header .dropdown-content .user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

header .dropdown-content .user-avatar {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: var(--text-light);
    border-radius: 50%;
    font-weight: bold;
    font-size: 1.2rem;
}

header .dropdown-content .user-details {
    flex: 1;
}

header .dropdown-content .user-name {
    font-weight: bold;
    color: var(--text-dark);
    margin-bottom: 3px;
}

header .dropdown-content .user-role {
    font-size: 0.8rem;
    color: #666;
}

header .dropdown-content a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: var(--text-dark);
    text-decoration: none;
    transition: var(--transition);
}

header .dropdown-content a:hover {
    background-color: #f5f5f5;
}

header .dropdown-content a i {
    color: var(--primary-color);
    width: 20px;
    text-align: center;
}

/* Menu Hamburger */
header .hamburger {
    display: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-light);
}

/* Responsivo */
@media (max-width: 992px) {
    header {
        padding: 15px 20px;
    }
    
    header nav ul {
        gap: 10px;
    }
    
    header .search-box {
        flex: 0 1 200px;
    }
}

@media (max-width: 768px) {
    header nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        flex-direction: column;
        background-color: var(--primary-color);
        padding: 20px;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        z-index: 100;
        gap: 15px;
        align-items: flex-start;
    }
    
    header nav.active {
        display: flex;
    }
    
    header nav ul {
        flex-direction: column;
        width: 100%;
    }
    
    header nav ul li {
        width: 100%;
    }
    
    header nav ul li a {
        width: 100%;
        display: flex;
        padding: 12px 15px;
    }
    
    header .search-box {
        width: 100%;
        margin: 10px 0;
    }
    
    header .auth-buttons {
        width: 100%;
        justify-content: space-between;
    }
    
    header .hamburger {
        display: block;
    }
}

@media (max-width: 480px) {
    header .logo img {
        height: 35px;
    }
    
    header .logo-text {
        font-size: 1.2rem;
    }
    
    header .btn-login,
    header .btn-register,
    header .btn-profile {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
}

        /* Hero Banner com Carrossel */
        .hero-banner {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease;
            background-size: cover;
            background-position: center;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--text-light);
            padding: 0 20px;
            background: rgba(0, 0, 0, 0.4);
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            animation: fadeInUp 1s ease;
        }

        .hero-content p {
            font-size: 1.3rem;
            max-width: 800px;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            animation: fadeInUp 1.3s ease;
        }

      .hero-btn {
    padding: 15px 30px;
    font-size: 1.2rem;
    background-color: var(--secondary-color);
    color: var(--text-dark);
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: var(--shadow);
    animation: fadeInUp 1.6s ease;
    text-decoration: none; /* Adicione esta linha para remover o sublinhado */
    display: inline-block; /* Garante que o botão se comporte corretamente */
}

.hero-btn:hover {
    background-color: #ffb74d;
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
    text-decoration: none; /* Mantém o botão sem sublinhado mesmo quando hover */
}
        /* Seção 7 Maravilhas */
        .maravilhas {
            padding: 80px 0;
            background-color: var(--light-bg);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--secondary-color);
        }

        .section-header p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .maravilhas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .maravilha-card {
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .maravilha-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .maravilha-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }

        .maravilha-img {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .maravilha-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .maravilha-card:hover .maravilha-img img {
            transform: scale(1.1);
        }

        .maravilha-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .maravilha-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .maravilha-content p {
            margin-bottom: 15px;
            color: var(--text-dark);
            flex-grow: 1;
        }

        .maravilha-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .maravilha-location {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }

        .maravilha-location i {
            margin-right: 5px;
            color: var(--primary-color);
        }

        /* Ver Todos Botão */
        .view-all-container {
            text-align: center;
            margin-top: 40px;
        }

        .btn-view-all {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: bold;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .btn-view-all:hover {
            background-color: #00695c;
            transform: translateY(-3px);
            box-shadow: var(--hover-shadow);
        }

        .btn-view-all i {
            margin-left: 8px;
        }

        /* Seção Destinos */
        .destinos {
            padding: 80px 0;
            background-color: #fff;
        }

        .destinos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .destino-card {
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
        }

        .destino-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .destino-img {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
        }

        .destino-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .destino-card:hover .destino-img img {
            transform: scale(1.1);
        }

        .destino-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .destino-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .destino-content p {
            margin-bottom: 15px;
            color: var(--text-dark);
            flex-grow: 1;
        }

        .destino-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .destino-location {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }

        .destino-location i {
            margin-right: 5px;
            color: var(--primary-color);
        }

        /* Seção Recentes */
        .recentes {
            padding: 80px 0;
            background-color: var(--light-bg);
        }

        .recentes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }

        .recentes-slider {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
            padding: 20px 0;
        }

        .recentes-slider::-webkit-scrollbar {
            display: none;
        }

        .recente-card {
            min-width: 300px;
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
        }

        .recente-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .recente-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--accent-color);
            color: var(--text-light);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }

        .recente-img {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .recente-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .recente-card:hover .recente-img img {
            transform: scale(1.1);
        }

        .recente-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .recente-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .recente-content p {
            margin-bottom: 15px;
            color: var(--text-dark);
            flex-grow: 1;
            font-size: 0.95rem;
        }

        .recente-date {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: auto;
        }

        .recente-date i {
            margin-right: 5px;
            color: var(--accent-color);
        }

        .recentes-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            left: 0;
            pointer-events: none;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            z-index: 2;
        }

        .recentes-nav button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: var(--text-dark);
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            pointer-events: auto;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .recentes-nav button:hover {
            background-color: #ffb74d;
            transform: scale(1.1);
        }

        /* Seção Fatos */
        .fatos {
            padding: 80px 0;
            background-color: #fff;
        }

        .fatos-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .fatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .fato-card {
            background-color: var(--light-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .fato-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .fato-icon {
            width: 100%;
            padding: 30px 0;
            background-color: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .fato-icon i {
            font-size: 3rem;
            color: var(--text-light);
        }

        .fato-content {
            padding: 20px;
            text-align: center;
        }

        .fato-content h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .fato-content p {
            color: var(--text-dark);
            margin-bottom: 0;
        }

        .counter-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .counter-item {
            text-align: center;
            margin: 15px;
        }

        .counter {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .counter-text {
            color: var(--text-dark);
            font-size: 1rem;
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .maravilhas-grid, .destinos-grid, .fatos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .recente-card {
                min-width: 250px;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .section-header h2 {
                font-size: 1.8rem;
            }
            
            .recentes-nav {
                display: none;
            }
        }
/* Estilos para o Footer */
.site-footer {
    background-color: var(--primary-color);
    color: rgba(255, 255, 255, 0.8);
    padding: 70px 0 20px;
    position: relative;
    margin-top: 80px;
}

.site-footer::before {
    content: '';
    position: absolute;
    top: -25px;
    left: 0;
    width: 100%;
    height: 25px;
    background-image: linear-gradient(135deg, var(--primary-color) 25%, transparent 25%),
                     linear-gradient(225deg, var(--primary-color) 25%, transparent 25%);
    background-size: 50px 50px;
    background-repeat: repeat-x;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-top {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 50px;
}

.footer-column h3 {
    color: var(--text-light);
    font-size: 1.3rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-column h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 2px;
    background-color: var(--secondary-color);
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.footer-logo img {
    height: 45px;
    width: auto;
    border-radius: 50%;
}

.footer-logo h3 {
    margin: 0;
    color: var(--text-light);
    font-size: 1.5rem;
    padding-bottom: 0;
}

.footer-logo h3::after {
    display: none;
}

.footer-about {
    margin-bottom: 20px;
    line-height: 1.6;
}

.social-links {
    display: flex;
    gap: 12px;
}

.social-links a {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 36px;
    height: 36px;
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    border-radius: 50%;
    transition: var(--transition);
}

.social-links a:hover {
    background-color: var(--secondary-color);
    color: var(--text-dark);
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: var(--transition);
}

.footer-links a i {
    margin-right: 8px;
    font-size: 0.8rem;
    color: var(--secondary-color);
}

.footer-links a:hover {
    color: var(--text-light);
    transform: translateX(5px);
}

.popular-links a i {
    font-size: 1rem;
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.footer-contact li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.footer-contact li i {
    color: var(--secondary-color);
    width: 20px;
    text-align: center;
}

.footer-newsletter h4 {
    color: var(--text-light);
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.newsletter-form {
    display: flex;
    height: 40px;
}

.newsletter-form input {
    flex: 1;
    padding: 8px 15px;
    border: none;
    border-radius: var(--border-radius) 0 0 var(--border-radius);
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
}

.newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.newsletter-form input:focus {
    outline: none;
    background-color: rgba(255, 255, 255, 0.2);
}

.newsletter-form button {
    width: 40px;
    background-color: var(--secondary-color);
    border: none;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    color: var(--text-dark);
    cursor: pointer;
    transition: var(--transition);
}

.newsletter-form button:hover {
    background-color: #ffb74d;
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.9rem;
}

.footer-bottom p {
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
}

.footer-bottom-links a:hover {
    color: var(--secondary-color);
}

/* Responsividade do Footer */
@media (max-width: 768px) {
    .footer-top {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-bottom {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .site-footer {
        padding: 50px 0 20px;
    }
    
    .footer-logo {
        justify-content: center;
    }
    
    .footer-column h3 {
        text-align: center;
    }
    
    .footer-column h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .social-links {
        justify-content: center;
    }
    
    .footer-about {
        text-align: center;
    }
}

    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../assets/images/logo_.PNG" alt="Logo da Aplicação" />
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
                    
                    <a href="login.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Entrar
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <!-- Hambúrguer para menu mobile (será controlado via JavaScript) -->
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </header>

    <!-- Hero Banner com Carrossel de Imagens -->
    <section class="hero-banner">
        <div class="hero-slider">
             <div class="hero-slide active" style="background-image: url('../assets/images/banner1.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner2.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner3.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner5.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner6.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner8.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner4.jpg');"></div>
            <div class="hero-slide" style="background-image: url('../assets/images/banner7.webp');"></div>
        </div>
        <div class="hero-content">
            <h1>Descubra a Magia de Angola</h1>
            <p>Venha conhecer as paisagens deslumbrantes, a cultura vibrante e a história fascinante de um dos mais belos países da África.</p>
            <a href="listar_destino.php" class="hero-btn"><i class="fas fa-compass"></i> Explore Nossos Destinos</a>
        </div>
    </section>

    <!-- Seção 7 Maravilhas de Angola -->
    <section id="maravilhas" class="maravilhas">
        <div class="section-header">
            <h2>O Melhor de Angola</h2>
            <p>Conheça as 7 Maravilhas Naturais de Angola, locais deslumbrantes que representam a incrível diversidade e beleza do país.</p>
        </div>
        
        <div class="maravilhas-grid">
            <?php
            if ($resultado_maravilhas->num_rows > 0) {
                while($maravilha = $resultado_maravilhas->fetch_assoc()) {
                    $imagem = !empty($maravilha['imagem']) ? '../assets/images/' . $maravilha['imagem'] : '../assets/images/imagem_padrao.jpg';
                    ?>
                    <div class="maravilha-card" onclick="window.location.href='detalhes_destino.php?id=<?= $maravilha['id'] ?>'">
                        <span class="maravilha-badge">Maravilha Natural</span>
                        <div class="maravilha-img">
                            <img src="<?= $imagem ?>" alt="<?= htmlspecialchars($maravilha['nome_destino']) ?>">
                        </div>
                        <div class="maravilha-content">
                            <h3><?= htmlspecialchars($maravilha['nome_destino']) ?></h3>
                            <p><?= encurtarTexto($maravilha['descricao']) ?></p>
                            <div class="maravilha-footer">
                                <div class="maravilha-location">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($maravilha['localizacao']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-results'>Nenhuma maravilha encontrada.</p>";
            }
            ?>
        </div>
        
        <div class="view-all-container">
            <a href="listar_destino.php?maravilhas=1" class="btn-view-all">
                Ver Todas Maravilhas <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Seção Destinos Angola -->
    <section id="destinos" class="destinos">
        <div class="section-header">
            <h2>Destinos Angola</h2>
            <p>Explore os mais belos e fascinantes destinos turísticos espalhados por todo o território angolano.</p>
        </div>
        
        <div class="destinos-grid">
            <?php
            if ($resultado_destinos->num_rows > 0) {
                while($destino = $resultado_destinos->fetch_assoc()) {
                    $imagem = !empty($destino['imagem']) ? '../assets/images/' . $destino['imagem'] : '../assets/images/imagem_padrao.jpg';
                    ?>
                    <div class="destino-card" onclick="window.location.href='detalhes_destino.php?id=<?= $destino['id'] ?>'">
                        <div class="destino-img">
                            <img src="<?= $imagem ?>" alt="<?= htmlspecialchars($destino['nome_destino']) ?>">
                        </div>
                        <div class="destino-content">
                            <h3><?= htmlspecialchars($destino['nome_destino']) ?></h3>
                            <p><?= encurtarTexto($destino['descricao']) ?></p>
                            <div class="destino-footer">
                                <div class="destino-location">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($destino['localizacao']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-results'>Nenhum destino encontrado.</p>";
            }
            ?>
        </div>
        
        <div class="view-all-container">
            <a href="listar_destino.php" class="btn-view-all">
                Ver Todos Destinos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Seção Adicionados Recentemente -->
    <section id="recentes" class="recentes">
        <div class="section-header">
            <h2>Adicionados Recentemente</h2>
            <p>Descubra os mais novos destinos turísticos adicionados à nossa plataforma.</p>
        </div>
        
        <div class="recentes-container">
            <div class="recentes-slider">
                <?php
                if ($resultado_recentes->num_rows > 0) {
                    while($recente = $resultado_recentes->fetch_assoc()) {
                        $imagem = !empty($recente['imagem']) ? '../assets/images/' . $recente['imagem'] : '../assets/images/imagem_padrao.jpg';
?>
<div class="recente-card" onclick="window.location.href='detalhes_destino.php?id=<?= $recente['id'] ?>'">
    <span class="recente-badge">Novo</span>
    <div class="recente-img">
        <img src="<?= $imagem ?>" alt="<?= htmlspecialchars($recente['nome_destino']) ?>">
    </div>
    <div class="recente-content">
        <h3><?= htmlspecialchars($recente['nome_destino']) ?></h3>
        <p><?= encurtarTexto($recente['descricao']) ?></p>
        <div class="recente-date">
            <i class="far fa-calendar-alt"></i> Adicionado em: <?= formatarData($recente['data_cadastro']) ?>
        </div>
    </div>
</div>
<?php
                    }
                } else {
                    echo "<p class='no-results'>Nenhum destino recente encontrado.</p>";
                }
?>
            </div>
            <div class="recentes-nav">
                <button class="prev-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="next-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Seção de Fatos e Estatísticas -->
    <section id="fatos" class="fatos">
        <div class="section-header">
            <h2>Fatos Sobre Angola</h2>
            <p>Conheça alguns dados interessantes sobre este incrível país africano.</p>
        </div>
        
        <div class="fatos-container">
            <div class="fatos-grid">
                <div class="fato-card">
                    <div class="fato-icon">
                        <i class="fas fa-globe-africa"></i>
                    </div>
                    <div class="fato-content">
                        <h3>Território Vasto</h3>
                        <p>Angola é o sétimo maior país da África, com uma área de mais de 1.246.700 km².</p>
                    </div>
                </div>
                
                <div class="fato-card">
                    <div class="fato-icon">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <div class="fato-content">
                        <h3>Biodiversidade</h3>
                        <p>O país abriga seis diferentes biomas terrestres, que vão desde desertos até florestas tropicais.</p>
                    </div>
                </div>
                
                <div class="fato-card">
                    <div class="fato-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="fato-content">
                        <h3>Cultural</h3>
                        <p>Angola possui mais de 90 grupos etnolinguísticos diferentes, cada um com suas próprias tradições.</p>
                    </div>
                </div>
            </div>
            
            <div class="counter-container">
                <div class="counter-item">
                    <div class="counter" data-target="21">0</div>
                    <div class="counter-text">Províncias</div>
                </div>
                
                <div class="counter-item">
                    <div class="counter" data-target="1600">0</div>
                    <div class="counter-text">Km de Litoral</div>
                </div>
                
                <div class="counter-item">
                    <div class="counter" data-target="2619">0</div>
                    <div class="counter-text">Metros (Ponto mais alto)</div>
                </div>
                
                <div class="counter-item">
                    <div class="counter" data-target="70">0</div>
                    <div class="counter-text">Destinos Turísticos Principais</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção Sobre Nós -->
    <section id="sobre" class="sobre">
        <div class="section-header">
            <h2>Sobre Nós</h2>
            <p>A Destinos Angola é uma plataforma dedicada a promover as maravilhas naturais e culturais de Angola. Criada com a missão de revelar ao mundo a incrível diversidade deste país africano, trabalhamos para conectar viajantes a experiências autênticas que valorizam as comunidades locais e preservam o patrimônio angolano.</p>
        </div>
    </section>
    <!-- Footer -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../assets/images/logo_.PNG" alt="Logo Destinos Angola">
                    <h3>Destinos Angola</h3>
                </div>
                <p class="footer-about">Sua porta de entrada para descobrir as belezas naturais, a cultura vibrante e os destinos fascinantes de Angola.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="#maravilhas"><i class="fas fa-chevron-right"></i> 7 Maravilhas</a></li>
                    <li><a href="#destinos"><i class="fas fa-chevron-right"></i> Destinos</a></li>
                    <li><a href="#recentes"><i class="fas fa-chevron-right"></i> Novidades</a></li>
                    <li><a href="#fatos"><i class="fas fa-chevron-right"></i> Fatos Sobre Angola</a></li>
                    <li><a href="#sobre"><i class="fas fa-chevron-right"></i> Sobre Nós</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Destinos Populares</h3>
                <ul class="footer-links popular-links">
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Parque Nacional da Kissama</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Quedas de Kalandula</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Baía Azul</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Serra da Leba</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contato</h3>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> Luanda, Angola</li>
                    <li><i class="fas fa-phone"></i> +244 941 227 898</li>
                    <li><i class="fas fa-envelope"></i> info@destinosangola.co.ao</li>
                </ul>
                <div class="footer-newsletter">
                    <h4>Receba Novidades</h4>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Seu e-mail">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Destinos Angola. Todos os direitos reservados.</p>
            <div class="footer-bottom-links">
                <a href="termos.php">Termos de Uso</a>
                <a href="privacidade.php">Política de Privacidade</a>
                <a href="contato.php">Contato</a>
            </div>
        </div>
    </div>
</footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Script para o carrossel de imagens do hero banner
        $(document).ready(function() {
            // Configuração do carrossel de imagens do hero banner
            let currentSlide = 0;
            const slides = $('.hero-slide');
            const slideCount = slides.length;
            
            function nextSlide() {
                slides.eq(currentSlide).removeClass('active');
                currentSlide = (currentSlide + 1) % slideCount;
                slides.eq(currentSlide).addClass('active');
            }
            
            // Muda o slide a cada 5 segundos
            setInterval(nextSlide, 5000);
            
            // Controles de navegação para o slider de destinos recentes
            $('.next-btn').click(function() {
                $('.recentes-slider').animate({
                    scrollLeft: "+=350"
                }, 300);
            });
            
            $('.prev-btn').click(function() {
                $('.recentes-slider').animate({
                    scrollLeft: "-=350"
                }, 300);
            });
            
            // Animação para os contadores
            function animateCounter() {
                $('.counter').each(function() {
                    const $this = $(this);
                    const target = parseInt($this.attr('data-target'));
                    
                    // Verifica se o contador está visível na tela
                    const isInViewport = function(elem) {
                        const bounding = elem.getBoundingClientRect();
                        return (
                            bounding.top >= 0 &&
                            bounding.left >= 0 &&
                            bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                            bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
                        );
                    };
                    
                    if (isInViewport(this) && !$this.hasClass('animated')) {
                        $this.addClass('animated');
                        $({ Counter: 0 }).animate({
                            Counter: target
                        }, {
                            duration: 2000,
                            easing: 'swing',
                            step: function() {
                                $this.text(Math.ceil(this.Counter));
                            },
                            complete: function() {
                                $this.text(target);
                            }
                        });
                    }
                });
            }
            
            // Verifica quando a seção de contadores entra na viewport
            $(window).scroll(function() {
                animateCounter();
            });
            
            // Executa quando a página carrega também
            animateCounter();
            
            // Controle do menu mobile
            $('.hamburger').click(function() {
                $('nav').toggleClass('active');
                $(this).toggleClass('active');
            });
            
            // Fechar menu ao clicar em um link (versão mobile)
            $('nav a').click(function() {
                if ($(window).width() <= 768) {
                    $('nav').removeClass('active');
                    $('.hamburger').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
<?php
$conexao->close();
?>