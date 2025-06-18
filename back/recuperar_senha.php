<?php
session_start();
require_once 'conexao_sqlserver.php';
require_once 'valida_campo_obrigatorio_back.php';

// Definir cabeçalho para JSON
header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Capturar e validar dados do formulário
    $email = campoObrigatorio('email', 'Email');
    $pergunta_id = campoObrigatorio('pergunta_id', 'Pergunta de Segurança');
    $nova_senha = campoObrigatorio('novaSenha', 'Nova Senha');
    $confirmar_senha = campoObrigatorio('confirmarSenha', 'Confirmação de Senha');
    
    // Validar se as senhas coincidem
    if ($nova_senha !== $confirmar_senha) {
        echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
        exit;
    }
    
    // Validar complexidade da senha
    if (strlen($nova_senha) < 9 || strlen($nova_senha) > 15) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter entre 9 e 15 caracteres']);
        exit;
    }
    
    // Validar se a senha contém pelo menos uma letra maiúscula, um número e um caractere especial
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,15}$/', $nova_senha)) {
        echo json_encode(['success' => false, 'message' => 'A senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial']);
        exit;
    }
    
    // Verificar se o usuário existe e está ativo
    $sql_verificar = "SELECT usuario_id FROM Usuario WHERE email = ? AND ativo = 1";
    $params_verificar = array($email);
    $stmt_verificar = sqlsrv_query($conn, $sql_verificar, $params_verificar);
    
    if ($stmt_verificar === false) {
        echo json_encode(['success' => false, 'message' => 'Erro ao consultar banco de dados']);
        exit;
    }
    
    $usuario = sqlsrv_fetch_array($stmt_verificar, SQLSRV_FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }
    
    // Hash da nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar a senha do usuário
    $sql_atualizar = "UPDATE Usuario SET senha = ? WHERE email = ? AND ativo = 1";
    $params_atualizar = array($senha_hash, $email);
    $stmt_atualizar = sqlsrv_query($conn, $sql_atualizar, $params_atualizar);
    
    if ($stmt_atualizar === false) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar senha']);
        exit;
    }
    
    // Verificar se a atualização foi bem-sucedida
    $rows_affected = sqlsrv_rows_affected($stmt_atualizar);
    
    if ($rows_affected > 0) {
        // Sucesso
        echo json_encode(['success' => true, 'message' => 'Senha atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração foi realizada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

sqlsrv_close($conn);
?> 