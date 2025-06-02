<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

/*/
$perguntas = [];
$sql = "SELECT pergunta_seguranca_id, pergunta FROM Pergunta_Seguranca";
$result = sqlsrv_query($conn, $sql);

if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $perguntas[] = $row;
    }
}

$tipoUsuarios = [];
$sql = "SELECT tipo_usuario_id, descricao FROM Tipo_Usuario";
$result = sqlsrv_query($conn, $sql);

if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $tipoUsuarios[] = $row;
    }
}
/*/
?>
<link rel="stylesheet" type="text/css" href="css/janelas.css">
<script src="scr/script.js"></script>
<div>
    <div class="janela-cadastro oculta" id="divCadastroFornecedor">
        <span class="titulo-janela">Cadastro de Fornecedores</span>
        <form id="form-cadastro" class="form-content" action="../back/putFornecedor.php" method="POST" onsubmit="return validateForm()" novalidate>

            <div class="form-group" style="display: none;">
                <label>Id
                    <input type="text" name="fornecedor_id" readonly> <!-- name deverá ser o nome do campo da tabela para que a função preencherFormulario consiga pegar os dados do item selecionado no datatables -->
                </label>
            </div>

            <!-- Campos para CNPJ -->
            <div id="cpf-fields" style="display: block;">
                <!--div id="dvTipoUsuario" class="form-group">
                    <label for="fk_tipo_usuario">Tipo de Usuário</label>
                    <select id="fk_tipo_usuario" name="fk_tipo_usuario" class="form-control" required>
                        <option value="">Selecione um tipo de usuário</option>
                        < ?php foreach ($tipoUsuarios as $tp):
                            print '<option value="' . $tp['tipo_usuario_id'] . '">';
                            print htmlspecialchars($tp['descricao']);
                            print '</option>';
                        endforeach; ? >
                    </select>
                </div-->
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-nome-razao_social" for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome completo" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-cpf-cnpj" for="cpf">CNPJ:</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" maxlength="14" placeholder="Digite seu CNPJ" required>
                    </div>
                </div>
            </div>

            <!-- Campos de Endereço -->
            <div class="form-row">
                <div class="form-group" style="display: none"><!-- Campo ocultado contendo id para localização do endereço que será enviado para gravação ao $_POST-->
                    <label for="idEnd">Id endereço:</label>
                    <input type="text" id="idEnd" name="endereco_id" class="form-control"> <!-- Repetindo: name deverá ser o nome do campo da tabela onde é armazenado o dado -->
                </div>
                <div class="form-group">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" placeholder="Ex.: 80000000" class="form-control"
                        maxlength="8" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" maxlength="50" placeholder="Ex.: Paraná"
                        class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" maxlength="50" placeholder="Ex.: Curitiba"
                        class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" maxlength="50" placeholder="Ex.: Portão"
                        class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="logradouro">Logradouro:</label>
                    <input type="text" id="logradouro" name="logradouro" maxlength="150" placeholder="Ex.: Rua Itajubá"
                        class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="number" id="numero" name="numero" placeholder="Digite o número do imóvel"
                        class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" id="complemento" name="complemento" maxlength="100" placeholder="Ex.: Bloco 1"
                        class="form-control">
                </div>
            </div>

            <!-- Outros campos do formulário -->
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" maxlength="50" placeholder="Ex.: email@dominio.com"
                        required>
                </div>
                <div class="form-group">
                    <label for="confirmEmail">Confirmar Email:</label>
                    <input type="email" id="confirmEmail" name="email" maxlength="50" placeholder="Confirme o email"
                        required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="num_principal">Número de Principal:</label>
                    <input type="text" id="num_principal" name="num_principal" maxlength="15"
                        placeholder="Ex.: (41) 99876-5432" required>
                </div>
                <div class="form-group">
                    <label for="num_secundario">Número de Secundário:</label>
                    <input type="text" id="num_secundario" name="num_secundario" maxlength="15"
                        placeholder="Ex.: (41) 3333-3333">
                </div>
            </div>

            <div class="form-row">
                <input type="submit" class="btn-cadastrar" value="Salvar">
                <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroFornecedor','ConsultaFornecedor');">Cancelar</button>
            </div>
        </form>
    </div>

    <?php
    //$sql = "SELECT * FROM [dbo].[fornecedores]";
    $sql = "SELECT [usuario_id],[fk_tipo_usuario],[nome],[cpf_cnpj],[data_nascimento],[email],[num_principal],[num_recado],[fk_endereco],[senha],[fk_pergunta_seguranca],[resposta_seguranca],[ativo] FROM [dbo].[Usuario]";
    $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);
    if ($stmt == false) {
        die(print_r(sqlsrv_errors(), false));
    }
    ?>
    <div class="janela-consulta" id="ConsultaFornecedor">
        <span class="titulo-janela">Fornecedores Cadastrados</span>
        <table id="tabelaFornecedores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Fone</th>
                    <th>Fone Recado</th>
                    <th>Email</th>
                    <th>Endereço</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <!-- Preenchido automaticamente por DataTables -->
            </tbody>
        </table>
    </div>
</div>
<script src="./scr/cadastro-usuario.js"></script>
<!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var oTable = new DataTable('#tabelaFornecedores', {
            ajax: {
                url: '../back/getFornecedores.php', // Endpoint PHP
                dataSrc: '' // DataTables já entende JSON como array de objetos
            },
            columns: [
                { data: 'fornecedor_id' },
                { data: 'nome' },
                { data: 'cpf_cnpj' },
                { data: 'num_principal' },
                { data: 'num_secundario' },
                { data: 'email' },
                { data: 'fk_endereco' },
                { data: 'situacao' }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: [
                {
                    text: 'Novo Fornecedor',
                    action: function () {
                        limpaCadastro();
                        alternaCadastroConsulta("divCadastroFornecedor", "ConsultaFornecedor");
                    }
                },
                {
                    text: 'Alterar Usuário',
                    action: function () {
                        var selectedRow = oTable.row({ selected: true }).data(); // Pega os dados diretamente do DataTables
                        if (selectedRow) {
                            console.log("Dados para edição:", selectedRow);
                            preencherFormulario('form-cadastro', selectedRow);
                            alternaCadastroConsulta("divCadastroFornecedor", "ConsultaFornecedor");
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