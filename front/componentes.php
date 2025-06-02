<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
    <div>
        <div class="janela-cadastro oculta" id="divCadastroComponente">
            <span class="titulo-janela">Cadastro de Componente</span>
            <form id="form-cadastro" action="../back/putComponente.php" method="POST">
                <div class="form-group" style="display: none" id="divID">
                    <label for="id">ID:</label>
                    <input type="text" id="componente_id" name="componente_id">
                </div>
                <div class="form-group" id="divNome">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group" id="divEspecificao">
                    <label for="especificacao">Especificação:</label>
                    <textarea id="especificacao" name="especificacao" rows="4"></textarea>
                </div>
                <div class="form-group" id="divQuantidade">
                    <label for="quantidade">Quantidade:</label>
                    <input type="number" id="quantidade" name="quantidade" step="1" min="1" required>
                </div>
                <div class="form-group" id="divNivelMinimo">
                    <label for="nivel_minimo">Nivel Mínimo:</label>
                    <input type="number" id="nivel_minimo" name="nivel_minimo" step="1" required>
                </div>
                <div class="form-group" id="divNivelMaximo">
                    <label for="nivel_maximo">Nivel Máximo:</label>
                    <input type="number" id="nivel_maximo" name="nivel_maximo" step="1" required>
                </div>
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroComponente','divConsultaComponentes');">Cancelar</button>
                </div>
            </form>
        </div>
        <?php
        $sql = "  SELECT [componente_id]
                        ,[nome]
                        ,[especificacao]
                        ,[quantidade]
                        ,[nivel_minimo]
                        ,[nivel_maximo]
                        ,[ativo]
                    FROM [dbo].[Componente]
                ";

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
                        <th>Quantidade</th>
                        <th>Nível Mínimo</th>
                        <th>Nível Máximo</th>
                        <!--th>Ativo</th-->
                    </tr>
                </thead>
                <tbody>
                    <!-- Exemplo de dados estáticos, você pode substituir por dados dinâmicos do banco de dados -->
                    <?php
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            print "<tr>";
                            print "<td name='componente_id'>" . $row['componente_id'] . "</td>\n";
                            print "<td name='nome'>" . $row['nome'] . "</td>\n";
                            print "<td name='especificacao'>" . $row['especificacao'] . "</td>\n";
                            print "<td name='quantidade'>" . $row['quantidade'] . "</td>\n";
                            print "<td name='nivel_minimo'>" . $row['nivel_minimo'] . "</td>\n";
                            print "<td name='nivel_maximo'>" . $row['nivel_maximo'] . "</td>\n";
                            /*        
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
        var oTable = new DataTable('#tabelaComponentes', {
            select: true,
            info: false,
            language: { url: "data/datatables-pt_br.json" },
            buttons: [
                {
                    text: 'Novo Componente',
                    action: function () {
                        limpaCadastroAlternaEdicao("divCadastroComponente","divConsultaComponentes");
                    }
                },
                {
                    text: 'Alterar Componente',
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
                            alternaCadastroConsulta("divCadastroComponente","divConsultaComponentes");
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
