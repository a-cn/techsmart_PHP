<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['produtofinal_id'] ?? null;

if ($id) {

    //Verifica se o produto final está associado a um pedido em andamento
    $sql = "SELECT COUNT(*) as total FROM ProdutoFinal pf JOIN Pedido_ProdutoFinal ppf ON ppf.fk_produtofinal = pf.produtofinal_id JOIN Pedido p ON p.pedido_id = ppf.fk_pedido WHERE pf.produtofinal_id = ? AND p.situacao NOT IN ('Entregue', 'Cancelado')";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    $resultado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $total = $resultado['total'];

    if ($total > 0) {
        echo json_encode(["sucesso" => false, "mensagem" => "Não é possível inativar produtos que estejam presentes em pedidos em andamento!"]);
        exit;
    }

    //Verifica se o produto final está associado a uma produção ativa
    $sql = "SELECT COUNT(*) as total FROM ProdutoFinal pf JOIN Producao p ON p.producao_id = pf.fk_producao WHERE pf.produtofinal_id = ? AND p.ativo = 1";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    $resultado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $total = $resultado['total'];

    if ($total > 0) {
        echo json_encode(["sucesso" => false, "mensagem" => "Não é possível inativar produtos que estejam presentes em produções ativas!"]);
        exit;
    }

    $sql = "UPDATE ProdutoFinal SET ativo = 0 WHERE produtofinal_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    echo json_encode(["sucesso" => $stmt !== false]);
} else {
    echo json_encode(["sucesso" => false]);
}