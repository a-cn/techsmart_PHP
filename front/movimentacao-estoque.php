<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão

// Consulta para buscar pedidos ativos
$sql = "SELECT p.pedido_id, p.data_hora, p.situacao, p.valor_total, u.cpf_cnpj 
          FROM Pedido p 
          JOIN Usuario u ON p.fk_usuario = u.usuario_id 
         WHERE p.ativo = 1
      ORDER BY p.data_hora DESC";

$stmt = sqlsrv_query($conn, $sql);

$pedidos = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
        $pedidos[] = $row;
    };
} 
else {
    $pedidos[] = "Sem pedidos";
    die("Erro na consulta ({$sql}) " . print_r(sqlsrv_errors(), true));
}
sqlsrv_free_stmt($stmt);

$sql = "SELECT [produtofinal_id],[descricao],[nome],[quantidade],[valor_venda],[nivel_minimo],[nivel_maximo] 
          FROM [dbo].[ProdutoFinal]";

$stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);

$produtofinal = [];
if ($stmt) {
    while ($row= sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
        $produtofinal[]=$row;
    };
} 
else {
    $produtofinal[] = "Sem produtos";
    die("Erro na consulta de produtos: " . print_r(sqlsrv_errors(), true));
}
sqlsrv_free_stmt($stmt);
?>
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <script src="scr/script.js"></script>
    <div>
        <div class="janela-cadastro oculta" id="divMovimentacao">
            <span class="titulo-janela">Cadastro de Movimentacao</span>
            <form id="form-cadastro" action="../back/putMovimentacao.php" method="POST" onsubmit="return validaMovimentacao();">
                <div class="form-group" style="display: none" id="divMovimentacao_id"> <!-- Pk Id da tela deve ser oculta ou readonly e não required -->
                    <label for="id">ID:</label>
                    <input type="text" id="movimentacao_id" name="movimentacao_id">
                </div>
                <div class="form-group" id="divPedido_id"> 
                    <label for="pedido_id">Pedido ID:</label>
                    <select id="pedido_id" name="pedido_id" class="form-control" required>
                        <option value="">Selecione um pedido</option>
                        <?php 
                        foreach ($pedidos as $pdd):
                            $pedData_hora = ($pdd['data_hora'] instanceof DateTime) ? $pdd['data_hora']->format('d/m/Y H:i:s') : $dataHoraStr = $pdd['data_hora'];
                            print '<option value="' . $pdd['pedido_id'] . '">';
                            print htmlspecialchars($pdd['pedido_id'].' | '.$pedData_hora.' | '. $pdd['situacao'].' | '.$pdd['valor_total'].' | '.$pdd['cpf_cnpj'] );
                            print '</option>';
                        endforeach; 
                        ?>
                    </select>
                </div>                
                <div class="form-group" id="divProdutofinal_id"> 
                    <label for="produtofinal_id">ProdutoFinal ID:</label>
                    <select id="produtofinal_id" name="produtofinal_id" class="form-control" required>
                        <option value="">Selecione um Produto</option>
                        <?php 
                        foreach ($produtofinal as $prd):
                            print '<option value="' . $prd['produtofinal_id'] . '">';
                            print htmlspecialchars($prd['nome'].' | '. $prd['descricao']);
                            print '</option>';
                        endforeach; 
                        ?>
                    </select>
                </div>
                <div class="form-group" id="divQuantidade">
                    <label for="quantidade">Quantidade:</label>
                    <input type="number" id="quantidade" name="quantidade" step="1" min="1" required>
                </div>
                <div class="form-group" id="divDataHora">
                    <label for="data_hora">Data/Hora:</label>
                    <input type="date" id="data_hora" name="data_hora" required>
                </div>
                <div class="form-group" id="divTipoMovimentacao">
                    <label for="tipo_movimentacao">Tipo Movimentação:</label>
                    <input type="number" id="tipo_movimentacao" name="tipo_movimentacao" step="1" min="1" required>
                </div>
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divMovimentacao','divConsultaMovimentacoes');">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="janela-consulta" id="divConsultaMovimentacoes">
            <span class="titulo-janela">Controle de Movimentação de Estoque</span>
            <table class="tabela-movimentacao" id="tabelaMovimentacao">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pedido ID</th>
                        <th>Produto ID</th>
                        <th>Nome do Produto</th>
                        <th>Qtde</th>
                        <th>Data/Hora</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Preenchido por Datatables via ajax com getMovimentacoes.php -->
                </tbody>
            </table>
        </div>
<script src="./scr/cadastro-movimentacao.js"></script>
<!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var oTable = new DataTable('#tabelaMovimentacao', {
            ajax: {
                url: '../back/getMovimentacoes.php', // Endpoint PHP
                dataSrc: '' // DataTables já entende JSON como array de objetos
            },
            columns: [
                { data: 'movimentacao_id' },
                { data: 'pedido_id' },
                { data: 'produtofinal_id' },
                { data: 'nome_produto' },
                { data: 'quantidade' },
                { data: 'data_hora' },
                { data: 'tipo_movimentacao' }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: [
                {
                    text: 'Incluir',
                    action: function () {
                        limpaCadastro();
                        alternaCadastroConsulta("divMovimentacao", "divConsultaMovimentacoes");
                    }
                },
                {
                    text: 'Alterar',
                    action: function () {
                        var selectedRow = oTable.row({ selected: true }).data(); // Pega os dados diretamente do DataTables
                        if (selectedRow) {
                            console.log("Dados para edição:", selectedRow);
                            alternaCadastroConsulta("divMovimentacao", "divConsultaMovimentacoes");
                        } else {
                            mostrarMensagem("Aviso","Por favor, selecione uma linha.","alerta");
                        }
                    }
                },
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            layout: {
                bottomStart: 'buttons'
            }
        });
    });
</script>