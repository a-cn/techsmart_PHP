<?php
require_once '../back/conexao_sqlserver.php'; // Usando o mesmo arquivo de conexão do primeiro código
require_once '../back/verifica_sessao.php'; // Garantindo autenticação
require_once '../back/funcoes_sessao.php';
$loginTimestamp = time(); // Mantendo controle de sessão

// Captura o ID do pedido passado pela URL
$pedido_id = $_GET['pedido_id'] ?? null;

if (!$pedido_id || !is_numeric($pedido_id)) {
    die("Erro: pedido_id inválido ou não informado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/formulario-feedback.css">
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
    <title>Enviar Feedback</title>
</head>
<body>
    <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->
    <header>Enviar Feedback</header>
    <form action="../back/processa_feedback.php" method="POST">
        <input type="hidden" name="fk_pedido" value="<?php echo htmlspecialchars($pedido_id); ?>">    <!-- Campo oculto para capturar o id do pedido e associá-lo ao fk_pedido da tabela Feedback -->
        <label for="avaliacao">Avaliação (1 a 5):</label><br>
        <div class="rating">
            <input type="radio" name="avaliacao" id="star5" value="5"><label for="star5">&#9733;</label>
            <input type="radio" name="avaliacao" id="star4" value="4"><label for="star4">&#9733;</label>
            <input type="radio" name="avaliacao" id="star3" value="3"><label for="star3">&#9733;</label>
            <input type="radio" name="avaliacao" id="star2" value="2"><label for="star2">&#9733;</label>
            <input type="radio" name="avaliacao" id="star1" value="1"><label for="star1">&#9733;</label>
        </div>

        <label for="observacao">Observação:</label><br>
        <textarea name="observacao" id="observacao" rows="4" cols="30"></textarea><br><br>

        <button type="submit">Enviar</button>
    </form>
</body>
</html>
