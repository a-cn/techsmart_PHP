<?php
include 'conexao_sqlserver.php';

header('Content-Type: application/json'); // Define cabeçalho para resposta JSON

$acao = $_GET['acao'] ?? ''; // Obtém ação a ser executada

try {
    switch ($acao) {
        case 'listar':
            // Consulta SQL para listar produções com suas etapas
            $sql = "SELECT 
                    p.producao_id as id, 
                    p.nome as tipo,
                    (
                        SELECT STRING_AGG(
                            e.nome + ' (Componente: ' + c.nome + ')', 
                            ', '
                        ) WITHIN GROUP (ORDER BY e.ordem)
                        FROM Etapa_Producao e
                        JOIN Componente c ON e.fk_componente = c.componente_id
                        WHERE e.fk_producao = p.producao_id AND e.ativo = 1
                    ) as etapas,
                    (
                        SELECT STRING_AGG(
                            '{\"nome\":\"' + e.nome + '\",\"componenteId\":\"' + CAST(e.fk_componente AS VARCHAR) + '\"}', 
                            ','
                        ) WITHIN GROUP (ORDER BY e.ordem)
                        FROM Etapa_Producao e
                        WHERE e.fk_producao = p.producao_id AND e.ativo = 1
                    ) as etapas_json
                FROM Producao p
                WHERE p.ativo = 1
                ORDER BY p.producao_id";
        
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            throw new Exception("Erro ao listar produções: " . print_r(sqlsrv_errors(), true));
        }
        
        $producoes = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Formata para exibição
            $row['etapas'] = $row['etapas'] ?: 'Sem etapas cadastradas';
            
            // Mantem o JSON para edição
            $row['etapas_json'] = $row['etapas_json'] ? '[' . $row['etapas_json'] . ']' : '[]';
            $producoes[] = $row;
        }
        
        echo json_encode($producoes);
        break;

        case 'incluir':
            // Processa inclusão de nova produção
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Erro ao decodificar JSON: " . json_last_error_msg());
            }
            
            $tipo = $data["tipo"] ?? '';
            $etapas = $data["etapas"] ?? [];
            
            if (empty($tipo)) {
                throw new Exception("Tipo de produção não informado");
            }
            
            if (empty($etapas)) {
                throw new Exception("Nenhuma etapa informada");
            }

            // Inicia transação
            if (sqlsrv_begin_transaction($conn) === false) {
                throw new Exception("Não foi possível iniciar a transação");
            }

            try {
                // Insere produção
                $sqlProducao = "INSERT INTO Producao (nome) OUTPUT INSERTED.producao_id VALUES (?)";
                $params = array($tipo);
                $stmtProducao = sqlsrv_query($conn, $sqlProducao, $params);
                
                if ($stmtProducao === false) {
                    throw new Exception("Erro ao inserir produção: " . print_r(sqlsrv_errors(), true));
                }
                
                if (!sqlsrv_fetch($stmtProducao)) {
                    throw new Exception("Erro ao obter ID da produção");
                }
                
                $producao_id = sqlsrv_get_field($stmtProducao, 0);
                
                // Insere etapas com componentes
                foreach ($etapas as $index => $etapa) {
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
                // Rollback em caso de erro
                sqlsrv_rollback($conn);
                throw $e;
            }
            break;

        case 'editar':
            // Processa edição de produção existente
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Erro ao decodificar JSON: " . json_last_error_msg());
            }
            
            $id = $data["id"] ?? 0;
            $tipo = $data["tipo"] ?? '';
            $etapas = $data["etapas"] ?? [];
            
            if (empty($id)) {
                throw new Exception("ID da produção não informado");
            }
            
            if (empty($tipo)) {
                throw new Exception("Tipo de produção não informado");
            }
        
            // Inicia transação
            if (sqlsrv_begin_transaction($conn) === false) {
                throw new Exception("Não foi possível iniciar a transação");
            }
        
            try {
                // 1. Atualiza o nome da produção
                $sqlProducao = "UPDATE Producao SET nome = ? WHERE producao_id = ?";
                $params = array($tipo, $id);
                $stmtProducao = sqlsrv_query($conn, $sqlProducao, $params);
                
                if ($stmtProducao === false) {
                    throw new Exception("Erro ao atualizar produção: " . print_r(sqlsrv_errors(), true));
                }
        
                // 2. Obtem etapas existentes
                $sqlEtapasExistentes = "SELECT etapa_producao_id, ordem FROM Etapa_Producao 
                                        WHERE fk_producao = ? AND ativo = 1
                                        ORDER BY ordem";
                $stmtEtapasExistentes = sqlsrv_query($conn, $sqlEtapasExistentes, array($id));
                
                if ($stmtEtapasExistentes === false) {
                    throw new Exception("Erro ao consultar etapas existentes: " . print_r(sqlsrv_errors(), true));
                }
                
                $etapasExistentes = [];
                while ($row = sqlsrv_fetch_array($stmtEtapasExistentes, SQLSRV_FETCH_ASSOC)) {
                    $etapasExistentes[$row['ordem']] = $row['etapa_producao_id'];
                }
        
                // 3. Processa cada etapa enviada
                foreach ($etapas as $index => $etapa) {
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
                        
                        // Remove da lista de etapas existentes
                        unset($etapasExistentes[$ordem]);
                    } else {
                        // Etapa não existe - insere nova
                        $sqlInserir = "INSERT INTO Etapa_Producao (fk_producao, ordem, nome, fk_componente) 
                                        VALUES (?, ?, ?, ?)";
                        $paramsInserir = array(
                            $id,
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
        
                // 4. Remover etapas que não foram enviadas (se necessário)
                if (!empty($etapasExistentes)) {
                    $idsParaRemover = implode(',', array_values($etapasExistentes));
                    $sqlRemover = "DELETE FROM Etapa_Producao 
                                    WHERE etapa_producao_id IN ($idsParaRemover)";
                    
                    $stmtRemover = sqlsrv_query($conn, $sqlRemover);
                    
                    if ($stmtRemover === false) {
                        throw new Exception("Erro ao remover etapas extras: " . print_r(sqlsrv_errors(), true));
                    }
                }
        
                // Commit da transação
                sqlsrv_commit($conn);
                echo json_encode(["message" => "Produção atualizada com sucesso!"]);
                
            } catch (Exception $e) {
                // Rollback em caso de erro
                sqlsrv_rollback($conn);
                throw $e;
            }
        break;

        case 'excluir':
            // Processa exclusão (soft delete) de produção
            $id = $_GET['id'] ?? 0;
            
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
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?> 