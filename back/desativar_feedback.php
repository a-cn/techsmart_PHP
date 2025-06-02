<?php
require_once 'consulta_feedback.php'; //Utiliza parâmetros em comum com este arquivo incluído

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = htmlspecialchars($_POST['feedback_id']); // Sanitização básica

    if (!$conn) {
        die("Erro ao conectar ao banco de dados: " . print_r(sqlsrv_errors(), true));
    }

    $sql = "UPDATE Feedback SET ativo = 0 WHERE feedback_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$feedback_id]);

    if ($stmt) {
        echo "<script>alert('Registro arquivado com sucesso!'); window.location.href = '../Front/Pages/consulta-feedback.php';</script>";
    } else {
        echo "<p>Erro ao alterar status: " . print_r(sqlsrv_errors(), true) . "</p>";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
} else {
    echo "<p>Erro: método inválido ou feedback_id ausente.</p>";
}
?>
