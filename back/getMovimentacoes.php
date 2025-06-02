<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco

// Consulta SQL
$sql = 
    " SELECT M.movimentacao_id
            ,M.fk_pedido AS pedido_id
            ,M.fk_produtofinal AS produtofinal_id
            ,PF.nome AS nome_produto
            ,M.quantidade
            ,M.data_hora
            ,M.tipo_movimentacao
        FROM Movimentacao M
        JOIN ProdutoFinal PF ON PF.produtofinal_id = M.fk_produtofinal
    ORDER BY M.data_hora DESC";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$dados = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['data_hora'] = $row['data_hora'] ? $row['data_hora']->format('Y/m/d H:i:s') : ''; // Formatar data
    $dados[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

//var_dump($dados); exit;  
// Retorna JSON
header('Content-Type: application/json');
echo json_encode($dados);
?>