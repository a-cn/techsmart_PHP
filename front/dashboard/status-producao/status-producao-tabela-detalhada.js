/*
Cria uma tabela interativa com DataTables para exibir o status de produção dos produtos, com:
• Filtro dropdown por status;
• Destaque visual por cor em cada linha conforme o status.
*/
//É necessário utilizar template literals (crase `), ao invés de aspas, para que ${BASE_URL} seja interpretado corretamente.

export async function renderTabelaStatusProducao(containerId) {
    const container = document.getElementById(containerId);
    
    // Adiciona estilos CSS para as cores dos status
    const styles = document.createElement('style');
    styles.textContent = `
        #tabelaStatusProducao tbody tr.status-acabado {
            background-color: rgba(40, 167, 69, 0.15) !important;
        }
        #tabelaStatusProducao tbody tr.status-producao-em-atraso {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }
        #tabelaStatusProducao tbody tr.status-acabado-com-atraso {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }
        /* Hover states para melhor contraste */
        #tabelaStatusProducao tbody tr.status-acabado:hover {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }
        #tabelaStatusProducao tbody tr.status-producao-em-atraso:hover {
            background-color: rgba(220, 53, 69, 0.25) !important;
        }
        #tabelaStatusProducao tbody tr.status-acabado-com-atraso:hover {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }
    `;
    document.head.appendChild(styles);

    container.innerHTML = `
        <h3>Status Atual de Produções Iniciadas</h3>
        <label for="filtroStatus">Filtrar por status:</label>
        <select id="filtroStatus">
            <option value="">Todos</option>
        </select>
        <table id="tabelaStatusProducao" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Nome do Produto</th>
                    <th>Data de Início</th>
                    <th>Data de Previsão</th>
                    <th>Data de Conclusão</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    `;

    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/status-produtos-producao.php`;
    const response = await fetch(API_URL);
    
    //Verifica se o endpoint foi localizado corretamente:
    if (!response.ok) {
        const textoErro = await response.text();
        console.error('Resposta não OK:', textoErro);
        throw new Error('Erro ao buscar dados do endpoint.');
    }

    const dados = await response.json();

    // Preenche o dropdown de filtro com status únicos
    const statusUnicos = [...new Set(dados.map(item => item.status))].sort();
    const filtro = container.querySelector('#filtroStatus');
    statusUnicos.forEach(status => {
        const option = document.createElement('option');
        option.value = status;
        option.textContent = status;
        filtro.appendChild(option);
    });

    function formatarData(data) {
        if (!data) return '-';
        return new Date(data).toLocaleDateString('pt-BR');
    }

    function normalizarClasseCSS(status) {
        // Remove acentos e caracteres especiais e converte para minúsculas
        return 'status-' + status.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]/g, '-');
    }

    // Inicializa o DataTable
    const tabela = new DataTable('#tabelaStatusProducao', {
        language: { url: `${BASE_URL}/front/data/datatables-pt_br.json` },
        data: dados,
        columns: [
            { data: 'produto_nome' },
            { 
                data: 'data_inicio',
                render: function(data) {
                    return formatarData(data);
                }
            },
            { 
                data: 'data_previsao',
                render: function(data) {
                    return formatarData(data);
                }
            },
            { 
                data: 'data_conclusao',
                render: function(data) {
                    return formatarData(data);
                }
            },
            { data: 'status' }
        ],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        info: false,
        select: true,
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        layout: {
            topStart: 'pageLength',
            bottomStart: 'buttons',
            bottomEnd: 'pagination'
        },
        responsive: true,
        order: [[1, 'desc']], // Ordena por data de início por padrão
        dom: '<"top"l>rt<"bottom d-flex justify-content-between"B<"ms-2"p>>', // Organiza os elementos na ordem correta
        createdRow: function(row, data) {
            $(row).addClass(normalizarClasseCSS(data.status));
        }
    });

    // Adiciona o evento de filtro
    filtro.addEventListener('change', function() {
        const statusSelecionado = this.value;
        
        if (statusSelecionado === "") {
            tabela.search('').columns().search('').draw();
        } else {
            tabela.column(4).search('^' + $.fn.dataTable.util.escapeRegex(statusSelecionado) + '$', true, false).draw();
        }
    });
}