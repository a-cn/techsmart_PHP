<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
$loginTimestamp = time(); //Redefine o momento de início da sessão

?>
<link rel="stylesheet" type="text/css" href="css/janelas.css">
<link rel="stylesheet" type="text/css" href="css/checkbox.css">
<script src="scr/script.js"></script>
<div>
    <div class="janela-cadastro oculta" id="divCadastroFornecedor">
        <span class="titulo-janela">Cadastro de Fornecedores</span>
        <form id="form-cadastro" class="form-content" action="../back/putFornecedor.php" method="POST" onsubmit="return validaFornecedor()" novalidate>

            <div class="form-group" style="display: none;">
                <label>Id
                    <input type="text" name="fornecedor_id" readonly> <!-- name deverá ser o nome do campo da tabela para que a função preencherFormulario consiga pegar os dados do item selecionado no datatables -->
                </label>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="checkbox-wrapper-35">
                        <input class="switch" type="checkbox" id="switch" name="ativo">
                        <label for="switch">
                            <span class="switch-x-text">Registro</span>
                                <span class="switch-x-toggletext">
                                <span class="switch-x-unchecked"><span class="switch-x-hiddenlabel">Unchecked: </span>Inativo</span>
                                <span class="switch-x-checked"><span class="switch-x-hiddenlabel">Checked: </span>Ativo</span>
                            </span>
                        </label>
                    </div>            
                </div>            
            </div>            

            <div class="form-row">
                <div class="form-group">
                    <label id="lbl-nome-razao_social" for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome completo" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label id="lbl-cpf-cnpj" for="cpf">CNPJ:</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" maxlength="14" placeholder="Digite seu CNPJ" pattern="\d{14}" required>
                </div>
            </div>

            <!-- Campos de Endereço -->
            <div class="form-row">
                <div class="form-group" style="display: none"><!-- Campo ocultado contendo id para localização do endereço que será enviado para gravação ao $_POST-->
                    <label for="idEnd">Id endereço:</label>
                    <input type="text" id="endereco_id" name="endereco_id" class="form-control"> <!-- Repetindo: name deverá ser o nome do campo da tabela onde é armazenado o dado -->
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
                    <input type="email" id="email" name="email" maxlength="50" placeholder="Ex.: email@dominio.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="num_principal">Telefone Fixo:</label>
                    <input type="text" id="num_principal" name="num_principal" maxlength="15"
                        placeholder="Ex.: (41) 3333-3333" required>
                </div>
                <div class="form-group">
                    <label for="num_secundario">Telefone Celular:</label>
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
                    <th>Ativo</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Fone Fixo</th>
                    <th>Fone Celular</th>
                    <th>Email</th>
                    <!--th>Endereço</th -->
                </tr>
            </thead>
            <tbody>
                <!-- Preenchido automaticamente por DataTables -->
            </tbody>
        </table>
    </div>
</div>
<script src="./scr/cadastro-fornecedor.js"></script>
<!-- Este script obrigatoriamente deve ser carregado após toda a renderização da página -->
<script>

    document.getElementById("cpf_cnpj").addEventListener("input", function() {
        // Remove todos os caracteres não numéricos
        this.value = this.value.replace(/\D/g, '');
        
        // Limita a 14 caracteres
        if (this.value.length > 14) {
            this.value = this.value.substring(0, 14);
        }
        
        // Validação durante a digitação (opcional)
        if (this.value.length === 14 && !validarCNPJ(this.value)) {
            mostrarMensagem("Aviso","CNPJ inválido! Por favor, insira um CNPJ válido.","alerta");

            //this.setCustomValidity("CNPJ inválido!");
        } else {
            //this.setCustomValidity("");
            this.focus();
        }
    });

    document.getElementById("cpf_cnpj").addEventListener("blur", function() {
        // Só valida se o campo estiver completo
        if (this.value.length === 14 && !validarCNPJ(this.value)) {
            // Usamos reportValidity() em vez de alert() para evitar loops
            mostrarMensagem("Aviso","CNPJ inválido! Por favor, insira um CNPJ válido.","erro");
            //this.setCustomValidity("CNPJ inválido! Por favor, insira um CNPJ válido.");
            this.reportValidity();
            this.focus(); // Mantém o foco no campo
        } else {
            this.setCustomValidity("");
        }
    });

    document.getElementById("email").addEventListener("blur", function() {
        let email = this.value;
        let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regex.test(email)) {

           mostrarMensagem("Aviso","E-mail inválido! Por favor, insira um e-mail válido.","alerta");
            this.focus(); // Retorna o foco ao campo caso esteja inválido
        }
    });    

    document.getElementById("num_principal").addEventListener("blur", function() {
        let fone = this.value;
        let regex = /^\(?[1-9]{2}\)?[ ]?[2-5]{1}[0-9]{3}-?[0-9]{4}$/;

        if (!regex.test(fone)) {
            mostrarMensagem("Aviso", "Telefone inválido! Por favor, insira um número válido.", "alerta");
            this.focus(); // Retorna o foco ao campo caso esteja inválido
        }
    });

    document.getElementById("num_secundario").addEventListener("blur", function() {
        let fone = this.value;
        let regex = /^\(?[1-9]{2}\)?[ ]?[8-9]{1}[0-9]{4}-?[0-9]{4}$/;

        if (!regex.test(fone)) {
            mostrarMensagem("Aviso", "Telefone inválido! Por favor, insira um número válido.", "alerta");
            this.focus(); // Retorna o foco ao campo caso esteja inválido
        }
    });    

    document.getElementById("cep").addEventListener("blur", function() {
        let cep = this.value;
        let regex = /^\d{8}$/;

        if (!regex.test(cep)) {
            mostrarMensagem("Aviso", "CEP inválido! Deve conter 8 dígitos.", "alerta");
            //this.focus();
            return;
        }
        
        // consulata a API ViaCEP
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    mostrarMensagem("Aviso", "CEP não encontrado!", "alerta");
                    this.value('');
                } else {
                    document.getElementById("logradouro").value = data.logradouro;
                    document.getElementById("bairro").value = data.bairro;
                    document.getElementById("cidade").value = data.localidade;
                    document.getElementById("estado").value = data.uf;
                }
            })
            .catch(error => {
                mostrarMensagem("Erro", "CEP inválido!", "erro");
                console.error("Erro:", error);
            });
    });    

    document.addEventListener("DOMContentLoaded", function () {
        var oTable = new DataTable('#tabelaFornecedores', {
            ajax: {
                url: '../back/getFornecedores.php',
                dataSrc: ''
            },
            columns: [
                { data: 'fornecedor_id' },
                { data: 'ativo', 
                    render: function(data, type, row) {
                        // Ícone baseado no status (ativo/inativo)
                        const icone = row.ativo == 1 ? 
                            '<span class="status-ativo">✔️</span>' : 
                            '<span class="status-inativo">❌</span>';                        
                        return `${icone}`;
                    }                    
                },
                { data: 'nome' },
                { data: 'cpf_cnpj' },
                { data: 'num_principal' },
                { data: 'num_secundario' },
                { data: 'email' }
                //{ data: 'fk_endereco' }
            ],
            rowCallback: function(row, data, index) {
                if (data.ativo == 0) {
                    $(row).addClass('inativo'); // Adiciona classe CSS
                }
            },            
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
                    text: 'Alterar Fornecedor',
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