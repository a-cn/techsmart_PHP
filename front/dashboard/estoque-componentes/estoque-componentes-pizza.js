//Cria um gráfico de pizza para mostrar a proporção entre estoque baixo, normal e alto de PRODUTOS. Serve para exibir a saúde geral do estoque.
//Mostra quantos produtos estão com estoque baixo, normal ou alto, com base na View vw_Estoque_Produtos_Alerta.

export async function renderPizzaEstoqueComponentes(canvasId) {
    console.log('Iniciando renderização do gráfico de pizza');
    
    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/estoque-componentes-alerta.php`;
    
    try {
        console.log('Buscando dados da API:', API_URL);
        const response = await fetch(API_URL);
        
        //Verifica se o endpoint foi localizado corretamente:
        if (!response.ok) {
            const textoErro = await response.text();
            console.error('Resposta não OK:', textoErro);
            throw new Error('Erro ao buscar dados do endpoint.');
        }

        const data = await response.json();
        console.log('Dados recebidos:', data);
        
        let baixo = 0, normal = 0, alto = 0;

        data.forEach(item => {
            if (item.alerta === 'Estoque Baixo') baixo++;
            else if (item.alerta === 'Estoque Alto') alto++;
            else normal++;
        });

        console.log('Contagem:', { baixo, normal, alto });

        const total = baixo + normal + alto;
        const calcularPorcentagem = (valor) => ((valor / total) * 100).toFixed(1);

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Canvas não encontrado:', canvasId);
            return;
        }

        // Ajusta o tamanho do canvas para corresponder ao seu container
        canvas.style.width = '100%';
        canvas.style.height = '100%';

        console.log('Canvas encontrado, iniciando criação do gráfico');
        const ctx = canvas.getContext('2d');

        // Destruir instância anterior do gráfico se existir
        const chartInstance = Chart.getChart(canvas);
        if (chartInstance) {
            console.log('Destruindo instância anterior do gráfico');
            chartInstance.destroy();
        }

        new Chart(ctx, {
            type: 'doughnut',
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
                maintainAspectRatio: false,
                cutout: '50%',
                layout: {
                    padding: {
                        top: 30,
                        right: 30,
                        bottom: 30,
                        left: 30
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuição Total do Estoque de Componentes',
                        padding: {
                            top: 10,
                            bottom: 50
                        },
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false  // legenda será renderizada separadamente
                    },
                    datalabels: {
                        color: '#000',
                        formatter: (value, ctx) => {
                            const porcentagem = calcularPorcentagem(value);
                            return `${value} (${porcentagem}%)`;
                        },
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        anchor: 'end',
                        align: 'end',
                        offset: 10,
                        backgroundColor: 'rgba(255, 255, 255, 0.8)',
                        borderRadius: 4,
                        padding: {
                            top: 4,
                            right: 4,
                            bottom: 4,
                            left: 4
                        },
                        display: function(context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Renderiza a legenda personalizada
        const legendaContainer = document.getElementById('legendaPizzaComponentes');
        const cores = ['rgba(255, 99, 132, 0.6)', 'rgba(75, 192, 192, 0.6)', 'rgba(255, 206, 86, 0.6)'];
        const labels = ['Estoque Baixo', 'Estoque Normal', 'Estoque Alto'];
        
        labels.forEach((label, index) => {
            const itemLegenda = document.createElement('div');
            itemLegenda.style.display = 'flex';
            itemLegenda.style.alignItems = 'center';
            itemLegenda.style.padding = '5px 10px';
            itemLegenda.style.borderRadius = '4px';
            itemLegenda.style.backgroundColor = cores[index];
            itemLegenda.textContent = label;
            legendaContainer.appendChild(itemLegenda);
        });
        
        console.log('Gráfico criado com sucesso');
    } catch (error) {
        console.error('Erro ao renderizar o gráfico:', error);
    }
}