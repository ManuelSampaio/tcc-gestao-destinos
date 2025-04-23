<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | TravelApp</title>
    <link rel="stylesheet" href="../assets/css/stylep.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
</head>
<body>
    <div class="layout">
        <nav class="sidebar">
            <div class="logo">
                <h2>Destinos Angola</h2>
            </div>
            <ul class="menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Página Inicial</a></li>
                <li class="active"><a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                <li><a href="avaliacoes.php"><i class="fas fa-star"></i> Avaliações</a></li>
                <li><a href="explorar.php"><i class="fas fa-compass"></i> Explorar</a></li>
                <li class="menu-footer"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>

        <main class="content">
            <header class="page-header">
                <h1>Meu Perfil</h1>
                <div class="user-actions">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                </div>
            </header>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button class="btn-edit"><i class="fas fa-pen"></i> Editar Perfil</button>
                    </div>
                </div>

                <div class="profile-card">
                    <h2>Informações Pessoais</h2>
                    <div class="info-group">
                        <label>Nome</label>
                        <p><?= htmlspecialchars($usuario['nome']) ?></p>
                    </div>
                    <div class="info-group">
                        <label>Email</label>
                        <p><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    <div class="info-group">
                        <label>Conta criada em</label>
                        <p><?= isset($usuario['data_cadastro']) ? date('d/m/Y', strtotime($usuario['data_cadastro'])) : '01/01/2023' ?></p>
                    </div>
                </div>

                <div class="profile-card">
                    <h2>Segurança</h2>
                    <div class="security-options">
                        <div class="security-option">
                            <div>
                                <h3>Senha</h3>
                                <p>Última alteração: <?= isset($usuario['ultima_alteracao_senha']) ? date('d/m/Y', strtotime($usuario['ultima_alteracao_senha'])) : '01/01/2023' ?></p>
                            </div>
                            <a href="editar_senha.php" class="btn-secondary">Alterar</a>
                        </div>
                        <div class="security-option">
                            <div>
                                <h3>Verificação em duas etapas</h3>
                                <p>Status: <span class="badge-inactive">Desativado</span></p>
                            </div>
                            <a href="configurar_2fa.php" class="btn-secondary">Configurar</a>
                        </div>
                    </div>
                </div>

                <div class="profile-footer">
                    <a href="index.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Voltar a Página Inicial</a>
                    <a href="excluir_conta.php" class="btn-danger">Excluir minha conta</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>