<?php
require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão

// Captura do parâmetro de filtro ativo
$ativo = isset($_GET['ativo']) ? (int) $_GET['ativo'] : 1;

// Consulta SQL
$sql = "SELECT f.[fornecedor_id],
               f.[nome],
               f.[cpf_cnpj],
               f.[num_principal],
               f.[num_secundario],
               f.[email],
               f.[fk_endereco],
               f.[ativo],
               e.cep,
               e.logradouro,
               e.numero,
               e.complemento,
               e.bairro,
               e.cidade,
               e.estado
        FROM [dbo].[Fornecedor] AS f
        INNER JOIN [Endereco] AS e ON f.fk_endereco = e.endereco_id
        WHERE ativo = ?"; //O atributo ativo será passado como parâmetro a ser identificado para exibição alternada no DataTables

$params = [$ativo]; //Parâmetro para a cláusula WHERE
$stmt = sqlsrv_query($conn, $sql, $params); //Passando o parâmetro

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
//Retorna JSON
header('Content-Type: application/json');
echo json_encode($fornecedores);
?>