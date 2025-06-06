//Cria um gráfico de barras agrupadas para exibir o total de consumo de componentes por pedido, indicando também a qual produto o componente se refere

export async function renderGraficoConsumoAgrupado(containerId) {
    const container = document.getElementById(containerId);
    
    // Adiciona o container do filtro e do gráfico
    container.innerHTML = `
        <div style="margin-bottom: 20px; text-align: center;">
            <label for="selectPedido" style="margin-right: 10px;">Selecione o Pedido:</label>
            <select id="selectPedido" style="padding: 5px; border-radius: 4px;"></select>
        </div>
        <div style="height: 500px; position: relative;">
            <canvas id="graficoConsumoAgrupado"></canvas>
        </div>
    `;

    try {
        const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
        const API_URL = `${BASE_URL}/back/api/consumo-componentes-pedido.php`;
        const response = await fetch(API_URL);
        
        if (!response.ok) {
            const textoErro = await response.text();
            console.error('Resposta não OK:', textoErro);
            throw new Error('Erro ao buscar dados do endpoint.');
        }
        
        const dados = await response.json();
        let grafico = null;

        // Popula o select com os pedidos únicos
        const selectPedido = document.getElementById('selectPedido');
        const pedidosUnicos = [...new Set(dados.map(d => d.pedido))].sort((a, b) => a - b);
        
        pedidosUnicos.forEach(pedido => {
            const option = document.createElement('option');
            option.value = pedido;
            option.textContent = `Pedido ${pedido}`;
            selectPedido.appendChild(option);
        });

        // Função para atualizar o gráfico
        function atualizarGrafico(pedidoSelecionado) {
            // Filtra os dados para o pedido selecionado
            const dadosFiltrados = dados.filter(d => d.pedido === pedidoSelecionado);
            
            // Prepara os dados para o gráfico
            const labels = dadosFiltrados.map(d => d.componente);
            const valores = dadosFiltrados.map(d => d.quantidade);
            const cores = labels.map(() => getRandomColor());

            // Encontra o valor máximo para ajustar a escala
            const maxValue = Math.max(...valores);

            // Se já existe um gráfico, destrói ele
            if (grafico) {
                grafico.destroy();
            }

            // Cria o novo gráfico
            grafico = new Chart(document.getElementById('graficoConsumoAgrupado'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Consumo - Pedido ${pedidoSelecionado}`,
                        data: valores,
                        backgroundColor: cores,
                        borderColor: cores.map(cor => cor.replace('0.6', '1')),
                        borderWidth: 1,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                padding: 10,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Consumo de Componentes por Pedido',
                            font: {
                                size: 14
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => value,
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            offset: 4,
                            padding: 0
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: maxValue * 1.2, // Adiciona 20% de espaço acima do valor máximo
                            title: {
                                display: true,
                                text: 'Quantidade Consumida',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                stepSize: 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Componentes',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: 15,
                            right: 15,
                            top: 30,
                            bottom: 10
                        }
                    }
                }
            });
        }

        // Adiciona o evento de change no select
        selectPedido.addEventListener('change', (e) => {
            atualizarGrafico(parseInt(e.target.value));
        });

        // Inicializa o gráfico com o primeiro pedido
        if (pedidosUnicos.length > 0) {
            atualizarGrafico(pedidosUnicos[0]);
        }

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