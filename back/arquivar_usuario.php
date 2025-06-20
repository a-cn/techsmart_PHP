<?php
header('Content-Type: application/json');

// Desabilita exibição de erros para evitar que HTML seja retornado
error_reporting(0);
ini_set('display_errors', 0);

require_once 'conexao_sqlserver.php'; //Conexão com o banco
require_once 'verifica_sessao.php'; //Garante sessão ativa e válida

try {
    // Pega o ID do usuário a ser "excluído"
    $input = json_decode(file_get_contents('php://input'), true);
    $usuarioId = $input['usuario_id'];

    //Verifica se o usuário possui pedidos em andamento ( !== entregue ou cancelado)
    $sql = "SELECT COUNT(*) as total_pedidos FROM Pedido WHERE fk_usuario = ? AND situacao != 'Entregue' AND situacao != 'Cancelado'";
    $params = [$usuarioId];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo json_encode(['sucesso' => false, 'message' => 'Erro na consulta de pedidos']);
        exit;
    }

    $resultado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $totalPedidos = $resultado['total_pedidos'];

    if ($totalPedidos > 0) {
        echo json_encode(['sucesso' => false, 'message' => 'Não é possível inativar usuários com pedidos em andamento!']);
        exit;
    }

    // Marca como arquivado sem alterar a estrutura do banco
    $sql = "UPDATE Usuario SET ativo = 0, data_ultimo_estado = GETDATE() WHERE usuario_id = ?";
    $params = [$usuarioId];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo json_encode(['sucesso' => true, 'message' => 'Operação concluída']);
    } else {
        echo json_encode(['sucesso' => false, 'message' => 'Erro na operação']);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>