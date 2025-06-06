//Gráfico de doughnut, mostrando a proporção total entre avaliações positivas, negativas, neutras e a média

export async function renderDoughnutResumoAvaliacoes() {
    try {
        //Resolve o problema de caminho:
        const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
        const API_URL = `${BASE_URL}/back/api/resumo-avaliacoes-geral.php`;
        const response = await fetch(API_URL);
        
        //Verifica se o endpoint foi localizado corretamente:
        if (!response.ok) {
            const textoErro = await response.text();
            console.error('Resposta não OK:', textoErro);
            throw new Error('Erro ao buscar dados do endpoint.');
        }

        const data = await response.json();

        const valores = [data.positivas, data.neutras, data.negativas];
        const total = data.total;

        const labels = ['Positivas', 'Neutras', 'Negativas'];
        const cores = [
            'rgba(75, 192, 192, 0.7)',     // verde-água
            'rgba(255, 206, 86, 0.7)',     // amarelo
            'rgba(255, 99, 132, 0.7)'      // vermelho
        ];

        const ctx = document.getElementById('graficoDoughnutAvaliacoes').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: cores
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 25 // Adiciona padding entre os itens da legenda
                        }
                    },
                    title: {
                        display: true,
                        text: 'Proporção Geral de Avaliações'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const val = context.raw;
                                const percent = ((val / total) * 100).toFixed(1);
                                return `${context.label}: ${val} (${percent}%)`;
                            }
                        }
                    },
                    datalabels: {
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        formatter: function(value, context) {
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${value} (${percent}%)`;
                        },
                        anchor: 'end',
                        align: 'end',
                        offset: 8,
                        textAlign: 'left'
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Exibe a média geral
        const media = parseFloat(data.media).toFixed(2);
        const mediaElement = document.getElementById('mediaGeralAvaliacoes');
        mediaElement.innerText = `Média Geral: ${media}`;

        // Cor personalizada conforme desempenho
        if (media >= 4) {
            mediaElement.style.color = 'green';
        } else if (media >= 3) {
            mediaElement.style.color = 'orange';
        } else {
            mediaElement.style.color = 'red';
        }

    } catch (error) {
        console.error('Erro ao carregar dados do gráfico:', error);
        document.getElementById('containerDoughnutFeedback').innerHTML +=
            `<p style="color:red;">Erro ao carregar os dados. Verifique o console.</p>`;
    }
}