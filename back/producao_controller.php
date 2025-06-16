<?php
header('Content-Type: application/json');
require_once 'conexao_sqlserver.php';
require_once 'verifica_sessao.php';

// Função auxiliar para formatar datas em ISO 8601
function formatarDataISO($datetime) {
    try {
        if ($datetime instanceof DateTime) {
            return $datetime->format('d/m/Y, H:i');
        }
        
        if (is_array($datetime) && isset($datetime['date'])) {
            $date = new DateTime($datetime['date']);
            return $date->format('d/m/Y, H:i');
        }
        
        // Se for uma string de data do SQL Server
        if (is_string($datetime)) {
            $date = new DateTime($datetime);
            return $date->format('d/m/Y, H:i');
        }
        
        return null;
    } catch (Exception $e) {
        error_log('Erro ao formatar data: ' . $e->getMessage());
        return null;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'listar_linhas':
            listarLinhasProducao($conn);
            break;
        case 'listar_produtos':
            listarProdutosFinais($conn);
            break;
        case 'iniciar':
            iniciarProducao($conn);
            break;
        case 'concluir_etapa':
            concluirEtapa($conn);
            break;
        case 'buscar_producao_historico':
            buscarProducaoHistorico($conn);
            break;
        default:
            echo json_encode(['status' => 'erro', 'mensagem' => 'Ação inválida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no servidor: ' . $e->getMessage()]);
}

function listarLinhasProducao($conn) {
    $sql = "SELECT producao_id, nome FROM Producao WHERE ativo = 1";
    $stmt = sqlsrv_query($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Erro na consulta de linhas de produção: ' . print_r(sqlsrv_errors(), true));
    }
    
    $linhas = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $linhas[] = $row;
    }
    
    echo json_encode($linhas);
}

function listarProdutosFinais($conn) {
    $producaoId = $_GET['producao_id'] ?? 0;
    
    if (!$producaoId) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'ID da produção não informado']);
        return;
    }
    
    $sql = "SELECT produtofinal_id, nome, tempo_producao_dias 
            FROM ProdutoFinal 
            WHERE fk_producao = ? AND ativo = 1";
    $stmt = sqlsrv_query($conn, $sql, [$producaoId]);
    
    if (!$stmt) {
        throw new Exception('Erro na consulta de produtos finais: ' . print_r(sqlsrv_errors(), true));
    }
    
    $produtos = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $produtos[] = $row;
    }
    
    echo json_encode($produtos);
}

function iniciarProducao($conn) {
    $producaoId = $_POST['producao_id'] ?? 0;
    $produtoId = $_POST['produto_id'] ?? 0;
    $quantidade = $_POST['quantidade'] ?? 0;
    
    if (!$producaoId || !$produtoId || !$quantidade) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos']);
        return;
    }
    
    // Obtém informações do produto (incluindo tempo de produção)
    $sqlProduto = "SELECT tempo_producao_dias FROM ProdutoFinal WHERE produtofinal_id = ?";
    $stmtProduto = sqlsrv_query($conn, $sqlProduto, [$produtoId]);
    
    if (!$stmtProduto || !sqlsrv_has_rows($stmtProduto)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Produto não encontrado']);
        return;
    }
    
    $produto = sqlsrv_fetch_array($stmtProduto, SQLSRV_FETCH_ASSOC);
    $diasProducao = $produto['tempo_producao_dias'];
    
    // Inicia a transação
    sqlsrv_begin_transaction($conn);
    
    try {
        // Insere no histórico de produção usando GETDATE() para data_inicio e DATEADD para data_previsao
        $sqlInsert = "INSERT INTO Historico_Producao 
                     (fk_producao, quantidade_produto, data_inicio, data_previsao, data_conclusao, ultima_etapa) 
                     VALUES (?, ?, GETDATE(), DATEADD(day, ?, GETDATE()), NULL, NULL);
                     SELECT SCOPE_IDENTITY() AS historico_producao_id;";
        
        $stmtInsert = sqlsrv_query($conn, $sqlInsert, [$producaoId, $quantidade, $diasProducao]);
        
        if (!$stmtInsert) {
            throw new Exception('Erro ao iniciar produção: ' . print_r(sqlsrv_errors(), true));
        }
        
        // Obtém o ID da produção iniciada
        sqlsrv_next_result($stmtInsert);
        $rowId = sqlsrv_fetch_array($stmtInsert, SQLSRV_FETCH_ASSOC);
        $historicoId = $rowId['historico_producao_id'];
        
        if (!$historicoId) {
            throw new Exception('Erro ao obter ID da produção iniciada');
        }
        
        // Busca as etapas da produção
        $sqlEtapas = "SELECT etapa_producao_id, ordem, nome, fk_componente 
                     FROM Etapa_Producao 
                     WHERE fk_producao = ? AND ativo = 1 
                     ORDER BY ordem";
        $stmtEtapas = sqlsrv_query($conn, $sqlEtapas, [$producaoId]);
        
        if (!$stmtEtapas) {
            throw new Exception('Erro ao buscar etapas: ' . print_r(sqlsrv_errors(), true));
        }
        
        $etapas = [];
        while ($etapa = sqlsrv_fetch_array($stmtEtapas, SQLSRV_FETCH_ASSOC)) {
            // Busca o nome do componente
            $componenteNome = 'Nenhum';
            if ($etapa['fk_componente']) {
                $sqlComponente = "SELECT nome FROM Componente WHERE componente_id = ?";
                $stmtComponente = sqlsrv_query($conn, $sqlComponente, [$etapa['fk_componente']]);
                if ($stmtComponente && sqlsrv_has_rows($stmtComponente)) {
                    $componente = sqlsrv_fetch_array($stmtComponente, SQLSRV_FETCH_ASSOC);
                    $componenteNome = $componente['nome'];
                }
            }
            
            $etapas[] = [
                'etapa_producao_id' => $etapa['etapa_producao_id'],
                'ordem' => $etapa['ordem'],
                'nome_etapa' => $etapa['nome'],
                'componente' => $componenteNome,
                'concluida' => false
            ];
        }
        
        // Commit da transação
        sqlsrv_commit($conn);
        
        // Obtém os dados atualizados da produção
        $sqlProducao = "SELECT * FROM Historico_Producao WHERE historico_producao_id = ?";
        $stmtProducao = sqlsrv_query($conn, $sqlProducao, [$historicoId]);
        $producaoRaw = sqlsrv_fetch_array($stmtProducao, SQLSRV_FETCH_ASSOC);

        // Formata as datas no padrão ISO 8601 para o JavaScript
        $producao = [
            'historico_producao_id' => $producaoRaw['historico_producao_id'],
            'fk_producao' => $producaoRaw['fk_producao'],
            'quantidade_produto' => $producaoRaw['quantidade_produto'],
            'ultima_etapa' => $producaoRaw['ultima_etapa'],
            'data_inicio' => formatarDataISO($producaoRaw['data_inicio']),
            'data_previsao' => formatarDataISO($producaoRaw['data_previsao']),
            'data_conclusao' => $producaoRaw['data_conclusao'] ? formatarDataISO($producaoRaw['data_conclusao']) : null
        ];
        
        echo json_encode([
            'status' => 'ok',
            'producao' => $producao,
            'etapas' => $etapas
        ]);
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
    }
}

