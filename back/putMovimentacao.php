<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    $id=$_POST["movimentacao_id"];
    $fk_pedido=campoObrigatorio("pedido_id","Id do Pedido");
    $fk_produtofinal=campoObrigatorio("produtofinal_id","Ïd do Produto");
    $quantidade=campoObrigatorio('quantidade', 'Qunatidade do Produto');
    $data_hora=campoObrigatorio('data_hora', 'Data/Hora');
    // Depois de verificar o campo data, formatamos a data hora para gravação.
    $data_hora = date('Y-m-d H:i:s', strtotime($data_hora));
    $tipo_movimentacao=campoObrigatorio('tipo_movimentacao', 'Tipo de Movimentação');
    //$ativo=1;//$_POST["ativo"];
    
    // Se $id for vazio inclui a movimentação, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO [dbo].[Movimentacao]
                ([fk_pedido],[fk_produtofinal],[quantidade],[data_hora],[tipo_movimentacao])
            VALUES
                (?,?,?,?,?)";
        $params_sql = [$fk_pedido,$fk_produtofinal,$quantidade,$data_hora,$tipo_movimentacao];
    } else {
        $sql= "UPDATE [dbo].[Movimentacao] SET
                      [fk_pedido] = ?
                     ,[fk_produtofinal] = ?
                     ,[quantidade] = ?
                     ,[data_hora] = ?
                     ,[tipo_movimentacao] = ?
                WHERE movimentacao_id = ?";
        $params_sql = [$fk_pedido,$fk_produtofinal,$quantidade,$data_hora,$tipo_movimentacao,$id];
    }
    //var_dump($_POST,$id, $sql, $params_sql); exit(); // Apenas para verificar o que será gravado (Bom manter)

    $stmt = sqlsrv_prepare($conn, $sql,$params_sql);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados, devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=movimentacao-estoque");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        die(print_r(sqlsrv_errors(), true));
        //die(var_dump(sqlsrv_errors()));  // Bom para debug
    }
}
