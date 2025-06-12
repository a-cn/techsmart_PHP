/*
• Lista todos os produtos com suas quantidades, níveis mínimo/máximo e alerta.

• Possui destaque de cor por linha de acordo com a situação:
Vermelho = Estoque Baixo;
Amarelo = Estoque Alto;
Verde = Estoque Normal.

• Tem um filtro dinâmico por situação (<select>).
*/

export async function renderTabelaEstoqueProdutos(containerId) {
    const container = document.getElementById(containerId);
    
    // Adiciona os estilos CSS para as cores das linhas e Select2
    const styles = document.createElement('style');
    styles.textContent = `
        #tabelaEstoqueProdutos tbody tr.estoque-normal {
            background-color: rgba(40, 167, 69, 0.15) !important;
        }
        #tabelaEstoqueProdutos tbody tr.estoque-normal:hover {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }
        #tabelaEstoqueProdutos tbody tr.estoque-alto {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }
        #tabelaEstoqueProdutos tbody tr.estoque-alto:hover {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }
        #tabelaEstoqueProdutos tbody tr.estoque-baixo {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }
        #tabelaEstoqueProdutos tbody tr.estoque-baixo:hover {
            background-color: rgba(220, 53, 69, 0.25) !important;
        }

        /* Estilos para o Select2 e filtros */
        .filtros-container {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
        }

        .filtro-grupo {
            display: flex;
            flex-direction: column;
        }

        .filtro-grupo label {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .select2-container {
            min-width: 200px !important;
        }
        
        .select2-container .select2-selection--single {
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 12px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 5px !important;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 6px !important;
        }
        
        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
    `;
    container.appendChild(styles);

    container.innerHTML += `
        <h3>Estoque Detalhado de Produtos Acabados</h3>
        <div class="filtros-container" style="margin-bottom: 20px; display: flex; gap: 20px;">
            <div class="filtro-grupo">
                <label for="filtroProduto">Filtrar por Produto:</label>
                <select id="filtroProduto" style="min-width: 200px; padding: 5px;">
                    <option value="todos">Todos</option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="filtroSituacao">Filtrar por Situação:</label>
                <select id="filtroSituacao" style="min-width: 150px; padding: 5px;">
                    <option value="">Todas as Situações</option>
                    <option value="Estoque Baixo">Estoque Baixo</option>
                    <option value="Estoque Normal">Estoque Normal</option>
                    <option value="Estoque Alto">Estoque Alto</option>
                </select>
            </div>
        </div>
        <table id="tabelaEstoqueProdutos" class="display responsive nowrap" style="width:100%">
            <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Mínimo</th>
                <th>Máximo</th>
                <th>Situação</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/estoque-produtos-alerta.php`;
    const response = await fetch(API_URL);
    
    //Verifica se o endpoint foi localizado corretamente:
    if (!response.ok) {
        const textoErro = await response.text();
        console.error('Resposta não OK:', textoErro);
        throw new Error('Erro ao buscar dados do endpoint.');
    }
    
    const data = await response.json();

    // Preenche o select de produtos com os valores únicos
    const produtos = [...new Set(data.map(item => item.produto))].sort();
    const selectProduto = document.getElementById('filtroProduto');
    
    // Remove todas as opções existentes
    selectProduto.innerHTML = '';
    
    // Adiciona a opção "Todos"
    const optionTodos = document.createElement('option');
    optionTodos.value = 'todos';
    optionTodos.textContent = 'Todos';
    selectProduto.appendChild(optionTodos);
    
    // Adiciona as demais opções
    produtos.forEach(produto => {
        const option = document.createElement('option');
        option.value = produto;
        option.textContent = produto;
        selectProduto.appendChild(option);
    });

    // Inicializa o Select2 no filtro de produtos
    $(selectProduto).select2({
        language: {
            noResults: function() {
                return "Nenhum resultado encontrado";
            },
            searching: function() {
                return "Pesquisando...";
            }
        }
    }).on('select2:open', function() {
        document.querySelector('.select2-search__field').focus();
    });

    // Inicializa o DataTable
    const table = $('#tabelaEstoqueProdutos').DataTable({
        data: data,
        responsive: true,
        dom: 'lrtip',
        columns: [
            { data: 'produto' },
            { data: 'quantidade' },
            { data: 'nivel_minimo' },
            { data: 'nivel_maximo' },
            { data: 'alerta' }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        createdRow: function(row, data, dataIndex) {
            $(row).addClass(data.alerta.replace(' ', '-').toLowerCase());
        }
    });

    // Adiciona os event listeners para os filtros
    $('#filtroProduto').on('change', function() {
        const produtoSelecionado = $(this).val();
        if (produtoSelecionado === 'todos') {
            table.column(0).search('').draw();
        } else {
            table.column(0).search(produtoSelecionado).draw();
        }
    });

    $('#filtroSituacao').on('change', function() {
        const situacaoSelecionada = $(this).val() || '';
        table.column(4).search(situacaoSelecionada).draw();
    });
}