function concluirEtapa($conn) {
    $historicoId = $_POST['historico_id'] ?? 0;
    $etapaId = $_POST['etapa_id'] ?? 0;
    
    if (!$historicoId || !$etapaId) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos: historico_id=' . $historicoId . ', etapa_id=' . $etapaId]);
        return;
    }
    
    // Inicia a transação
    sqlsrv_begin_transaction($conn);
    
    try {
        // 1. Primeiro obtém a ordem da etapa atual
        $sqlEtapaAtual = "SELECT ordem, fk_producao FROM Etapa_Producao WHERE etapa_producao_id = ?";
        $stmtEtapaAtual = sqlsrv_query($conn, $sqlEtapaAtual, [$etapaId]);
        
        if (!$stmtEtapaAtual || !sqlsrv_has_rows($stmtEtapaAtual)) {
            throw new Exception('Etapa não encontrada');
        }
        
        $etapaAtual = sqlsrv_fetch_array($stmtEtapaAtual, SQLSRV_FETCH_ASSOC);
        $ordemAtual = $etapaAtual['ordem'];
        $producaoId = $etapaAtual['fk_producao'];

        // 2. Atualiza o histórico com a ordem da última etapa concluída
        $sqlUpdate = "UPDATE Historico_Producao 
                     SET ultima_etapa = ?
                     WHERE historico_producao_id = ?";
        $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, [$ordemAtual, $historicoId]);
        
        if (!$stmtUpdate) {
            throw new Exception('Erro ao atualizar histórico: ' . print_r(sqlsrv_errors(), true));
        }
        
        // 3. Verifica se esta é a última etapa
        $sqlProximaEtapa = "SELECT COUNT(*) AS total 
                           FROM Etapa_Producao 
                           WHERE fk_producao = ? AND ordem > ? AND ativo = 1";
        $stmtProximaEtapa = sqlsrv_query($conn, $sqlProximaEtapa, [$producaoId, $ordemAtual]);
        $proximaEtapa = sqlsrv_fetch_array($stmtProximaEtapa, SQLSRV_FETCH_ASSOC);
        
        $producaoConcluida = false;
        if ($proximaEtapa['total'] == 0) {
            // Esta é a última etapa - marca a produção como concluída
            $sqlConcluir = "UPDATE Historico_Producao 
                           SET data_conclusao = GETDATE()
                           WHERE historico_producao_id = ?";
            $stmtConcluir = sqlsrv_query($conn, $sqlConcluir, [$historicoId]);
            
            if (!$stmtConcluir) {
                throw new Exception('Erro ao concluir produção: ' . print_r(sqlsrv_errors(), true));
            }

            // 4. Atualiza a quantidade do produto final
            $sqlAtualizarEstoque = "UPDATE pf
                                  SET pf.quantidade = pf.quantidade + hp.quantidade_produto
                                  FROM ProdutoFinal pf
                                  INNER JOIN Historico_Producao hp ON hp.fk_producao = pf.fk_producao
                                  WHERE hp.historico_producao_id = ?";
            $stmtAtualizarEstoque = sqlsrv_query($conn, $sqlAtualizarEstoque, [$historicoId]);
            
            if (!$stmtAtualizarEstoque) {
                throw new Exception('Erro ao atualizar estoque: ' . print_r(sqlsrv_errors(), true));
            }
            
            $producaoConcluida = true;
        }
        
        // Commit da transação
        sqlsrv_commit($conn);
        
        // Obtém os dados atualizados da produção
        $sqlProducao = "SELECT * FROM Historico_Producao WHERE historico_producao_id = ?";
        $stmtProducao = sqlsrv_query($conn, $sqlProducao, [$historicoId]);
        $producaoRaw = sqlsrv_fetch_array($stmtProducao, SQLSRV_FETCH_ASSOC);

        $producao = [
            'historico_producao_id' => $producaoRaw['historico_producao_id'],
            'fk_producao' => $producaoRaw['fk_producao'],
            'quantidade_produto' => $producaoRaw['quantidade_produto'],
            'ultima_etapa' => $producaoRaw['ultima_etapa'],
            'data_inicio' => formatarDataISO($producaoRaw['data_inicio']),
            'data_previsao' => formatarDataISO($producaoRaw['data_previsao']),
            'data_conclusao' => $producaoRaw['data_conclusao'] ? formatarDataISO($producaoRaw['data_conclusao']) : null
        ];
        
        echo json_encode([
            'status' => 'ok',
            'mensagem' => $producaoConcluida ? 'Produção concluída com sucesso e estoque atualizado!' : 'Etapa concluída com sucesso!',
            'producao' => $producao
        ]);
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
    }
}

