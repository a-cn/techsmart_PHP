<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios
    require_once 'validacoes.php'; //Chama a função para validar campos obrigatórios

    /*/ IMPORTANTE: Lembrar de gravar o registro das FKs antes da PK correspondente /*/

    $fornecedor_id = $_POST['fornecedor_id'] ?? null;

    /*/ Gravação de Endereço /*/
    $cep = campoObrigatorio('cep', 'CEP');
    $logradouro = campoObrigatorio('logradouro', 'Logradouro');
    $numero = campoObrigatorio('numero', 'Número');
    $complemento = $_POST['complemento'] ?? null; //Pode ser nulo
    $bairro = campoObrigatorio('bairro', 'Bairro');
    $cidade = campoObrigatorio('cidade', 'Cidade');
    $estado = campoObrigatorio('estado', 'Estado');

    //Se for alteração de dados, buscar fk_endereco
    if (!empty($fornecedor_id)) {
        $sql_busca_endereco = "SELECT fk_endereco FROM Fornecedor WHERE fornecedor_id = ?";
        $stmt_busca_endereco = sqlsrv_query($conn, $sql_busca_endereco, [$fornecedor_id]);
        $row_endereco = sqlsrv_fetch_array($stmt_busca_endereco, SQLSRV_FETCH_ASSOC);
        $fk_endereco = $row_endereco['fk_endereco'] ?? null;

        if ($fk_endereco) {
            $sql_update_endereco = "UPDATE Endereco SET
                cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
                WHERE endereco_id = ?";
            $params_update = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $fk_endereco];
            $stmt_update_endereco = sqlsrv_query($conn, $sql_update_endereco, $params_update);
            if (!$stmt_update_endereco) die("Erro ao atualizar endereço: " . print_r(sqlsrv_errors(), true));
        } else {
            die("Endereço associado ao fornecedor não encontrado.");
        }
    } else {
        $sql_insert_endereco = "INSERT INTO Endereco (cep, logradouro, numero, complemento, bairro, cidade, estado)
            OUTPUT INSERTED.endereco_id VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado];
        $stmt_endereco = sqlsrv_query($conn, $sql_insert_endereco, $params_endereco);
        if (!$stmt_endereco || !($row = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC))) {
            die("Erro ao cadastrar endereço: " . print_r(sqlsrv_errors(), true));
        }
        $fk_endereco = $row['endereco_id'];
    }

    /*/ Gravação de Fornecedor /*/
    $nome = campoObrigatorio('nome', 'Razão Social ou Nome');
    $cpf_cnpj = campoObrigatorio('cpf_cnpj', 'CNPJ ou CPF');
    $num_principal = campoObrigatorio('num_principal', 'Número Principal para Contato');
    $num_secundario = $_POST['num_secundario'] ?? null; //Pode ser nulo
    $email = campoObrigatorio('email', 'E-mail');
    $ativo = 1;

    /*/ INSERT se for novo registro ou UPDATE se for alteração /*/
    if (empty($fornecedor_id)) {
        $sql_insert = "INSERT INTO Fornecedor (
            nome, cpf_cnpj, num_principal, num_secundario, email, fk_endereco, ativo
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params_insert = [$nome, $cpf_cnpj, $num_principal, $num_secundario, $email, $fk_endereco, $ativo];

        $stmt_insert = sqlsrv_prepare($conn, $sql_insert, $params_insert);
        if (!sqlsrv_execute($stmt_insert)) {
            die("Erro ao inserir fornecedor: " . print_r(sqlsrv_errors(), true));
        }
    } else {
        $sql_update = "UPDATE Fornecedor SET
            nome = ?, cpf_cnpj = ?, num_principal = ?, num_secundario = ?, email = ?, fk_endereco = ?, ativo = ?
            WHERE fornecedor_id = ?";
        $params_update = [$nome, $cpf_cnpj, $num_principal, $num_secundario, $email, $fk_endereco, $ativo, $fornecedor_id];

        $stmt_update = sqlsrv_prepare($conn, $sql_update, $params_update);
        if (!sqlsrv_execute($stmt_update)) {
            die("Erro ao atualizar fornecedor: " . print_r(sqlsrv_errors(), true));
        }
    }

    sqlsrv_close($conn);
    header("Location: ../front/index.php?pg=fornecedores");
    exit();
}
?>