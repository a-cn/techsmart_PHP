<?php
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';
$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da produção inválido.']);
    exit;
}

if ($acao === 'iniciar') {
    $dias = intval($_POST['dias'] ?? 0);
    if ($dias <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Quantidade de dias inválida.']);
        exit;
    }

    $data_inicio = date('Y-m-d');
    $data_previsao = date('Y-m-d', strtotime("+$dias days"));

    $sql = "INSERT INTO Historico_Producao (fk_producao, data_inicio, data_previsao) VALUES (?, ?, ?)";
    $stmt = sqlsrv_query($conn, $sql, [$id, $data_inicio, $data_previsao]);

    if ($stmt === false) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao iniciar produção', 'detalhes' => sqlsrv_errors()]);
    } else {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produção iniciada com sucesso.']);
    }

} elseif ($acao === 'concluir_etapa') {
    // Buscar a próxima etapa ativa e não concluída
    $sql = "SELECT etapa_producao_id, nome FROM Etapa_Producao 
            WHERE fk_producao = ? AND ativo = 1 AND data_conclusao IS NULL
            ORDER BY etapa_producao_id ASC";

    $stmt = sqlsrv_query($conn, $sql, [$id]);
    if ($stmt === false) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar etapas', 'detalhes' => sqlsrv_errors()]);
        exit;
    }

    $etapa = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$etapa) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhuma etapa pendente para esta produção.']);
        exit;
    }

    $etapa_id = $etapa['etapa_producao_id'];
    $nome_etapa = $etapa['nome'];

    // Marcar a etapa como concluída
    $updateEtapa = "UPDATE Etapa_Producao SET data_conclusao = GETDATE() WHERE etapa_producao_id = ?";
    $resEtapa = sqlsrv_query($conn, $updateEtapa, [$etapa_id]);

    // Atualizar a última etapa no registro da produção
    $updateUltima = "UPDATE Producao SET ultima_etapa = ? WHERE producao_id = ?";
    $resUltima = sqlsrv_query($conn, $updateUltima, [$nome_etapa, $id]);

    if ($resEtapa === false || $resUltima === false) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao concluir etapa', 'detalhes' => sqlsrv_errors()]);
    } else {
        echo json_encode(['status' => 'sucesso', 'mensagem' => "Etapa '$nome_etapa' concluída com sucesso."]);
    }

} elseif ($acao === 'finalizar') {
    $sql = "UPDATE Historico_Producao SET data_conclusao = GETDATE() 
            WHERE fk_producao = ? AND data_conclusao IS NULL";

    $stmt = sqlsrv_query($conn, $sql, [$id]);

    if ($stmt === false) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao finalizar produção', 'detalhes' => sqlsrv_errors()]);
    } else {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produção finalizada com sucesso.']);
    }

} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Ação inválida']);
}
