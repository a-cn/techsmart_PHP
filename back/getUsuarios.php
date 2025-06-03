<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco

$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;

// Consulta SQL
$sql = "    SELECT u.[usuario_id],
		           u.[fk_tipo_usuario],
                   tu.[descricao],
		           u.[nome],
		           u.[cpf_cnpj],
		           u.[data_nascimento],
		           u.[email],
                   u.[num_principal],
                   u.[num_recado],
                   u.[senha],
                   u.[fk_pergunta_seguranca],
                   u.[resposta_seguranca],
                   u.[ativo],
                   e.*
     	      FROM [Usuario] AS u
        INNER JOIN [Endereco] AS e ON u.fk_endereco = e.endereco_id
        INNER JOIN [Tipo_Usuario] AS tu ON u.fk_tipo_usuario = tu.tipo_usuario_id
        WHERE u.ativo = ?"; //O atributo ativo será passado como parâmetro a ser identificado para exibição alternada no DataTables

$params = [$ativo]; //Parâmetro para a cláusula WHERE
$stmt = sqlsrv_query($conn, $sql, $params); //Passando o parâmetro

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$usuarios = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['data_nascimento'] = $row['data_nascimento'] ? $row['data_nascimento']->format('d/m/Y') : ''; // Formatar data
    $usuarios[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

//var_dump($usuarios); exit;  
// Retorna JSON
header('Content-Type: application/json');
echo json_encode($usuarios);
?>