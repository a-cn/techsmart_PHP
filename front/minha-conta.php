<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
        <link rel="stylesheet" type="text/css" href="css/janelas.css">
        <div class="janela-consulta" id="divMinhaConta">
            <span class="titulo-janela">Minha Conta</span>
            <p>Não é da minha conta ainda...</p>
            <p>Mas com certeza será!</p>
            <p style="height: 1000px;">Muita informação para testar a rolagem!</p>
        </div>
