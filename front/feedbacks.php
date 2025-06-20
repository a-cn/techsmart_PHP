<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/funcoes_sessao.php';
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>

<!-- tela de feedbacks registrados -->
<link rel="stylesheet" type="text/css" href="css/janelas.css">

<div class="janela-consulta" id="divFeedbacks">
    <span class="titulo-janela">Histórico de Feedbacks</span>
    <table id="tabelaFeedbacks">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Pedido</th>
                <th>Avaliação (1-5)</th>
                <th>Observação</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
    </table>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pedidoIdParam = new URLSearchParams(window.location.search).get("pedido_id");

            // Botões para feedbacks ativos
            const botoesAtivos = [
                <?php if (mostrarSeCliente()): ?>
                {
                    text: 'Alterar Feedback',
                    action: function () {
                        const data = oTable.row({ selected: true }).data();
                        if (!data) return mostrarMensagem("Aviso", "Selecione um feedback.", "alerta");

                        window.location.href = `index.php?pg=alterar-feedback&pedido_id=${data.fk_pedido}`;
                    }
                },
                {
                    text: 'Excluir Feedback',
                    action: function () {
                        const data = oTable.row({ selected: true }).data();
                        if (!data) return mostrarMensagem("Aviso", "Selecione um feedback.", "alerta");

                        mostrarDialogo("Excluir Feedback", "Deseja excluir permanentemente este feedback? Você poderá enviar um novo feedback para este pedido depois.", () => {
                            const formData = new FormData();
                            formData.append("feedback_id", data.feedback_id);
                            fetch("../back/deletar_feedback.php", {
                                method: "POST",
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.sucesso) {
                                    mostrarMensagem("Sucesso", data.mensagem, "sucesso", () => {
                                        // Redireciona para o formulário de feedback se houver pedido_id
                                        if (data.pedido_id) {
                                            window.location.href = `index.php?pg=formulario-feedback&pedido_id=${data.pedido_id}`;
                                        } else {
                                            oTable.ajax.reload();
                                        }
                                    });
                                } else {
                                    mostrarMensagem("Erro", data.erro, "erro");
                                }
                            })
                            .catch(error => {
                                console.error('Erro:', error);
                                mostrarMensagem("Erro", "Erro ao excluir feedback. Tente novamente.", "erro");
                            });
                        }, null, "alerta");
                    }
                },
                <?php endif; ?>
                <?php if (esconderSeCliente()): ?>
                {
                    text: 'Inativar Feedback',
                    action: function () {
                        var selectedRow = oTable.row({ selected: true }).data();
                        if (!selectedRow) {
                            mostrarMensagem("Aviso", "Por favor, selecione um feedback.", "alerta");
                            return;
                        }
                        mostrarDialogo(
                            "Confirmar Inativação",
                            "Deseja realmente inativar este feedback?",
                            () => {
                                // aoConfirmar
                                fetch('../back/desativar_feedback.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ feedback_id: selectedRow.feedback_id })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.sucesso) {
                                        mostrarMensagem("Sucesso", "Feedback inativado com sucesso.", "sucesso");
                                        oTable.ajax.reload();
                                    } else {
                                        mostrarMensagem("Erro", data.mensagem || "Erro ao inativar feedback.", "erro");
                                    }
                                });
                            },
                            () => {
                                // aoCancelar
                                console.log("Inativação cancelada.");
                            },
                            "alerta"
                        );
                    }
                },
                <?php endif; ?>
                <?php if (esconderSeCliente()): ?>
                {
                    text: 'Ver Inativos',
                    action: function () {
                        oTable.destroy();
                        carregarTabela(0);
                    }
                },
                <?php endif; ?>
                <?php if (esconderSeCliente()): ?>'copy', 'csv', 'excel', 'pdf', 'print'<?php endif; ?>
            ];

            // Botões para feedbacks inativos
            const botoesInativos = [
                {
                    text: 'Reativar Registro',
                    action: function () {
                        var selectedRow = oTable.row({ selected: true }).data();
                        if (!selectedRow) {
                            mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");
                            return;
                        }
                        mostrarDialogo(
                            "Confirmar Reativação",
                            "Deseja reativar este registro?",
                            () => {
                                fetch('../back/reativar_feedback.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ feedback_id: selectedRow.feedback_id })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.sucesso) {
                                        mostrarMensagem("Sucesso", "Registro reativado com sucesso.", "sucesso");
                                        oTable.ajax.reload();
                                    } else {
                                        mostrarMensagem("Erro", "Erro ao reativar registro.", "erro");
                                    }
                                });
                            },
                            () => {
                                console.log("Reativação cancelada.");
                            },
                            "alerta"
                        );
                    }
                },
                {
                    text: 'Ver Ativos',
                    action: function () {
                        oTable.destroy();
                        carregarTabela(1);
                    }
                },
                'copy', 'csv', 'excel', 'pdf', 'print'
            ];

            let oTable;
            function carregarTabela(ativo) {
                oTable = new DataTable('#tabelaFeedbacks', {
                    ajax: {
                        url: `../back/getFeedbacks.php?ativo=${ativo}` + (pedidoIdParam ? `&pedido_id=${pedidoIdParam}` : ''),
                        dataSrc: ''
                    },
                    columns: [
                        { data: 'feedback_id' },
                        { data: 'fk_pedido' },
                        { data: 'avaliacao' },
                        { data: 'observacao' },
                        { data: 'data_hora' }
                    ],
                    select: true,
                    language: { url: "data/datatables-pt_br.json" },
                    buttons: ativo ? botoesAtivos : botoesInativos,
                    layout: {
                        bottomStart: 'buttons'
                    }
                });

                // Aplicar filtro automático pelo pedido_id
                if (ativo && pedidoIdParam) {
                    setTimeout(() => {
                        const input = document.querySelector('#tabelaFeedbacks_filter input');
                        if (input) {
                            input.value = pedidoIdParam;
                            input.dispatchEvent(new Event('input'));
                        }
                    }, 300);
                }
            }

            carregarTabela(1); // Inicializa com ativos
        });
        </script>
</div>