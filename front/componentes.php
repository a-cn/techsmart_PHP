<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

//Carrega os fornecedores ativos
$fornecedores = [];
$sqlFornecedores = "SELECT fornecedor_id, nome FROM Fornecedor WHERE ativo = 1 ORDER BY nome";
$stmtFornecedores = sqlsrv_query($conn, $sqlFornecedores);
if ($stmtFornecedores) {
    while ($row = sqlsrv_fetch_array($stmtFornecedores, SQLSRV_FETCH_ASSOC)) {
        $fornecedores[] = $row;
    }
    sqlsrv_free_stmt($stmtFornecedores);
}
?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> <!-- CSS da biblioteca Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> <!-- JS da biblioteca Select2 -->
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
    <!-- Estilo para padronizar o visual do campo Select2, seguindo os demais -->
    <style>
        .select2-container--default .select2-selection--single {
            height: 45px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }
    </style>
    <div>
        <div class="janela-cadastro oculta" id="divCadastroComponente">
            <span class="titulo-janela">Cadastro de Componente</span>
            <form id="form-cadastro" action="../back/putComponente.php" method="POST">
                <div class="form-group" style="display: none" id="divID">
                    <label for="id">ID:</label>
                    <input type="text" id="componente_id" name="componente_id">
                </div>
                <div class="form-row">
                    <div class="form-group" id="divNome">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group" id="divQuantidade">
                        <label for="quantidade">Quantidade Inicial:</label>
                        <input type="number" id="quantidade" name="quantidade" step="1" min="1" required>
                    </div>
                    <div class="form-group" id="divFornecedor">
                        <label for="fk_fornecedor">Fornecedor:</label>
                        <select id="fk_fornecedor" name="fk_fornecedor" required>
                            <option></option>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= $fornecedor['fornecedor_id'] ?>"><?= htmlspecialchars($fornecedor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" id="divCusto">
                        <label for="custo_componente">Custo (R$):</label>
                        <input type="number" id="custo_componente" name="custo_componente" step="0.01" required>
                    </div>
                    <div class="form-group" id="divNivelMinimo">
                        <label for="nivel_minimo">Nível Mínimo:</label>
                        <input type="number" id="nivel_minimo" name="nivel_minimo" step="1" required>
                    </div>
                    <div class="form-group" id="divNivelMaximo">
                        <label for="nivel_maximo">Nível Máximo:</label>
                        <input type="number" id="nivel_maximo" name="nivel_maximo" step="1" required>
                    </div>
                </div>
                <div class="form-group" id="divEspecificao">
                    <label for="especificacao">Especificação:</label>
                    <textarea id="especificacao" name="especificacao" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroComponente','divConsultaComponentes');">Cancelar</button>
                </div>
            </form>
        </div>

        <?php
        $sql = "SELECT c.componente_id, c.nome, c.especificacao, c.quantidade, c.nivel_minimo, c.nivel_maximo, fc.custo_componente, f.nome AS fornecedor
                FROM Componente c
                INNER JOIN Fornecedor_Componente fc ON c.componente_id = fc.fk_componente
                INNER JOIN Fornecedor f ON fc.fk_fornecedor = f.fornecedor_id
                WHERE c.ativo = 1";
        $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);
        if ($stmt == false) {
            die(print_r(sqlsrv_errors(), false));
        }
        ?>

        <div class="janela-consulta" id="divConsultaComponentes">
            <span class="titulo-janela">Componentes Cadastrados</span>
            <table id="tabelaComponentes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Especificação</th>
                        <th>Fornecedor</th>
                        <th>Custo</th>
                        <th>Quantidade Disponível</th>
                        <th>Nível Mínimo</th>
                        <th>Nível Máximo</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="janela-consulta oculta" id="divConsultaComponentesInativos">
            <span class="titulo-janela">Componentes Inativos</span>
            <table id="tabelaComponentesInativos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Especificação</th>
                        <th>Fornecedor</th>
                        <th>Custo</th>
                        <th>Quantidade Disponível</th>
                        <th>Nível Mínimo</th>
                        <th>Nível Máximo</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

                
    <!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
    <script>
        //Tabelas de exibição de dados e botões
        const botoesAtivos = [
        {
            text: 'Novo Componente',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (data) return mostrarMensagem("Aviso", "Desmarque a linha selecionada antes de tentar criar um novo registro.", "alerta");
                limpaCadastroAlternaEdicao("divCadastroComponente", "divConsultaComponentes");
            }
        },
        {
            text: 'Alterar Componente',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) {
                    mostrarMensagem("Aviso", "Por favor, selecione um componente.", "alerta");
                    return;
                }

                preencherFormulario('form-cadastro', {
                    componente_id: data.componente_id,
                    fk_fornecedor: data.fk_fornecedor,
                    nome: data.nome,
                    especificacao: data.especificacao,
                    quantidade: data.quantidade,
                    custo_componente: data.custo_componente,
                    nivel_minimo: data.nivel_minimo,
                    nivel_maximo: data.nivel_maximo
                });
                $('#fk_fornecedor').val(data.fk_fornecedor).trigger('change');

                alternaCadastroConsulta("divCadastroComponente", "divConsultaComponentes");
            }
        },
        {
            text: 'Inativar Componente',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Selecione um componente.", "alerta");
                mostrarDialogo("Inativar Componente", "Deseja inativar este componente?", () => {
                    fetch("../back/desativar_componente.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ componente_id: data.componente_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Componente inativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", "Erro ao inativar componente.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Inativos',
            action: function () {
                oTable.destroy();
                document.getElementById("divConsultaComponentes").classList.add("oculta");
                carregarTabelaComponentes(0);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    const botoesInativos = [
        {
            text: 'Reativar Componente',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Selecione um componente.", "alerta");
                mostrarDialogo("Reativar Componente", "Deseja reativar este componente?", () => {
                    fetch("../back/reativar_componente.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ componente_id: data.componente_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Componente reativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", "Erro ao reativar componente.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Ativos',
            action: function () {
                oTable.destroy();
                document.getElementById("divConsultaComponentesInativos").classList.add("oculta");
                carregarTabelaComponentes(1);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    let oTable;
    function carregarTabelaComponentes(ativo = 1) {
        const idTabela = ativo ? "#tabelaComponentes" : "#tabelaComponentesInativos";
        const divAtiva = ativo ? "divConsultaComponentes" : "divConsultaComponentesInativos";

        document.getElementById(divAtiva).classList.remove("oculta");

        oTable = new DataTable(idTabela, {
            ajax: {
                url: `../back/getComponentes.php?ativo=${ativo}`,
                dataSrc: ''
            },
            columns: [
                { data: 'componente_id', name: 'componente_id' },
                { data: 'nome', name: 'nome' },
                { data: 'especificacao', name: 'especificacao' },
                { data: 'fornecedor', name: 'fornecedor' },
                { 
                    data: 'custo_componente', 
                    name: 'custo_componente',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return 'R$ ' + parseFloat(data).toFixed(2);
                        }
                        return data;
                    }
                },
                { data: 'quantidade', name: 'quantidade' },
                { data: 'nivel_minimo', name: 'nivel_minimo' },
                { data: 'nivel_maximo', name: 'nivel_maximo' }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: ativo ? botoesAtivos : botoesInativos,
            layout: {
                bottomStart: 'buttons'
            }
        });
    }

    carregarTabelaComponentes(1);

    document.addEventListener("DOMContentLoaded", function () {
    // Inicializa o Select2 no campo de seleção do fornecedor
    $('#fk_fornecedor').select2({
        placeholder: "Selecione o fornecedor associado",
        allowClear: true, // permite limpar a seleção
        width: '100%', // usa 100% da largura do container
    });

    // Adiciona validação do formulário
    document.getElementById('form-cadastro').addEventListener('submit', function(e) {
        const nivelMinimo = parseFloat(document.getElementById('nivel_minimo').value);
        const nivelMaximo = parseFloat(document.getElementById('nivel_maximo').value);

        if (nivelMaximo < nivelMinimo) {
            e.preventDefault();
            mostrarMensagem("Aviso", "O nível máximo não pode ser menor que o nível mínimo.", "alerta");
        }
    });
});
</script>
