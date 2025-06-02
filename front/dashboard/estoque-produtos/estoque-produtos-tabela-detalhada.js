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
    container.innerHTML = `
        <h3>Estoque Detalhado de Produtos Finais</h3>
        <label for="filtroSituacaoProduto">Filtrar por situação:</label>
        <select id="filtroSituacaoProduto">
            <option value="todos">Todos</option>
            <option value="Estoque Baixo">Estoque Baixo</option>
            <option value="Estoque Normal">Estoque Normal</option>
            <option value="Estoque Alto">Estoque Alto</option>
        </select>
        <table class="tabelaEstoque">
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

    const tabelaBody = container.querySelector("tbody");
    const selectFiltro = container.querySelector("#filtroSituacaoProduto");

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

    function aplicarFiltro() {
        const filtro = selectFiltro.value;
        tabelaBody.innerHTML = '';

        data.forEach(item => {
            if (filtro === 'todos' || item.alerta === filtro) {
                const tr = document.createElement('tr');
                tr.classList.add(item.alerta.replace(' ', '-').toLowerCase());

                tr.innerHTML = `
                    <td>${item.produto}</td>
                    <td>${item.quantidade}</td>
                    <td>${item.nivel_minimo}</td>
                    <td>${item.nivel_maximo}</td>
                    <td>${item.alerta}</td>
                `;
                tabelaBody.appendChild(tr);
            }
        });
    }

    selectFiltro.addEventListener('change', aplicarFiltro);
    aplicarFiltro();
}
