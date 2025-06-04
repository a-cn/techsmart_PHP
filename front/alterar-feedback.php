<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php';
require_once '../back/funcoes_sessao.php';

$tipo_usuario = $_SESSION['tipo_usuario']; //Identifica o tipo de usuário na sessão: administrador, colaborador ou cliente

if (isset($_GET['pedido_id'])) {
    $pedido_id = htmlspecialchars($_GET['pedido_id']);

    if (!$conn || !is_resource($conn)) {
        die("Erro na conexão com o banco de dados: " . print_r(sqlsrv_errors(), true));
    }

    $sql = "SELECT * FROM Feedback WHERE fk_pedido = ?";
    $stmt = sqlsrv_query($conn, $sql, [$pedido_id]);

    if (!$stmt) {
        die("Erro ao buscar o registro: " . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($row) {
        $desabilitarCampos = $tipo_usuario !== "cliente" ? 'disabled' : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/janelas.css">
    <link rel="stylesheet" type="text/css" href="../css/formulario-feedback.css">
    <title>Alterar Feedback</title>
</head>
<body>
    <div class="janela-consulta" id="divAlterarFeedback">
        <span class="titulo-janela">Alterar Feedback</span>

        <form action="../back/atualizar_feedback.php" method="POST">
            <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($row['feedback_id']); ?>">

            <label for="avaliacao">Avaliação (1 a 5):</label><br>
            <div class="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="avaliacao" id="star<?php echo $i; ?>" value="<?php echo $i; ?>"
                        <?php echo $desabilitarCampos; ?>
                        <?php if ((int)$row['avaliacao'] === $i) echo 'checked'; ?>>
                    <label for="star<?php echo $i; ?>">&#9733;</label>
                <?php endfor; ?>
            </div>

            <label for="observacao">Observação:</label><br>
            <textarea name="observacao" id="observacao" rows="4" cols="30" <?php echo $desabilitarCampos; ?>><?php echo htmlspecialchars($row['observacao']); ?></textarea><br><br>

            <?php if ($tipo_usuario === "cliente"): ?>
                <button type="submit">Salvar</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php
    } else {
        // Redireciona com um alerta
        echo "<script>alert('Registro não encontrado.'); window.location.href = 'historico_pedidos.php';</script>";
    }

    if (isset($stmt) && is_resource($stmt)) {
        sqlsrv_free_stmt($stmt);
    }
    if (isset($conn) && is_resource($conn)) {
        sqlsrv_close($conn);
    }
} else {
    echo "Erro1: pedido nao incontrado";
}
?>