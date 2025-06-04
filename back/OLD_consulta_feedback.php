<?php
//CÓDIGO PARA CONSULTAR OS REGISTROS DE FEEDBACK EXISTENTES NO BANCO

require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco

$dados = [];

$sql = "SELECT feedback_id, fk_pedido, avaliacao, observacao, data_hora, ativo FROM Feedback";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt !== false) {
    //Atribui na variável de array $dados o conteúdo selecionado da tabela
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        //Formata a data e hora em "dd/mm/aaaa, hh:mm:ss" para melhor visualização na tela
        if (!empty($row['data_hora'])) {
            $row['data_hora'] = $row['data_hora']->format('d/m/Y, H:i:s');
        }
        $dados[] = $row;
    }
}

return $dados;
?>