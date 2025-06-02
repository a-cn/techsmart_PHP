document.addEventListener('DOMContentLoaded', function () {
    fetch('dados_graficos.php')
        .then(res => res.json())
        .then(data => {
            // Gráfico de Estoque (Barra)
            new Chart(document.getElementById('graficoEstoque'), {
                type: 'bar',
                data: {
                    labels: data.estoque.labels,
                    datasets: [{
                        label: 'Quantidade em Estoque',
                        data: data.estoque.data,
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Gráfico de Situação dos Componentes (Pizza)
            new Chart(document.getElementById('graficoSituacao'), {
                type: 'pie',
                data: {
                    labels: ['Abaixo do Mínimo', 'Dentro do Nível', 'Acima do Máximo'],
                    datasets: [{
                        data: [
                            data.componentes.abaixo,
                            data.componentes.normal,
                            data.componentes.acima
                        ],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745']
                    }]
                }
            });
        })
        .catch(err => {
            console.error("Erro ao carregar dados dos gráficos:", err);
        });
});
