//Cria um gráfico de linha com filtros interativos para mostrar a demanda por produto ao longo dos meses

export async function renderPrevisaoDemanda(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <h3>Demanda por Produto</h3>
        <div class="filtros">
            <label for="filtroProduto">Filtrar por Produto:</label>
            <select id="filtroProduto">
                <option value="">Todos</option>
            </select>

            <label for="filtroMesDemanda" style="margin-left: 1rem;">Filtrar por Mês:</label>
            <select id="filtroMesDemanda">
                <option value="">Todos</option>
            </select>
        </div>
        <canvas id="graficoPrevisaoDemanda"></canvas>
    `;

    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/previsao-demanda.php`;
    const response = await fetch(API_URL);
    
    //Verifica se o endpoint foi localizado corretamente:
    if (!response.ok) {
        const textoErro = await response.text();
        console.error('Resposta não OK:', textoErro);
        throw new Error('Erro ao buscar dados do endpoint.');
    }
    
    const json = await response.json();
    const { labels, datasets } = json;

    const filtroProduto = container.querySelector('#filtroProduto');
    const filtroMesDemanda = container.querySelector('#filtroMesDemanda');
    const ctx = container.querySelector('#graficoPrevisaoDemanda');
    let chart;

    // Preenche filtros
    datasets.forEach(ds => {
        const opt = document.createElement('option');
        opt.value = ds.label;
        opt.textContent = ds.label;
        filtroProduto.appendChild(opt);
    });

    labels.forEach(mes => {
        const opt = document.createElement('option');
        opt.value = mes;
        opt.textContent = mes;
        filtroMesDemanda.appendChild(opt);
    });

    function desenharGrafico(produtoSelecionado = '', mesSelecionado = '') {
        const labelsFiltradas = mesSelecionado
            ? labels.filter(m => m === mesSelecionado)
            : labels;

        const datasetsFiltrados = datasets
            .filter(ds => !produtoSelecionado || ds.label === produtoSelecionado)
            .map(ds => {
                const dataFiltrada = mesSelecionado
                    ? [ds.data[labels.indexOf(mesSelecionado)] ?? 0]
                    : ds.data;
                return {
                    label: ds.label,
                    data: dataFiltrada,
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 7
                };
            });

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsFiltradas,
                datasets: datasetsFiltrados
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Demanda Mensal por Produto'
                    },
                    //Garante que o usuário veja todas as linhas próximas ao passar o mouse:
                    tooltip: {
                        mode: 'nearest',
                        intersect: false
                    }
                }
            }
        });
    }

    filtroProduto.addEventListener('change', () => {
        desenharGrafico(filtroProduto.value, filtroMesDemanda.value);
    });

    filtroMesDemanda.addEventListener('change', () => {
        desenharGrafico(filtroProduto.value, filtroMesDemanda.value);
    });

    // Mostra o gráfico completo por padrão
    desenharGrafico();
}