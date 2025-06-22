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

    //Valida se o valor de venda é menor que o valor de produção
    $sql = "SELECT
                ISNULL(SUM(fc.custo_componente), 0) as valor_producao
            FROM
                Producao p
                LEFT JOIN Etapa_Producao ep ON ep.fk_producao = p.producao_id
                LEFT JOIN Componente c ON c.componente_id = ep.fk_componente
                LEFT JOIN Fornecedor_Componente fc ON fc.fk_componente = c.componente_id
            WHERE
                p.producao_id = $fk_producao
        ";
    $stmt = sqlsrv_query($conn, $sql);
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao calcular valor de produção: ' . implode(', ', sqlsrv_errors())
        ]);
        exit;
    }
    
    $valor_producao = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $valor_producao_total = $valor_producao['valor_producao'] ?? 0;
    
    if ($valor_producao_total > $valor_venda) {
        // Retorna JSON para ser tratado pelo frontend
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'O valor de venda não pode ser menor que o valor de produção.',
            'valor_producao' => $valor_producao_total,
            'valor_venda' => $valor_venda
        ]);
        exit;
    }
    
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

        // Retorna sucesso em JSON
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Produto salvo com sucesso!'
        ]);
        exit;
    } else {
        // Retorna erro em JSON
        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao salvar produto: ' . implode(', ', sqlsrv_errors())
        ]);
        exit;
    }
}
