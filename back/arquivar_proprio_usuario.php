<?php
require_once 'conexao_sqlserver.php'; //Conexão com o banco
require_once 'verifica_sessao.php'; //Garante sessão ativa e válida

header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'];

//Desativa o próprio usuário logado na sessão
$sql = "UPDATE Usuario SET ativo = 0 WHERE usuario_id = ?";
$params = [$usuario_id];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['sucesso' => false, 'message' => 'Erro ao desativar conta.']);
} else {
    session_destroy();
    echo json_encode(['sucesso' => true, 'message' => 'Conta desativada com sucesso.']);
}
?>