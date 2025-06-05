<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;

$sql = "SELECT produtofinal_id, fk_producao, nome, descricao, quantidade, valor_venda, nivel_minimo, nivel_maximo, tempo_producao_dias
        FROM ProdutoFinal
        WHERE ativo = ?";

$stmt = sqlsrv_query($conn, $sql, [$ativo]);
$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = $row;
}

echo json_encode($dados);
?>