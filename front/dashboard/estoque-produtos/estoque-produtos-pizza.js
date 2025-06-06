//Cria um gráfico de pizza para mostrar a proporção entre estoque baixo, normal e alto de PRODUTOS. Serve para exibir a saúde geral do estoque.
//Mostra quantos produtos estão com estoque baixo, normal ou alto, com base na View vw_Estoque_Produtos_Alerta.

export async function renderPizzaEstoqueProdutos(canvasId) {
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
    let baixo = 0, normal = 0, alto = 0;

    data.forEach(item => {
        if (item.alerta === 'Estoque Baixo') baixo++;
        else if (item.alerta === 'Estoque Alto') alto++;
        else normal++;
    });

    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Estoque Baixo', 'Estoque Normal', 'Estoque Alto'],
            datasets: [{
                data: [baixo, normal, alto],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',   // Vermelho
                    'rgba(75, 192, 192, 0.6)',   // Verde Água
                    'rgba(255, 206, 86, 0.6)'    // Amarelo
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Distribuição de Estoque de Produtos',
                    padding: {
                        top: 10,
                        bottom: 50
                    },
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'bottom',
                    padding: {
                        top: 30,
                        bottom: 10
                    },
                    labels: {
                        padding: 20,
                        font: {
                            size: 14
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((acc, data) => acc + data, 0);
                        const percentage = ((value * 100) / total).toFixed(1) + '%';
                        return `${value}\n(${percentage})`;
                    },
                    anchor: 'end',
                    align: 'end',
                    offset: 10
                }
            }
        }
    });
}