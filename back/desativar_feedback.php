<?php

header('Content-Type: application/json');

// Desabilita exibição de erros para evitar que HTML seja retornado
error_reporting(0);
ini_set('display_errors', 0);

require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

try {
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

        //Verifica se o feedback está vinculado a um usuário ativo
        $sql = "SELECT COUNT(*) as total_feedbacks FROM Feedback f JOIN Pedido p ON f.fk_pedido = p.pedido_id JOIN Usuario u ON p.fk_usuario = u.usuario_id WHERE f.feedback_id = ? AND u.ativo = 1";
        $params = [$feedback_id];
        $stmt = sqlsrv_query($conn, $sql, $params);
        $resultado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $totalFeedbacks = $resultado['total_feedbacks'];

        if ($totalFeedbacks > 0) {
            echo json_encode(["sucesso" => false, "mensagem" => "Não é possível inativar feedbacks vinculados a usuários ativos!"]);
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
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno: ' . $e->getMessage()]);
}
?>
