<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['componente_id'] ?? null;

if ($id) {
    $sql = "UPDATE Componente SET ativo = 1 WHERE componente_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    echo json_encode(["sucesso" => $stmt !== false]);
} else {
    echo json_encode(["sucesso" => false]);
}
?>