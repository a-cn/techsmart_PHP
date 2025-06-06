<?php
require_once '../back/conexao_sqlserver.php';
require_once '../back/verifica_sessao.php';
$loginTimestamp = time();

// Carrega componentes disponíveis
$componentes = [];
$sql = "SELECT componente_id, nome FROM Componente WHERE ativo = 1";
$result = sqlsrv_query($conn, $sql);

if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $componentes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Produção</title>
  <link rel="stylesheet" type="text/css" href="css/janelas.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
  <!-- jQuery -->
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- DataTables JS -->
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
  <!-- DataTables Buttons -->
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
  <!-- Font Awesome para ícones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

  
  <div>
    <div class="janela-cadastro oculta" id="divCadastroProducao">
      <span class="titulo-janela">Cadastro de Produção</span>
      <form id="form-producao" class="form-content" method="POST" novalidate>
        <input type="hidden" name="acao" id="acao" value="incluir">
        <input type="hidden" name="id" id="producao_id" value="">

        <div class="form-row">
          <div class="form-group">
            <label for="tipoProducao">Tipo de Produção:</label>
            <input type="text" id="tipoProducao" name="tipo" placeholder="Digite o tipo de produção" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Adicionar Etapa:</label>
            <div style="display: flex; gap: 10px;">
              <input type="text" id="novaEtapaNome" placeholder="Nome da etapa" style="flex: 1;">
              <select id="novaEtapaComponente" style="flex: 1;">
                <option value="">Selecione um componente</option>
                <?php foreach ($componentes as $componente): ?>
                  <option value="<?= $componente['componente_id'] ?>"><?= htmlspecialchars($componente['nome']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn-cadastrar" onclick="adicionarEtapa()" style="width: auto;">
                <i class="fas fa-plus"></i> Adicionar
              </button>
            </div>
          </div>
        </div>

        <div id="containerEtapas" class="etapas-container">
          <h3>Etapas da Produção</h3>
          <div id="listaEtapas"></div>
        </div>

        <div class="form-row">
          <input type="submit" class="btn-cadastrar" value="Salvar Produção">
          <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroProducao','divConsultaProducoes');">Cancelar</button>
        </div>
      </form>
      <div id="error-message" class="error"></div>
    </div>

    <div class="janela-consulta" id="divConsultaProducoes">
      <span class="titulo-janela">Linhas de Produção Cadastradas</span>
      <div class="pesquisa">
        <input type="text" id="pesquisar" placeholder="Pesquisar linha de produção...">
        <button class="btn-pesquisar" onclick="pesquisarProducao()">Pesquisar</button>
      </div>
      <table id="tabelaProducoes" class="display nowrap" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>TIPO DE PRODUÇÃO</th>
            <th>ETAPAS</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <script>
  // Variáveis globais
  let producaoTable = null;
  const componentesDisponiveis = <?php echo json_encode($componentes); ?>;
  let etapas = [];

  // Função para adicionar nova etapa
  function adicionarEtapa() {
      const nome = document.getElementById('novaEtapaNome').value.trim();
      const componenteId = document.getElementById('novaEtapaComponente').value;
      
      if (!nome) {
          mostrarMensagem("Aviso", "Por favor, informe o nome da etapa", "alerta");
          return;
      }
      
      if (!componenteId) {
          mostrarMensagem("Aviso", "Por favor, selecione um componente", "alerta");
          return;
      }
      
      // Encontra o componente selecionado
      const componente = componentesDisponiveis.find(c => c.componente_id == componenteId);
      
      // Adiciona à lista de etapas
      etapas.push({
          nome: nome,
          componenteId: componenteId,
          componenteNome: componente.nome
      });
      
      // Atualiza a visualização
      renderizarEtapas();
      
      // Limpa os campos
      document.getElementById('novaEtapaNome').value = '';
      document.getElementById('novaEtapaComponente').value = '';
  }

  // Função para remover etapa
  function removerEtapa(index) {
      etapas.splice(index, 1);
      renderizarEtapas();
  }

  // Função para renderizar a lista de etapas
  function renderizarEtapas() {
      const listaEtapas = document.getElementById('listaEtapas');
      listaEtapas.innerHTML = '';
      
      if (etapas.length === 0) {
          listaEtapas.innerHTML = '<p>Nenhuma etapa adicionada</p>';
          return;
      }
      
      etapas.forEach((etapa, index) => {
          const div = document.createElement('div');
          div.className = 'etapa-item';
          div.innerHTML = `
              <div class="etapa-info">
                  <strong>${etapa.nome}</strong> - Componente: ${etapa.componenteNome}
              </div>
              <div class="etapa-acoes">
                  <button class="btn-remover" onclick="removerEtapa(${index})">
                      <i class="fas fa-trash"></i> Remover
                  </button>
              </div>
          `;
          listaEtapas.appendChild(div);
      });
  }

  // Função para editar uma produção
  async function editarProducao(id) {
      try {
          const response = await fetch(`../back/controlador_producao.php?acao=obter&id=${id}`);
          if (!response.ok) throw new Error('Erro ao carregar produção');
          
          const producao = await response.json();
          
          // Preenche os campos do formulário
          document.getElementById('producao_id').value = producao.id;
          document.getElementById('tipoProducao').value = producao.tipo;
          document.getElementById('acao').value = 'editar';
          
          // Carrega as etapas
          etapas = producao.etapas || [];
          renderizarEtapas();
          
          // Alterna para a janela de cadastro
          alternaCadastroConsulta("divCadastroProducao", "divConsultaProducoes");
          
      } catch (error) {
          console.error('Erro ao editar produção:', error);
          mostrarMensagem("Erro", "Não foi possível carregar a produção para edição", "erro");
      }
  }

  // Função para excluir uma produção
  function excluirProducao(id) {
      mostrarDialogo(
          "Confirmação", 
          "Deseja realmente excluir esta produção?", 
          async () => {
              try {
                  const response = await fetch(`../back/controlador_producao.php?acao=excluir&id=${id}`);
                  if (!response.ok) throw new Error('Erro ao excluir produção');
                  
                  const result = await response.json();
                  mostrarMensagem("Sucesso", "Produção excluída com sucesso!", "sucesso");
                  producaoTable.ajax.reload();
              } catch (error) {
                  console.error('Erro ao excluir:', error);
                  mostrarMensagem("Erro", "Não foi possível excluir a produção", "erro");
              }
          }, 
          () => {}
      );
  }

  // Função para pesquisar produções
  function pesquisarProducao() {
      const termo = document.getElementById('pesquisar').value;
      producaoTable.search(termo).draw();
  }

  // Inicialização quando o DOM estiver carregado
  document.addEventListener("DOMContentLoaded", function() {
      // Inicializa o DataTable
      producaoTable = $('#tabelaProducoes').DataTable({
          ajax: {
              url: '../back/controlador_producao.php?acao=listar',
              dataSrc: ''
          },
          columns: [
              { data: 'id' },
              { data: 'tipo' },
              { 
                  data: 'etapas',
                  render: function(data, type, row) {
                      return data || 'Sem etapas cadastradas';
                  }
              },
              { 
                  data: null,
                  render: function(data, type, row) {
                      return `
                          <button class="editar" onclick="editarProducao(${row.id})">
                              <i class="fas fa-edit"></i> Editar
                          </button>
                          <button class="excluir" onclick="excluirProducao(${row.id})">
                              <i class="fas fa-trash"></i> Excluir
                          </button>
                      `;
                  },
                  orderable: false
              }
          ],
          language: {
              url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
          },
          dom: 'Bfrtip',
          buttons: [
              {
                  text: '<i class="fas fa-plus"></i> Nova Produção',
                  action: function() {
                      document.getElementById('form-producao').reset();
                      document.getElementById('acao').value = 'incluir';
                      document.getElementById('producao_id').value = '';
                      etapas = [];
                      renderizarEtapas();
                      alternaCadastroConsulta("divCadastroProducao", "divConsultaProducoes");
                  }
              },
              {
                  extend: 'copy',
                  text: '<i class="fas fa-copy"></i> Copiar'
              },
              {
                  extend: 'excel',
                  text: '<i class="fas fa-file-excel"></i> Excel'
              },
              {
                  extend: 'pdf',
                  text: '<i class="fas fa-file-pdf"></i> PDF'
              },
              {
                  extend: 'print',
                  text: '<i class="fas fa-print"></i> Imprimir'
              }
          ],
          scrollX: true,
          responsive: true
      });

      // Configura o envio do formulário via AJAX
      document.getElementById('form-producao').addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const form = e.target;
          const formData = new FormData(form);
          const action = formData.get('acao');
          const url = `../back/controlador_producao.php?acao=${action}`;
          
          try {
              // Prepara os dados para envio
              const data = {
                  id: formData.get('id'),
                  tipo: formData.get('tipo'),
                  etapas: etapas
              };

              const response = await fetch(url, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                  },
                  body: JSON.stringify(data)
              });

              if (!response.ok) {
                  const errorData = await response.json();
                  throw new Error(errorData.error || 'Erro ao salvar produção');
              }

              const result = await response.json();
              mostrarMensagem("Sucesso", result.message || "Operação realizada com sucesso!", "sucesso");
              form.reset();
              etapas = [];
              renderizarEtapas();
              producaoTable.ajax.reload();
              alternaCadastroConsulta("divConsultaProducoes", "divCadastroProducao");
          } catch (error) {
              console.error('Erro:', error);
              mostrarMensagem("Erro", error.message || "Erro ao processar a requisição", "erro");
          }
      });
  });
  function alternaCadastroConsulta(idMostrar, idEsconder) {
      document.getElementById(idMostrar).classList.remove('oculta');
      document.getElementById(idEsconder).classList.add('oculta');
  }

  function limpaCadastroAlternaEdicao(idMostrar, idEsconder) {
      document.getElementById('form-producao').reset();
      etapas = [];
      renderizarEtapas();
      alternaCadastroConsulta(idMostrar, idEsconder);
  }
  </script>

  <style>
  .etapas-container {
      margin: 20px 0;
      padding: 15px;
      background-color: #f9f9f9;
      border-radius: 5px;
      border: 1px solid #ddd;
  }

  .etapa-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      margin-bottom: 10px;
      background-color: #fff;
      border-radius: 5px;
      border: 1px solid #eee;
  }

  .etapa-info {
      flex: 1;
  }

  .etapa-acoes {
      margin-left: 15px;
  }

  .btn-remover {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
  }

  .btn-remover:hover {
      opacity: 0.9;
  }

  .editar, .excluir {
      padding: 5px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      color: white;
      margin-right: 5px;
  }

  .editar {
      background-color: #ffc107;
  }

  .excluir {
      background-color: #dc3545;
  }

  .error {
      color: #dc3545;
      margin-top: 10px;
      font-weight: bold;
  }

  .btn-cadastrar, .btn-pesquisar {
      display: inline-flex;
      align-items: center;
      gap: 5px;
  }
  </style>
</body>
</html>