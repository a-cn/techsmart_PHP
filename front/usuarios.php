<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

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

?>
<link rel="stylesheet" type="text/css" href="css/janelas.css">
<script src="scr/script.js"></script>
<script src="scr/cadastro-usuario.js"></script>
<div>
    <div class="janela-cadastro oculta" id="divCadastroUsuario">
        <span class="titulo-janela" id="form-usr-titulo">Cadastro de Usuário</span>
        <form id="form-cadastro" class="form-content" action="../back/putUsuario.php" method="POST" onsubmit="return validateForm()" novalidate>

            <!-- Seleção entre CPF e CNPJ -->
            <div class="form-row">
                <div class="form-group" style="display: none;">
                    <label>Id
                        <input type="text" name="usuario_id" readonly> <!-- name deverá ser o nome do campo da tabela para que a função preencherFormulario consiga pegar os dados do item selecionado no datatables -->
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="tipo_pessoa" id="cbCPF" value="cpf" onclick="toggleCPFCNPJ()" required> CPF
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="tipo_pessoa" id="cbCNPJ" value="cnpj" onclick="toggleCPFCNPJ()"> CNPJ
                    </label>
                </div>
            </div>

            <!-- Campos para CPF -->
            <div id="cpf-fields" style="display: block;">
                <div id="dvTipoUsuario" class="form-group">
                    <label for="fk_tipo_usuario">Tipo de Usuário</label>
                    <select id="fk_tipo_usuario" name="fk_tipo_usuario" class="form-control" required>
                        <option value="">Selecione um tipo de usuário</option>
                        <?php foreach ($tipoUsuarios as $tp):
                            print '<option value="' . $tp['tipo_usuario_id'] . '">';
                            print htmlspecialchars($tp['descricao']);
                            print '</option>';
                        endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-nome-razao_social" for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome completo" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-cpf-cnpj" for="cpf">CPF:</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" maxlength="14" placeholder="Digite seu CPF" required>
                    </div>
                    <div id="dvDtNasc" class="form-group">
                        <label for="data_nascimento">Data de Nascimento:</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" max="<?= date('d-m-Y') ?>" required>
                        <!-- O atributo "max" impede que datas futuras à atual sejam selecionadas -->
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
            <div class="form-group" style="margin-top: -20px;">
                <small id="erroCep" style="display: none; color: #1976d2; font-weight: 500;"></small>
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
                    <input type="email" id="confirmEmail" name="confirmEmail" maxlength="50" placeholder="Confirme o email"
                        required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="num_principal">Número Principal para Contato:</label>
                    <input type="text" id="num_principal" name="num_principal" maxlength="15"
                        placeholder="Ex.: (41) 99876-5432" required>
                </div>
                <div class="form-group">
                    <label for="num_recado">Número de Recado:</label>
                    <input type="text" id="num_recado" name="num_recado" maxlength="15"
                        placeholder="Ex.: (41) 3333-3333">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" maxlength="15" placeholder="Digite sua senha"
                        required>
                </div>
                <div class="form-group">
                    <label for="confirmSenha">Confirmar Senha:</label>
                    <input type="password" id="confirmSenha" name="confirmSenha" maxlength="15"
                        placeholder="Confirme sua senha" required>
                </div>
            </div>
            <div class="form-group" id="regraSenha" style="display: none; margin-top: -20px;">
                <small style="color: #1976d2; font-weight: 500;">As senhas devem ter entre 9 e 15 caracteres, conter pelo menos uma letra maiúscula, um número e um caractere especial.</small>
            </div>

            <!-- Campo para selecionar perguntas de segurança, puxando-as do banco de dados -->
            <!--div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="securityQuestion">Escolha uma pergunta de segurança:</label>
                        <select id="securityQuestion" name="fk_pergunta_seguranca" class="form-control" required>
                            <option value="">Selecione uma pergunta</option>
                            < ?php 
                            foreach ($perguntas as $p):
                                print '<option value="' . $p['pergunta_seguranca_id'] . '">';
                                print htmlspecialchars($p['pergunta']);
                                print '</option>';
                            endforeach;
                            ?>                        
                        </select>
                    </div>
                </div>

                < !-- Resposta da pergunta -- >
                <div class="form-row">
                    <div class="form-group">
                        <label for="securityAnswer">Resposta para a pergunta escolhida:</label>
                        <input type="text" id="securityAnswer" name="resposta_seguranca" maxlength="100"
                        placeholder="Digite sua resposta" required>
                    </div>
                </div>
                </div-->
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Salvar">
                    <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroUsuario','divConsultaUsuarios');">Cancelar</button>
                </div>
        </form>
        <!-- Mensagem de erro -->
        <div id="error-message" class="error"></div>
    </div>

    <?php
    //$sql = "SELECT * FROM [dbo].[Usuario]";
    $sql = "SELECT [usuario_id],[fk_tipo_usuario],[nome],[cpf_cnpj],[data_nascimento],[email],[num_principal],[num_recado],[fk_endereco],[senha],[fk_pergunta_seguranca],[resposta_seguranca],[ativo] FROM [dbo].[Usuario]";
    $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => 'static']);
    if ($stmt == false) {
        die(print_r(sqlsrv_errors(), false));
    }
    ?>
    <div class="janela-consulta" id="divConsultaUsuarios">
        <span class="titulo-janela">Usuários Cadastrados</span>
        <table id="tabelaUsuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>CPF/CNPJ</th>
                    <th>Dt. Nasc</th>
                    <th>Email</th>
                    <th>Fone</th>
                    <th>Fone Recado</th>
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
    function marcarCheckboxCPFCNPJ(cpf_cnpj) {
        // Remove tudo que não for número
        const valorNumerico = cpf_cnpj.replace(/\D/g, '');
        const tam = valorNumerico.length;

        const cbCPF = document.getElementById('cbCPF');
        const cbCNPJ = document.getElementById('cbCNPJ');

        // Desmarcar ambos inicialmente
        cbCPF.checked = false;
        cbCNPJ.checked = false;

        // Marcar conforme a quantidade de dígitos
        cbCPF.checked  = (tam === 11);
        cbCNPJ.checked = (tam === 14);
    }

    // Muda o tipo de pessoa dependendo do dado informado em cpf_cnpj
    document.getElementById("cpf_cnpj").addEventListener("input", function (e) {
        marcarCheckboxCPFCNPJ(e.target.value);
        toggleCPFCNPJ();
    });

    //Configuração da tabela de registros (botões, funções, DataTables)
    document.addEventListener("DOMContentLoaded", function () {
        //Para a exibição de registros ativos:
        const botoesAtivos = [
            {
                text: 'Adicionar Usuário',
                action: function () {
                    limpaCadastro(); // Limpa campos
                    document.getElementById("form-usr-titulo").innerText = "Cadastro de Usuário"; // Título padrão
                    alternaCadastroConsulta("divCadastroUsuario", "divConsultaUsuarios");
                }
            },
            {
                text: 'Alterar Usuário',
                action: function () {
                    var selectedRow = oTable.row({ selected: true }).data(); // Pega os dados diretamente do DataTables
                    if (selectedRow) {
                        console.log("Dados para edição:", selectedRow);
                        marcarCheckboxCPFCNPJ(selectedRow["cpf_cnpj"]);
                        toggleCPFCNPJ();
                        preencherFormulario('form-cadastro', selectedRow);
                        document.getElementById("form-usr-titulo").innerText = "Alterar Usuário"; // Altera título
                        document.getElementById("senha").value = ""; //Evita que o campo "senha" venha preenchido ao editar
                        document.getElementById("confirmSenha").value = ""; //Evita que o campo "senha" venha preenchido ao editar
                        alternaCadastroConsulta("divCadastroUsuario", "divConsultaUsuarios");
                    } else {
                        mostrarMensagem("Aviso","Por favor, selecione uma linha.","alerta");
                    }
                }
            },
            {
                text: 'Inativar Usuário',
                action: function () {
                    var selectedRow = oTable.row({ selected: true }).data();
                    if (!selectedRow) {
                        mostrarMensagem("Aviso","Por favor, selecione uma linha.","alerta");
                        return;
                    }
                    mostrarDialogo(
                        "Confirmar Inativação",
                        "Deseja realmente inativar este usuário?",
                        () => {
                            // aoConfirmar
                            fetch('../back/arquivar_usuario.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ usuario_id: selectedRow.usuario_id })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.sucesso) {
                                    mostrarMensagem("Sucesso","Usuário inativado com sucesso.","sucesso");
                                    oTable.ajax.reload();
                                } else {
                                    mostrarMensagem("Erro","Erro ao inativar usuário.","erro");
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
            {
                text: 'Ver Inativos', //Ao clicar em "Ver Inativos", a tabela será reconstruída para comportar os botoesInativos
                action: function () {
                    oTable.destroy();
                    oTable = new DataTable('#tabelaUsuarios', {
                        ajax: {
                            url: '../back/getUsuarios.php?ativo=0',
                            dataSrc: ''
                        },
                        columns: [
                            { data: 'usuario_id' },
                            { data: 'descricao' },
                            { data: 'nome' },
                            { data: 'cpf_cnpj' },
                            { data: 'data_nascimento' },
                            { data: 'email' },
                            { data: 'num_principal' },
                            { data: 'num_recado' }
                        ],
                        select: true,
                        language: { url: "data/datatables-pt_br.json" },
                        buttons: botoesInativos,
                        layout: {
                            bottomStart: 'buttons'
                        }
                    });
                }
            },
            'copy', 'csv', 'excel', 'pdf', 'print'
        ];
        //Para a exibição de registros inativos:
        const botoesInativos = [
            {
                text: 'Reativar Usuário',
                action: function () {
                    var selectedRow = oTable.row({ selected: true }).data();
                    if (!selectedRow) {
                        mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");
                        return;
                    }
                    mostrarDialogo(
                        "Confirmar Reativação",
                        "Deseja reativar este usuário?",
                        () => {
                            fetch('../back/reativar_usuario.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ usuario_id: selectedRow.usuario_id })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.sucesso) {
                                    mostrarMensagem("Sucesso", "Usuário reativado com sucesso.", "sucesso");
                                    oTable.ajax.reload();
                                } else {
                                    mostrarMensagem("Erro", "Erro ao reativar usuário.", "erro");
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
                text: 'Ver Ativos', //Ao clicar em "Ver Inativos", a tabela será reconstruída para comportar os botoesAtivos
                action: function () {
                    oTable.destroy();
                    oTable = new DataTable('#tabelaUsuarios', {
                        ajax: {
                            url: '../back/getUsuarios.php?ativo=1',
                            dataSrc: ''
                        },
                        columns: [
                            { data: 'usuario_id' },
                            { data: 'descricao' },
                            { data: 'nome' },
                            { data: 'cpf_cnpj' },
                            { data: 'data_nascimento' },
                            { data: 'email' },
                            { data: 'num_principal' },
                            { data: 'num_recado' }
                        ],
                        select: true,
                        language: { url: "data/datatables-pt_br.json" },
                        buttons: botoesAtivos,
                        layout: {
                            bottomStart: 'buttons'
                        }
                    });
                }
            },
            'copy', 'csv', 'excel', 'pdf', 'print'
        ];

        //Inicializa a tabela com os registros de usuários ativos
        var oTable = new DataTable('#tabelaUsuarios', {
            ajax: {
                url: '../back/getUsuarios.php?ativo=1', // Endpoint PHP
                dataSrc: '' // DataTables já entende JSON como array de objetos
            },
            columns: [
                { data: 'usuario_id' },
                { data: 'descricao' },
                { data: 'nome' },
                { data: 'cpf_cnpj' },
                { data: 'data_nascimento' },
                { data: 'email' },
                { data: 'num_principal' },
                { data: 'num_recado' }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: botoesAtivos,
            layout: {
                bottomStart: 'buttons'
            }
        });
    });
</script>