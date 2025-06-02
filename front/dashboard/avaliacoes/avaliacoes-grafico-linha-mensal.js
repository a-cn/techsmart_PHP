//Gráfico de linha, mostrando a evolução mensal entre avaliações positivas, negativas, neutras e média do mês

export async function renderGraficoLinhaAvaliacoesMensal(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <div>
            <label for="filtroMesAvaliacao">Filtrar por Mês:</label>
            <select id="filtroMesAvaliacao">
                <option value="">Todos</option>
            </select>
        </div>
        <canvas id="graficoLinhaAvaliacoesMensal" height="120"></canvas>
        <div id="mediaMensalInfo" style="margin-top: 10px; font-weight: bold;"></div>
    `;

    //Resolve o problema de caminho:
    const BASE_URL = `${window.location.origin}${window.location.pathname.split('/').slice(0, -2).join('/')}`;
    const API_URL = `${BASE_URL}/back/api/resumo-avaliacoes-mensal.php`;
    const response = await fetch(API_URL);
    
    //Verifica se o endpoint foi localizado corretamente:
    if (!response.ok) {
        const textoErro = await response.text();
        console.error('Resposta não OK:', textoErro);
        throw new Error('Erro ao buscar dados do endpoint.');
    }
    
    const dados = await response.json();

    const meses = dados.map(d => d.mes);
    const positivas = dados.map(d => d.positivas);
    const negativas = dados.map(d => d.negativas);
    const neutras = dados.map(d => d.neutras);
    const medias = dados.map(d => d.media);

    const filtro = document.getElementById('filtroMesAvaliacao');
    meses.forEach(mes => {
        const option = document.createElement('option');
        option.value = mes;
        option.textContent = mes;
        filtro.appendChild(option);
    });

    const ctx = document.getElementById('graficoLinhaAvaliacoesMensal').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [
                {
                    label: 'Positivas',
                    data: positivas,
                    borderColor: 'green',
                    tension: 0.1
                },
                {
                    label: 'Negativas',
                    data: negativas,
                    borderColor: 'red',
                    tension: 0.1
                },
                {
                    label: 'Neutras',
                    data: neutras,
                    borderColor: 'gray',
                    tension: 0.1
                },
                {
                    label: 'Média (nota)',
                    data: medias,
                    borderColor: 'blue',
                    borderDash: [5, 5],
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            stacked: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantidade de Avaliações'
                    }
                },
                y1: {
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Média (nota)'
                    },
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    filtro.addEventListener('change', () => {
        const mesSelecionado = filtro.value;
        if (mesSelecionado === '') {
            chart.data.labels = meses;
            chart.data.datasets[0].data = positivas;
            chart.data.datasets[1].data = negativas;
            chart.data.datasets[2].data = neutras;
            chart.data.datasets[3].data = medias;
            document.getElementById('mediaMensalInfo').textContent = '';
        } else {
            const index = meses.indexOf(mesSelecionado);
            chart.data.labels = [mesSelecionado];
            chart.data.datasets[0].data = [positivas[index]];
            chart.data.datasets[1].data = [negativas[index]];
            chart.data.datasets[2].data = [neutras[index]];
            chart.data.datasets[3].data = [medias[index]];
            document.getElementById('mediaMensalInfo').textContent = `Média do mês ${mesSelecionado}: ${medias[index]}`;
        }
        chart.update();
    });
}