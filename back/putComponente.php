<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    $id=$_POST["componente_id"];
    $nome=campoObrigatorio('nome', 'Nome do Componente');
    $especificacao=campoObrigatorio('especificacao', 'especificação do Componente');
    $quantidade=campoObrigatorio('quantidade', 'Quantidade do Componente');
    $nivel_minimo=campoObrigatorio('nivel_minimo', 'Nível Mínimo do Componente');
    $nivel_maximo=campoObrigatorio('nivel_maximo', 'Nível Máximo do Componente');
    $ativo=1;//$_POST["ativo"];
    
    // Se $id for vazio inclui o produto, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO [dbo].[Componente]
                        ([nome]
                        ,[especificacao]
                        ,[quantidade]
                        ,[nivel_minimo]
                        ,[nivel_maximo]
                        ,[ativo])
                    VALUES
                        ('$nome'
                        ,'$especificacao'
                        ,$quantidade
                        ,$nivel_minimo
                        ,$nivel_maximo
                        ,$ativo);
            ";
    } else {
        $sql="  UPDATE [dbo].[Componente]
                    SET  [nome] = '$nome'
                        ,[especificacao] = '$especificacao'
                        ,[quantidade] = $quantidade
                        ,[nivel_minimo] = $nivel_minimo
                        ,[nivel_maximo] = $nivel_maximo
                        ,[ativo] = $ativo
                        WHERE componente_id = $id";
    }
    //var_dump($_POST,$id, $sql); exit(); // Apenas para verificar o que será gravado (Bom manter)
    
    $stmt = sqlsrv_prepare($conn, $sql);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=componentes");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        //die(print_r(sqlsrv_errors(), true));
        die(var_dump(sqlsrv_errors(), $sql));  // Bom para debug
    }
}
