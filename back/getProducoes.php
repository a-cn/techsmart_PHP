<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco

// Consulta SQL
$sql = "SELECT  [producao_id]
               ,[nome]
          FROM [dbo].[Producao]";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$producao = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $producao[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

//var_dump($producao); exit;  
// Retorna JSON
header('Content-Type: application/json');
echo json_encode($producao);
?>