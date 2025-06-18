<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexao_sqlserver.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$email = $_POST['email'] ?? '';
$resposta = $_POST['resposta'] ?? '';

if (empty($email) || empty($resposta)) {
    echo json_encode(['success' => false, 'message' => 'Email e resposta são obrigatórios']);
    exit;
}

try {
    // Buscar o usuário e validar a resposta
    $sql = "SELECT usuario_id, resposta_seguranca 
            FROM Usuario 
            WHERE email = ? AND ativo = 1";
    
    $params = array($email);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Erro ao consultar banco de dados']);
        exit;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if ($row) {
        // Comparar a resposta (case-insensitive)
        if (trim(hash('sha256', $resposta)) === trim($row['resposta_seguranca'])) {
            echo json_encode(['success' => true, 'message' => 'Resposta correta']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resposta incorreta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

sqlsrv_close($conn);
?> 