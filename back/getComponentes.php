<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;

$sql = "SELECT c.componente_id, c.nome, c.especificacao, c.quantidade, c.nivel_minimo, c.nivel_maximo, fc.custo_componente, f.nome AS fornecedor
        FROM Componente c
        INNER JOIN Fornecedor_Componente fc ON c.componente_id = fc.fk_componente
        INNER JOIN Fornecedor f ON fc.fk_fornecedor = f.fornecedor_id
        WHERE c.ativo = ?";

$stmt = sqlsrv_query($conn, $sql, [$ativo]);
$componentes = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $componentes[] = $row;
}

echo json_encode($componentes);
?>
