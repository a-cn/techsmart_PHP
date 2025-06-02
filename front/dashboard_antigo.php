<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" type="text/css" href="css/janelas.css">
<div class="janela-consulta" id="divdashBoard">
    <span class="titulo-janela">Dashboard</span>

    <div class="container mt-4">
        <!-- Abas -->
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#visao" type="button">Visão
                    Geral</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#estoque" type="button">Estoque</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#producao" type="button">Produção</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#graficos" type="button">Gráficos</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Visão Geral -->
            <div class="tab-pane fade show active" id="visao">
                <div class="row g-4">
                    <?php
                    function consulta($conn, $sql)
                    {
                        $stmt = sqlsrv_query($conn, $sql);
                        $row = sqlsrv_fetch_array($stmt);
                        return $row[0]; /*/ Esta função consulta retorna somente a primeira linha da consulta para facilitar o retorno de totalizações /*/
                    }

                    $totalComponentes = consulta($conn, "SELECT COUNT(*) FROM Componente WHERE ativo = 1");
                    $baixoEstoque = consulta($conn, "SELECT COUNT(*) FROM Componente WHERE quantidade < nivel_minimo AND ativo = 1");
                    $produtosEstoque = consulta($conn, "SELECT SUM(quantidade) FROM ProdutoFinal WHERE ativo = 1") ?? 0;
                    $valorPedidos = consulta($conn, "SELECT SUM(valor_total) FROM Pedido WHERE ativo = 1");
                    $mediaAvaliacao = consulta($conn, "SELECT AVG(avaliacao * 1.0) FROM Feedback WHERE ativo = 1");
                    ?>

                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total de Componentes</h5>
                                <h2><?= $totalComponentes ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Estoque Baixo</h5>
                                <h2><?= $baixoEstoque ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Produtos em Estoque</h5>
                                <h2><?= $produtosEstoque ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Valor Total dos Pedidos</h5>
                                <h2>R$ <?= number_format($valorPedidos, 2, ',', '.') ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5>Média de Avaliação</h5>
                                <h2><?= number_format($mediaAvaliacao, 1) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estoque -->
            <!-- Sugirido que o div abaixo seja colocado num arquivo dashboard-estoque.php e descomentado o include_once abaixo -->
            <!-- include_once dashboard-estoque.php-->
            <div class="tab-pane fade" id="estoque">
                <h5>Componentes com estoque abaixo do mínimo:</h5>
                <ul>
                    <?php
                    $query = "SELECT nome, quantidade, nivel_minimo FROM Componente WHERE quantidade < nivel_minimo AND ativo = 1";
                    $stmt = sqlsrv_query($conn, $query);
                    while ($row = sqlsrv_fetch_array($stmt)) {
                        echo "<li>{$row['nome']} (Qtd: {$row['quantidade']} / Mínimo: {$row['nivel_minimo']})</li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Produção -->
            <!-- Sugirido que o div abaixo seja colocado num arquivo dashboard-producao.php e descomentado o include_once abaixo -->
            <!-- include_once dashboard-producao.php-->
            <div class="tab-pane fade" id="producao">
                <h5>Produções Ativas:</h5>
                <ul>
                    <?php
                    $query = "SELECT nome FROM Producao WHERE ativo = 1";
                    $stmt = sqlsrv_query($conn, $query);
                    while ($row = sqlsrv_fetch_array($stmt)) {
                        echo "<li>{$row['nome']}</li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Área dos Gráficos -->
            <div class="tab-pane fade" id="graficos">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="graficoEstoque"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="graficoSituacao"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="scr/dashboard.js"></script>