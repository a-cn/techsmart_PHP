<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    $id = $_POST["componente_id"];
    $nome = campoObrigatorio('nome', 'Nome do Componente');
    $especificacao = campoObrigatorio('especificacao', 'Especificação do Componente');
    $quantidade = campoObrigatorio('quantidade', 'Quantidade do Componente');
    $nivel_minimo = campoObrigatorio('nivel_minimo', 'Nível Mínimo do Componente');
    $nivel_maximo=campoObrigatorio('nivel_maximo', 'Nível Máximo do Componente');
    $ativo = 1; //Marca o componente como ativo por padrão
    
    //Dados de fornecedor e custo
    $fk_fornecedor = campoObrigatorio('fk_fornecedor', 'Fornecedor Associado ao Componente');
    $custo_componente = campoObrigatorio('custo_componente', 'Custo do Componente');

    //Se $id for vazio inclui o produto, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        //Inserção do componente
        $sql = "INSERT INTO [dbo].[Componente]
                        ([nome]
                        ,[especificacao]
                        ,[quantidade]
                        ,[nivel_minimo]
                        ,[nivel_maximo]
                        ,[ativo])
                    OUTPUT INSERTED.componente_id
                    VALUES
                        ('$nome'
                        ,'$especificacao'
                        ,$quantidade
                        ,$nivel_minimo
                        ,$nivel_maximo
                        ,$ativo);
            ";

        $stmt = sqlsrv_prepare($conn, $sql);
        if (sqlsrv_execute($stmt)) {
            //Recupera o ID do componente inserido
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $fk_componente = $row['componente_id'];  //ID do componente inserido

            //Verifica se o ID do componente foi obtido corretamente
            if (empty($fk_componente)) {
                die("Erro: Não foi possível obter o ID do componente.");
            }

            //Agora insere a relação do componente com o fornecedor
            $sqlFornecedorComponente = "
                INSERT INTO Fornecedor_Componente (fk_fornecedor, fk_componente, custo_componente)
                VALUES ($fk_fornecedor, $fk_componente, $custo_componente);
            ";
            $stmtFornecedorComponente = sqlsrv_prepare($conn, $sqlFornecedorComponente);
            if (sqlsrv_execute($stmtFornecedorComponente)) {
                sqlsrv_free_stmt($stmtFornecedorComponente);
            } else {
                die(var_dump(sqlsrv_errors(), $sqlFornecedorComponente)); //Para debug
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            
            //Após gravado, redireciona para a página inicial de componentes
            header("Location: ../front/index.php?pg=componentes");
            exit(); //Para evitar execução de código após o redirecionamento
        } else {
            die(var_dump(sqlsrv_errors(), $sql)); //Para debug
        }

    } else {
        //Atualização do componente
        $sql="  UPDATE [dbo].[Componente]
                    SET  [nome] = '$nome'
                        ,[especificacao] = '$especificacao'
                        ,[quantidade] = $quantidade
                        ,[nivel_minimo] = $nivel_minimo
                        ,[nivel_maximo] = $nivel_maximo
                        ,[ativo] = $ativo
                        WHERE componente_id = $id";
        
        $stmt = sqlsrv_prepare($conn, $sql);
        if (sqlsrv_execute($stmt)) {
            //Atualiza a relação do componente com o fornecedor
            $sqlFornecedorComponente = "
                UPDATE Fornecedor_Componente
                SET fk_fornecedor = $fk_fornecedor, custo_componente = $custo_componente
                WHERE fk_componente = $id;
            ";
            $stmtFornecedorComponente = sqlsrv_prepare($conn, $sqlFornecedorComponente);
            if (sqlsrv_execute($stmtFornecedorComponente)) {
                sqlsrv_free_stmt($stmtFornecedorComponente);
            } else {
                die(var_dump(sqlsrv_errors(), $sqlFornecedorComponente)); //Para debug
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);

            //Após a atualização, redireciona para a página de componentes
            header("Location: ../front/index.php?pg=componentes");
            exit(); //Para evitar execução de código após o redirecionamento
        } else {
            die(var_dump(sqlsrv_errors(), $sql)); //Para debug
        }
    }
}
?>