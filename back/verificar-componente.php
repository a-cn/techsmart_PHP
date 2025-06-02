<?php
include 'conexao_sqlserver.php';

header('Content-Type: application/json'); // Define cabeçalho para resposta JSON

// Consulta para verificar se existem componentes ativos
$sql = "SELECT COUNT(*) as total FROM Componente WHERE ativo = 1";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["existe" => false]); // Retorna false em caso de erro
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
echo json_encode(["existe" => ($row['total'] > 0)]); // Retorna true se houver componentes
?> 

