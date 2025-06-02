<?php
//CÓDIGO PARA ENCERRAR A SESSÃO DO USUÁRIO (DESLOGAR)

session_start();
session_destroy();
header("Location: ../index.html");
exit;
?>

<!-- Para o botão de Sair, na página:
<a href="../../Back/logout.php">Sair</a>
-->