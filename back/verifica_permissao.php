<?php
//CÓDIGO PARA VERIFICAR PERMISSÕES DE ACESSO ÀS PÁGINAS

//Validar permissão por tipo de usuário -> Permite acesso apenas aos tipos declarados na variável $tiposPermitidos em cada página do sistema
//Se a variável $tiposPermitidos existir, verifica se o tipo está permitido
if (isset($tiposPermitidos) && !in_array($_SESSION['tipo_usuario'], $tiposPermitidos)) {
    echo "Acesso não autorizado.";
    exit;
}
?>

<!--
IMPORTANTE:
É necessário passar os tipos permitidos como parâmetro no início de cada página:

<php
$tiposPermitidos = ['cliente']; ou ['administrador', 'colaborador']; ou ['administrador'];
require_once '../../Back/verifica_permissao.php'; //Chama este código para validar as permissões
>
-->