<?php
//Endpoint referente à View vw_Status_Produtos_Producao
//Relatório de produtos semiacabados VS acabados

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT Produto, quantidade, StatusProducao FROM vw_Status_Produtos_Producao";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = [
        'produto' => $row['Produto'],
        'quantidade' => (int)$row['quantidade'],
        'status' => $row['StatusProducao'] // "Acabado" ou "Semiacabado"
    ];
}

echo json_encode($dados);
?>