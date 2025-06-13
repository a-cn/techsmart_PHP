<?php
header('Content-Type: application/json');
require_once 'conexao_sqlserver.php';

// Decodifica JSON do corpo da requisição, se necessário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
} else {
    $data = $_POST; // fallback para formulários normais
}

$acao = $_GET['action'] ?? $_GET['acao'] ?? '';
$id = $_GET['id'] ?? 0;

try {
    switch (strtolower($acao)) {
        case 'listar':
            $sql = "SELECT 
                    p.producao_id as id, 
                    p.nome as tipo,
                    (
                        SELECT STRING_AGG(e.nome + ' (Componente: ' + c.nome + ')', ', ')
                        FROM Etapa_Producao e
                        JOIN Componente c ON e.fk_componente = c.componente_id
                        WHERE e.fk_producao = p.producao_id AND e.ativo = 1
                    ) as etapas
                FROM Producao p
                WHERE p.ativo = 1";
                
            $stmt = sqlsrv_query($conn, $sql);
            
            if ($stmt === false) {
                throw new Exception("Erro SQL: " . print_r(sqlsrv_errors(), true));
            }
            
            $resultados = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $resultados[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados)
            ]);
            break;
            
 
        
        case 'obter':
    if (empty($_GET['id'])) {
        throw new Exception("ID da produção não informado");
    }
    $id = $_GET['id'];

    $sql = "SELECT 
                p.producao_id as id, 
                p.nome as tipo,
                (
                    SELECT STRING_AGG(
                        '{\"nome\":\"' + e.nome + '\",\"componenteId\":\"' + CAST(e.fk_componente AS VARCHAR) + '\"}', 
                        ','
                    ) WITHIN GROUP (ORDER BY e.ordem)
                    FROM Etapa_Producao e
                    WHERE e.fk_producao = p.producao_id AND e.ativo = 1
                ) as etapas_json
            FROM Producao p
            WHERE p.producao_id = ? AND p.ativo = 1";
    
    $stmt = sqlsrv_query($conn, $sql, array($id));
    if ($stmt === false) {
        throw new Exception("Erro ao obter produção: " . print_r(sqlsrv_errors(), true));
    }

    $producao = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$producao) {
        throw new Exception("Produção não encontrada");
    }

    $producao['etapas'] = $producao['etapas_json'] ? json_decode('[' . $producao['etapas_json'] . ']', true) : [];
    unset($producao['etapas_json']);

    echo json_encode($producao);
    break;

    case 'buscar_custos':
    // Busca os custos dos componentes
    $sql = "SELECT fc.fk_componente, fc.custo_componente 
            FROM Fornecedor_Componente fc
            INNER JOIN Componente c ON fc.fk_componente = c.componente_id
            WHERE c.ativo = 1";
    
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        throw new Exception("Erro ao buscar custos: " . print_r(sqlsrv_errors(), true));
    }
    
    $custos = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $custos[$row['fk_componente']] = $row['custo_componente'];
    }
    
    echo json_encode($custos);
    break;

    case 'incluir':
        // Validar campos obrigatórios
        if (empty($data['tipo'])) {
            throw new Exception("Tipo de produção não informado");
        }
    
        if (empty($data['etapas'])) {
            throw new Exception("Nenhuma etapa informada");
        }
    
        // Buscar custos dos componentes (como no buscar_custos)
        $sql = "SELECT fc.fk_componente, fc.custo_componente 
                FROM Fornecedor_Componente fc
                INNER JOIN Componente c ON fc.fk_componente = c.componente_id
                WHERE c.ativo = 1";
    
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            throw new Exception("Erro ao buscar custos: " . print_r(sqlsrv_errors(), true));
        }
    
        $custos = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $custos[$row['fk_componente']] = $row['custo_componente'];
        }
    
        // Calcular custo total da produção
        $custoTotal = 0;
        foreach ($data['etapas'] as $etapa) {
            $componenteId = $etapa['componenteId'];
            if (!isset($custos[$componenteId])) {
                throw new Exception("Custo do componente ID $componenteId não encontrado.");
            }
            $custoTotal += floatval($custos[$componenteId]);
        }
    
        // Inicia transação
        if (sqlsrv_begin_transaction($conn) === false) {
            throw new Exception("Não foi possível iniciar a transação");
        }
    
        try {
            // Inserir produção com custo
            $sqlProducao = "INSERT INTO Producao (nome, custo) OUTPUT INSERTED.producao_id VALUES (?, ?)";
            $params = array($data['tipo'], $custoTotal);
            $stmtProducao = sqlsrv_query($conn, $sqlProducao, $params);
    
            if ($stmtProducao === false) {
                throw new Exception("Erro ao inserir produção: " . print_r(sqlsrv_errors(), true));
            }
    
            if (!sqlsrv_fetch($stmtProducao)) {
                throw new Exception("Erro ao obter ID da produção");
            }
    
            $producao_id = sqlsrv_get_field($stmtProducao, 0);
    
            // Inserir etapas
            foreach ($data['etapas'] as $index => $etapa) {
                $ordem = $index + 1;
                $sqlEtapa = "INSERT INTO Etapa_Producao (fk_producao, ordem, nome, fk_componente) 
                            VALUES (?, ?, ?, ?)";
                $paramsEtapa = array(
                    $producao_id,
                    $ordem,
                    $etapa['nome'],
                    $etapa['componenteId']
                );
    
                $stmtEtapa = sqlsrv_query($conn, $sqlEtapa, $paramsEtapa);
                if ($stmtEtapa === false) {
                    throw new Exception("Erro ao inserir etapa $ordem: " . print_r(sqlsrv_errors(), true));
                }
            }
    
            // Commit da transação
            sqlsrv_commit($conn);
            echo json_encode(["message" => "Produção cadastrada com sucesso!"]);
    
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            throw $e;
        }
        break;    

        case 'editar':
            // Processa edição de produção existente
            if (empty($data['id'])) {
                throw new Exception("ID da produção não informado");
            }
        
            if (empty($data['tipo'])) {
                throw new Exception("Tipo de produção não informado");
            }
        
            // Buscar custos dos componentes
            $sql = "SELECT fc.fk_componente, fc.custo_componente 
                    FROM Fornecedor_Componente fc
                    INNER JOIN Componente c ON fc.fk_componente = c.componente_id
                    WHERE c.ativo = 1";
        
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                throw new Exception("Erro ao buscar custos: " . print_r(sqlsrv_errors(), true));
            }
        
            $custos = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $custos[$row['fk_componente']] = $row['custo_componente'];
            }
        
            // Calcular custo total atualizado
            $custoTotal = 0;
            foreach ($data['etapas'] as $etapa) {
                $componenteId = $etapa['componenteId'];
                if (!isset($custos[$componenteId])) {
                    throw new Exception("Custo do componente ID $componenteId não encontrado.");
                }
                $custoTotal += floatval($custos[$componenteId]);
            }
        
            // Inicia transação
            if (sqlsrv_begin_transaction($conn) === false) {
                throw new Exception("Não foi possível iniciar a transação");
            }
        
            try {
                // 1. Atualiza o nome e custo da produção
                $sqlProducao = "UPDATE Producao SET nome = ?, custo = ? WHERE producao_id = ?";
                $params = array($data['tipo'], $custoTotal, $data['id']);
                $stmtProducao = sqlsrv_query($conn, $sqlProducao, $params);
        
                if ($stmtProducao === false) {
                    throw new Exception("Erro ao atualizar produção: " . print_r(sqlsrv_errors(), true));
                }
        
                // 2. Obtem etapas existentes
                $sqlEtapasExistentes = "SELECT etapa_producao_id, ordem FROM Etapa_Producao 
                        WHERE fk_producao = ? AND ativo = 1
                        ORDER BY ordem";
                $stmtEtapasExistentes = sqlsrv_query($conn, $sqlEtapasExistentes, array($data['id'])); 

                if ($stmtEtapasExistentes === false) {
                    throw new Exception("Erro ao consultar etapas existentes: " . print_r(sqlsrv_errors(), true));
                }
        
                $etapasExistentes = [];
                while ($row = sqlsrv_fetch_array($stmtEtapasExistentes, SQLSRV_FETCH_ASSOC)) {
                    $etapasExistentes[$row['ordem']] = $row['etapa_producao_id'];
                }
        
                // 3. Processa cada etapa enviada
                foreach ($data['etapas'] as $index => $etapa) {
                    $ordem = $index + 1;
        
                    if (isset($etapasExistentes[$ordem])) {
                        // Etapa existe - atualiza
                        $sqlAtualizar = "UPDATE Etapa_Producao 
                                        SET nome = ?, fk_componente = ?
                                        WHERE etapa_producao_id = ?";
                        $paramsAtualizar = array(
                            $etapa['nome'],
                            $etapa['componenteId'],
                            $etapasExistentes[$ordem]
                        );
        
                        $stmtAtualizar = sqlsrv_query($conn, $sqlAtualizar, $paramsAtualizar);
                        if ($stmtAtualizar === false) {
                            throw new Exception("Erro ao atualizar etapa $ordem: " . print_r(sqlsrv_errors(), true));
                        }
        
                        unset($etapasExistentes[$ordem]); // remove da lista
                    } else {
                        // Etapa nova
                        $sqlInserir = "INSERT INTO Etapa_Producao (fk_producao, ordem, nome, fk_componente) 
                                        VALUES (?, ?, ?, ?)";
                        $paramsInserir = array(
                            $data['id'],
                            $ordem,
                            $etapa['nome'],
                            $etapa['componenteId']
                        );
        
                        $stmtInserir = sqlsrv_query($conn, $sqlInserir, $paramsInserir);
                        if ($stmtInserir === false) {
                            throw new Exception("Erro ao inserir etapa $ordem: " . print_r(sqlsrv_errors(), true));
                        }
                    }
                }
        
                // 4. Remover etapas não reenviadas
                if (!empty($etapasExistentes)) {
                    $idsParaRemover = implode(',', array_values($etapasExistentes));
                    $sqlRemover = "UPDATE Etapa_Producao SET ativo = 0 
                                    WHERE etapa_producao_id IN ($idsParaRemover)";
                    $stmtRemover = sqlsrv_query($conn, $sqlRemover);
                    if ($stmtRemover === false) {
                        throw new Exception("Erro ao remover etapas extras: " . print_r(sqlsrv_errors(), true));
                    }
                }
        
                sqlsrv_commit($conn);
                echo json_encode(["message" => "Produção atualizada com sucesso!"]);
        
            } catch (Exception $e) {
                sqlsrv_rollback($conn);
                throw $e;
            }
            break;

        case 'excluir':
            // Processa exclusão (soft delete) de produção
            if (empty($id)) {
                throw new Exception("ID da produção não informado");
            }
            
            // Inicia transação
            if (sqlsrv_begin_transaction($conn) === false) {
                throw new Exception("Não foi possível iniciar a transação");
            }

            try {
                // 1. Marca produção como inativa
                $sqlProducao = "UPDATE Producao SET ativo = 0 WHERE producao_id = ?";
                $stmtProducao = sqlsrv_query($conn, $sqlProducao, array($id));
                
                if ($stmtProducao === false) {
                    throw new Exception("Erro ao excluir produção: " . print_r(sqlsrv_errors(), true));
                }

                // 2. Marca etapas como inativas
                $sqlEtapas = "UPDATE Etapa_Producao SET ativo = 0 WHERE fk_producao = ?";
                $stmtEtapas = sqlsrv_query($conn, $sqlEtapas, array($id));
                
                if ($stmtEtapas === false) {
                    throw new Exception("Erro ao inativar etapas: " . print_r(sqlsrv_errors(), true));
                }

                // Commit da transação
                sqlsrv_commit($conn);
                echo json_encode(["message" => "Produção excluída com sucesso!"]);
                
            } catch (Exception $e) {
                // Rollback em caso de erro
                sqlsrv_rollback($conn);
                throw $e;
            }
            break;

        default:
            throw new Exception("Ação não reconhecida");

            case 'listar_linhas_status':
    $sql = "SELECT 
                p.producao_id as id,
                p.nome,
                pf.nome as produto_final,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM Historico_Producao hp 
                        WHERE hp.fk_producao = p.producao_id 
                        AND hp.data_conclusao IS NULL
                    ) THEN 'Ativa'
                    ELSE 'Concluída'
                END as status,
                COALESCE(
                    (
                        SELECT COUNT(e.etapa_producao_id) 
                        FROM Etapa_Producao e 
                        WHERE e.fk_producao = p.producao_id
                    ), 0
                ) as total_etapas,
                COALESCE(
                    (
                        SELECT COUNT(he.etapa_producao_id) 
                        FROM Historico_Etapas he
                        JOIN Historico_Producao hp ON he.fk_historico = hp.historico_producao_id
                        WHERE hp.fk_producao = p.producao_id
                        AND he.concluida = 1
                    ), 0
                ) as etapas_concluidas
            FROM Producao p
            LEFT JOIN ProdutoFinal pf ON pf.fk_producao = p.producao_id
            WHERE p.ativo = 1
            ORDER BY status DESC, p.nome";
    
    $stmt = sqlsrv_query($conn, $sql);
    $linhas = [];
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $totalEtapas = $row['total_etapas'] ? (int)$row['total_etapas'] : 1;
        $concluidas = $row['etapas_concluidas'] ? (int)$row['etapas_concluidas'] : 0;
        $progresso = round(($concluidas / $totalEtapas) * 100);
        
        $linhas[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'produto_final' => $row['produto_final'] ?? 'N/A',
            'status' => $row['status'],
            'progresso' => $progresso
        ];
    }
    
    echo json_encode($linhas);
    break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?> 