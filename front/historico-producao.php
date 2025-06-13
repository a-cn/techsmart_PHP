<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Produção</title>
  <!-- DataTables + Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      padding: 20px;
      background-color: #f7f7f7;
    }

    h2 {
      color: #007bff;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .info-bloco {
      margin-bottom: 20px;
      padding: 15px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
    }

    .info-bloco span {
      display: inline-block;
      margin-right: 20px;
      font-weight: 500;
    }

    label {
      font-weight: 500;
      margin-top: 10px;
    }

    .btn {
      margin-top: 10px;
      margin-right: 5px;
    }

    table.dataTable thead th {
      background-color: #f1f1f1;
    }
    
    .etapa-concluida {
      background-color: #e6ffe6;
    }
  </style>
</head>
<body>

  <h2>Iniciar Linha de Produção</h2>

  <div class="row mb-3">
    <div class="col-md-6">
      <label for="linhaProducao">Selecionar Linha de Produção:</label>
      <select id="linhaProducao" class="form-select">
        <option value="">Selecione...</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="produtoFinal">Produto Final:</label>
      <select id="produtoFinal" class="form-select" disabled>
        <option value="">Selecione a linha primeiro</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="quantidade">Quantidade a Produzir:</label>
      <input type="number" id="quantidade" class="form-control" min="1">
    </div>
  </div>
  
  <div class="row mb-3">
    <div class="col-md-12 d-flex justify-content-end">
      <button id="btnIniciar" class="btn btn-success">Iniciar Produção</button>
    </div>
  </div>

  <div id="infoProducao" class="info-bloco d-none">
    <span><strong>Linha:</strong> <span id="nomeLinha"></span></span>
    <span><strong>Produto:</strong> <span id="nomeProduto"></span></span>
    <span><strong>Quantidade:</strong> <span id="qtdProducao"></span></span>
    <span><strong>Início:</strong> <span id="dataInicio"></span></span>
    <span><strong>Previsão:</strong> <span id="dataPrevisao"></span></span>
    <span><strong>Término:</strong> <span id="dataTermino"></span></span>
  </div>

  <table id="tabelaEtapas" class="display nowrap" style="width:100%">
    <thead>
      <tr>
        <th>Ordem</th>
        <th>Nome da Etapa</th>
        <th>Componente Utilizado</th>
        <th>Situação</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
<script>
    let tabela;
    let producaoAtiva = null;
    
    $(document).ready(function () {
    // Configuração do DataTables com tradução embutida
    tabela = $('#tabelaEtapas').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
            "decimal": "",
            "emptyTable": "Nenhum dado disponível na tabela",
            "info": "Mostrando _START_ até _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 até 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros no total)",
            "infoPostFix": "",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Pesquisar:",
            "zeroRecords": "Nenhum registro correspondente encontrado",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": ativar para ordenar coluna de forma ascendente",
                "sortDescending": ": ativar para ordenar coluna de forma descendente"
            }
        },
        columns: [
            { data: 'ordem', title: 'Ordem' },
            { data: 'nome_etapa', title: 'Nome da Etapa' },
            { data: 'componente', title: 'Componente Utilizado' },
            { 
                data: 'concluida', 
                title: 'Situação',
                render: function(data, type, row) {
                    return data ? 'Concluída' : 'Pendente';
                }
            },
            {
                data: null,
                title: 'Ações',
                render: function(data, type, row) {
                    if (row.concluida) {
                        return '<button class="btn btn-sm btn-secondary" disabled>Concluído</button>';
                    } else {
                        return `<button class="btn btn-sm btn-primary btn-concluir" data-etapa="${row.etapa_producao_id}">Concluir</button>`;
                    }
                }
            }
        ],
        rowId: 'etapa_producao_id'
    });

    
    // Carrega as linhas de produção
    carregarLinhasProducao();
    
    // Quando seleciona uma linha de produção, carrega os produtos finais
    $('#linhaProducao').on('change', function() {
        const linhaId = $(this).val();
        if (linhaId) {
            carregarProdutosFinais(linhaId);
            $('#produtoFinal').prop('disabled', false);
        } else {
            $('#produtoFinal').prop('disabled', true).html('<option value="">Selecione a linha primeiro</option>');
        }
    });
    
    // Botão iniciar produção
    $('#btnIniciar').on('click', function () {
        const linhaId = $('#linhaProducao').val();
        const produtoId = $('#produtoFinal').val();
        const nomeLinha = $('#linhaProducao option:selected').text();
        const nomeProduto = $('#produtoFinal option:selected').text();
        const qtd = parseInt($('#quantidade').val());

        if (!linhaId || !produtoId || !qtd || qtd <= 0) {
            alert('Preencha todos os campos corretamente.');
            return;
        }

        $.ajax({
            url: '../back/producao_controller.php?action=iniciar',
            method: 'POST',
            data: {
                producao_id: linhaId,
                produto_id: produtoId,
                quantidade: qtd
            },
            dataType: 'json',
            success: function (resposta) {
                if (resposta.status === 'ok') {
                    producaoAtiva = resposta.producao;
                    console.log('Produção ativa:', producaoAtiva); // Debug
                    
                    $('#nomeLinha').text(nomeLinha);
                    $('#nomeProduto').text(nomeProduto);
                    $('#qtdProducao').text(qtd);
                    $('#dataInicio').text(formatarData(resposta.producao.data_inicio));
                    $('#dataPrevisao').text(formatarData(resposta.producao.data_previsao));
                    $('#dataTermino').text(resposta.producao.data_conclusao ? formatarData(resposta.producao.data_conclusao) : '-');
                    $('#infoProducao').removeClass('d-none');

                    // Preenche a tabela de etapas
                    tabela.clear().rows.add(resposta.etapas).draw();
                } else {
                    alert('Erro: ' + resposta.mensagem);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao iniciar produção:', error);
                alert('Erro ao iniciar produção. Consulte o console para detalhes.');
            }
        });
    });

    // Delegation para o botão concluir etapa (já que são dinâmicos)
    $('#tabelaEtapas').on('click', '.btn-concluir', function() {
        const etapaId = $(this).data('etapa');
        concluirEtapa(etapaId);
    });
});

