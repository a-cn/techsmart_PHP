<?php
require_once 'conexao_sqlserver.php';

//Lê o corpo JSON da requisição
$input = json_decode(file_get_contents("php://input"), true);

//Verifica se a chave "feedback_id" está presente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['feedback_id'])) {
    $feedback_id = (int) $input['feedback_id']; // Cast seguro

    if (!$conn) {
        http_response_code(500);
        echo json_encode(["sucesso" => false, "mensagem" => "Erro na conexão com o banco."]);
        exit;
    }

    $sql = "UPDATE Feedback SET ativo = 0 WHERE feedback_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$feedback_id]);

    if ($stmt) {
        echo json_encode(["sucesso" => true]);
    } else {
        http_response_code(500);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao executar update.",
            "erro_sqlsrv" => sqlsrv_errors()
        ]);
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
} else {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Requisição inválida ou ID ausente."]);
}
?>
