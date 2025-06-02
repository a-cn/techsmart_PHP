/*
Cria um gráfico de barras para exibir os top 10 produtos com maior risco de atingir o nível mínimo (ponto crítico).
Para isso:
• Calcula a diferença entre quantidade do produto e seu nivel_minimo, e ordenar do menor para o maior;
• Mostra os produtos mais próximos de gerar um problema no estoque;
• Mesmo produtos com quantidade “alta” podem aparecer, se estiverem abaixo do seu mínimo.
*/

export async function renderTopEstoqueBaixoProdutos(canvasId) {
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

    const top10 = [...data]
        .map(p => ({
            ...p,
            risco: p.quantidade - p.nivel_minimo
        }))
        .sort((a, b) => a.risco - b.risco)
        .slice(0, 10);

    const labels = top10.map(p => p.produto);
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
            plugins: {
                title: {
                    display: true,
                    text: 'Top 10 Produtos com Menor Estoque'
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}