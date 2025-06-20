<?php
require_once '../back/conexao_sqlserver.php'; // Usando o mesmo arquivo de conexão do primeiro código
require_once '../back/verifica_sessao.php'; // Garantindo autenticação
require_once '../back/funcoes_sessao.php';
$loginTimestamp = time(); // Mantendo controle de sessão

// Captura o ID do pedido passado pela URL
$pedido_id = $_GET['pedido_id'] ?? null;

if (!$pedido_id || !is_numeric($pedido_id)) {
    echo "<script>
        mostrarMensagem('Erro', 'ID do pedido inválido ou não informado', 'erro', () => {
            window.location.href = 'index.php?pg=pedidos';
        });
    </script>";
    exit;
}

// Verifica se o pedido existe e está entregue
$sql_verificacao = "SELECT situacao FROM Pedido WHERE pedido_id = ? AND fk_usuario = ? AND ativo = 1";
$stmt_verificacao = sqlsrv_query($conn, $sql_verificacao, [$pedido_id, $_SESSION['usuario_id']]);

if (!$stmt_verificacao) {
    echo "<script>
        mostrarMensagem('Erro', 'Erro ao verificar o pedido', 'erro', () => {
            window.location.href = 'index.php?pg=pedidos';
        });
    </script>";
    exit;
}

$pedido = sqlsrv_fetch_array($stmt_verificacao, SQLSRV_FETCH_ASSOC);

if (!$pedido) {
    echo "<script>
        mostrarMensagem('Erro', 'Pedido não encontrado ou sem permissão para enviar feedback', 'erro', () => {
            window.location.href = 'index.php?pg=pedidos';
        });
    </script>";
    exit;
}

if ($pedido['situacao'] !== 'Entregue') {
    echo "<script>
        mostrarMensagem('Aviso', 'Só é possível enviar feedback para pedidos que já foram entregues.', 'alerta', () => {
            window.location.href = 'index.php?pg=pedidos';
        });
    </script>";
    exit;
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
    <div class="janela-consulta" id="divEnviarFeedback">
        <span class="titulo-janela">Enviar Feedback</span>
        <form id="formEnviarFeedback" action="../back/processa_feedback.php" method="POST">
            <input type="hidden" name="fk_pedido" value="<?php echo htmlspecialchars($pedido_id); ?>">
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
    </div>

    <script>
        document.getElementById('formEnviarFeedback').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Verifica se uma avaliação foi selecionada
            if (!document.querySelector('input[name="avaliacao"]:checked')) {
                mostrarMensagem("Aviso", "Por favor, selecione uma avaliação de 1 a 5 estrelas.", "alerta");
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('../back/processa_feedback.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    mostrarMensagem("Sucesso", data.mensagem, "sucesso", () => {
                        // Redireciona para a tela de pedidos após fechar a mensagem
                        window.location.href = 'index.php?pg=pedidos';
                    });
                } else {
                    mostrarMensagem("Erro", data.erro, "erro");
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarMensagem("Erro", "Erro ao enviar feedback. Tente novamente.", "erro");
            });
        });
    </script>
</body>
</html>
