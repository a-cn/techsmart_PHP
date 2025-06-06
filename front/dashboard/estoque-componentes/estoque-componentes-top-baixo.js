/*
Cria um gráfico de barras para exibir os top 10 componentes com maior risco de atingir o nível mínimo (ponto crítico).
Para isso:
• Calcula a diferença entre quantidade do componente e seu nivel_minimo, e ordenar do menor para o maior;
• Mostra os componentes mais próximos de gerar um problema no estoque;
• Mesmo componentes com quantidade "alta" podem aparecer, se estiverem abaixo do seu mínimo.
*/

export async function renderTopEstoqueBaixoComponentes(canvasId) {
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

    const top10 = [...data]
        .map(p => ({
            ...p,
            risco: p.quantidade - p.nivel_minimo
        }))
        .sort((a, b) => a.risco - b.risco)
        .slice(0, 10);

    const labels = top10.map(p => p.componente);
    const quantidades = top10.map(p => p.quantidade);
    const cores = top10.map(p => {
        if (p.alerta === 'Estoque Baixo') return 'rgba(255, 99, 132, 0.6)';
        if (p.alerta === 'Estoque Alto') return 'rgba(255, 206, 86, 0.6)';
        return 'rgba(75, 192, 192, 0.6)';
    });

    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantidade em Estoque',
                data: quantidades,
                backgroundColor: cores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Top 10 Componentes com Menor Estoque Relativo ao Nível Mínimo',
                    padding: {
                        top: 10,
                        bottom: 50
                    },
                    font: {
                        size: 16
                    }
                },
                legend: {
                    display: false
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: (value) => value,
                    font: {
                        weight: 'bold'
                    },
                    padding: 6,
                    offset: 4
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            layout: {
                padding: {
                    top: 30,
                    right: 10,
                    left: 10,
                    bottom: 10
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}