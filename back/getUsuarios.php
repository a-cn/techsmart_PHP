<?php
require_once 'conexao_sqlserver.php'; // Conecte ao banco

// Consulta SQL
$sql = "    SELECT u.[usuario_id],
		           u.[fk_tipo_usuario],
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
        INNER JOIN [Endereco] AS e ON u.fk_endereco = e.endereco_id;";
$stmt = sqlsrv_query($conn, $sql);
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