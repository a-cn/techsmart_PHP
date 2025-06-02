<?php
//CÓDIGO DE CONEXÃO COM O SQL SERVER

$serverName = ".\\SQLEXPRESS"; //serverName\instanceName

$connectionInfo = array(
    "Database"=>"TechSmartDB",
    "UID"=>"techsmart_user",
    "PWD"=>"Teste123!",
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( !$conn ) {
    echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
} /* else {
    echo "Conectado com sucesso!";
}*/
?>