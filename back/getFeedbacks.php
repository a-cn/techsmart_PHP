<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;
$pedidoId = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : null;

$usuarioId = $_SESSION['usuario_id'];
$tipoUsuario = $_SESSION['tipo_usuario'];

$sql = "SELECT f.feedback_id, f.fk_pedido, f.avaliacao, f.observacao, f.data_hora
        FROM Feedback f
        JOIN Pedido p ON f.fk_pedido = p.pedido_id
        WHERE f.ativo = ?";
$params = [$ativo];

//Se for Cliente, limitar ao usuário logado
if ($tipoUsuario === 'cliente') {
    $sql .= " AND p.fk_usuario = ?";
    $params[] = $usuarioId;
}

//Se houver filtro por pedido específico
if ($pedidoId) {
    $sql .= " AND f.fk_pedido = ?";
    $params[] = $pedidoId;
}

$stmt = sqlsrv_query($conn, $sql, $params);
$dados = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['data_hora'] = $row['data_hora'] ? $row['data_hora']->format('d/m/Y, H:i') : '';
    $dados[] = $row;
}

echo json_encode($dados);