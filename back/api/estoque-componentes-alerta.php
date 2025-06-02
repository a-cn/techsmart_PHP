<?php
//Endpoint referente à View vw_Estoque_Componentes_Alerta
//Estoque mínimo e máximo de componentes com indicador

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT Componente, quantidade, nivel_minimo, nivel_maximo, Alerta FROM vw_Estoque_Componentes_Alerta";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta']);
    exit;
}

$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = [
        'componente' => $row['Componente'],
        'quantidade' => $row['quantidade'],
        'nivel_minimo' => $row['nivel_minimo'],
        'nivel_maximo' => $row['nivel_maximo'],
        'alerta' => $row['Alerta']
    ];
}

echo json_encode($dados);
?>