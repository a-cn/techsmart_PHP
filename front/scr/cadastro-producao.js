// Variáveis globais
let producaoEditando = null; // Armazena a produção sendo editada
let componentesDisponiveis = []; // Armazena os componentes disponíveis

// Carrega componentes ao iniciar
document.addEventListener('DOMContentLoaded', async function() {
    await carregarComponentes();
    listarProducoes();
});

// Função auxiliar para verificar elementos
function getElementOrThrow(id) {
    const element = document.getElementById(id);
    if (!element) {
        throw new Error(`Elemento com ID ${id} não encontrado`);
    }
    return element;
}

// Carrega os componentes disponíveis
async function carregarComponentes() {
    try {
        const response = await fetch('../../Back/controlador_componente.php?acao=listar');
        if (!response.ok) throw new Error('Erro ao carregar componentes');
        componentesDisponiveis = await response.json();
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar componentes: ' + error.message);
    }
}

// Lista todas as produções cadastradas
async function listarProducoes() {
  try {
      const response = await fetch('../../Back/controlador_producao.php?acao=listar');
      if (!response.ok) throw new Error('Erro ao carregar produções');
      
      const dados = await response.json();
      const tbody = document.querySelector('#tabelaProducoes tbody');
      tbody.innerHTML = '';
      
      dados.forEach(item => {
          const tr = document.createElement('tr');
          
          // Criar objeto com os dados completos para edição
          const itemData = {
              id: item.id,
              tipo: item.tipo,
              etapas: item.etapas_json // JSON para edição
          };
          
          // Preenche as células da tabela
          tr.innerHTML = `
              <td>${item.id}</td>
              <td>${item.tipo}</td>
              <td>${item.etapas}</td>
              <td class="acoes">
                  <button class="editar" onclick='editarProducao(${JSON.stringify(itemData)})'>Editar</button>
                  <button class="excluir" onclick='excluirProducao(${item.id})'>Excluir</button>
              </td>
          `;
          tbody.appendChild(tr);
      });
  } catch (error) {
      console.error('Erro:', error);
      alert('Erro ao carregar produções: ' + error.message);
  }
}

// Gera os campos de etapas dinamicamente
function gerarCamposEtapas() {
    try {
        const qtd = parseInt(getElementOrThrow('quantidadeEtapas').value) || 0;
        const container = getElementOrThrow('containerEtapas');
        container.innerHTML = '';
        
        if (qtd > 0 && componentesDisponiveis.length > 0) {
            for (let i = 1; i <= qtd; i++) {
                const div = document.createElement('div');
                div.className = 'etapa-container';
                
                // Campo para nome da etapa
                const labelNome = document.createElement('label');
                labelNome.htmlFor = `etapa_nome_${i}`;
                labelNome.textContent = `Nome da Etapa ${i}:`;
                
                const inputNome = document.createElement('input');
                inputNome.type = 'text';
                inputNome.id = `etapa_nome_${i}`;
                inputNome.placeholder = `Nome da etapa ${i}`;
                inputNome.className = 'etapa-input';
                
                // Campo para seleção de componente
                const labelComponente = document.createElement('label');
                labelComponente.htmlFor = `etapa_componente_${i}`;
                labelComponente.textContent = `Componente para Etapa ${i}:`;
                
                const selectComponente = document.createElement('select');
                selectComponente.id = `etapa_componente_${i}`;
                selectComponente.className = 'etapa-select';
                
                // Opção padrão
                const optionPadrao = document.createElement('option');
                optionPadrao.value = '';
                optionPadrao.textContent = 'Selecione um componente';
                selectComponente.appendChild(optionPadrao);
                
                // Adiciona componentes disponíveis
                componentesDisponiveis.forEach(componente => {
                    const option = document.createElement('option');
                    option.value = componente.id;
                    option.textContent = componente.nome;
                    selectComponente.appendChild(option);
                });
                
                div.appendChild(labelNome);
                div.appendChild(inputNome);
                div.appendChild(labelComponente);
                div.appendChild(selectComponente);
                container.appendChild(div);
            }
        } else if (componentesDisponiveis.length === 0) {
            container.innerHTML = '<p class="erro">Nenhum componente cadastrado. Cadastre componentes antes de criar produções.</p>';
        }
    } catch (error) {
        console.error('Erro ao gerar campos:', error);
    }
}

// Salva uma nova produção
async function salvarProducao() {
    try {
        const tipo = getElementOrThrow('tipoProducao').value.trim();
        const qtd = parseInt(getElementOrThrow('quantidadeEtapas').value);
        
        // Validações
        if (!tipo || isNaN(qtd) || qtd <= 0) {
            alert("Preencha o tipo de produção e uma quantidade válida de etapas!");
            return;
        }

        // Coleta etapas com componentes
        const etapas = [];
        for (let i = 1; i <= qtd; i++) {
            const nome = getElementOrThrow(`etapa_nome_${i}`).value.trim();
            const componenteId = getElementOrThrow(`etapa_componente_${i}`).value;
            
            if (!nome) {
                alert(`Preencha o nome da etapa ${i}!`);
                return;
            }
            
            if (!componenteId) {
                alert(`Selecione um componente para a etapa ${i}!`);
                return;
            }
            
            etapas.push({
                nome: nome,
                componenteId: componenteId
            });
        }

        // Enviar para o backend
        const response = await fetch("../../Back/controlador_producao.php?acao=incluir", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tipo, etapas })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || "Erro ao cadastrar produção");
        }

        alert("Produção cadastrada com sucesso!");
        limparCampos();
        listarProducoes();
    } catch (error) {
        console.error('Erro ao salvar:', error);
        alert('Erro ao salvar produção: ' + error.message);
    }
}

