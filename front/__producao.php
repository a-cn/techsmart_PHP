<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
        <link rel="stylesheet" type="text/css" href="css/janelas.css">
        <div class="janela-consulta" id="divProducao">
            <span class="titulo-janela">Controle de Produção</span>
            <p>Em Breve...</p>
            <p>Conteúdo!</p>
            <p style="height: 1000px;">Por enquanto teste de rolagem!</p>
        </div>
