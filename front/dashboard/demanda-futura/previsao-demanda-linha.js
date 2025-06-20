//Cria um gráfico de linha com filtros interativos para mostrar a demanda por produto ao longo dos meses

export async function renderPrevisaoDemanda(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <h3>Demanda por Produto Acabado</h3>
        <div class="filtros">
            <label for="filtroAno">Filtrar por Ano:</label>
            <select id="filtroAno">
                <option value="">Todos</option>
            </select>

            <label for="filtroProduto" style="margin-left: 1rem;">Filtrar por Produto:</label>
            <select id="filtroProduto">
                <option value="">Todos</option>
            </select>
        </div>
        <div style="height: 400px; position: relative; margin: 20px 0;">
            <canvas id="graficoPrevisaoDemanda"></canvas>
        </div>
    `;

    const colorPalette = [
        'rgb(255, 99, 132)',  // Red
        'rgb(54, 162, 235)',  // Blue
        'rgb(255, 206, 86)', // Yellow
        'rgb(75, 192, 192)',  // Teal
        'rgb(153, 102, 255)',// Purple
        'rgb(255, 159, 64)', // Orange
        'rgb(46, 204, 113)', // Green
        'rgb(231, 76, 60)',  // Pomegranate
        'rgb(149, 165, 166)',// Gray
        'rgb(52, 73, 94)'    // Dark Blue
    ];

    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/previsao-demanda.php`;
    
    try {
        const response = await fetch(API_URL);
        if (!response.ok) {
            throw new Error('Erro ao buscar dados: ' + await response.text());
        }
        
        const json = await response.json();
        const { datasets: rawDatasets, anos } = json;

        const filtroProduto = container.querySelector('#filtroProduto');
        const filtroAno = container.querySelector('#filtroAno');
        const ctx = container.querySelector('#graficoPrevisaoDemanda');
        let chart;

        // Preenche os filtros
        rawDatasets.forEach(ds => {
            const opt = document.createElement('option');
            opt.value = ds.label;
            opt.textContent = ds.label;
            filtroProduto.appendChild(opt);
        });

        anos.forEach(ano => {
            const opt = document.createElement('option');
            opt.value = ano.toString();
            opt.textContent = ano.toString();
            filtroAno.appendChild(opt);
        });

        function formatarMesAno(mes, ano) {
            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                            'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return `${meses[parseInt(mes) - 1]}/${ano}`;
        }

        function desenharGrafico(produtoSelecionado = '', anoSelecionado = '') {
            // Filtra os datasets por produto, se um produto for selecionado
            const datasetsFiltradosPorProduto = rawDatasets
                .filter(ds => !produtoSelecionado || ds.label === produtoSelecionado);

            // Coleta todas as chaves (mês/ano) de todos os datasets filtrados e as ordena cronologicamente
            const todasAsChaves = new Set();
            datasetsFiltradosPorProduto.forEach(ds => {
                const dadosFiltradosPorAno = ds.data
                    .filter(d => !anoSelecionado || d.ano.toString() === anoSelecionado);
                dadosFiltradosPorAno.forEach(d => {
                    todasAsChaves.add(formatarMesAno(parseInt(d.x), d.ano));
                });
            });
            
            const labelsOrdenadas = Array.from(todasAsChaves).sort((a, b) => {
                const [mesStrA, anoStrA] = a.split('/');
                const [mesStrB, anoStrB] = b.split('/');
                const anoA = parseInt(anoStrA);
                const anoB = parseInt(anoStrB);
                if (anoA !== anoB) return anoA - anoB;
                const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                return meses.indexOf(mesStrA) - meses.indexOf(mesStrB);
            });

            // Mapeia os datasets para o formato do Chart.js, garantindo que todos usem as mesmas labels ordenadas
            const datasetsFiltrados = datasetsFiltradosPorProduto.map((ds, index) => {
                const dadosPorMes = new Map();
                ds.data
                    .filter(d => !anoSelecionado || d.ano.toString() === anoSelecionado)
                    .forEach(d => {
                        dadosPorMes.set(formatarMesAno(parseInt(d.x), d.ano), d.y);
                    });

                const data = labelsOrdenadas.map(chave => ({
                    x: chave,
                    y: dadosPorMes.has(chave) ? dadosPorMes.get(chave) : null
                }));

                const color = colorPalette[index % colorPalette.length];

                return {
                    label: ds.label,
                    data: data,
                    borderColor: color,
                    backgroundColor: color,
                    borderWidth: 2,
                    tension: 0.1,
                    fill: false,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    spanGaps: true // Conectar pontos sobre dados nulos para manter a linha contínua
                };
            }).filter(ds => ds.data.some(d => d.y !== null)); // Apenas incluir datasets com dados

            if (chart) {
                chart.destroy();
            }

            if (datasetsFiltrados.length > 0) {
                let maxY = 0;
                datasetsFiltrados.forEach(dataset => {
                    dataset.data.forEach(dataPoint => {
                        if (dataPoint.y !== null && dataPoint.y > maxY) {
                            maxY = dataPoint.y;
                        }
                    });
                });

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: datasetsFiltrados
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                mode: 'nearest', //Modo para o tooltip exibir apenas quando passar sobre um ponto
                                intersect: true, //Exibir tooltip apenas quando o ponto exato for alcançado
                                callbacks: {
                                    title: (context) => {
                                        return context[0].label; //Mostra o título (mês/ano)
                                    },
                                    label: (context) => {
                                        if (context.parsed.y !== null) {
                                            return `${context.dataset.label}: ${context.parsed.y}`;
                                        }
                                        return null;
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 5,
                                color: '#666',
                                font: {
                                    weight: 'bold',
                                    size: 11
                                },
                                formatter: (value) => (value.y !== null ? value.y : '')
                            }
                        },
                        scales: {
                            x: {
                                type: 'category',
                                display: true,
                                title: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 45
                                }
                            },
                            y: {
                                display: true,
                                title: {
                                    display: false
                                },
                                beginAtZero: true,
                                suggestedMax: maxY + 1,
                                ticks: {
                                    callback: (value) => (value % 1 === 0 ? value : '')
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            } else {
                if (ctx.getContext) {
                    const context = ctx.getContext('2d');
                    context.clearRect(0, 0, ctx.width, ctx.height);
                    context.font = '14px Arial';
                    context.textAlign = 'center';
                    context.fillText('Nenhum dado disponível para os filtros selecionados', ctx.width / 2, ctx.height / 2);
                }
            }
        }

        filtroProduto.addEventListener('change', () => {
            desenharGrafico(filtroProduto.value, filtroAno.value);
        });

        filtroAno.addEventListener('change', () => {
            desenharGrafico(filtroProduto.value, filtroAno.value);
        });

        // Desenha o gráfico inicial
        desenharGrafico();

    } catch (error) {
        console.error('Erro ao renderizar o gráfico:', error);
        container.innerHTML += `<div class="erro">Erro ao carregar o gráfico: ${error.message}</div>`;
    }
}