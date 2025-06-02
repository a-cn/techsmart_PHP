<?php
//Endpoint referente à View vw_Previsao_Demanda
//Previsões de demandas futuras com base no histórico de movimentações

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT Mes, Produto, Total_Saida FROM vw_Previsao_Demanda";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

// Organiza os dados por produto
$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $produto = $row['Produto'];
    $mes = $row['Mes'];
    $saida = (int)$row['Total_Saida'];

    if (!isset($dados[$produto])) {
        $dados[$produto] = ['label' => $produto, 'data' => []];
    }

    $dados[$produto]['data'][$mes] = $saida;
}

// Monta arrays finais
$labels = []; // meses únicos
foreach ($dados as $produto => &$info) {
    foreach ($info['data'] as $mes => $valor) {
        if (!in_array($mes, $labels)) {
            $labels[] = $mes;
        }
    }
}

// Ordena meses
sort($labels);

$datasets = [];
foreach ($dados as $produto => $info) {
    $linha = [];
    foreach ($labels as $mes) {
        $linha[] = $info['data'][$mes] ?? 0;
    }
    $datasets[] = [
        'label' => $produto,
        'data' => $linha
    ];
}

echo json_encode([
    'labels' => $labels,
    'datasets' => $datasets
]);
?>