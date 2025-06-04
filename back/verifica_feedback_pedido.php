<?php
require_once 'conexao_sqlserver.php';

header('Content-Type: application/json'); //Necessário para o fetch interpretar como JSON

if (!isset($_GET['pedido_id'])) {
    echo json_encode(['temFeedback' => false]);
    exit;
}

$pedido_id = intval($_GET['pedido_id']);

$sql = "SELECT COUNT(*) AS total FROM Feedback WHERE fk_pedido = ? AND ativo = 1";
$params = [$pedido_id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['temFeedback' => false]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$temFeedback = $row['total'] > 0;

echo json_encode(['temFeedback' => $temFeedback]);