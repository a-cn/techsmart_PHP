//Cria um gráfico de barras agrupadas para exibir o total de consumo de componentes por pedido, indicando também a qual produto o componente se refere

export async function renderGraficoConsumoAgrupado(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `<canvas id="graficoConsumoAgrupado"></canvas>`;

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

        // Organiza os dados por pedido e componente
        const pedidos = [...new Set(dados.map(d => d.pedido))];
        const componentes = [...new Set(dados.map(d => d.componente))];

        const datasets = componentes.map(componente => ({
            label: componente,
            data: pedidos.map(pedido => {
                const registro = dados.find(d => d.pedido === pedido && d.componente === componente);
                return registro ? registro.quantidade : 0;
            }),
            backgroundColor: getRandomColor()
        }));

        new Chart(document.getElementById('graficoConsumoAgrupado'), {
            type: 'bar',
            data: {
                labels: pedidos,
                datasets: datasets
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Consumo de Componentes por Pedido'
                    },
                    //tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Quantidade Consumida' } },
                    y: { title: { display: true, text: 'Pedido' } }
                }
            }
        });

    } catch (error) {
        console.error('Erro ao carregar gráfico:', error);
        container.innerHTML += `<p style="color:red;">Erro ao carregar o gráfico. Verifique o console.</p>`;
    }

    function getRandomColor() {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        return `rgba(${r}, ${g}, ${b}, 0.6)`;
    }
}