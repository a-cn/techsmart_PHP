<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios
 
    //Coleta os dados de ENDEREÇO
    //Atribui à variável $fk_endereco o valor da coluna endereco_id da linha encontrada

    $id_endereco = campoObrigatorio('endereco_id', 'Id do endereço'); // campo oculto do form contendo o id do endereço
    $cep         = campoObrigatorio('cep', 'CEP');
    $logradouro  = campoObrigatorio('logradouro', 'Logradouro');
    $numero      = campoObrigatorio('numero', 'Número');
    $complemento = $_POST['complemento'] ?? ''; // Pode ser vazio ou nulo
    $bairro      = campoObrigatorio('bairro', 'Bairro');
    $cidade      = campoObrigatorio('cidade', 'Cidade');
    $estado      = campoObrigatorio('estado', 'Estado');
    
    // Altera os dados do endereço informado
    $sql_endereco = "UPDATE [dbo].[Endereco]
                        SET [cep] = ?,
                            [logradouro] = ?,
                            [numero] = ?,
                            [complemento] = ?,
                            [bairro] = ?,
                            [cidade] = ?,
                            [estado] = ?
                    WHERE endereco_id = ?";

    $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $id_endereco];
    //var_dump($_POST, $sql_endereco, $params_endereco); exit;

    $stmt_endereco = sqlsrv_prepare($conn, $sql_endereco, $params_endereco);

    if (!$stmt_endereco || !sqlsrv_execute($stmt_endereco)) {
        die("Erro ao atualizar endereço: " . print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($stmt_endereco);    

    $id=$_POST["producao_id"];
    $nome=campoObrigatorio('nome', 'Nome do Fornecedor');
    $cpf_cnpj=campoObrigatorio('cpf_cnpj', 'CNPJ');
    $num_principal=campoObrigatorio('num_principal', 'Telefone Principal');
    $num_secundario=campoObrigatorio('num_secundario', 'Telefone Secundário');
    $email=campoObrigatorio('email', 'email');
    $fk_endereco=campoObrigatorio('fk_endereco', 'Endereço');
    $situacao=campoObrigatorio('situacao', 'Situacao');
    $ativo=1;//$_POST["ativo"];
    
    // Se $id for vazio inclui o produto, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO [dbo].[Fornecedor]
                         ([nome]
                         ,[cpf_cnpj]
                         ,[num_principal]
                         ,[num_secundario]
                         ,[email]
                         ,[fk_endereco]
                         ,[situacao]
                         ,[ativo])
                     VALUES
                         ($nome,
                         ,$cpf_cnpj,
                         ,$num_principal,
                         ,$num_secundario,
                         ,$email,
                         ,$fk_endereco,
                         ,$situacao,
                         ,$ativo)";
    } else {
        $sql= "UPDATE [dbo].[Fornecedor]
                  SET [nome] = <nome, varchar(100),>
                      ,[cpf_cnpj] = <cpf_cnpj, varchar(14),>
                      ,[num_principal] = <num_principal, varchar(15),>
                      ,[num_secundario] = <num_secundario, varchar(15),>
                      ,[email] = <email, varchar(50),>
                      ,[fk_endereco] = <fk_endereco, int,>
                      ,[situacao] = <situacao, varchar(15),>
                      ,[ativo] = <ativo, bit,>
                WHERE producao_id = $id";
    }
    //var_dump($_POST,$id, $sql); exit(); // Apenas para verificar o que será gravado (Bom manter)
    
    $stmt = sqlsrv_prepare($conn, $sql);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=producao");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        die(print_r(sqlsrv_errors(), true));
        //die(var_dump(sqlsrv_errors()));  // Bom para debug
    }
}
