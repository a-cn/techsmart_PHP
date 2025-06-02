<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Carrega o Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Carrega o Bootstrap 5 -->
<link rel="stylesheet" type="text/css" href="css/janelas.css"> <!-- Carrega o estilo CSS geral das janelas -->
<link rel="stylesheet" type="text/css" href="css/dashboard-estilos.css"> <!-- Carrega o estilo CSS de dashboards -->

<div class="janela-consulta" id="divdashBoard">
  <span class="titulo-janela">Dashboard</span>

  <div class="container mt-4">
    <!-- Abas -->
    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#aba-avaliacoes" type="button">Avaliações</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-estoque-componentes" type="button">Estoque de Componentes</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-estoque-produtos" type="button">Estoque de Produtos</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-status-producao" type="button">Status de Produção</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-consumo-componentes" type="button">Consumo de Componentes</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aba-demanda-futura" type="button">Demanda de Produtos</button>
      </li>
    </ul>

    <div class="tab-content mt-3">
      <!-- RELATÓRIOS SOBRE FEEDBACK DO CLIENTE (AVALIAÇÕES) -->
      <div class="tab-pane fade show active" id="aba-avaliacoes">
        <!-- Gráfico de Doughnut (Avaliações) -->
        <div class="grafico-container">
            <h5 class="text-center">Distribuição Geral das Avaliações</h5>
            <div id="containerDoughnutAvaliacaoGeral"></div>
            <div id="legendaDoughnutAvaliacaoGeral" style="text-align: center;">
                <canvas id="graficoDoughnutAvaliacoes"></canvas>
                <p id="mediaGeralAvaliacoes" style="margin-top: 1rem; font-weight: bold; font-size: 1.2rem;"></p>
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
        <div class="grafico-container">
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
        <div class="grafico-container">
            <canvas id="graficoPizzaEstoqueComponentes"></canvas>
            <script type="module">
                import { renderPizzaEstoqueComponentes } from './dashboard/estoque-componentes/estoque-componentes-pizza.js';
                renderPizzaEstoqueComponentes('graficoPizzaEstoqueComponentes');
            </script>
        </div>
      </div>

      <!-- RELATÓRIOS DE ESTOQUE DE PRODUTOS FINAIS -->
      <div class="tab-pane fade" id="aba-estoque-produtos">
        <!-- Tabela Detalhada (Produtos Finais) -->
        <div class="grafico-container">
            <div id="tabelaEstoqueProdutosContainer"></div>
            <script type="module">
                import { renderTabelaEstoqueProdutos } from './dashboard/estoque-produtos/estoque-produtos-tabela-detalhada.js';
                renderTabelaEstoqueProdutos('tabelaEstoqueProdutosContainer');
            </script>
        </div>
        <!-- Gráfico de Barras (Produtos Finais) -->
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
        <!-- Gráfico de Pizza (Produtos Finais) -->
        <div class="grafico-container">
            <canvas id="graficoPizzaEstoqueProdutos"></canvas>
            <script type="module">
                import { renderPizzaEstoqueProdutos } from './dashboard/estoque-produtos/estoque-produtos-pizza.js';
                renderPizzaEstoqueProdutos('graficoPizzaEstoqueProdutos');
            </script>
        </div>
      </div>

      <!-- RELATÓRIOS DE STATUS DA PRODUÇÃO -->
      <div class="tab-pane fade" id="aba-status-producao">
        <!-- Tabela Detalhada (Produtos Acabados VS Semiacabados) -->
        <div class="grafico-container">
            <div id="tabelaStatusProducaoContainer"></div>
            <script type="module">
                import { renderTabelaStatusProducao } from './dashboard/status-producao/status-producao-tabela-detalhada.js';
                renderTabelaStatusProducao('tabelaStatusProducaoContainer');
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