<?php
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
?> 

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro de Produção</title>
  <link rel="stylesheet" type="text/css" href="../CSS/cadastro-producao.css">
</head>
<body>
  <?php include 'sidebar-header.php'; ?> 
  <div class="container">
    <h2 class="titulo-fornecedores">Cadastro de Produção</h2>

    <div class="form-container">
      <label for="tipoProducao">Tipo de Produção:</label>
      <input type="text" id="tipoProducao" placeholder="Digite o tipo de produção" />

      <label for="quantidadeEtapas">Quantidade de Etapas:</label><!-- Container dinâmico para as etapas -->
      <input type="number" id="quantidadeEtapas" min="1" placeholder="Digite a quantidade de etapas" oninput="gerarCamposEtapas()" />

      <div id="containerEtapas"></div>

      <button class="btn-incluir" onclick="salvarProducao()">Salvar</button>
    </div>

    <div class="table-container">
      <h2>LINHAS DE PRODUÇÃO CADASTRADAS</h2>
      <input type="text" id="pesquisar" placeholder="Pesquisar linha de produção..." />
      <button class="btn-pesquisar" onclick="pesquisarProducao()">Pesquisar</button>
      <table id="tabelaProducoes">
        <thead>
          <tr>
            <th>ID</th>
            <th>TIPO DE PRODUÇÃO</th>
            <th>ETAPAS</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody></tbody> <!-- Corpo da tabela será preenchido dinamicamente -->
      </table>
    </div>
  </div>

  <script src="../JavaScript/cadastro-producao.js"></script>
</body>
</html> 