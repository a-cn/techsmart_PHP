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

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email é obrigatório']);
    exit;
}

try {
    // Primeiro, vamos verificar se o usuário existe
    $sql_check = "SELECT usuario_id, fk_pergunta_seguranca FROM Usuario WHERE email = ? AND ativo = 1";
    $params_check = array($email);
    $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);
    
    if ($stmt_check === false) {
        $errors = sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => 'Erro ao verificar usuário: ' . $errors[0]['message']]);
        exit;
    }
    
    $user = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email não encontrado no sistema']);
        exit;
    }
    
    // Se o usuário não tem pergunta de segurança definida
    if ($user['fk_pergunta_seguranca'] === null) {
        echo json_encode(['success' => false, 'message' => 'Usuário não possui pergunta de segurança cadastrada']);
        exit;
    }
    
    // Buscar a pergunta de segurança
    $sql = "SELECT pergunta_seguranca_id, pergunta 
            FROM Pergunta_Seguranca 
            WHERE pergunta_seguranca_id = ?";
    
    $params = array($user['fk_pergunta_seguranca']);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar pergunta: ' . $errors[0]['message']]);
        exit;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if ($row) {
        echo json_encode([
            'success' => true,
            'pergunta_id' => $row['pergunta_seguranca_id'],
            'pergunta' => $row['pergunta']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pergunta de segurança não encontrada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

sqlsrv_close($conn);
?> 