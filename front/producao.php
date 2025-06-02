<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
<link rel="stylesheet" type="text/css" href="css/janelas.css">
<script src="scr/script.js"></script>
<div>
  <div class="janela-cadastro oculta" id="divCadastroProducao">
    <span class="titulo-janela">Cadastro de Produções</span>
    <form id="form-cadastro" action="../back/putProducao.php" method="POST">
      <div class="form-group" style="display: none" id="divID">
        <label for="id">ID:</label>
        <input type="text" id="producao_id" name="producao_id">
      </div>
      <div class="form-group" id="divNome">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
      </div>
      <div class="form-row">
        <input type="submit" class="btn-cadastrar" value="Salvar">
        <button type="button" class="btn-pesquisar"
          onclick="limpaCadastroAlternaEdicao('divCadastroProducao','divConsultaProducoes');">Cancelar</button>
      </div>
    </form>
  </div>
  <div class="janela-consulta" id="divConsultaProducoes">
    <span class="titulo-janela">Produções Cadastradas</span>
    <table id="tabelaProducao">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <!--th>TIPO DE PRODUÇÃO</th>
          <th>ETAPAS</th>
          <th>AÇÕES</th-->
        </tr>
      </thead>
      <tbody></tbody> <!-- Corpo da tabela será preenchido dinamicamente -->
    </table>
  </div>
</div>
<!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var oTable = new DataTable('#tabelaProducao', {
      ajax: {
        url: '../back/getProducoes.php', // Endpoint PHP
        dataSrc: '' // DataTables já entende JSON como array de objetos
      },
      columns: [
        { data: 'producao_id' },
        { data: 'nome' }
      ],
      select: true,
      language: { url: "data/datatables-pt_br.json" },
      buttons: [
        {
            text: 'Nova Produção',
            action: function () {
                limpaCadastro();
                alternaCadastroConsulta("divCadastroProducao", "divConsultaProducoes");
            }
        },
        {
          text: 'Alterar Produção',
          action: function () {
            var selectedRow = oTable.row({ selected: true }).data(); // Pega os dados diretamente do DataTables
            if (selectedRow) {
              console.log("Dados para edição:", selectedRow);
              preencherFormulario('form-cadastro', selectedRow);
              alternaCadastroConsulta("divCadastroProducao", "divConsultaProducoes");
            } else {
              mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");
            }
          }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
      ],
      layout: {
        bottomStart: 'buttons'
      }
    });
  });
</script>