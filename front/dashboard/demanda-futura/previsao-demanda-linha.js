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
            // Filtra os datasets
            const datasetsFiltrados = rawDatasets
                .filter(ds => !produtoSelecionado || ds.label === produtoSelecionado)
                .map(ds => {
                    // Filtra os dados pelo ano
                    const dadosFiltrados = ds.data
                        .filter(d => !anoSelecionado || d.ano.toString() === anoSelecionado);

                    // Cria um objeto para agrupar por mês
                    const dadosPorMes = {};
                    dadosFiltrados.forEach(d => {
                        const mes = parseInt(d.x);
                        const ano = d.ano;
                        const chave = formatarMesAno(mes, ano);
                        dadosPorMes[chave] = d.y;
                    });

                    // Ordena as chaves para garantir ordem cronológica
                    const chaves = Object.keys(dadosPorMes).sort((a, b) => {
                        const [mesA, anoA] = a.split('/');
                        const [mesB, anoB] = b.split('/');
                        if (anoA !== anoB) return parseInt(anoA) - parseInt(anoB);
                        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                                        'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                        return meses.indexOf(mesA) - meses.indexOf(mesB);
                    });

                    return {
                        label: ds.label,
                        data: chaves.map(chave => ({
                            x: chave,
                            y: dadosPorMes[chave]
                        })),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgb(54, 162, 235)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: false,
                        pointStyle: 'circle',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    };
                })
                .filter(ds => ds.data.length > 0);

            if (chart) {
                chart.destroy();
            }

            if (datasetsFiltrados.length > 0) {
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
                                display: false
                            },
                            tooltip: {
                                mode: 'nearest', //Modo para o tooltip exibir apenas quando passar sobre um ponto
                                intersect: true, //Exibir tooltip apenas quando o ponto exato for alcançado
                                callbacks: {
                                    title: (context) => {
                                        return context[0].label; //Mostra o título (mês/ano)
                                    },
                                    label: (context) => {
                                        return `${context.dataset.label}: ${context.parsed.y}`;
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
                                formatter: (value) => value.y
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