function carregarLinhasProducao() {
    $.ajax({
        url: '../back/producao_controller.php?action=listar_linhas',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Dados recebidos:', data); // Para debug
            if (!Array.isArray(data)) {
                console.error('Os dados recebidos não são um array:', data);
                alert('Formato de dados inválido recebido do servidor');
                return;
            }
            
            const select = $('#linhaProducao');
            select.empty().append('<option value="">Selecione...</option>');

            data.forEach(function(linha) {
                if (linha.producao_id && linha.nome) {
                    select.append(`<option value="${linha.producao_id}">${linha.nome}</option>`);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            alert('Erro ao carregar linhas de produção. Verifique o console para detalhes.');
        }
    });
}

// Função modificada para carregar produtos finais
function carregarProdutosFinais(linhaId) {
    if (!linhaId) {
        $('#produtoFinal').prop('disabled', true).html('<option value="">Selecione a linha primeiro</option>');
        return;
    }

    $.ajax({
        url: '../back/producao_controller.php?action=listar_produtos',
        method: 'GET',
        data: { producao_id: linhaId },
        dataType: 'json',
        beforeSend: function() {
            $('#produtoFinal').prop('disabled', true).html('<option value="">Carregando produtos...</option>');
        },
        success: function(data) {
            const select = $('#produtoFinal');
            select.empty().append('<option value="">Selecione...</option>');

            if (data.length === 0) {
                select.append('<option value="" disabled>Nenhum produto disponível para esta linha</option>');
            } else {
                data.forEach(function(produto) {
                    select.append(`<option value="${produto.produtofinal_id}" data-dias="${produto.tempo_producao_dias}">${produto.nome}</option>`);
                });
            }
            
            select.prop('disabled', false);
            
            // Adiciona evento para mostrar o tempo de produção quando selecionar um produto
            select.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const diasProducao = selectedOption.data('dias');
                if (diasProducao) {
                    console.log(`Tempo de produção: ${diasProducao} dias`);
                    // Você pode exibir esta informação na interface se desejar
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar produtos finais:', error);
            $('#produtoFinal').prop('disabled', true).html('<option value="">Erro ao carregar produtos</option>');
            alert('Erro ao carregar produtos finais. Consulte o console para detalhes.');
        }
    });
}

// Modificação no evento change da linha de produção
$('#linhaProducao').on('change', function() {
    const linhaId = $(this).val();
    $('#produtoFinal').prop('disabled', true).html('<option value="">Selecione a linha primeiro</option>');
    $('#quantidade').val('');
    
    if (linhaId) {
        carregarProdutosFinais(linhaId);
    }
});

function concluirEtapa(etapaId) {
    if (!producaoAtiva || !producaoAtiva.historico_producao_id) {
        console.error('Produção ativa:', producaoAtiva);
        alert('Nenhuma produção ativa encontrada ou ID da produção inválido.');
        return;
    }

    console.log('Concluindo etapa:', {
        historico_id: producaoAtiva.historico_producao_id,
        etapa_id: etapaId
    });

    $.ajax({
        url: '../back/producao_controller.php?action=concluir_etapa',
        method: 'POST',
        data: {
            historico_id: producaoAtiva.historico_producao_id,
            etapa_id: etapaId
        },
        dataType: 'json',
        success: function (resposta) {
            if (resposta.status === 'ok') {
                // Atualiza a linha na tabela usando o ID da etapa
                const row = tabela.row(`#${etapaId}`);
                if (row.length) {
                    const data = row.data();
                    data.concluida = true;
                    row.data(data).draw();
                }
                
                // Atualiza a produção ativa
                producaoAtiva = resposta.producao;
                
                // Atualiza a data de término se a produção foi concluída
                if (resposta.producao.data_conclusao) {
                    $('#dataTermino').text(formatarData(resposta.producao.data_conclusao));
                }
                
                alert('Etapa concluída com sucesso!');
            } else {
                alert('Erro: ' + resposta.mensagem);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao concluir etapa:', error);
            console.error('Resposta do servidor:', xhr.responseText);
            alert('Erro ao concluir etapa. Consulte o console para detalhes.');
        }
    });
}

// Função para formatar a data
function formatarData(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    if (isNaN(data.getTime())) return 'Data inválida';
    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

</script>
</body>
</html>