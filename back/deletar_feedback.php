<?php
require_once 'consulta_feedback.php'; //Utiliza parâmetros em comum com este arquivo incluído

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = htmlspecialchars($_POST['feedback_id']);

    if (!$conn || !is_resource($conn)) {
        die("Erro na conexão com o banco de dados: " . print_r(sqlsrv_errors(), true));
    }

    // Consulta SQL para excluir o registro
    $sql = "DELETE FROM Feedback WHERE feedback_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$feedback_id]);

    if ($stmt) {
        echo "<script>alert('Registro excluído com sucesso!'); window.location.href = '../Front/Pages/consulta-feedback.php';</script>";
    } else {
        die("Erro ao excluir registro: " . print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
} else {
    echo "Método inválido ou feedback_id ausente.";
}
?>
