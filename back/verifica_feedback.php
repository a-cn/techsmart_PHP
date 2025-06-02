<?php
//ESTE CÓDIGO SERVE PARA CONSULTAR A EXISTÊNCIA DE FEEDBACK DO CLIENTE PARA DETERMINADO PEDIDO

require_once 'conexao_sqlserver.php'; //Chama a conexão com o banco
require_once 'verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página

$pedido_id = $_GET['pedido_id'] ?? null;
$usuario_id = $_GET['usuario_id'] ?? null;

if (!$pedido_id || !$usuario_id) {
    echo json_encode(['erro' => 'Parâmetros ausentes']);
    exit;
}

$sql = "SELECT COUNT(*) AS total
        FROM Feedback f
        INNER JOIN Pedido p ON f.fk_pedido = p.pedido_id
        WHERE f.fk_pedido = ? AND p.fk_usuario = ? AND f.ativo = 1";
$params = [$pedido_id, $usuario_id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['erro' => 'Erro na consulta']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$existe = $row && $row['total'] > 0;

echo json_encode(['existe' => $existe]);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>