function buscarProducaoHistorico($conn) {
    $historicoId = $_GET['historico_id'] ?? 0;
    
    if (!$historicoId) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'ID do histórico não informado']);
        return;
    }
    
    // Busca os dados da produção no histórico
    $sql = "SELECT 
                hp.*,
                p.nome as nome_producao,
                pf.nome as nome_produto,
                pf.produtofinal_id
            FROM Historico_Producao hp
            INNER JOIN Producao p ON p.producao_id = hp.fk_producao
            LEFT JOIN ProdutoFinal pf ON pf.fk_producao = p.producao_id
            WHERE hp.historico_producao_id = ?";
            
    $stmt = sqlsrv_query($conn, $sql, [$historicoId]);
    
    if (!$stmt || !sqlsrv_has_rows($stmt)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Produção não encontrada']);
        return;
    }
    
    $producao = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    // Busca as etapas da produção
    $sqlEtapas = "SELECT 
                    e.etapa_producao_id,
                    e.ordem,
                    e.nome as nome_etapa,
                    c.nome as nome_componente,
                    CASE 
                        WHEN e.ordem <= ? THEN 1
                        ELSE 0
                    END as concluida
                FROM Etapa_Producao e
                LEFT JOIN Componente c ON c.componente_id = e.fk_componente
                WHERE e.fk_producao = ? AND e.ativo = 1
                ORDER BY e.ordem";
                
    $stmtEtapas = sqlsrv_query($conn, $sqlEtapas, [$producao['ultima_etapa'], $producao['fk_producao']]);
    
    if (!$stmtEtapas) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar etapas: ' . print_r(sqlsrv_errors(), true)]);
        return;
    }
    
    $etapas = [];
    while ($etapa = sqlsrv_fetch_array($stmtEtapas, SQLSRV_FETCH_ASSOC)) {
        $etapas[] = [
            'etapa_producao_id' => $etapa['etapa_producao_id'],
            'ordem' => $etapa['ordem'],
            'nome_etapa' => $etapa['nome_etapa'],
            'componente' => $etapa['nome_componente'] ?? 'Nenhum',
            'concluida' => (bool)$etapa['concluida']
        ];
    }
    
    // Formata as datas
    $producaoFormatada = [
        'historico_producao_id' => $producao['historico_producao_id'],
        'fk_producao' => $producao['fk_producao'],
        'quantidade_produto' => $producao['quantidade_produto'],
        'ultima_etapa' => $producao['ultima_etapa'],
        'data_inicio' => formatarDataISO($producao['data_inicio']),
        'data_previsao' => formatarDataISO($producao['data_previsao']),
        'data_conclusao' => $producao['data_conclusao'] ? formatarDataISO($producao['data_conclusao']) : null,
        'nome_producao' => $producao['nome_producao'],
        'nome_produto' => $producao['nome_produto'],
        'produtofinal_id' => $producao['produtofinal_id']
    ];
    
    echo json_encode([
        'status' => 'ok',
        'producao' => $producaoFormatada,
        'etapas' => $etapas
    ]);
}
?>