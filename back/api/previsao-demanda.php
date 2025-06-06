<?php
//Endpoint referente à View vw_Previsao_Demanda
//Previsões de demandas futuras com base no histórico de movimentações

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "WITH ProdutosComVendas AS (
    SELECT DISTINCT Produto
    FROM vw_Previsao_Demanda
    WHERE Total_Saida > 0
)
SELECT 
    d.Mes,
    YEAR(CONVERT(date, d.Mes + '-01')) as Ano,
    MONTH(CONVERT(date, d.Mes + '-01')) as MesNumero,
    d.Produto, 
    d.Total_Saida 
FROM vw_Previsao_Demanda d
INNER JOIN ProdutosComVendas p ON d.Produto = p.Produto
ORDER BY d.Mes ASC";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

// Organiza os dados por produto e ano
$dados = [];
$anos = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $produto = $row['Produto'];
    $ano = $row['Ano'];
    $mesNumero = $row['MesNumero'];
    $saida = (int)$row['Total_Saida'];

    // Armazena anos únicos
    if (!in_array($ano, $anos)) {
        $anos[] = $ano;
    }

    // Inicializa array do produto se não existir
    if (!isset($dados[$produto])) {
        $dados[$produto] = ['label' => $produto, 'data' => []];
    }

    // Armazena os dados usando o número do mês como índice
    $dados[$produto]['data'][] = [
        'x' => $mesNumero,
        'y' => $saida,
        'ano' => $ano
    ];
}

// Ordena anos
sort($anos);

// Prepara datasets
$datasets = [];
foreach ($dados as $produto => $info) {
    // Ordena os dados por mês
    usort($info['data'], function($a, $b) {
        return $a['x'] - $b['x'];
    });
    
    $datasets[] = [
        'label' => $produto,
        'data' => $info['data']
    ];
}

echo json_encode([
    'datasets' => $datasets,
    'anos' => $anos
]);
?>