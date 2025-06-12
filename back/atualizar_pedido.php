<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

// Garante que é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Verifica se os dados necessários foram enviados
if (!isset($_POST['pedido_id'], $_POST['situacao'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Dados incompletos']);
    exit();
}

header('Content-Type: application/json');

try {
    $pedido_id = $_POST['pedido_id'];
    $situacao = $_POST['situacao'];
    
    $sql = "UPDATE Pedido SET situacao = ? WHERE pedido_id = ?";
    $params = array($situacao, $pedido_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        throw new Exception("Erro na atualização: " . print_r(sqlsrv_errors(), true));
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 