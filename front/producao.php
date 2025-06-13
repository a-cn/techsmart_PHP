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
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- jQuery -->
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- DataTables JS -->
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
  <!-- DataTables Buttons -->
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
+            <th>AÇÕES</th>
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
  let custosComponentes = {};

  // Adicionar esta função para buscar custos dos componentes
async function buscarCustosComponentes() {
    try {
        const response = await fetch('../back/controlador_producao.php?action=buscar_custos');
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Erro ao buscar custos: ${errorText}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao buscar custos:', error);
        return {};
    }   
}

async function adicionarEtapa() {
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
    
    // Se ainda não temos os custos, buscamos
    if (Object.keys(custosComponentes).length === 0) {
        try {
            const response = await fetch('../back/controlador_producao.php?action=buscar_custos');
            if (!response.ok) throw new Error('Erro ao buscar custos');
            custosComponentes = await response.json();
        } catch (error) {
            console.error('Erro ao buscar custos:', error);
            custosComponentes = {};
        }
    }
    
    // Encontra o componente selecionado
    const componente = componentesDisponiveis.find(c => c.componente_id == componenteId);
    const custo = custosComponentes[componenteId] || 0;
    
    // Adiciona à lista de etapas
    etapas.push({
        nome: nome,
        componenteId: componenteId,
        componenteNome: componente.nome,
        custo: parseFloat(custo)
    });
    
    // Atualiza a visualização
    renderizarEtapas();
    
    // Limpa os campos
    document.getElementById('novaEtapaNome').value = '';
    document.getElementById('novaEtapaComponente').value = '';
}

// Atualizar a função renderizarEtapas para mostrar custos
function renderizarEtapas() {
    const listaEtapas = document.getElementById('listaEtapas');
    listaEtapas.innerHTML = '';
    
    if (etapas.length === 0) {
        listaEtapas.innerHTML = '<p class="nenhuma-etapa">Nenhuma etapa adicionada</p>';
        return;
    }
    
    let totalCusto = 0;
    
    etapas.forEach((etapa, index) => {
        totalCusto += etapa.custo;
        
        const divEtapa = document.createElement('div');
        divEtapa.className = 'etapa-item';
        
        const divInfo = document.createElement('div');
        divInfo.className = 'etapa-info';
        divInfo.innerHTML = `
            <span class="etapa-nome">${etapa.nome}</span>
            <span class="etapa-componente">Componente: ${etapa.componenteNome}</span>
            <span class="etapa-custo">Custo: R$ ${etapa.custo.toFixed(2)}</span>
        `;
        
        const divAcoes = document.createElement('div');
        divAcoes.className = 'etapa-acoes';
        divAcoes.innerHTML = `
            <button class="btn-remover" onclick="removerEtapa(${index})">
                <i class="fas fa-trash"></i> Remover
            </button>
        `;
        
        divEtapa.appendChild(divInfo);
        divEtapa.appendChild(divAcoes);
        listaEtapas.appendChild(divEtapa);
    });
    
    // Adiciona o total
    const divTotal = document.createElement('div');
    divTotal.className = 'etapa-total';
    divTotal.innerHTML = `<strong>Total:</strong> R$ ${totalCusto.toFixed(2)}`;
    listaEtapas.appendChild(divTotal);
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
          () => {},
          "alerta"
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
        url: '../back/controlador_producao.php',
        data: { acao: 'listar' },  // Envia como parâmetro POST
        type: 'GET',  // Alternativa mais segura que GET
        dataSrc: 'data',  // Acessa a propriedade 'data' da resposta
        error: function(xhr) {
            let errorMsg = 'Erro ao carregar dados';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg += ': ' + (response.error || xhr.statusText);
                console.error('Detalhes:', response);
            } catch (e) {
                errorMsg += '. Status: ' + xhr.status;
            }
            $('#tabelaProducoes').html(`<div class="error-msg">${errorMsg}</div>`);
        }
    },
    columns: [
        { data: 'id' },
        { data: 'tipo' },
        { 
            data: null,
            render: function(data, type, row) {
                try {
                    let html = '<div class="lista-etapas">';
                    
                    if (row.etapas && row.etapas !== 'Sem etapas cadastradas') {
                        const etapasArray = row.etapas.split(', ');
                        etapasArray.forEach(etapa => {
                            html += `<div class="etapa-linha">${etapa.trim()}</div>`;
                        });
                    } else {
                        html += '<div class="etapa-linha">Sem etapas cadastradas</div>';
                    }
                    
                    if (row.custo_total) {
                        html += `<div class="custo-total">Custo Total: R$ ${parseFloat(row.custo_total).toFixed(2)}</div>`;
                    }
                    
                    return html + '</div>';
                } catch (e) {
                    console.error('Erro ao renderizar etapas:', e);
                    return 'Erro ao carregar etapas';
                }
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
            }
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
                  etapas: etapas.map(etapa => ({
                      nome: etapa.nome,
                      componenteId: etapa.componenteId
                  }))
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

      // Inicializa o Select2 no campo de seleção de componente
      $('#novaEtapaComponente').select2({
          placeholder: "Selecione um componente",
          allowClear: true,
          width: '100%',
          dropdownParent: $('#novaEtapaComponente').parent()
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
 /* Estilos para a lista de etapas na tabela */
.lista-etapas {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.etapa-linha {
    padding: 4px 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

/* Ajustes para a tabela */
#tabelaProducoes td {
    vertical-align: top;
    padding: 8px 12px;
}

#tabelaProducoes .lista-etapas {
    max-height: 200px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Barra de rolagem personalizada */
#tabelaProducoes .lista-etapas::-webkit-scrollbar {
    width: 5px;
}

#tabelaProducoes .lista-etapas::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#tabelaProducoes .lista-etapas::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
}

#tabelaProducoes .lista-etapas::-webkit-scrollbar-thumb:hover {
    background: #555;
}
.custo-total {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #ccc;
    font-weight: bold;
    color: #28a745;
}
/* Botões de ação */
.editar, .excluir {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: white;
    margin-right: 5px;
    transition: all 0.2s;
}

.editar {
    background-color: #ffc107;
}

.editar:hover {
    background-color: #e0a800;
}

.excluir {
    background-color: #dc3545;
}

.excluir:hover {
    background-color: #c82333;
}

/* Formulário */
.form-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
}

.form-group input,
.form-group select {
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.15s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn-cadastrar {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    transition: all 0.2s;
}

.btn-cadastrar:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

.btn-pesquisar {
    background-color: #17a2b8;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    transition: all 0.2s;
}

.btn-pesquisar:hover {
    background-color: #138496;
    transform: translateY(-1px);
}

/* Estilos para o Select2 */
.select2-container {
  width: 100% !important;
  flex: 1;
}
.select2-container--default .select2-selection--single {
  height: 45px;
  border: 1px solid #ccc;
  display: flex;
  align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: normal !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 100%;
}
  </style>
</body>
</html>