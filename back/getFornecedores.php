<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

// Consulta SQL
$sql = "    SELECT [fornecedor_id]
                   ,[nome]
                   ,[cpf_cnpj]
                   ,[num_principal]
                   ,[num_secundario]
                   ,[email]
                   ,[fk_endereco]
                   ,[situacao]
                   ,[ativo]
                   ,e.*
              FROM [dbo].[Fornecedor] AS f
        INNER JOIN [Endereco] AS e ON f.fk_endereco = e.endereco_id;";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$fornecedores = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $fornecedores[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

//var_dump($fornecedores); exit;  
// Retorna JSON
header('Content-Type: application/json');
echo json_encode($fornecedores);
?>