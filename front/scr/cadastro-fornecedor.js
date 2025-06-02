//Trata do cadastro de fornecedores:
document.getElementById('formFornecedor').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('../../Back/cadastro_fornecedor.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Fornecedor cadastrado com sucesso!');
        this.reset();
      } else {
        alert('Erro ao cadastrar fornecedor: ' + (data.error || 'Erro desconhecido'));
        console.error(data);
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
      alert('Erro na requisição. Veja o console.');
    });
});

//Trata da busca pelo CEP no formulário de cadastro:
document.getElementById('buscarCep').addEventListener('click', function () {
  const cep = document.getElementById('cep').value.replace(/\D/g, '');

  if (cep.length !== 8) {
    alert('CEP inválido!');
    return;
  }

  fetch(`https://viacep.com.br/ws/${cep}/json/`)
    .then(res => res.json())
    .then(data => {
      if (data.erro) {
        alert('CEP não encontrado!');
        return;
      }

      document.getElementById('logradouro').value = data.logradouro || '';
      document.getElementById('bairro').value = data.bairro || '';
      document.getElementById('cidade').value = data.localidade || '';
      document.getElementById('estado').value = data.estado || '';
    })
    .catch(() => alert('Erro ao buscar o CEP.'));
});

//Trata da listagem de registros na tela:
function carregarFornecedores() {
  fetch('../../Back/controlador_fornecedor.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'read' })
  })
  .then(res => res.json())
  .then(fornecedores => {
    const tbody = document.querySelector('#tabelaFornecedores tbody');
    tbody.innerHTML = ''; //Limpa a tabela

    fornecedores.forEach(f => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="checkbox" class="selecionar-fornecedor" data-id="${f.fornecedor_id}"></td>
        <td>${f.cpf_cnpj}</td>
        <td>${f.nome}</td>
        <td>${f.endereco}</td>
        <td>
          ${f.email}<br>
          ${f.num_principal}
          ${f.num_secundario ? '<br>' + f.num_secundario : ''}
        </td>
        <td>${f.situacao}</td>
        <td>
          <button onclick="arquivarFornecedor(${f.fornecedor_id})">Arquivar</button>
          <button onclick="excluirFornecedor(${f.fornecedor_id})">Excluir</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  })
  .catch(error => console.error('Erro ao carregar fornecedores:', error));
}
window.addEventListener('DOMContentLoaded', carregarFornecedores); //Chama a função para carregar os componentes ao acessar a página

//Trata da exclusão lógica do registro (arquivamento):
function arquivarFornecedor(id) {
  if (!confirm('Deseja arquivar este fornecedor?')) return;

  fetch('../../Back/controlador_fornecedor.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'arquivar', id })
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      alert('Fornecedor arquivado com sucesso!');
      carregarFornecedores(); //Atualiza a lista
    } else {
      alert('Erro ao arquivar fornecedor.');
    }
  })
  .catch(error => console.error('Erro ao arquivar fornecedor:', error));
}

//Trata da exclusão física (definitiva) do registro:
function excluirFornecedor(id) {
  if (!confirm('Tem certeza que deseja excluir permanentemente este fornecedor e endereço associado? Essa ação não poderá ser desfeita.')) {
    return;
  }

  fetch('../../Back/controlador_fornecedor.php', {
    method: 'POST',
    body: new URLSearchParams({
      action: 'excluir',
      id: id
    })
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      alert('Fornecedor e endereço excluídos com sucesso!');
      carregarFornecedores(); //Atualiza a tabela com os dados atualizados
    } else {
      alert('Erro ao excluir fornecedor: ' + (result.error || 'Erro desconhecido.'));
      console.error(result);
    }
  })
  .catch(error => {
    alert('Erro na requisição. Veja o console para mais detalhes.');
    console.error('Erro na exclusão:', error);
  });
}