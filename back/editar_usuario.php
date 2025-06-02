<?php
// DEVE SER A PRIMEIRA LINHA DO ARQUIVO
header('Content-Type: application/json');

// Configuração de erro para desenvolvimento (remova em produção)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Função para lidar com erros
function handleError($message, $code = 500) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

try {
    // Verifica método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Método não permitido', 405);
    }

    // Recebe os dados JSON
    $jsonInput = file_get_contents('php://input');
    if ($jsonInput === false) {
        handleError('Erro ao ler dados de entrada');
    }

    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        handleError('JSON inválido: ' . json_last_error_msg(), 400);
    }

    // Validação básica
    if (empty($data['usuario_id'])) {
        handleError('ID do usuário é obrigatório', 400);
    }

    require_once 'conexao_sqlserver.php';

    // Inicia transação
    if (sqlsrv_begin_transaction($conn) === false) {
        handleError('Falha ao iniciar transação');
    }

    // 1. Atualiza Endereço
    $sqlEndereco = "UPDATE Endereco SET 
                    cep = ?, 
                    logradouro = ?, 
                    complemento = ? 
                    WHERE endereco_id = (
                        SELECT fk_endereco FROM Usuario WHERE usuario_id = ?
                    )";
    
    $paramsEndereco = [
        $data['cep'] ?? null,
        $data['logradouro'] ?? null,
        $data['complemento'] ?? null,
        $data['usuario_id']
    ];
    
    $stmtEndereco = sqlsrv_query($conn, $sqlEndereco, $paramsEndereco);
    if ($stmtEndereco === false) {
        sqlsrv_rollback($conn);
        handleError('Erro ao atualizar endereço: ' . print_r(sqlsrv_errors(), true));
    }

    // 2. Atualiza Usuário
    $sqlUsuario = "UPDATE Usuario SET 
                  nome = ?, 
                  cpf_cnpj = ?, 
                  email = ?, 
                  num_principal = ?, 
                  num_recado = ? 
                  WHERE usuario_id = ?";
    
    $paramsUsuario = [
        $data['nome'],
        $data['cpf_cnpj'] ?? null,
        $data['email'],
        $data['num_principal'],
        $data['num_recado'] ?? null,
        $data['usuario_id']
    ];
    
    $stmtUsuario = sqlsrv_query($conn, $sqlUsuario, $paramsUsuario);
    if ($stmtUsuario === false) {
        sqlsrv_rollback($conn);
        handleError('Erro ao atualizar usuário: ' . print_r(sqlsrv_errors(), true));
    }

    // Commit da transação
    sqlsrv_commit($conn);

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Usuário atualizado com sucesso'
    ]);

} catch (Exception $e) {
    if (isset($conn) && sqlsrv_errors()) {
        sqlsrv_rollback($conn);
    }
    handleError('Erro: ' . $e->getMessage());
}
?>