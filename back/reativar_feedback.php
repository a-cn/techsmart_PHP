<?php
header('Content-Type: application/json');

require_once 'conexao_sqlserver.php';

$input = json_decode(file_get_contents('php://input'), true);
$feedbackId = $input['feedback_id'];

$sql = "UPDATE Feedback SET ativo = 1 WHERE feedback_id = ?";
$params = [$feedbackId];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false]);
}
?>