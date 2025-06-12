<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;

$sql = "SELECT 
            pf.produtofinal_id, 
            pf.fk_producao, 
            pf.nome, 
            pf.descricao, 
            pf.quantidade, 
            p.custo as custo, 
            pf.nivel_minimo, 
            pf.nivel_maximo, 
            pf.tempo_producao_dias
        FROM 
            ProdutoFinal pf
            JOIN Producao p ON p.producao_id = pf.fk_producao
        WHERE 
            pf.ativo = ?";

$stmt = sqlsrv_query($conn, $sql, [$ativo]);
$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $dados[] = $row;
}

echo json_encode($dados);
?>