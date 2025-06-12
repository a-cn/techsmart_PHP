/*
Cria uma tabela interativa com filtros combináveis, utilizando o DataTables.
Exibe os dados da view vw_Consumo_Componentes_Por_Pedido em uma tabela.
*/

export async function renderTabelaConsumoComponentes(containerId) {
    const container = document.getElementById(containerId);

    container.innerHTML = `
        <h3>Consumo de Componentes por Pedido</h3>
        <table id="tabelaConsumoComponentes" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Produto</th>
                    <th>Componente</th>
                    <th>Qtde Consumida</th>
                    <th>Custo</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

    try {
        //Resolve o problema de caminho:
        const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
        const API_URL = `${BASE_URL}/back/api/consumo-componentes-pedido.php`;
        const response = await fetch(API_URL);
        
        //Verifica se o endpoint foi localizado corretamente:
        if (!response.ok) {
            const textoErro = await response.text();
            console.error('Resposta não OK:', textoErro);
            throw new Error('Erro ao buscar dados do endpoint.');
        }
        
        const dados = await response.json();

        const tbody = container.querySelector('tbody');

        dados.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.pedido}</td>
                <td>${item.produto}</td>
                <td>${item.componente}</td>
                <td>${item.quantidade}</td>
                <td>R$ ${item.custo.toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });

        // Inicializa DataTable após renderizar conteúdo
        const dataTable = new DataTable('#tabelaConsumoComponentes', {
            select: true,
            info: false,
            language: { url: `${BASE_URL}/front/data/datatables-pt_br.json` },
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            layout: { bottomStart: 'buttons' },
            responsive: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'asc']]
        });

    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        container.innerHTML += `<p style="color:red;">Erro ao carregar os dados. Verifique o console.</p>`;
    }
}