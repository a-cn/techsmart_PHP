<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Carrega o Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script> <!-- Carrega o plugin de datalabels -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Carrega o Bootstrap 5 -->
<!-- DataTables CSS e JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/janelas.css"> <!-- Carrega o estilo CSS geral das janelas -->
<link rel="stylesheet" type="text/css" href="css/dashboard-estilos.css"> <!-- Carrega o estilo CSS de dashboards -->

<!-- Registra o plugin de datalabels globalmente -->
<script>
Chart.register(ChartDataLabels);
</script>

<div class="janela-consulta" id="divdashBoard">
    <span class="titulo-janela">Dashboard</span>

    <div class="container-fluid mt-4">
        <!-- Abas -->
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#aba-status-producao" type="button">Status de Produção</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-consumo-componentes" type="button">Consumo de Componentes</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-demanda-futura" type="button">Demanda de Produtos Acabados</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-estoque-componentes" type="button">Estoque de Componentes</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-estoque-produtos" type="button">Estoque de Produtos Acabados</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-avaliacoes" type="button">Avaliações</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
        <!-- RELATÓRIOS SOBRE FEEDBACK DO CLIENTE (AVALIAÇÕES) -->
        <div class="tab-pane fade" id="aba-avaliacoes">
            <!-- Gráfico de Doughnut (Avaliações) -->
            <div class="grafico-container" style="width: 35%; margin: auto;">
                <h5 class="text-center">Distribuição Geral das Avaliações</h5>
                <div style="position: relative;">
                    <canvas id="graficoDoughnutAvaliacoes"></canvas>
                </div>
                <div style="margin-top: 2rem;">
                    <div id="legendaDoughnutAvaliacaoGeral" style="text-align: center;">
                        <p id="mediaGeralAvaliacoes" style="margin-top: 1rem; font-weight: bold; font-size: 1.2rem;"></p>
                    </div>
                </div>
                <script type="module">
                    import { renderDoughnutResumoAvaliacoes } from './dashboard/avaliacoes/avaliacoes-resumo-geral.js';
                    renderDoughnutResumoAvaliacoes('containerDoughnutAvaliacaoGeral');
                </script>
            </div>
            <!-- Gráfico de Linha (Avaliações Mensais) -->
            <div class="grafico-container">
                <h5 class="text-center">Média Mensal de Avaliações</h5>
                <div id="containerLinhaAvaliacoesMensal" style="width: 100%; max-width: 1000px; margin: auto;">
                    <canvas id="graficoLinhaAvaliacoesMensal"></canvas>
                </div>
                <script type="module">
                    import { renderGraficoLinhaAvaliacoesMensal } from './dashboard/avaliacoes/avaliacoes-grafico-linha-mensal.js';
                    renderGraficoLinhaAvaliacoesMensal('containerLinhaAvaliacoesMensal');
                </script>
            </div>
        </div>

        <!-- RELATÓRIOS DE ESTOQUE DE COMPONENTES -->
        <div class="tab-pane fade" id="aba-estoque-componentes">
            <!-- Tabela Detalhada (Componentes) -->
            <div class="grafico-container">
                <div id="tabelaEstoqueComponentesContainer"></div>
                <script type="module">
                    import { renderTabelaEstoqueComponentes } from './dashboard/estoque-componentes/estoque-componentes-tabela-detalhada.js';
                    renderTabelaEstoqueComponentes('tabelaEstoqueComponentesContainer');
                </script>
            </div>
            <!-- Gráfico de Barras (Componentes) -->
            <div class="grafico-container" style="width: 100% !important; margin: 0 auto !important; display: flex; flex-direction: column; align-items: center;">
                <canvas id="graficoTopEstoqueBaixoComponentes"></canvas>
                <script type="module">
                    import { renderTopEstoqueBaixoComponentes } from './dashboard/estoque-componentes/estoque-componentes-top-baixo.js';
                    renderTopEstoqueBaixoComponentes('graficoTopEstoqueBaixoComponentes');
                </script>
                <div id="legendaEstoqueComponentes">
                    <span style="background-color:rgba(255, 99, 132, 0.6);padding:5px 10px;border-radius:4px;margin-right:10px;">Estoque Baixo</span>
                    <span style="background-color:rgba(75, 192, 192, 0.6);padding:5px 10px;border-radius:4px;margin-right:10px;">Estoque Normal</span>
                    <span style="background-color:rgba(255, 206, 86, 0.6);padding:5px 10px;border-radius:4px;">Estoque Alto</span>
                </div>
            </div>
            <!-- Gráfico de Pizza (Componentes) -->
            <div class="grafico-container" style="width: 50%; margin: 1rem auto;">
                <div style="position: relative; height: 450px; display: flex; flex-direction: column;">
                    <div style="flex: 1; position: relative;">
                        <canvas id="graficoPizzaEstoqueComponentes"></canvas>
                    </div>
                    <div style="margin-top: 2rem;">
                        <div id="legendaPizzaComponentes" style="display: flex; justify-content: center; gap: 1rem;"></div>
                    </div>
                </div>
                <script type="module">
                    import { renderPizzaEstoqueComponentes } from './dashboard/estoque-componentes/estoque-componentes-pizza.js';
                    renderPizzaEstoqueComponentes('graficoPizzaEstoqueComponentes');
                </script>
            </div>
        </div>

        <!-- RELATÓRIOS DE ESTOQUE DE PRODUTOS ACABADOS -->
        <div class="tab-pane fade" id="aba-estoque-produtos">
            <!-- Tabela Detalhada (Produtos Acabados) -->
            <div class="grafico-container">
                <div id="tabelaEstoqueProdutosContainer"></div>
                <script type="module">
                    import { renderTabelaEstoqueProdutos } from './dashboard/estoque-produtos/estoque-produtos-tabela-detalhada.js';
                    renderTabelaEstoqueProdutos('tabelaEstoqueProdutosContainer');
                </script>
            </div>
            <!-- Gráfico de Barras (Produtos Acabados) -->
            <div class="grafico-container">
                <canvas id="graficoTopEstoqueBaixoProdutos"></canvas>
                <script type="module">
                    import { renderTopEstoqueBaixoProdutos } from './dashboard/estoque-produtos/estoque-produtos-top-baixo.js';
                    renderTopEstoqueBaixoProdutos('graficoTopEstoqueBaixoProdutos');
                </script>
                <div id="legendaEstoqueProdutos">
                    <span style="background-color:rgba(255, 99, 132, 0.6);padding:5px 10px;border-radius:4px;margin-right:10px;">Estoque Baixo</span>
                    <span style="background-color:rgba(75, 192, 192, 0.6);padding:5px 10px;border-radius:4px;margin-right:10px;">Estoque Normal</span>
                    <span style="background-color:rgba(255, 206, 86, 0.6);padding:5px 10px;border-radius:4px;">Estoque Alto</span>
                </div>
            </div>
            <!-- Gráfico de Pizza (Produtos Acabados) -->
            <div class="grafico-container" style="width: 50%; margin: 2rem auto; display: flex; flex-direction: column; align-items: center;">
                <canvas id="graficoPizzaEstoqueProdutos"></canvas>
                <script type="module">
                    import { renderPizzaEstoqueProdutos } from './dashboard/estoque-produtos/estoque-produtos-pizza.js';
                    renderPizzaEstoqueProdutos('graficoPizzaEstoqueProdutos');
                </script>
            </div>

            <!-- Tabela Detalhada (Movimentações de Estoque) -->
            <div class="grafico-container">
                <div id="tabelaMovimentacoesContainer"></div>
                <script type="module">
                    import { renderTabelaMovimentacoes } from './dashboard/estoque-produtos/movimentacoes-tabela-detalhada.js';
                    renderTabelaMovimentacoes('tabelaMovimentacoesContainer');
                </script>
            </div>
        </div>

        <!-- RELATÓRIOS DE STATUS DA PRODUÇÃO -->
        <div class="tab-pane fade show active" id="aba-status-producao">
            <!-- Tabela Detalhada de Status de Produção -->
            <div class="grafico-container">
                <div id="tabelaStatusProducaoContainer"></div>
                <script type="module">
                    import { renderTabelaStatusProducao } from './dashboard/status-producao/status-producao-tabela-detalhada.js';
                    renderTabelaStatusProducao('tabelaStatusProducaoContainer');
                </script>
            </div>

            <!-- Gráfico de Pizza (Status de Produção) -->
            <div class="grafico-container" style="width: 80%; margin: 2rem auto;">
                <div id="containerPizzaStatusProducao" style="height: 400px; display: flex; justify-content: center; align-items: center;"></div>
                <script type="module">
                    import { renderPizzaStatusProducao } from './dashboard/status-producao/status-producao-pizza.js';
                    renderPizzaStatusProducao('containerPizzaStatusProducao');
                </script>
            </div>
        </div>

        <!-- RELATÓRIOS DE CONSUMO DE COMPONENTES POR ITEM DE PEDIDOS -->
        <div class="tab-pane fade" id="aba-consumo-componentes">
            <!-- Tabela Detalhada (Consumo de Componentes) -->
            <div class="grafico-container">
                <!-- Container da tabela -->
                <div id="tabelaConsumoComponentesContainer"></div>
                <!-- Script da tabela -->
                <script type="module">
                    import { renderTabelaConsumoComponentes } from './dashboard/consumo-componentes/consumo-componentes-tabela-detalhada.js';
                    renderTabelaConsumoComponentes('tabelaConsumoComponentesContainer');
                </script>
            </div>
            <!-- Gráfico de Barras Agrupadas (Consumo de Componentes) -->
            <div class="grafico-container">
                <div id="graficoConsumoAgrupadoContainer"></div>
                <script type="module">
                    import { renderGraficoConsumoAgrupado } from './dashboard/consumo-componentes/consumo-componentes-grafico-agrupado.js';
                    renderGraficoConsumoAgrupado('graficoConsumoAgrupadoContainer');
                </script>
            </div>
            <!-- Gráfico de Doughnut (Consumo de Componentes) -->
            <div class="grafico-container">
                <div id="graficoDoughnutComponentesContainer"></div>
                <script type="module">
                    import { renderGraficoConsumoTotalComponentes } from './dashboard/consumo-componentes/consumo-componentes-doughnut-total.js';
                    renderGraficoConsumoTotalComponentes('graficoDoughnutComponentesContainer');
                </script>
            </div>
        </div>

        <!-- RELATÓRIO DE PREVISÃO DE DEMANDA FUTURA (PRODUTOS) -->
        <div class="tab-pane fade" id="aba-demanda-futura">
            <!-- Gráfico de Linha (Previsão de Demanda) -->
            <div class="grafico-container">
                <div id="containerPrevisaoDemanda"></div>
                <script type="module">
                    import { renderPrevisaoDemanda } from './dashboard/demanda-futura/previsao-demanda-linha.js';
                    renderPrevisaoDemanda('containerPrevisaoDemanda');
                </script>
            </div>
        </div>
        </div>

    </div>

</div>