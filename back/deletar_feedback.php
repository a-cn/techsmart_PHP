<?php
require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

if (!isset($_POST['feedback_id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID do feedback não fornecido']);
    exit;
}

$feedback_id = (int)$_POST['feedback_id'];
$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Apenas clientes podem excluir feedbacks
if ($tipo_usuario !== 'cliente') {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas clientes podem excluir feedbacks']);
    exit;
}

try {
    if (!$conn) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    // Verifica se o feedback existe e pertence ao cliente
    $sql_verificacao = "SELECT f.feedback_id, f.fk_pedido 
                       FROM Feedback f
                       JOIN Pedido p ON f.fk_pedido = p.pedido_id
                       WHERE f.feedback_id = ? 
                       AND p.fk_usuario = ? 
                       AND f.ativo = 1";
    
    $stmt_verificacao = sqlsrv_query($conn, $sql_verificacao, [$feedback_id, $usuario_id]);
    
    if ($stmt_verificacao === false) {
        throw new Exception("Erro ao verificar o feedback");
    }

    $feedback = sqlsrv_fetch_array($stmt_verificacao, SQLSRV_FETCH_ASSOC);
    if (!$feedback) {
        throw new Exception("Feedback não encontrado ou sem permissão para excluir");
    }

    // Executa a exclusão
    $sql_delete = "DELETE FROM Feedback WHERE feedback_id = ?";
    $stmt_delete = sqlsrv_query($conn, $sql_delete, [$feedback_id]);

    if ($stmt_delete === false) {
        throw new Exception("Erro ao excluir o feedback");
    }

    // Libera os recursos
    sqlsrv_free_stmt($stmt_verificacao);
    sqlsrv_free_stmt($stmt_delete);
    sqlsrv_close($conn);

    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Feedback excluído com sucesso',
        'pedido_id' => $feedback['fk_pedido'] // Retorna o ID do pedido para possível redirecionamento
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
