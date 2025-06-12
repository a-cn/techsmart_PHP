<?php
header('Content-Type: application/json');

require_once 'conexao_sqlserver.php';

$input = json_decode(file_get_contents('php://input'), true);
$usuarioId = $input['usuario_id'];

$sql = "UPDATE Usuario SET ativo = 1, data_ultimo_estado = GETDATE() WHERE usuario_id = ?";
$params = [$usuarioId];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false]);
}
?>