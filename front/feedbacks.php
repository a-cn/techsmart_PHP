<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
        <link rel="stylesheet" type="text/css" href="css/janelas.css">
        <div class="janela-consulta" id="divFeedbacks">
            <span class="titulo-janela">Controle de Feedbacks</span>
            <p>Em Breve...</p>
            <p style="height: 400px;">Conteúdo!</p>
        </div>
