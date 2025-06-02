<?php
//Endpoint referente à View vw_Feedback_Por_Pedido
//Relatório de feedback do cliente por pedido

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT feedback_id, data_hora, Cliente, pedido_id, avaliacao, observacao FROM vw_Feedback_Por_Pedido";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

$feedbacks = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $feedbacks[] = [
        'id' => $row['feedback_id'],
        'dataHora' => $row['data_hora']->format('Y-m-d H:i:s'), //garante que o tipo DateTime seja convertido corretamente para string
        'cliente' => $row['Cliente'],
        'pedidoId' => $row['pedido_id'],
        'avaliacao' => $row['avaliacao'],
        'observacao' => $row['observacao']
    ];
}

echo json_encode($feedbacks);
?>