<?php
//Endpoint referente à View vw_Consumo_Componentes_Por_Pedido
//Registrar o consumo de componentes por item do pedido

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT 
            PedidoID,
            Produto,
            Componente,
            TotalConsumido,
            Custo
        FROM vw_Consumo_Componentes_Por_Pedido
        ORDER BY PedidoID, Produto, Componente";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = [
        'pedido' => $row['PedidoID'],
        'produto' => $row['Produto'],
        'componente' => $row['Componente'],
        'quantidade' => (int)$row['TotalConsumido'],
        'custo' => (float)$row['Custo']
    ];
}

echo json_encode($dados);
?>