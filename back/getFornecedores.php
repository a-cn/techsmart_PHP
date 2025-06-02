<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco

// Consulta SQL
$sql = "SELECT   [fornecedor_id]
                ,[nome]
                ,[cpf_cnpj]
                ,[num_principal]
                ,[num_secundario]
                ,[email]
                ,[fk_endereco]
                ,[situacao]
                ,[ativo]
          FROM [dbo].[Fornecedor]";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$fornecedores = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    //$row['data_nascimento'] = $row['data_nascimento'] ? $row['data_nascimento']->format('d/m/Y') : ''; // Formatar data
    $fornecedores[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

//var_dump($fornecedores); exit;  
// Retorna JSON
header('Content-Type: application/json');
echo json_encode($fornecedores);
?>