export async function renderPizzaStatusProducao(containerId) {
    try {
        //Puxa os dados pela API
        const response = await fetch('../back/api/status-produtos-producao.php');
        const data = await response.json();

        //Processa dados para contar as ocorrências de status
        const statusCounts = data.reduce((acc, item) => {
            acc[item.status] = (acc[item.status] || 0) + 1;
            return acc;
        }, {});

        //Cria uma div de container
        const container = document.getElementById(containerId);
        container.style.display = 'flex';
        container.style.gap = '0.5rem';
        container.style.alignItems = 'center';
        container.style.justifyContent = 'center';
        container.style.height = '400px';
        container.style.padding = '0.5rem';

        //Cria container para o canvas
        const canvasContainer = document.createElement('div');
        canvasContainer.style.flex = '0 1 600px';
        canvasContainer.style.position = 'relative';
        canvasContainer.style.height = '100%';
        canvasContainer.style.minWidth = '0';
        const canvas = document.createElement('canvas');
        canvas.id = 'graficoPizzaStatusProducao';
        canvasContainer.appendChild(canvas);
        container.appendChild(canvasContainer);

        //Cria container para filtro
        const filterContainer = document.createElement('div');
        filterContainer.style.width = '200px';
        filterContainer.style.flexShrink = '0';
        filterContainer.style.alignSelf = 'center';
        filterContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px;">
                <h6 style="margin: 0 0 15px 0; color: #212529;">Filtrar por Status:</h6>
                
                <div style="position: relative; margin-bottom: 10px;">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Buscar status..." 
                           id="searchStatus"
                           style="padding: 0.375rem 0.75rem; font-size: 0.9rem;">
                </div>

                <div style="max-height: 200px; overflow-y: auto;">
                    <div class="form-check" style="margin-bottom: 8px;">
                        <input class="form-check-input" type="checkbox" id="checkTodos" checked>
                        <label class="form-check-label" for="checkTodos" style="font-size: 0.9rem;">
                            Todos
                        </label>
                    </div>
                    ${Object.keys(statusCounts).map(status => `
                        <div class="form-check" style="margin-bottom: 8px;">
                            <input class="form-check-input" type="checkbox" id="check_${status}" value="${status}" checked>
                            <label class="form-check-label" for="check_${status}" style="font-size: 0.9rem;">
                                ${status}
                            </label>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.appendChild(filterContainer);

        //Inicializa o gráfico
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',  // Verde para Acabado
                        'rgba(255, 206, 86, 0.8)',  // Amarelo para Acabado com atraso
                        'rgba(255, 99, 132, 0.8)',  // Vermelho para Produção em atraso
                        'rgba(54, 162, 235, 0.8)',  // Azul para Em produção
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 20,
                        right: 20
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuição por Status de Produção',
                        font: {
                            size: 16
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: false
                    },
                    datalabels: {
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        textAlign: 'center',
                        anchor: 'center',
                        align: 'center',
                        offset: 0,
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((acc, data) => acc + data, 0);
                            const percentage = ((value * 100) / total).toFixed(1) + '%';
                            
                            //Quebra o texto em palavras
                            const label = ctx.chart.data.labels[ctx.dataIndex];
                            const words = label.split(' ');
                            
                            //Agrupa palavras em linhas de no máximo 12 caracteres
                            let lines = [];
                            let currentLine = words[0];
                            
                            for(let i = 1; i < words.length; i++) {
                                const word = words[i];
                                if ((currentLine + ' ' + word).length <= 12) {
                                    currentLine += ' ' + word;
                                } else {
                                    lines.push(currentLine);
                                    currentLine = word;
                                }
                            }
                            lines.push(currentLine);
                            
                            //Adiciona o valor e a porcentagem em novas linhas
                            return [...lines, `${value}`, `(${percentage})`];
                        },
                        listeners: {
                            beforeDraw: function(chart) {
                                chart.ctx.save();
                                chart.ctx.textBaseline = 'middle';
                            },
                            afterDraw: function(chart) {
                                chart.ctx.restore();
                            }
                        }
                    }
                }
            }
        });

        //Eventos para filtragem
        const searchInput = document.getElementById('searchStatus');
        const checkTodos = document.getElementById('checkTodos');
        const statusCheckboxes = Object.keys(statusCounts).map(status => 
            document.getElementById(`check_${status}`)
        );

        //Funcionalidade de busca
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            statusCheckboxes.forEach(checkbox => {
                const label = checkbox.nextElementSibling.textContent.trim().toLowerCase();
                const div = checkbox.closest('.form-check');
                div.style.display = label.includes(searchTerm) ? '' : 'none';
            });
        });

        //Checkbox "Todos"
        checkTodos.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            statusCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateChart();
        });

        //Checkboxes individuais
        statusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const allChecked = statusCheckboxes.every(cb => cb.checked);
                const noneChecked = statusCheckboxes.every(cb => !cb.checked);
                
                checkTodos.checked = allChecked;
                checkTodos.indeterminate = !allChecked && !noneChecked;
                
                updateChart();
            });
        });

        function updateChart() {
            const selectedStatus = statusCheckboxes
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            const filteredCounts = Object.entries(statusCounts)
                .filter(([status]) => selectedStatus.includes(status))
                .reduce((acc, [status, count]) => {
                    acc[status] = count;
                    return acc;
                }, {});

            chart.data.labels = Object.keys(filteredCounts);
            chart.data.datasets[0].data = Object.values(filteredCounts);
            chart.update();
        }

    } catch (error) {
        console.error('Erro ao renderizar o gráfico:', error);
    }
}
