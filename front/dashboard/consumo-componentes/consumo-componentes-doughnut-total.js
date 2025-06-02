//Cria um gráfico de doughnut visualizar componentes mais ou menos consumidos como um todo (proporção total de consumo de cada componente)

export async function renderGraficoConsumoTotalComponentes(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `<canvas id="graficoDoughnutComponentes"></canvas>`;

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

        // Agrupa total consumido por componente
        const consumoPorComponente = {};
        dados.forEach(item => {
            const comp = item.componente;
            consumoPorComponente[comp] = (consumoPorComponente[comp] || 0) + item.quantidade;
        });

        const labels = Object.keys(consumoPorComponente);
        const valores = Object.values(consumoPorComponente);

        new Chart(document.getElementById('graficoDoughnutComponentes'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: labels.map(() => getRandomColor())
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' },
                    title: {
                        display: true,
                        text: 'Proporção Total de Consumo de Componentes'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let valor = context.raw;
                                let total = context.chart._metasets[0].total;
                                let percentual = ((valor / total) * 100).toFixed(1);
                                return `${label}: ${valor} (${percentual}%)`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Erro ao carregar gráfico de doughnut:', error);
        container.innerHTML += `<p style="color:red;">Erro ao carregar o gráfico. Verifique o console.</p>`;
    }

    function getRandomColor() {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        return `rgba(${r}, ${g}, ${b}, 0.6)`;
    }
}