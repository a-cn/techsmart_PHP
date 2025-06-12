<?php

// Define o cabeçalho da resposta como JSON

header('Content-Type: application/json');
// Configurações para exibir todos os erros (útil para desenvolvimento)

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclui os arquivos necessários

require __DIR__ . '/conexao_sqlserver.php';
require __DIR__ . '/verifica_sessao.php';

    // Verifica se o usuário está autenticado (sessão existe)

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não autenticado");
    }

    // Consulta SQL para buscar os dados do usuário

    $sql = "SELECT 
                u.usuario_id,
                u.nome,
                u.cpf_cnpj,
                CONVERT(VARCHAR(10), u.data_nascimento, 23) AS data_nascimento,
                u.email,
                u.num_principal,
                u.num_recado,
                t.descricao AS tipo_usuario,
                e.cep,
                e.logradouro,
                e.numero,
                e.complemento,
                e.bairro,
                CONCAT(e.cidade, '/', e.estado) AS cidade_estado
            FROM Usuario u
            INNER JOIN Tipo_Usuario t ON u.fk_tipo_usuario = t.tipo_usuario_id
            INNER JOIN Endereco e ON u.fk_endereco = e.endereco_id
            WHERE u.usuario_id = ? AND u.ativo = 1";  //Filtra pelo ID do usuário na sessão e só ativos

        // Parâmetros para a consulta (ID do usuário da sessão)

    $params = [$_SESSION['usuario_id']];
        // Executa a consulta no SQL Server

    $stmt = sqlsrv_query($conn, $sql, $params);
        // Tratamento de erro na execução da consulta

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        error_log("ERRO SQL: " . print_r($errors, true)); // Loga o erro no servidor
        throw new Exception("Falha na consulta ao banco de dados");
    }
        // Obtém os dados do resultado da consulta

    $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        // Verifica se encontrou dados

    if (empty($data)) {
        throw new Exception("Nenhum dado encontrado para este usuário");
    }

    // Formata os dados para o frontend
    $response = [
        'status' => 'success',
        'data' => [
            'nome' => $data['nome'],
            'email' => $data['email'],
            'tipo' => $data['tipo_usuario'],
            'cpf-cnpj' => $data['cpf_cnpj'],
            'data_nascimento' => $data['data_nascimento'],
            'num-principal' => $data['num_principal'],
            'num-recado' => $data['num_recado'],
            'cep' => $data['cep'],
            'logradouro' => $data['logradouro'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'],
            'bairro' => $data['bairro'],
            'cidade-estado' => $data['cidade_estado']
        ]
    ];

        // Retorna a resposta em formato JSON

    echo json_encode($response);
    exit;
        // Em caso de erro, retorna status 500 e a mensagem de erro

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>