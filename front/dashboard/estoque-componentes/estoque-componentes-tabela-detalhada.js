/*
• Lista todos os componentes com suas quantidades, níveis mínimo/máximo e alerta.

• Possui destaque de cor por linha de acordo com a situação:
Vermelho = Estoque Baixo;
Amarelo = Estoque Alto;
Verde = Estoque Normal.

• Tem filtros dinâmicos por componente e situação (<select>).
*/

export async function renderTabelaEstoqueComponentes(containerId) {
    const container = document.getElementById(containerId);
    
    // Adiciona os estilos para a tabela
    const style = document.createElement('style');
    style.textContent = `
        #tabelaEstoqueComponentes_filter {
            display: none;
        }
        
        /* Cores para as situações de estoque */
        #tabelaEstoqueComponentes tbody tr.estoque-normal {
            background-color: rgba(40, 167, 69, 0.15) !important;
        }
        #tabelaEstoqueComponentes tbody tr.estoque-normal:hover {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }
        
        #tabelaEstoqueComponentes tbody tr.estoque-alto {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }
        #tabelaEstoqueComponentes tbody tr.estoque-alto:hover {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }
        
        #tabelaEstoqueComponentes tbody tr.estoque-baixo {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }
        #tabelaEstoqueComponentes tbody tr.estoque-baixo:hover {
            background-color: rgba(220, 53, 69, 0.25) !important;
        }
        
        /* Garante que as cores se mantenham mesmo com o stripe do DataTables */
        #tabelaEstoqueComponentes tbody tr.odd,
        #tabelaEstoqueComponentes tbody tr.even {
            background-color: inherit !important;
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

        #filtroSituacaoComponente {
            height: 38px !important;
            min-width: 200px !important;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            appearance: none !important;
        }

        /* Estilo para os botões de exportação */
        div.dt-buttons {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .dt-button {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #fff;
            margin-right: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dt-button:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
    `;
    document.head.appendChild(style);
    
    container.innerHTML = `
        <h3>Estoque Detalhado de Componentes</h3>
        <div class="filtros-container">
            <div class="filtro-grupo">
                <label for="filtroComponente">Filtrar por componente:</label>
                <select id="filtroComponente" class="form-select">
                    <option value="todos">Todos</option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="filtroSituacaoComponente">Filtrar por situação:</label>
                <select id="filtroSituacaoComponente" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="Estoque Baixo">Estoque Baixo</option>
                    <option value="Estoque Normal">Estoque Normal</option>
                    <option value="Estoque Alto">Estoque Alto</option>
                </select>
            </div>
        </div>
        <table id="tabelaEstoqueComponentes" class="display responsive nowrap" style="width:100%">
            <thead>
            <tr>
                <th>Componente</th>
                <th>Quantidade</th>
                <th>Mínimo</th>
                <th>Máximo</th>
                <th>Situação</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

    const selectFiltroComponente = container.querySelector("#filtroComponente");
    const selectFiltroSituacao = container.querySelector("#filtroSituacaoComponente");
    let dataTable;

    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/estoque-componentes-alerta.php`;
    const response = await fetch(API_URL);
    
    //Verifica se o endpoint foi localizado corretamente:
    if (!response.ok) {
        const textoErro = await response.text();
        console.error('Resposta não OK:', textoErro);
        throw new Error('Erro ao buscar dados do endpoint.');
    }

    const data = await response.json();

    // Preenche o select de componentes com opções únicas
    const componentesUnicos = [...new Set(data.map(item => item.componente))].sort();
    componentesUnicos.forEach(componente => {
        const option = document.createElement('option');
        option.value = componente;
        option.textContent = componente;
        selectFiltroComponente.appendChild(option);
    });

    // Inicializa o Select2 no filtro de componentes
    $(selectFiltroComponente).select2({
        placeholder: 'Selecione ou pesquise um componente',
        allowClear: true,
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

    function aplicarFiltros() {
        const filtroComponente = selectFiltroComponente.value;
        const filtroSituacao = selectFiltroSituacao.value;
        
        // Se a DataTable já existe, destrua-a
        if (dataTable) {
            dataTable.destroy();
        }

        // Aplica ambos os filtros
        const dadosFiltrados = data.filter(item => {
            const matchComponente = filtroComponente === 'todos' || item.componente === filtroComponente;
            const matchSituacao = filtroSituacao === 'todos' || item.alerta === filtroSituacao;
            return matchComponente && matchSituacao;
        });

        // Inicializa a nova DataTable com os dados filtrados
        dataTable = $('#tabelaEstoqueComponentes').DataTable({
            data: dadosFiltrados,
            columns: [
                { data: 'componente' },
                { data: 'quantidade' },
                { data: 'nivel_minimo' },
                { data: 'nivel_maximo' },
                { data: 'alerta' }
            ],
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            rowCallback: function(row, data) {
                // Adiciona classes de cor baseadas no alerta
                $(row).removeClass('estoque-baixo estoque-normal estoque-alto');
                $(row).addClass(data.alerta.replace(' ', '-').toLowerCase());
            },
            dom: '<"top"f>rt<"bottom"Bp>', // Move os botões para baixo
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copiar'
                },
                {
                    extend: 'csv',
                    text: 'CSV'
                },
                {
                    extend: 'excel',
                    text: 'Excel'
                },
                {
                    extend: 'pdf',
                    text: 'PDF'
                },
                {
                    extend: 'print',
                    text: 'Imprimir'
                }
            ],
            pageLength: 10,
            order: [[0, 'asc']],
            stripeClasses: [] // Remove as classes de stripe padrão do DataTables
        });
    }

    // Adiciona listeners para ambos os filtros
    $(selectFiltroComponente).on('change', aplicarFiltros);
    selectFiltroSituacao.addEventListener('change', aplicarFiltros);
    
    // Aplica os filtros inicialmente
    aplicarFiltros();
}
