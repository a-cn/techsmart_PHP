<?php
//Endpoint referente à View vw_Resumo_Avaliacoes_Geral
//Proporção total entre avaliações positivas, negativas e neutras

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT TotalAvaliacoes, AvaliacoesPositivas, AvaliacoesNegativas, AvaliacoesNeutras, MediaGeral FROM vw_Resumo_Avaliacoes_Geral";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta ou nenhum dado retornado.']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

echo json_encode([
    'total' => (int)$row['TotalAvaliacoes'],
    'positivas' => (int)$row['AvaliacoesPositivas'],
    'negativas' => (int)$row['AvaliacoesNegativas'],
    'neutras' => (int)$row['AvaliacoesNeutras'],
    'media' => round((float)$row['MediaGeral'], 2)
]);

//Por ser um resumo geral e único, não necessita de array de objetos, visto que teria um único registro
?>