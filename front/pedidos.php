<?php
require_once '../back/conexao_sqlserver.php'; // Usando o mesmo arquivo de conexão do primeiro código
require_once '../back/verifica_sessao.php'; // Garantindo autenticação
require_once '../back/funcoes_sessao.php';
$loginTimestamp = time(); // Mantendo controle de sessão

// Processa atualizações de situação via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'], $_POST['situacao'])) {
    // Redireciona requisições POST para um arquivo separado
    header("Location: ../back/atualizar_pedido.php");
    exit();
}

// Consulta para buscar pedidos ativos e filtrar somente pedidos do cliente logado, se for o caso
$usuarioId = $_SESSION['usuario_id'];
$tipoUsuario = $_SESSION['tipo_usuario']; //Administrador, Colaborador ou Cliente

// Monta a cláusula WHERE base
$where = "WHERE p.ativo = 1";
$params = [];

// Adiciona filtro de usuário se for cliente
if ($tipoUsuario === 'cliente') {
    $where .= " AND p.fk_usuario = ?";
    $params[] = $usuarioId;
}

$sql = "SELECT 
            p.pedido_id, 
            p.data_hora, 
            p.situacao, 
            SUM(prod.custo * ppf.quantidade_item) as custo, 
            u.cpf_cnpj 
        FROM 
            Pedido p 
            JOIN Usuario u on u.usuario_id = p.fk_usuario 
            JOIN Pedido_ProdutoFinal ppf on ppf.fk_pedido = p.pedido_id 
            JOIN ProdutoFinal pf on pf.produtofinal_id = ppf.fk_produtofinal
            JOIN Producao prod on prod.producao_id = pf.fk_producao 
        $where
        GROUP BY
            p.pedido_id, 
            p.data_hora, 
            p.situacao,  
            u.cpf_cnpj 
        ORDER BY p.data_hora DESC";

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Erro na consulta: " . print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Pedidos - TechSmart</title>
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
</head>
<body>
    <div class="janela-consulta" id="divConsultaPedidos">
        <span class="titulo-janela">Histórico de Pedidos</span>

        <table id="tabelaPedidos">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>CPF/CNPJ</th>
                    <th>Data/Hora</th>
                    <th>Situação</th>
                    <th>Custo Total</th>
                    <?php if (esconderSeCliente()): ?><th>Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                <tr id="row-<?= $row['pedido_id'] ?>">
                    <td name="pedido_id"><?= $row['pedido_id'] ?></td>
                    <td name="cpf_cnpj"><?= htmlspecialchars($row['cpf_cnpj']) ?></td>
                    <td name="data_hora"><?= $row['data_hora']->format('d/m/Y, H:i') ?></td>
                    <td name="situacao" class="situacao-cell"><?= htmlspecialchars($row['situacao']) ?></td>
                    <td name="custo">R$ <?= number_format($row['custo'], 2, ',', '.') ?></td>
                    <?php if (esconderSeCliente()): ?>
                    <td class="actions">
                        <button type="button" class="btn-pesquisar" onclick="enableEdit(<?= $row['pedido_id'] ?>)">Editar</button>
                        <button type="button" class="btn-cadastrar" style="display:none" onclick="saveChanges(<?= $row['pedido_id'] ?>)">Salvar</button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    // Verifica se há mensagem de sucesso na URL e mostra alert
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success') && urlParams.get('success') === 'updated') {
            mostrarMensagem("Sucesso", "Pedido atualizado com sucesso!", "sucesso");
        }
    }

    // Configuração do DataTable
    var oTable = new DataTable('#tabelaPedidos', {
        select: true,
        info: false,
        language: { url: "data/datatables-pt_br.json" },
        buttons: [
            <?php if (esconderSeCliente()): ?>
            {
                text: 'Visualizar Feedback',
                action: function () {
                    var selectedRow = oTable.row({ selected: true }).data();
                    if (!selectedRow) {
                        mostrarMensagem("Aviso", "Por favor, selecione um pedido para visualizar o feedback.", "alerta");
                        return;
                    }
                    var pedidoId = selectedRow[0]; // primeira coluna da linha
                    // Verifica no backend se existe feedback para o pedido
                    fetch(`../back/verifica_feedback_pedido.php?pedido_id=${pedidoId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.temFeedback) {
                                window.location.href = `index.php?pg=feedbacks&pedido_id=${pedidoId}`;
                            } else {
                                // Primeiro verifica se o pedido está entregue
                                fetch(`../back/verifica_situacao_pedido.php?pedido_id=${pedidoId}`)
                                    .then(response => response.json())
                                    .then(situacaoData => {
                                        if (situacaoData.situacao === 'Entregue') {
                                            window.location.href = `index.php?pg=formulario-feedback&pedido_id=${pedidoId}`;
                                        } else {
                                            mostrarMensagem("Aviso", "Só é possível enviar feedback para pedidos que já foram entregues.", "alerta");
                                        }
                                    })
                                    .catch(() => {
                                        mostrarMensagem("Erro", "Erro ao verificar situação do pedido. Tente novamente.", "erro");
                                    });
                            }
                        })
                        .catch(() => {
                            mostrarMensagem("Erro", "Erro ao verificar feedback. Tente novamente.", "erro");
                        });
                }
            },
            <?php endif; ?>
            <?php if (mostrarSeCliente()): ?>
            {
                text: 'Feedback',
                action: function () {
                    var selectedRow = oTable.row({ selected: true }).data();
                    if (!selectedRow) {
                        mostrarMensagem("Aviso", "Por favor, selecione um pedido.", "alerta");
                        return;
                    }

                    // Verifica se o pedido está entregue antes de mostrar opções de feedback
                    if (selectedRow[3] !== 'Entregue') { // índice 3 é a coluna de situação
                        mostrarMensagem("Aviso", "Só é possível enviar feedback para pedidos que já foram entregues.", "alerta");
                        return;
                    }

                    var pedidoId = selectedRow[0]; // primeira coluna da linha
                    // Verifica no backend se existe feedback para o pedido
                    fetch(`../back/verifica_feedback_pedido.php?pedido_id=${pedidoId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.temFeedback) {
                                window.location.href = `index.php?pg=feedbacks&pedido_id=${pedidoId}`;
                            } else {
                                window.location.href = `index.php?pg=formulario-feedback&pedido_id=${pedidoId}`;
                            }
                        })
                        .catch(() => {
                            mostrarMensagem("Erro", "Erro ao verificar feedback. Tente novamente.", "erro");
                        });
                }
            },
            <?php endif; ?>
            {
                text: 'Atualizar Tabela',
                action: function () {
                    location.reload();
                }
            },
            <?php if (esconderSeCliente()): ?>'copy', 'csv', 'excel', 'pdf', 'print'<?php endif; ?>
        ],
        layout: {
            bottomStart: 'buttons'
        }
    });

    // Função para habilitar a edição
    function enableEdit(pedidoId) {
        const row = document.getElementById(`row-${pedidoId}`);
        const situacao = row.querySelector('.situacao-cell').textContent.trim();
        
        row.querySelector('.situacao-cell').innerHTML = `
            <select class="situacao-select" id="select-${pedidoId}">
                <option value="Aguardando pagamento" ${situacao === 'Aguardando pagamento' ? 'selected' : ''}>Aguardando pagamento</option>
                <option value="Em preparação" ${situacao === 'Em preparação' ? 'selected' : ''}>Em preparação</option>
                <option value="Enviado" ${situacao === 'Enviado' ? 'selected' : ''}>Enviado</option>
                <option value="Entregue" ${situacao === 'Entregue' ? 'selected' : ''}>Entregue</option>
                <option value="Cancelado" ${situacao === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
            </select>`;
        
        // Alterna os botões (usando classes do primeiro código)
        row.querySelector('.btn-pesquisar').style.display = 'none';
        row.querySelector('.btn-cadastrar').style.display = 'inline-block';
    }

    // Função para salvar as alterações
    function saveChanges(pedidoId) {
        const situacao = document.getElementById(`select-${pedidoId}`).value;
        const saveBtn = document.querySelector(`#row-${pedidoId} .btn-cadastrar`);
        
        saveBtn.disabled = true;
        saveBtn.textContent = 'Salvando...';
        
        fetch('../back/atualizar_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pedido_id=${pedidoId}&situacao=${encodeURIComponent(situacao)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mostrarMensagem("Sucesso", "Situação do pedido atualizada com sucesso!", "sucesso", () => {
                    window.location.href = '../front/index.php?pg=pedidos';
                });
            } else {
                throw new Error(data.error || 'Erro ao atualizar');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarMensagem("Erro", "Erro ao atualizar: " + error.message, "erro", () => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Salvar';
            });
        });
    }
    </script>

    <?php
    sqlsrv_free_stmt($stmt);
    ?>
</body>
</html>