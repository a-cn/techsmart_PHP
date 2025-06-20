<?php
require_once '../back/conexao_sqlserver.php';
require_once '../back/verifica_sessao.php';
$loginTimestamp = time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Produção - TechSmart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/_styles.css">
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <link rel="stylesheet" type="text/css" href="css/borderglass.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }
        .status-ativa {
            background-color: #d4edda;
            color: #155724;
        }
        .status-concluida {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .btn-acessar {
            transition: all 0.3s;
        }
        .btn-acessar:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-buttons .btn {
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-bar {
            background-color: #0d6efd;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="janela-consulta">
        <span class="titulo-janela">Histórico de Produção</span>
        
        <div class="card bordeglass mt-3">
            <div class="card-body">
                <div class="filter-buttons mb-3">
                    <button class="btn btn-outline-primary active filter-btn" data-filter="todas">
                        <i class="fas fa-list"></i> Todas as Produções
                    </button>
                    <button class="btn btn-outline-success filter-btn" data-filter="ativas">
                        <i class="fas fa-play-circle"></i> Produções Ativas
                    </button>
                    <button class="btn btn-outline-secondary filter-btn" data-filter="concluidas">
                        <i class="fas fa-check-circle"></i> Produções Concluídas
                    </button>
                </div>
                
                <table id="tabelaLinhas" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Produto Montado</th>
                            <th>Status</th>
                            <th>Progresso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scr/janelas.js"></script>
    <script>
        $(document).ready(function() {
            const tabelaLinhas = $('#tabelaLinhas').DataTable({
                ajax: {
                    url: '../back/controlador_producao.php?acao=listar_linhas_status',
                    dataSrc: '',
                    error: function(xhr, error, thrown) {
                        console.error('Erro na requisição AJAX:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            thrown: thrown
                        });
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'nome' },
                    { data: 'produto_final' },
                    { 
                        data: 'status',
                        render: function(data) {
                            const badgeClass = data === 'Ativa' ? 'status-ativa' : 'status-concluida';
                            return `<span class="status-badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: 'progresso',
                        render: function(data) {
                            return `
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: ${data}%" 
                                        aria-valuenow="${data}" aria-valuemin="0" aria-valuemax="100">
                                        ${data}%
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.status === 'Ativa') {
                                return `
                                    <button class="btn btn-primary btn-sm btn-acessar" 
                                        onclick="acessarLinha(${row.id})">
                                        <i class="fas fa-tasks"></i> Acessar
                                    </button>
                                `;
                            }
                            return '<span class="text-muted">Concluída</span>';
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                createdRow: function(row, data) {
                    $(row).attr('data-status', data.status.toLowerCase());
                }
            });

            // Filtros
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                const filter = $(this).data('filter');
                if (filter === 'todas') {
                    tabelaLinhas.column(3).search('').draw();
                } else {
                    tabelaLinhas.column(3).search(filter === 'ativas' ? 'Ativa' : 'Concluída').draw();
                }
            });
        });

        function acessarLinha(id) {
            // Redireciona para a tela de controle de produção com o ID do histórico
            window.location.href = `index.php?pg=controle-producao&historico_id=${id}`;
        }
    </script>
</body>
</html>