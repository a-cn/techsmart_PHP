export function renderTabelaMovimentacoes(containerId) {
    // Adiciona estilos CSS para controlar o layout
    const style = document.createElement('style');
    style.textContent = `
        .bottom-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .dt-buttons {
            float: none !important;
        }
        .dataTables_paginate {
            float: none !important;
        }
        .dataTables_filter {
            display: none !important;
        }
        .filtros-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filtro-grupo {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        .filtro-grupo label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filtro-grupo select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .titulo-tabela {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
    `;
    document.head.appendChild(style);

    fetch('../back/getMovimentacoes.php')
        .then(response => response.json())
        .then(data => {
            const table = $('<table>').addClass('display responsive nowrap').width('100%');
            const container = $(`#${containerId}`);
            
            // Criar título
            const titulo = $('<h2>').addClass('titulo-tabela').text('Tabela de Movimentação de Produtos Finais');
            
            // Criar container de filtros
            const filtrosContainer = $('<div>').addClass('filtros-container');
            
            // Filtro de Produto
            const produtoFilter = $('<div>').addClass('filtro-grupo').append(
                $('<label>').text('Produto:'),
                $('<select>').append(
                    $('<option>').val('').text('Todos')
                )
            );
            
            // Filtro de Tipo de Movimentação
            const tipoMovFilter = $('<div>').addClass('filtro-grupo').append(
                $('<label>').text('Tipo de Movimentação:'),
                $('<select>').append(
                    $('<option>').val('').text('Todos')
                )
            );
            
            // Filtro de Pedido
            const pedidoFilter = $('<div>').addClass('filtro-grupo').append(
                $('<label>').text('Pedido:'),
                $('<select>').append(
                    $('<option>').val('').text('Todos')
                )
            );
            
            filtrosContainer.append(produtoFilter, tipoMovFilter, pedidoFilter);
            container.empty().append(titulo, filtrosContainer, table);

            // Função para formatar data
            function formatarData(dataStr) {
                const data = new Date(dataStr);
                const dia = String(data.getDate()).padStart(2, '0');
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const ano = data.getFullYear();
                const hora = String(data.getHours()).padStart(2, '0');
                const minuto = String(data.getMinutes()).padStart(2, '0');
                return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
            }

            // Inicializar DataTable
            const dataTable = $(table).DataTable({
                data: data,
                responsive: true,
                dom: '<"top"f>rt<"bottom-controls"<"left"B><"right"p>>i',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                columns: [
                    {
                        title: 'Nome do Produto',
                        data: 'nome_produto'
                    },
                    {
                        title: 'Tipo de Movimentação',
                        data: 'tipo_movimentacao'
                    },
                    {
                        title: 'Quantidade',
                        data: 'quantidade'
                    },
                    {
                        title: 'Data/Hora',
                        data: 'data_hora',
                        render: function(data) {
                            return formatarData(data);
                        }
                    },
                    {
                        title: 'Pedido',
                        data: 'pedido_id',
                        render: function(data) {
                            return data ? `Pedido ${data}` : '-';
                        }
                    }
                ],
                order: [[3, 'desc']], // Ordena por data/hora decrescente
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                }
            });

            // Preencher opções dos filtros
            const produtos = [...new Set(data.map(item => item.nome_produto))].sort();
            produtos.forEach(produto => {
                produtoFilter.find('select').append(
                    $('<option>').val(produto).text(produto)
                );
            });

            const tiposMovimentacao = [...new Set(data.map(item => item.tipo_movimentacao))].sort();
            tiposMovimentacao.forEach(tipo => {
                tipoMovFilter.find('select').append(
                    $('<option>').val(tipo).text(tipo)
                );
            });

            const pedidos = [...new Set(data.map(item => item.pedido_id).filter(Boolean))].sort((a, b) => a - b);
            pedidos.forEach(pedido => {
                pedidoFilter.find('select').append(
                    $('<option>').val(pedido).text(`Pedido ${pedido}`)
                );
            });

            // Aplicar filtros
            $('.filtro-grupo select').on('change', function() {
                const produtoVal = produtoFilter.find('select').val();
                const tipoMovVal = tipoMovFilter.find('select').val();
                const pedidoVal = pedidoFilter.find('select').val();

                dataTable.column(0).search(produtoVal);
                dataTable.column(1).search(tipoMovVal);
                
                // Ajuste no filtro de pedidos para correspondência exata
                if (pedidoVal) {
                    dataTable.column(4).search(`^Pedido ${pedidoVal}$`, true, false);
                } else {
                    dataTable.column(4).search('');
                }
                
                dataTable.draw();
            });
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            $(`#${containerId}`).html('<div class="alert alert-danger">Erro ao carregar dados da tabela</div>');
        });
}
