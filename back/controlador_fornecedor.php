<?php
header('Content-Type: application/json');
require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;

switch ($action) {
    case 'read':
        //Seleciona colunas específicas de fornecedores ativos e seus respectivos endereços, com texto concatenado para visualização conjunta
        //Os prefixos "f" e "e" foram definidos para identificar atributos das tabelas Fornecedor e Endereco, respectivamente
        $sql = "SELECT f.fornecedor_id, f.cpf_cnpj, f.nome, f.situacao, f.num_principal, f.num_secundario, f.email,
                        CONCAT(e.logradouro, ', ', e.numero, ' - ', e.bairro, ', ', e.cidade, ' - ', e.estado) AS endereco
                FROM Fornecedor f
                JOIN Endereco e ON f.fk_endereco = e.endereco_id
                WHERE f.ativo = 1";
        $stmt = sqlsrv_query($conn, $sql);
        $dados = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $dados[] = $row;
        }
        echo json_encode($dados);
        break;

    case 'arquivar':
        $stmt = sqlsrv_query($conn, "UPDATE Fornecedor SET ativo = 0 WHERE fornecedor_id = ?", [$id]);
        echo json_encode(['success' => $stmt !== false]);
        break;
    
    case 'excluir':
        //Garante que somente administradores possam realizar exclusões definitivas
        if ($_SESSION['tipo_usuario'] !== 'administrador') {
            echo json_encode(['success' => false, 'error' => 'Apenas administradores podem executar esta ação.']);
            exit;
        }
        //Busca o endereço relacionado ao fornecedor
        $sql_endereco = "SELECT fk_endereco FROM Fornecedor WHERE fornecedor_id = ?";
        $stmt_endereco = sqlsrv_query($conn, $sql_endereco, [$id]);
        $row = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC);
        //Mostra um erro se não encontrar a linha do registro
        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Fornecedor não encontrado.']);
            exit;
        }
        //Atribui o respectivo endereço à variável $fk_endereco
        $fk_endereco = $row['fk_endereco'];

        //Exclui o fornecedor definitivamente por primeiro
        $stmt_fornecedor = sqlsrv_query($conn, "DELETE FROM Fornecedor WHERE fornecedor_id = ?", [$id]);

        if ($stmt_fornecedor) {
            //Exclui o endereço definitivamente, após exclusão do fornecedor relacionado a ele
            $stmt_endereco = sqlsrv_query($conn, "DELETE FROM Endereco WHERE endereco_id = ?", [$fk_endereco]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir fornecedor.']);
        }
        break;
        
        /*
    case 'delete':
        $admin = $_POST['admin'] ?? '0';
        if ($admin === '1') {
            $stmt = sqlsrv_query($conn, "DELETE FROM Fornecedor WHERE fornecedor_id = ?", [$id]);
        } else {
            $stmt = sqlsrv_query($conn, "UPDATE Fornecedor SET ativo = 0 WHERE id_fornecedor = ?", [$id]);
        }
        echo json_encode(['success' => $stmt !== false]);
        break;
        */

    default:
        echo json_encode(['error' => 'Ação inválida']);
}
?>