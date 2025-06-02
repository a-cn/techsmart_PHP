<?php
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechSmart - Fornecedores</title>
  <link rel="stylesheet" type="text/css" href="../CSS/cadastro-fornecedor.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
  <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->

  <div class="container">
    <h1 class="titulo-fornecedores">FORNECEDORES</h1>

    <!-- Formulário de Cadastro -->
    <div class="form-container">
      <h2>CADASTRAR FORNECEDOR</h2>
      <form id="formFornecedor" action="../../Back/cadastro_fornecedor.php" method="POST">
        <label for="cpf_cnpj">CPF/CNPJ:</label>
        <input type="text" id="cpf_cnpj" name="cpf_cnpj" placeholder="000.000.000-00 ou 00.000.000/0000-00" required>

        <label for="nome">Nome/Razão Social:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="telefone">Telefone (com DDD):</label>
        <input type="text" id="telefone" name="num_principal" placeholder="(00) 0000-0000">

        <label for="celular">Celular (com DDD):</label>
        <input type="text" id="celular" name="num_secundario" placeholder="(00) 00000-0000">

        <label for="cep">CEP:</label>
        <input type="text" id="cep" name="cep" placeholder="00000-000" required>
        <button type="button" id="buscarCep">Buscar CEP</button>

        <label for="logradouro">Logradouro:</label>
        <input type="text" id="logradouro" name="logradouro" required>

        <label for="numero">Número:</label>
        <input type="text" id="numero" name="numero" required>

        <label for="complemento">Complemento:</label>
        <input type="text" id="complemento" name="complemento">

        <label for="bairro">Bairro:</label>
        <input type="text" id="bairro" name="bairro" required>

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required>

        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" required>

        <label for="situacao">Situação do Cadastro:</label>
        <select id="situacao" name="situacao">
          <option value="ATIVO">ATIVO</option>
          <option value="INATIVO">INATIVO</option>
          <option value="PENDENTE">PENDENTE</option>
        </select>

        <button type="submit" class="btn-incluir">Incluir</button>
      </form>
    </div>

    <!-- Tabela de Fornecedores Cadastrados -->
    <div class="table-container">
      <h2>FORNECEDORES CADASTRADOS</h2>
      <input type="text" id="pesquisar" placeholder="Pesquisar fornecedor...">
      <button class="btn-pesquisar" onclick="pesquisarFornecedor()">Pesquisar</button>
      <button class="btn-selecionar-todos" onclick="selecionarTodos()">Selecionar Todos</button>
      <table id="tabelaFornecedores">
        <thead>
          <tr>
            <th>Selecionar</th>
            <th>CPF/CNPJ</th>
            <th>NOME</th>
            <th>ENDEREÇO</th>
            <th>CONTATO</th>
            <th>SITUAÇÃO</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <!-- Dados serão inseridos dinamicamente via JavaScript -->
        </tbody>
      </table>
      <button class="btn-imprimir" onclick="imprimirSelecionados()">Imprimir Selecionados</button>
    </div>
  </div>

  <script src="../JavaScript/cadastro-fornecedor.js"></script>
</body>
</html>