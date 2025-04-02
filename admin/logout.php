<?php
session_start();
session_destroy(); // Encerra a sessão

// Exibe uma mensagem de logout antes de redirecionar
echo "<script>alert('Você foi desconectado com sucesso.'); window.location.href='login.php';</script>";
exit();
?>
