<?php
require_once '../back/conexao_sqlserver.php';
require_once '../back/verifica_sessao.php';
require_once '../back/funcoes_sessao.php';
$loginTimestamp = time(); // Mantendo controle de sessão

// Buscar todas as produções ativas
$sql = "
    SELECT p.producao_id, p.nome AS nome_producao, hp.data_inicio, hp.data_previsao, hp.data_conclusao,
            COUNT(ep.etapa_producao_id) AS qtd_etapas,
            STRING_AGG(ep.nome, ', ') AS etapas
    FROM Producao p
    LEFT JOIN Historico_Producao hp ON hp.fk_producao = p.producao_id AND hp.data_conclusao IS NULL
    LEFT JOIN Etapa_Producao ep ON ep.fk_producao = p.producao_id AND ep.ativo = 1
    WHERE p.ativo = 1
    GROUP BY p.producao_id, p.nome, hp.data_inicio, hp.data_previsao, hp.data_conclusao
";

$result = sqlsrv_query($conn, $sql);
$producoes = [];
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $producoes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Produção</title>
    <link rel="stylesheet" href="css/janelas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="janela-consulta">
    <h2>Produções Ativas</h2>
    <button onclick="iniciarProducao()">Nova Produção</button>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Nome da Produção</th>
                <th>Qtd Etapas</th>
                <th>Etapas</th>
                <th>Data Início</th>
                <th>Previsão Término</th>
                <th>Data Conclusão</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($producoes as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome_producao']) ?></td>
                <td><?= $p['qtd_etapas'] ?></td>
                <td><?= htmlspecialchars($p['etapas']) ?></td>
                <td><?= $p['data_inicio'] ? $p['data_inicio']->format('d/m/Y') : '-' ?></td>
                <td><?= $p['data_previsao'] ? $p['data_previsao']->format('d/m/Y') : '-' ?></td>
                <td><?= $p['data_conclusao'] ? $p['data_conclusao']->format('d/m/Y') : '-' ?></td>
                <td>
                    <?php if (!$p['data_inicio']): ?>
                        <button onclick="definirInicio(<?= $p['producao_id'] ?>)">Iniciar</button>
                    <?php elseif (!$p['data_conclusao']): ?>
                        <button onclick="concluirEtapa(<?= $p['producao_id'] ?>)">Concluir Etapa</button>
                        <button onclick="finalizarProducao(<?= $p['producao_id'] ?>)">Finalizar</button>
                    <?php else: ?>
                        Produção Finalizada
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function definirInicio(id) {
    const dias = prompt("Informe o tempo estimado de produção (em dias):");
    if (dias && !isNaN(dias)) {
        $.post('../back/acoes_producao.php', { acao: 'iniciar', id: id, dias: dias }, function(res) {
            location.reload();
        });
    }
}

function concluirEtapa(id) {
    $.post('../back/acoes_producao.php', { acao: 'concluir_etapa', id: id }, function(res) {
        location.reload();
    });
}

function finalizarProducao(id) {
    if (confirm("Deseja finalizar esta produção?")) {
        $.post('../back/acoes_producao.php', { acao: 'finalizar', id: id }, function(res) {
            location.reload();
        });
    }
}

function iniciarProducao() {
    window.location.href = 'index.php?pg=producao';
}
</script>
</body>
</html>
