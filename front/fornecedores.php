<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>

<link rel="stylesheet" type="text/css" href="css/janelas.css">
<script src="scr/script.js"></script>
<div>
    <div class="janela-cadastro oculta" id="divCadastroFornecedor">
        <span class="titulo-janela" id="form-fornecedor-titulo">Cadastro de Fornecedor</span>
        <form id="form-cadastro" class="form-content" action="../back/putFornecedor.php" method="POST" onsubmit="return validateForm()" novalidate>

            <div class="form-group" style="display: none;">
                <label>Id
                    <input type="text" name="fornecedor_id" readonly> <!-- name deverá ser o nome do campo da tabela para que a função preencherFormulario consiga pegar os dados do item selecionado no datatables -->
                </label>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label id="lbl-nome-razao_social" for="nome">Razão Social ou Nome:</label>
                    <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite a razão social ou nome completo" required>
                </div>
                <div class="form-group">
                    <label id="lbl-cpf-cnpj" for="cpf_cnpj">CNPJ ou CPF:</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" maxlength="14" placeholder="Digite o CNPJ ou CPF" pattern="\d{14}" required>
                    <div class="form-group" style="margin-top: -10px;">
                        <small id="erroCpfCnpj" style="display: none; color: #ff431b; font-weight: 500;"></small>
                    </div>
                </div>
                <!-- Campos de Endereço -->
                <div class="form-group" style="display: none"><!-- Campo ocultado contendo id para localização do endereço que será enviado para gravação ao $_POST-->
                    <label for="idEnd">Id endereço:</label>
                    <input type="text" id="endereco_id" name="endereco_id" class="form-control"> <!-- Repetindo: name deverá ser o nome do campo da tabela onde é armazenado o dado -->
                </div>
                <div class="form-group">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" placeholder="Ex.: 80000000" class="form-control"
                        maxlength="8" required>
                    <div class="form-group" style="margin-top: -10px;">
                        <small id="erroCep" style="display: none; color: #ff431b; font-weight: 500;"></small>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="estado">Estado (UF):</label>
                    <input type="text" id="estado" name="estado" maxlength="2" placeholder="Ex.: PR"
                        class="form-control" required>
                </div>
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
                    <input type="email" id="email" name="email" maxlength="50" placeholder="Ex.: email@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="num_principal">Número Principal para Contato:</label>
                    <input type="text" id="num_principal" name="num_principal" maxlength="15"
                        placeholder="Ex.: (41) 3333-3333" required>
                </div>
                <div class="form-group">
                    <label for="num_secundario">Número de Recado:</label>
                    <input type="text" id="num_secundario" name="num_secundario" maxlength="15"
                        placeholder="Ex.: (41) 99876-5432">
                </div>
            </div>

            <div class="form-row">
                <input type="submit" class="btn-cadastrar" value="Salvar">
                <button type="button" class="btn-pesquisar" onclick="limpaCadastroAlternaEdicao('divCadastroFornecedor','ConsultaFornecedor');">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="janela-consulta" id="ConsultaFornecedor">
        <span class="titulo-janela">Fornecedores Cadastrados</span>
        <table id="tabelaFornecedores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ/CPF</th>
                    <th>Fone</th>
                    <th>Fone Recado</th>
                    <th>Email</th>
                    <th>Endereço</th>
                </tr>
            </thead>
            <tbody>
                <!-- Preenchido automaticamente por DataTables -->
            </tbody>
        </table>
    </div>

    <div class="janela-consulta oculta" id="ConsultaFornecedorInativos">
        <span class="titulo-janela">Fornecedores Inativos</span>
        <table id="tabelaFornecedoresInativos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ/CPF</th>
                    <th>Fone</th>
                    <th>Fone Recado</th>
                    <th>Email</th>
                    <th>Endereço</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script type="module" src="./scr/cadastro-fornecedor.js"></script>

<!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
<script>
    const botoesAtivos = [
        {
            text: 'Adicionar Fornecedor',
            action: function () {
                limpaCadastro();
                document.getElementById("form-fornecedor-titulo").innerText = "Cadastro de Fornecedor";
                alternaCadastroConsulta("divCadastroFornecedor", "ConsultaFornecedor");
            }
        },
        {
            text: 'Alterar Fornecedor',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");

                preencherFormulario("form-cadastro", data);
                document.getElementById("form-fornecedor-titulo").innerText = "Alterar Dados de Fornecedor";
                alternaCadastroConsulta("divCadastroFornecedor", "ConsultaFornecedor");
            }
        },
        {
            text: 'Inativar Fornecedor',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");

                mostrarDialogo("Confirmar Inativação", "Deseja realmente inativar este fornecedor?", () => {
                    fetch("../back/desativar_fornecedor.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ fornecedor_id: data.fornecedor_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Fornecedor inativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", data.mensagem || "Erro ao inativar fornecedor.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Inativos',
            action: function () {
                oTable.destroy();
                document.getElementById("ConsultaFornecedor").classList.add("oculta");
                carregarTabelaFornecedores(0);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    const botoesInativos = [
        {
            text: 'Reativar Fornecedor',
            action: function () {
                const data = oTable.row({ selected: true }).data();
                if (!data) return mostrarMensagem("Aviso", "Por favor, selecione uma linha.", "alerta");

                mostrarDialogo("Confirmar Reativação", "Deseja reativar este fornecedor?", () => {
                    fetch("../back/reativar_fornecedor.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ fornecedor_id: data.fornecedor_id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem("Sucesso", "Fornecedor reativado com sucesso.", "sucesso");
                            oTable.ajax.reload();
                        } else {
                            mostrarMensagem("Erro", "Erro ao reativar fornecedor.", "erro");
                        }
                    });
                }, null, "alerta");
            }
        },
        {
            text: 'Ver Ativos',
            action: function () {
                oTable.destroy();
                document.getElementById("ConsultaFornecedorInativos").classList.add("oculta");
                carregarTabelaFornecedores(1);
            }
        },
        'copy', 'csv', 'excel', 'pdf', 'print'
    ];

    let oTable;
    function carregarTabelaFornecedores(ativo = 1) {
        const tabelaId = ativo ? "#tabelaFornecedores" : "#tabelaFornecedoresInativos";
        const divId = ativo ? "ConsultaFornecedor" : "ConsultaFornecedorInativos";

        document.getElementById(divId).classList.remove("oculta");

        oTable = new DataTable(tabelaId, {
            ajax: {
                url: `../back/getFornecedores.php?ativo=${ativo}`,
                dataSrc: ''
            },
            columns: [
                { data: 'fornecedor_id', name: 'fornecedor_id' },
                { data: 'nome', name: 'nome' },
                { data: 'cpf_cnpj', name: 'cpf_cnpj' },
                { data: 'num_principal', name: 'num_principal' },
                { data: 'num_secundario', name: 'num_secundario' },
                { data: 'email', name: 'email' },
                {
                    data: null,
                    render: function (data) {
                        let complemento = data.complemento ? ` - ${data.complemento}` : '';
                        return `${data.logradouro}, ${data.numero}${complemento} - ${data.bairro}, ${data.cidade} - ${data.estado}, ${data.cep}`;
                    }
                }
            ],
            select: true,
            language: { url: "data/datatables-pt_br.json" },
            buttons: ativo ? botoesAtivos : botoesInativos,
            layout: {
                bottomStart: 'buttons'
            }
        });
    }

    carregarTabelaFornecedores(1);
</script>