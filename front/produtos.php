<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
    <div>
        <div class="janela-cadastro oculta" id="divCadastroProduto">
            <span class="titulo-janela">Cadastro de Produto</span>
            <form id="form-cadastro" action="../back/putProduto.php" method="POST">
                <div class="form-group" style="display: none" id="divID">
                    <label for="id">ID:</label>
                    <input type="text" id="produtofinal_id" name="produtofinal_id">
                </div>
                <div class="form-group" id="divNome">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group" id="divDescricao">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="4"></textarea>
                </div>
                <div class="form-group" id="divQuantidade">
                    <label for="quantidade">Quantidade:</label>
                    <input type="number" id="quantidade" name="quantidade" step="1" min="1" required>
                </div>
                <div class="form-group" id="divValorVenda">
                    <label for="valor_venda">Valor:</label>
                    <input type="number" id="valor_venda" name="valor_venda" step="0.01" required>
                </div>
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroProduto','divConsultaProdutos');">Cancelar</button>
                </div>
            </form>
        </div>

        <?php
        $sql = "SELECT [produtofinal_id],[descricao],[nome],[quantidade],[valor_venda],[nivel_minimo],[nivel_maximo] FROM [dbo].[ProdutoFinal]";
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
                        <th>Quantidade</th>
                        <th>Valor</th>
                        <!-- <th>Ações</th> -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Exemplo de dados estáticos, você pode substituir por dados dinâmicos do banco de dados -->
                    <?php
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            print "<tr>";
                            print "<td name='produtofinal_id'>" . $row['produtofinal_id'] . "</td>\n";
                            print "<td name='nome'>" . $row['nome'] . "</td>\n";
                            print "<td name='descricao'>" . $row['descricao'] . "</td>\n";
                            print "<td name='quantidade'>" . $row['quantidade'] . "</td>\n";
                            print "<td name='valor_venda'>" . $row['valor_venda'] . "</td>\n";
                            /*        
                            print "<td>" . $row['nivel_minimo'] . "</td>";
                            print "<td>" . $row['nivel_maximo'] . "</td>";
                            print "<td>" . "<button class='btn-desativar' onclick=\"getDados();\">Editar</button>" . "</td>";
                            */
                            print "</tr>\n";
                        };
                        //    var_dump($row);
                        sqlsrv_free_stmt($stmt);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
    <script>
        var oTable = new DataTable('#tabelaProdutos', {
            select: true,
            info: false,
            language: { url: "data/datatables-pt_br.json" },
            buttons: [
                {
                    text: 'Novo Produto',
                    action: function () {
                        limpaCadastroAlternaEdicao("divCadastroProduto","divConsultaProdutos");
                    }
                },
                {
                    text: 'Alterar Produto',
                    action: function () {
                        var selectedRow = oTable.row({ selected: true }).node(); // Obtém o elemento da linha selecionada
                        if (selectedRow) {
                            var rowData = {}; // Cria um objeto para armazenar os dados
                            selectedRow.querySelectorAll("td").forEach(td => {
                                if (td.getAttribute("name")) { // Verifica se a célula tem um atributo 'name'
                                    rowData[td.getAttribute("name")] = td.innerText; // Armazena os valores no objeto
                                }
                            });
                            console.log("Dados para edição:", rowData);
                            preencherFormulario('form-cadastro', rowData);
                            alternaCadastroConsulta("divCadastroProduto","divConsultaProdutos");
                        } else {
                            mostrarMensagem("Aviso","Por favor, selecione uma linha.","alerta");
                            return null;
                        }
                    }
                },
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            layout: {
                bottomStart: 'buttons'
            }
        });
    </script>
