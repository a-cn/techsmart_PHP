<?php
require_once 'conexao_sqlserver.php'; //Conexão com o banco
require_once 'verifica_sessao.php'; //Garante que o usuário está logado

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['avaliacao'], $_POST['observacao'])) {
    $feedback_id = htmlspecialchars(trim($_POST['feedback_id']));
    $avaliacao = (int) $_POST['avaliacao'];
    $observacao = htmlspecialchars(trim($_POST['observacao']));

    if (!$conn || !is_resource($conn)) {
        die("Erro na conexão com o banco de dados: " . print_r(sqlsrv_errors(), true));
    }

    $sql = "UPDATE Feedback SET avaliacao = ?, observacao = ? WHERE feedback_id = ?";
    $params = [$avaliacao, $observacao, $feedback_id];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        //Libera e fecha a conexão
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        //Redireciona com um alerta de sucesso
        echo "<script>alert('Sua alteração foi registrada!'); window.location.href = '../Front/Pages/consulta-feedback.php';</script>";
        exit;
    } else {
        echo "Erro ao atualizar o feedback: " . print_r(sqlsrv_errors(), true);
    }
} else {
    echo "Dados incompletos ou método inválido.";
}
?>