<?php
require_once 'conexao_sqlserver.php'; //Conexão com o banco
require_once 'verifica_sessao.php'; //Garante que o usuário está logado

header('Content-Type: application/json'); //Define o tipo de resposta como JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['avaliacao'], $_POST['observacao'])) {
    try {
        $feedback_id = htmlspecialchars(trim($_POST['feedback_id']));
        $avaliacao = (int) $_POST['avaliacao'];
        $observacao = htmlspecialchars(trim($_POST['observacao']));

        if (!$conn || !is_resource($conn)) {
            throw new Exception("Erro na conexão com o banco de dados");
        }

        // Define a data e hora atuais no fuso horário correto
        date_default_timezone_set('America/Sao_Paulo');
        $data_hora = date('Y-m-d H:i:s');

        // Verifica se o usuário tem permissão para alterar este feedback
        $usuario_id = $_SESSION['usuario_id'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Se for cliente, verifica se o feedback pertence a ele
        if ($tipo_usuario === 'cliente') {
            $sql_verificacao = "SELECT f.feedback_id 
                               FROM Feedback f
                               JOIN Pedido p ON f.fk_pedido = p.pedido_id
                               WHERE f.feedback_id = ? AND p.fk_usuario = ? AND f.ativo = 1";
            $stmt_verificacao = sqlsrv_query($conn, $sql_verificacao, [$feedback_id, $usuario_id]);
            
            if (!$stmt_verificacao || !sqlsrv_fetch_array($stmt_verificacao, SQLSRV_FETCH_ASSOC)) {
                throw new Exception("Você não tem permissão para alterar este feedback");
            }
        }

        // Atualiza o feedback com a nova data/hora
        $sql = "UPDATE Feedback 
                SET avaliacao = ?, 
                    observacao = ?, 
                    data_hora = CONVERT(datetime, ?, 120) 
                WHERE feedback_id = ?";
        $params = [$avaliacao, $observacao, $data_hora, $feedback_id];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Erro ao atualizar o feedback no banco de dados");
        }

        //Libera e fecha a conexão
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Feedback atualizado com sucesso!'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'erro' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Dados incompletos ou método inválido'
    ]);
}
?>