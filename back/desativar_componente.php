<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['componente_id'] ?? null;

if ($id) {

    //Verifica se o componente está vinculado a etapas de produção ativas
    $sql = "SELECT COUNT(*) as total_etapas FROM Componente c JOIN Etapa_Producao ep ON ep.fk_componente = c.componente_id WHERE c.componente_id = ? AND ep.ativo = 1";
    $params = [$id];
    $stmt = sqlsrv_query($conn, $sql, $params);
    $resultado = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $totalEtapas = $resultado['total_etapas'];

    if ($totalEtapas > 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Não é possível inativar componentes vinculados a etapas de produção ativas!']);
        exit;
    }

    $sql = "UPDATE Componente SET ativo = 0 WHERE componente_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    echo json_encode(["sucesso" => $stmt !== false]);
} else {
    echo json_encode(["sucesso" => false]);
}
?>