// Função para editar produção
function editarProducao(item) {
  try {
      producaoEditando = item; // Armazena item sendo editado
      
      // Debug: mostrar dados recebidos
      console.log('Dados recebidos para edição:', item);
      
      // Preencher tipo de produção
      getElementOrThrow('tipoProducao').value = item.tipo || '';
      
      // Converter etapas JSON para array
      let etapas = [];
      if (item.etapas && item.etapas !== '[]') {
          try {
              etapas = JSON.parse(item.etapas);
          } catch (e) {
              console.error('Erro ao parsear etapas:', e);
              etapas = [];
          }
      }
      
      // Definir quantidade de etapas
      getElementOrThrow('quantidadeEtapas').value = etapas.length;
      
      // Gerar campos de etapas
      gerarCamposEtapas();
      
      // Preencher os campos após um pequeno delay (para garantir que os campos existam)
      setTimeout(() => {
          etapas.forEach((etapa, index) => {
              try {
                  const pos = index + 1;
                  getElementOrThrow(`etapa_nome_${pos}`).value = etapa.nome || '';
                  
                  const select = getElementOrThrow(`etapa_componente_${pos}`);
                  if (etapa.componenteId) {
                      select.value = etapa.componenteId;
                  }
              } catch (error) {
                  console.error(`Erro ao preencher etapa ${index + 1}:`, error);
              }
          });
          
          // Mudar o botão para modo de atualização
          const btnSalvar = document.querySelector('.btn-incluir');
          if (btnSalvar) {
              btnSalvar.textContent = 'Atualizar';
              btnSalvar.onclick = atualizarProducao;
          }
          
      }, 100);
      
  } catch (error) {
      console.error('Erro ao editar produção:', error);
      alert('Erro ao preparar edição: ' + error.message);
  }
}

// Função auxiliar para debug
function debugEdicao(item) {
  console.log('Dados recebidos para edição:', item);
  console.log('Tipo:', typeof item.etapas, 'Valor:', item.etapas);
  try {
      console.log('Etapas parseadas:', JSON.parse(item.etapas));
  } catch (e) {
      console.log('Não foi possível parsear as etapas como JSON');
  }
}

// Atualiza uma produção existente
async function atualizarProducao() {
  try {
      if (!producaoEditando) {
          alert("Nenhuma produção selecionada para edição!");
          return;
      }

      const tipo = getElementOrThrow('tipoProducao').value.trim();
      const qtd = parseInt(getElementOrThrow('quantidadeEtapas').value);
      
      if (!tipo || isNaN(qtd) || qtd <= 0) {
          alert("Preencha o tipo de produção e uma quantidade válida de etapas!");
          return;
      }

      // Coletar etapas com componentes
      const etapas = [];
      for (let i = 1; i <= qtd; i++) {
          const nome = getElementOrThrow(`etapa_nome_${i}`).value.trim();
          const componenteId = getElementOrThrow(`etapa_componente_${i}`).value;
          
          if (!nome) {
              alert(`Preencha o nome da etapa ${i}!`);
              return;
          }
          
          if (!componenteId) {
              alert(`Selecione um componente para a etapa ${i}!`);
              return;
          }
          
          etapas.push({
              nome: nome,
              componenteId: componenteId
          });
      }

      // DEBUG: Mostrar dados que serão enviados
      console.log('Dados para atualização:', {
          id: producaoEditando.id,
          tipo: tipo,
          etapas: etapas
      });

      // Enviar para atualização
      const response = await fetch("../../Back/controlador_producao.php?acao=editar", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
              id: producaoEditando.id,
              tipo: tipo,
              etapas: etapas
          })
      });

      const data = await response.json();
      
      if (!response.ok) {
          throw new Error(data.error || "Erro ao atualizar produção");
      }

      alert("Produção atualizada com sucesso!");
      limparCampos();
      listarProducoes();
      producaoEditando = null;
  } catch (error) {
      console.error('Erro ao atualizar:', error);
      alert('Erro ao atualizar produção: ' + error.message);
  }
}

// Exclui uma produção (soft delete)
function excluirProducao(id) {
    if (confirm('Deseja realmente excluir esta produção?')) {
        fetch(`../../Back/controlador_producao.php?acao=excluir&id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao excluir produção');
                }
                return response.json();
            })
            .then(data => {
                alert("Produção excluída com sucesso!");
                listarProducoes();
            })
            .catch(error => {
                console.error('Erro ao excluir:', error);
                alert('Erro ao excluir produção: ' + error.message);
            });
    }
}

// Limpa todos os campos do formulário
function limparCampos() {
    try {
        getElementOrThrow('tipoProducao').value = '';
        getElementOrThrow('quantidadeEtapas').value = '';
        getElementOrThrow('containerEtapas').innerHTML = '';
        
        // Restaura o botão para modo de salvar
        const btnSalvar = document.querySelector('.btn-incluir');
        if (btnSalvar) {
            btnSalvar.textContent = 'Salvar';
            btnSalvar.onclick = salvarProducao;
        }
        
        producaoEditando = null;
    } catch (error) {
        console.error("Erro ao limpar campos:", error);
    }
} 