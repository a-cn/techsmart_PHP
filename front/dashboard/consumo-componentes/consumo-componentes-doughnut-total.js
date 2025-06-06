//Cria um gráfico de doughnut visualizar componentes mais ou menos consumidos como um todo (proporção total de consumo de cada componente)

export async function renderGraficoConsumoTotalComponentes(containerId) {
    const container = document.getElementById(containerId);
    
    // Adiciona o container do filtro e do gráfico
    container.innerHTML = `
        <div style="display: flex; align-items: flex-start; justify-content: center; gap: 20px;">
            <div style="height: 432px; width: 576px; position: relative;">
                <canvas id="graficoDoughnutComponentes"></canvas>
            </div>
            <div style="text-align: left;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">Filtrar por Componente:</label>
                <div id="checkboxContainer" style="
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    width: 250px;
                    background: white;
                    padding: 10px;
                ">
                    <div style="margin-bottom: 10px;">
                        <input type="text" id="searchComponente" placeholder="Buscar componente..." style="
                            width: 100%;
                            padding: 5px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            margin-bottom: 10px;
                        ">
                        <label style="display: block;">
                            <input type="checkbox" value="todos" checked> <span style="font-weight: bold;">Todos</span>
                        </label>
                    </div>
                    <div id="componentesCheckboxes" style="
                        max-height: 360px;
                        overflow-y: auto;
                        border-top: 1px solid #eee;
                        padding-top: 10px;
                    "></div>
                </div>
            </div>
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

        // Função para calcular o consumo por componente
        function calcularConsumoPorComponente(dados, componentesSelecionados) {
            const consumoPorComponente = {};
            dados.forEach(item => {
                const comp = item.componente;
                if (componentesSelecionados.includes('todos') || componentesSelecionados.includes(comp)) {
                    consumoPorComponente[comp] = (consumoPorComponente[comp] || 0) + item.quantidade;
                }
            });
            return consumoPorComponente;
        }

        // Função para obter componentes selecionados
        function getComponentesSelecionados() {
            const todosCheckbox = document.querySelector('#checkboxContainer input[value="todos"]');
            if (todosCheckbox.checked) {
                return ['todos'];
            }
            const checkboxes = document.querySelectorAll('#componentesCheckboxes input[type="checkbox"]:not([data-filtered="true"])');
            const selecionados = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            return selecionados;
        }

        // Função para filtrar os componentes
        function filtrarComponentes(searchTerm) {
            const componentesDiv = document.querySelectorAll('#componentesCheckboxes > div');
            const searchTermLower = searchTerm.toLowerCase();
            
            componentesDiv.forEach(div => {
                const label = div.querySelector('label').textContent.toLowerCase();
                const checkbox = div.querySelector('input[type="checkbox"]');
                
                if (label.includes(searchTermLower)) {
                    div.style.display = '';
                    checkbox.removeAttribute('data-filtered');
                } else {
                    div.style.display = 'none';
                    checkbox.setAttribute('data-filtered', 'true');
                }
            });
        }

        // Popula os checkboxes com os componentes únicos
        const componentesContainer = document.getElementById('componentesCheckboxes');
        const componentesUnicos = [...new Set(dados.map(d => d.componente))].sort();
        
        componentesUnicos.forEach(componente => {
            const div = document.createElement('div');
            div.style.marginBottom = '8px';
            div.innerHTML = `
                <label style="display: block;">
                    <input type="checkbox" value="${componente}" checked> ${componente}
                </label>
            `;
            componentesContainer.appendChild(div);
        });

        // Adiciona o evento de busca
        const searchInput = document.getElementById('searchComponente');
        searchInput.addEventListener('input', (e) => {
            filtrarComponentes(e.target.value);
        });

        // Função para atualizar o gráfico
        function atualizarGrafico() {
            const componentesSelecionados = getComponentesSelecionados();
            const consumoPorComponente = calcularConsumoPorComponente(dados, componentesSelecionados);
            const labels = Object.keys(consumoPorComponente);
            const valores = Object.values(consumoPorComponente);

            // Se já existe um gráfico, destrói ele
            if (grafico) {
                grafico.destroy();
            }

            // Verifica se deve mostrar apenas números
            const mostrarApenasNumeros = componentesSelecionados.includes('todos') || componentesSelecionados.length > 2;

            // Cria o novo gráfico
            grafico = new Chart(document.getElementById('graficoDoughnutComponentes'), {
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
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Proporção Total de Consumo de Componentes',
                            font: {
                                size: 15,
                                weight: 'bold'
                            },
                            padding: {
                                bottom: 18
                            }
                        },
                        datalabels: {
                            color: 'white',
                            font: {
                                weight: 'bold',
                                size: mostrarApenasNumeros ? 14 : 11
                            },
                            formatter: (value, context) => {
                                const label = context.chart.data.labels[context.dataIndex];
                                return mostrarApenasNumeros ? `${value}` : `${label}\n${value}`;
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let valor = context.raw;
                                    return `${label}: ${valor}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Adiciona eventos de change nos checkboxes
        const checkboxContainer = document.getElementById('checkboxContainer');
        checkboxContainer.addEventListener('change', (e) => {
            const checkbox = e.target;
            if (checkbox.type === 'checkbox') {
                if (checkbox.value === 'todos') {
                    // Se "todos" foi clicado, atualiza os outros checkboxes visíveis
                    const checkboxes = document.querySelectorAll('#componentesCheckboxes input[type="checkbox"]:not([data-filtered="true"])');
                    checkboxes.forEach(cb => {
                        cb.checked = checkbox.checked;
                    });
                } else {
                    // Se outro checkbox foi clicado, desmarca "todos"
                    const todosCheckbox = document.querySelector('#checkboxContainer input[value="todos"]');
                    todosCheckbox.checked = false;
                }
                atualizarGrafico();
            }
        });

        // Inicializa o gráfico
        atualizarGrafico();

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