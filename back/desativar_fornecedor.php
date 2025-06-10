<?php
require_once 'conexao_sqlserver.php'; //Conexão com o banco
require_once 'verifica_sessao.php'; //Garante sessão ativa e válida

//Lê o corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

//Verifica se o ID foi enviado
if (!isset($input['fornecedor_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID do fornecedor não fornecido.']);
    exit;
}

$fornecedor_id = (int) $input['fornecedor_id'];

//Atualiza o status do fornecedor para inativo (0)
$sql = "UPDATE Fornecedor SET ativo = 0 WHERE fornecedor_id = ?";
$params = [$fornecedor_id];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao desativar fornecedor.', 'erro' => sqlsrv_errors()]);
} else {
    echo json_encode(['sucesso' => true]);
}

sqlsrv_close($conn);
?>