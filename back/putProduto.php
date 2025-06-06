<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    $id=$_POST["produtofinal_id"];
    $fk_producao = campoObrigatorio('fk_producao', 'Linha de Produção Associada ao Produto'); 
    $nome=campoObrigatorio('nome', 'Nome do Produto');
    $descricao=campoObrigatorio('descricao', 'Descrição do Produto');
    $valor_venda=campoObrigatorio('valor_venda', 'Valor de Venda do Produto');
    $quantidade=campoObrigatorio('quantidade', 'Quantidade do Produto');
    $nivel_minimo=campoObrigatorio('nivel_minimo', 'Nível Mínimo de Estoque do Produto');
    $nivel_maximo=campoObrigatorio('nivel_maximo', 'Nível Máximo de Estoque do Produto');
    $tempo_producao_dias=campoObrigatorio('tempo_prod', 'Tempo de Produção do Produto');
    $ativo=1;
    
    // Se $id for vazio inclui o produto, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO [dbo].[ProdutoFinal]
                ([fk_producao]
                ,[nome]
                ,[descricao]
                ,[valor_venda]
                ,[quantidade]
                ,[nivel_minimo]
                ,[nivel_maximo]
                ,[tempo_producao_dias]
                ,ativo)
            VALUES
                ($fk_producao
                ,'$nome'
                ,'$descricao'
                ,$valor_venda
                ,$quantidade
                ,$nivel_minimo
                ,$nivel_maximo
                ,$tempo_producao_dias
                ,$ativo)";
    } else {
        $sql= "UPDATE [dbo].[ProdutoFinal] SET
            [fk_producao] = $fk_producao
            ,[nome] = '$nome'
            ,[descricao] = '$descricao'
            ,[valor_venda] = $valor_venda
            ,[quantidade] = $quantidade
            ,[nivel_minimo] = $nivel_minimo
            ,[nivel_maximo] = $nivel_maximo
            ,[tempo_producao_dias] = $tempo_producao_dias
            ,[ativo] = $ativo
        WHERE produtofinal_id = $id";
    }
    //var_dump($_POST,$id, $sql); exit(); // Apenas para verificar o que será gravado (Bom manter)
    
    $stmt = sqlsrv_prepare($conn, $sql);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=produtos");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        die(print_r(sqlsrv_errors(), true));
        //die(var_dump(sqlsrv_errors()));  // Bom para debug
    }
}
