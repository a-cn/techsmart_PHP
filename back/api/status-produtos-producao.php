<?php
//Endpoint referente à View vw_Status_Producao_Produto
//Relatório de status de produção dos produtos

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT data_inicio, data_previsao, data_conclusao, Status, produto_nome FROM vw_Status_Producao_Produto";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = [
        'data_inicio' => $row['data_inicio'] instanceof DateTime ? $row['data_inicio']->format('Y-m-d') : null,
        'data_previsao' => $row['data_previsao'] instanceof DateTime ? $row['data_previsao']->format('Y-m-d') : null,
        'data_conclusao' => $row['data_conclusao'] instanceof DateTime ? $row['data_conclusao']->format('Y-m-d') : null,
        'status' => $row['Status'],
        'produto_nome' => $row['produto_nome']
    ];
}

echo json_encode($dados);
?>