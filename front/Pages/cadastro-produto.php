<?php
require_once '../../Back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <!-- DataTables : https://datatables.net/  -->
    <!-- DataTables Personalização: https://datatables.net/download/  -->
    <!-- Tradução DataTables : https://diegomariano.com/datatables-em-portugues/ -->
    <link
        href="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.0.4/date-1.5.5/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.2/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css"
        rel="stylesheet" integrity="sha384-a+StyOLQiEaYS/laq/DITBpYX64P+2aT81Tk3kqGwdCotkppjr8nkKIxQ8wy0qD0"
        crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
        integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
        integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n"
        crossorigin="anonymous"></script>
    <script
        src="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.0.4/date-1.5.5/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.2/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.js"
        integrity="sha384-3AahuaUhHb3d/pxER58ULhJ1kNnGqaEytbGeqjhkWXxFKB+v++ZsQYtuQX/L3nbY"
        crossorigin="anonymous"></script>
    <!-- Google Fonts  -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,700,0,200&icon_names=chevron_left,chevron_right,first_page,last_page" />
    <!-- Link para o arquivo CSS -->
    <link rel="stylesheet" type="text/css" href="../CSS/cadastro-produto.css">
    <script src="../JavaScript/cadastro-produto.js"></script>
</head>

<body>
    <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->
    <div class="container">
        <div class="cadastro-produto oculta" id="divCadastroProduto">
            <h2>Cadastro de Produto</h2>
            <form id="cadastroForm" action="../../Back/cadastrar_produto.php" method="POST">
                <div class="form-group oculta" id="divID">
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
                <input type="submit" class="btn-cadastrar" value="Salvar">
                <button type="button" class="btn-pesquisar" onclick="limpaCadastroProdutos();">Cancelar</button>
            </form>
        </div>

        <?php
        $sql = "SELECT [produtofinal_id],[descricao],[nome],[quantidade],[valor_venda],[nivel_minimo],[nivel_maximo] FROM [dbo].[ProdutoFinal]";
        $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);
        if ($stmt == false) {
            die(print_r(sqlsrv_errors(), false));
        }
        ?>
        <div class="produtos-cadastrados" id="divConsultaProdutos">
            <h2>Produtos Cadastrados</h2>
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
                        }
                        ;
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
            language: { url: "../Data/datatables-pt_br.json" },
            buttons: [
                {
                    text: 'Novo Produto',
                    action: function () {
                        limpaCadastroProdutos();
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
                            preencherFormulario('cadastroForm', rowData);
                            alternaCadastroConsulta();
                        } else {
                            alert("Por favor, selecione uma linha.");
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
</body>
</html>