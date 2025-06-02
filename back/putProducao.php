<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    $id=$_POST["producao_id"];
    $nome=campoObrigatorio('nome', 'Nome do Produto');
    $ativo=1;//$_POST["ativo"];
    
    // Se $id for vazio inclui o produto, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO [dbo].[Producao]
                ([nome]
                ,ativo)
            VALUES
                ('$nome'
                ,$ativo)";
    } else {
        $sql= "UPDATE [dbo].[Producao] SET
             [nome] = '$nome'
            ,[ativo] = $ativo
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
