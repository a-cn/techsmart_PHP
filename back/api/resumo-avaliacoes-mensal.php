<?php
//Endpoint referente à View vw_Resumo_Avaliacoes_Mensal
//Proporção mensal entre avaliações positivas, negativas e neutras

header('Content-Type: application/json');
include '../conexao_sqlserver.php';

$sql = "SELECT Mes, TotalAvaliacoes, AvaliacoesPositivas, AvaliacoesNegativas, AvaliacoesNeutras, MediaMensal 
        FROM vw_Resumo_Avaliacoes_Mensal 
        ORDER BY Mes";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar consulta ou nenhum dado retornado.']);
    exit;
}

$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = [
        'mes' => $row['Mes'],
        'total' => (int)$row['TotalAvaliacoes'],
        'positivas' => (int)$row['AvaliacoesPositivas'],
        'negativas' => (int)$row['AvaliacoesNegativas'],
        'neutras' => (int)$row['AvaliacoesNeutras'],
        'media' => round((float)$row['MediaMensal'], 2)
    ];
}

echo json_encode($dados);
?>