<?php
header('Content-Type: application/json');

// Conexão com o banco
require_once 'conexao_sqlserver.php';

// Pega o ID do usuário a ser "excluído"
$input = json_decode(file_get_contents('php://input'), true);
$usuarioId = $input['usuario_id'];

// Marca como arquivado sem alterar a estrutura do banco
$sql = "UPDATE Usuario SET ativo = 0 WHERE usuario_id = ?";
$params = [$usuarioId];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(['sucesso' => true, 'message' => 'Operação concluída']);
} else {
    echo json_encode(['sucesso' => false, 'message' => 'Erro na operação']);
}
?>