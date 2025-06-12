<?php
require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Garantindo autenticação

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pedido_id'])) {
    try {
        $pedido_id = $_GET['pedido_id'];
        $usuario_id = $_SESSION['usuario_id'];

        // Verifica a situação do pedido
        $sql = "SELECT situacao 
                FROM Pedido 
                WHERE pedido_id = ? 
                AND fk_usuario = ? 
                AND ativo = 1";

        $stmt = sqlsrv_query($conn, $sql, [$pedido_id, $usuario_id]);

        if ($stmt === false) {
            throw new Exception("Erro ao verificar situação do pedido");
        }

        $pedido = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou sem permissão para acessar");
        }

        echo json_encode([
            'sucesso' => true,
            'situacao' => $pedido['situacao']
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
        'erro' => 'Pedido não especificado'
    ]);
}
?> 