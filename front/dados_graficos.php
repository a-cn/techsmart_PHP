<?php
header('Content-Type: application/json');
include_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados

// 1. Estoque por ProdutoFinal
$sql1 = "SELECT nome, quantidade FROM ProdutoFinal WHERE ativo = 1";
$stmt1 = sqlsrv_query($conn, $sql1);
$produtos = [];
$quantidades = [];

while ($row = sqlsrv_fetch_array($stmt1)) {
    $produtos[] = $row['nome'];
    $quantidades[] = $row['quantidade'];
}

// 2. Situação dos Componentes
$sql2 = "
    SELECT
        SUM(CASE WHEN quantidade < nivel_minimo THEN 1 ELSE 0 END) AS abaixo,
        SUM(CASE WHEN quantidade BETWEEN nivel_minimo AND nivel_maximo THEN 1 ELSE 0 END) AS normal,
        SUM(CASE WHEN quantidade > nivel_maximo THEN 1 ELSE 0 END) AS acima
    FROM Componente WHERE ativo = 1";
$stmt2 = sqlsrv_query($conn, $sql2);
$row2 = sqlsrv_fetch_array($stmt2);

echo json_encode([
    'estoque' => [
        'labels' => $produtos,
        'data' => $quantidades
    ],
    'componentes' => [
        'abaixo' => (int) $row2['abaixo'],
        'normal' => (int) $row2['normal'],
        'acima' => (int) $row2['acima']
    ]
]);
?>
