<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Destino</title>
</head>
<body>
    <h1>Cadastrar Novo Destino</h1>
    <form action="/admin/cadastrar_destino.php" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome do Destino:</label><br>
        <input type="text" id="nome" name="nome" required><br><br>

        <label for="localizacao">Localização:</label><br>
        <input type="text" id="localizacao" name="localizacao" required><br><br>

        <label for="descricao">Descrição:</label><br>
        <textarea id="descricao" name="descricao" required></textarea><br><br>

        <label for="imagem">Imagem:</label><br>
        <input type="file" id="imagem" name="imagem" accept="image/*" required><br><br>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>
