<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

//Carrega as linhas de produção ativas
$linhasProducao = [];
$sqlProducao = "SELECT producao_id, nome FROM Producao WHERE ativo = 1 ORDER BY nome";
$stmtProducao = sqlsrv_query($conn, $sqlProducao);
if ($stmtProducao) {
    while ($row = sqlsrv_fetch_array($stmtProducao, SQLSRV_FETCH_ASSOC)) {
        $linhasProducao[] = $row;
    }
    sqlsrv_free_stmt($stmtProducao);
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
        <div class="janela-cadastro oculta" id="divCadastroProduto">
            <span class="titulo-janela">Cadastro de Produto</span>
            <form id="form-cadastro" action="../back/putProduto.php" method="POST">
                <div class="form-group" style="display: none" id="divID">
                    <label for="id">ID:</label>
                    <input type="text" id="produtofinal_id" name="produtofinal_id">
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
                    <div class="form-group" id="divValorVenda">
                        <label for="valor_venda">Valor de Venda (R$):</label>
                        <input type="number" id="valor_venda" name="valor_venda" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" id="divNivelMinimo">
                        <label for="nivel_minimo">Nível Mínimo:</label>
                        <input type="number" id="nivel_minimo" name="nivel_minimo" step="1" min="1" required>
                    </div>
                    <div class="form-group" id="divNivelMaximo">
                        <label for="nivel_maximo">Nível Máximo:</label>
                        <input type="number" id="nivel_maximo" name="nivel_maximo" step="1" min="1" required>
                    </div>
                    <div class="form-group" id="divProducao">
                        <label for="fk_producao">Linha de Produção:</label>
                        <select id="fk_producao" name="fk_producao" required>
                            <option></option>
                            <?php foreach ($linhasProducao as $linha_prod): ?>
                                <option value="<?= $linha_prod['producao_id'] ?>"><?= htmlspecialchars($linha_prod['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="divTempoProd">
                        <label for="tempo_prod">Tempo de Produção (em dias):</label>
                        <input type="number" id="tempo_prod" name="tempo_prod" step="1" min="1" required>
                    </div>
                </div>
                <div class="form-group" id="divDescricao">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroProduto','divConsultaProdutos');">Cancelar</button>
                </div>
            </form>
        </div>

        <?php
        $sql = "SELECT [produtofinal_id],[descricao],[nome],[quantidade],[valor_venda],[nivel_minimo],[nivel_maximo],[tempo_producao_dias],[ativo] FROM [dbo].[ProdutoFinal]";
        $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);
        if ($stmt == false) {
            die(print_r(sqlsrv_errors(), false));
        }
        ?>

        <div class="janela-consulta" id="divConsultaProdutos">
        <span class="titulo-janela">Produtos Cadastrados</span>
        <table id="tabelaProdutos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Qtd Disponível</th>
                    <th>Valor de Venda</th>
                    <th>Nível Min.</th>
                    <th>Nível Máx.</th>
                    <th>Dias p/ Produção</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="janela-consulta oculta" id="divConsultaProdutosInativos">
        <span class="titulo-janela">Produtos Inativos</span>
        <table id="tabelaProdutosInativos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Qtd Disponível</th>
                    <th>Valor de Venda</th>
                    <th>Nível Min.</th>
                    <th>Nível Máx.</th>
                    <th>Dias p/ Produção</th>
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
            text: 'Novo Produto',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (data) return mostrarMensagem("Aviso", "Desmarque a linha selecionada antes de tentar criar um novo registro.", "alerta");
                limpaCadastroAlternaEdicao("divCadastroProduto", "divConsultaProdutos");
            }
        },
        {
            text: 'Alterar Produto',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) {
                    mostrarMensagem("Aviso", "Por favor, selecione um produto.", "alerta");
                    return;
                }

                preencherFormulario('form-cadastro', {
                    produtofinal_id: data.produtofinal_id,
                    fk_producao: data.fk_producao,
                    nome: data.nome,
                    descricao: data.descricao,
                    quantidade: data.quantidade,
                    valor_venda: data.valor_venda,
                    nivel_minimo: data.nivel_minimo,
                    nivel_maximo: data.nivel_maximo,
                    tempo_prod: data.tempo_producao_dias
                });
                $('#fk_producao').val(data.fk_producao).trigger('change');

                alternaCadastroConsulta("divCadastroProduto", "divConsultaProdutos");
            }
        },
        {
            text: 'Inativar Produto',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Selecione um produto.", "alerta");
                mostrarDialogo("Inativar Produto", "Deseja inativar este produto?", () => {
                    fetch("../back/desativar_produto.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ produtofinal_id: data.produtofinal_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Produto inativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", "Erro ao inativar produto.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Inativos',
            action: function () {
                oTable.destroy();
                document.getElementById("divConsultaProdutos").classList.add("oculta");
                carregarTabelaProdutos(0);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    const botoesInativos = [
        {
            text: 'Reativar Produto',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Selecione um produto.", "alerta");
                mostrarDialogo("Reativar Produto", "Deseja reativar este produto?", () => {
                    fetch("../back/reativar_produto.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ produtofinal_id: data.produtofinal_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Produto reativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", "Erro ao reativar produto.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Ativos',
            action: function () {
                oTable.destroy();
                document.getElementById("divConsultaProdutosInativos").classList.add("oculta");
                carregarTabelaProdutos(1);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    let oTable;
    function carregarTabelaProdutos(ativo = 1) {
        const idTabela = ativo ? "#tabelaProdutos" : "#tabelaProdutosInativos";
        const divAtiva = ativo ? "divConsultaProdutos" : "divConsultaProdutosInativos";

        document.getElementById(divAtiva).classList.remove("oculta");

        oTable = new DataTable(idTabela, {
            ajax: {
                url: `../back/getProdutos.php?ativo=${ativo}`,
                dataSrc: ''
            },
            columns: [
                { data: 'produtofinal_id', name: 'produtofinal_id' },
                { data: 'nome', name: 'nome' },
                { data: 'descricao', name: 'descricao' },
                { data: 'quantidade', name: 'quantidade' },
                { data: 'valor_venda', name: 'valor_venda' },
                { data: 'nivel_minimo', name: 'nivel_minimo' },
                { data: 'nivel_maximo', name: 'nivel_maximo' },
                { data: 'tempo_producao_dias', name: 'tempo_producao_dias' }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: ativo ? botoesAtivos : botoesInativos,
            layout: {
                bottomStart: 'buttons'
            }
        });
    }

    carregarTabelaProdutos(1);

    document.addEventListener("DOMContentLoaded", function () {
        // Inicializa o Select2 no campo de seleção da linha de produção
        $('#fk_producao').select2({
            placeholder: "Selecione a linha de produção associada",
            allowClear: true, // permite limpar a seleção
            width: '100%', // usa 100% da largura do container
        });
    });
</script>