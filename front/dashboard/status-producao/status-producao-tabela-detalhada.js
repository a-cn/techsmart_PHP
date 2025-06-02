/*
Cria uma tabela interativa com DataTables para exibir os produtos conforme o status de produção ("Acabado" ou "Semiacabado"), com:
• Filtro dropdown por status;
• Destaque visual por cor em cada linha:
Verde = Acabado;
Amarelo = Semiacabado.
*/
//É necessário utilizar template literals (crase `), ao invés de aspas, para que ${BASE_URL} seja interpretado corretamente.

export async function renderTabelaStatusProducao(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <h3>Status Atual da Produção de Produtos</h3>
        <label for="filtroStatus">Filtrar por status:</label>
        <select id="filtroStatus">
            <option value="todos">Todos</option>
            <option value="Acabado">Acabado</option>
            <option value="Semiacabado">Semiacabado</option>
        </select>
        <table id="tabelaStatusProducao" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade em Estoque</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody></tbody>
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

    const tbody = container.querySelector('tbody');
    const filtro = container.querySelector('#filtroStatus');

    function atualizarTabela() {
        const statusSelecionado = filtro.value;

        tbody.innerHTML = ''; // Limpa a tabela

        dados.forEach(item => {
            if (statusSelecionado === 'todos' || item.status === statusSelecionado) {
                const tr = document.createElement('tr');
                tr.classList.add(item.status.toLowerCase()); // usado para o estilo (acabado ou semiacabado)

                tr.innerHTML = `
                    <td>${item.produto}</td>
                    <td>${item.quantidade}</td>
                    <td>${item.status}</td>
                `;
                tbody.appendChild(tr);
            }
        });

        if ($.fn.DataTable.isDataTable('#tabelaStatusProducao')) {
            $('#tabelaStatusProducao').DataTable().destroy();
        }

        new DataTable('#tabelaStatusProducao', {
            language: { url: `${BASE_URL}/front/data/datatables-pt_br.json` },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            info: false,
            select: true,
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            layout: { bottomStart: 'buttons' },
            responsive: true
        });
    }

    filtro.addEventListener('change', atualizarTabela);
    atualizarTabela();
}