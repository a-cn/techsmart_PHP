<?php
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
require_once '../back/verifica_sessao.php';   //Garante que somente usuários logados possam acessar a página
require_once '../back/validacoes.php';   //Garante que somente usuários logados possam acessar a página
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
<script src="scr/janelas.js"></script>
<script src="scr/script.js"></script>
<script type="module" src="scr/validacoes.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const response = await fetch('../back/dados_usuario.php', { credentials: 'include' });
        const data = await response.json();
        if (data.status === 'success') {
            const user = data.data;

            //Preenche os campos com os dados do usuário logado
            document.getElementById('nome').value = user.nome || '';
            document.getElementById('cpf_cnpj').value = user['cpf-cnpj'] || '';
            marcarCheckboxCPFCNPJ(user['cpf-cnpj'] || ''); // Marca o tipo de documento
            document.getElementById('data_nascimento').value = user.data_nascimento || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('num_principal').value = user['num-principal'] || '';
            document.getElementById('num_recado').value = user['num-recado'] || '';
            document.getElementById('cep').value = user.cep || '';
            document.getElementById('logradouro').value = user.logradouro || '';
            document.getElementById('numero').value = user.numero || '';
            document.getElementById('complemento').value = user.complemento || '';
            document.getElementById('bairro').value = user.bairro || '';
            document.getElementById('cidade').value = user['cidade-estado']?.split('/')[0] || '';
            document.getElementById('estado').value = user['cidade-estado']?.split('/')[1] || '';

            // Ajusta a visibilidade dos campos baseado no tipo de documento
            toggleCPFCNPJ();
        }
    });

    // Muda o tipo de pessoa dependendo do dado informado em cpf_cnpj
    document.getElementById("cpf_cnpj").addEventListener("input", function (e) {
        marcarCheckboxCPFCNPJ(e.target.value);
        toggleCPFCNPJ();
    });
</script>

<div>
    <div class="janela-cadastro" id="divAlterarConta">
        <span class="titulo-janela" id="form-usr-titulo">Alterar Dados da Conta</span>
        <form id="form-cadastro" class="form-content" action="../back/putMinha_conta.php" method="POST" onsubmit="return validateForm()">

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
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-nome-razao_social" for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome completo" required>
                        <input type="hidden" id="razao_social" name="razao_social">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label id="lbl-cpf-cnpj" for="cpf">CPF:</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" maxlength="14" placeholder="Digite seu CPF" required>
                        <div class="form-group" style="margin-top: -10px;">
                            <small id="erroCpfCnpj" style="display: none; color: #ff431b; font-weight: 500;"></small>
                        </div>
                    </div>
                    <div id="dvDtNasc" class="form-group">
                        <label for="data_nascimento">Data de Nascimento:</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" max="<?= date('d-m-Y') ?>">
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
                    <div class="form-group" style="margin-top: -10px;">
                        <small id="erroCep" style="display: none; color: #ff431b; font-weight: 500;"></small>
                    </div>
                </div>
                <div class="form-group">
                    <label for="estado">Estado (UF):</label>
                    <input type="text" id="estado" name="estado" maxlength="2" placeholder="Ex.: PR"
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
                    <label for="complemento">Complemento (opcional):</label>
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
                        placeholder="Ex.: 41999999999" required>
                </div>
                <div class="form-group">
                    <label for="num_recado">Número de Recado (opcional):</label>
                    <input type="text" id="num_recado" name="num_recado" maxlength="15"
                        placeholder="Ex.: 4133333333">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="senha">Nova Senha (opcional):</label>
                    <input type="password" id="senha" name="senha" maxlength="15" placeholder="Digite sua senha">
                </div>
                <div class="form-group">
                    <label for="confirmSenha">Confirmar Nova Senha:</label>
                    <input type="password" id="confirmSenha" name="confirmSenha" maxlength="15"
                        placeholder="Confirme sua senha">
                </div>
            </div>
            <div class="form-group" id="regraSenha" style="display: none; margin-top: -20px;">
                <small style="color: #ff431b; font-weight: 500;">As senhas devem ter entre 9 e 15 caracteres, conter pelo menos uma letra maiúscula, um número e um caractere especial.</small>
            </div>

            <!-- Campo para selecionar perguntas de segurança, puxando-as do banco de dados -->
            <div class="form-row">
                <div class="form-group">
                    <label for="securityQuestion">Escolha uma pergunta de segurança (opcional):</label>
                    <select id="securityQuestion" name="fk_pergunta_seguranca" class="form-control">
                        <option value="">Selecione uma pergunta</option>
                        <?php 
                        foreach ($perguntas as $p):
                            print '<option value="' . $p['pergunta_seguranca_id'] . '">';
                            print htmlspecialchars($p['pergunta']);
                            print '</option>';
                        endforeach;
                        ?>                        
                    </select>
                </div>
            </div>

            <!-- Resposta da pergunta -->
            <div class="form-row">
                <div class="form-group">
                    <label for="securityAnswer">Resposta para a pergunta escolhida (opcional):</label>
                    <input type="text" id="securityAnswer" name="resposta_seguranca" maxlength="100"
                    placeholder="Digite sua resposta">
                </div>
            </div>

            <!-- Botões -->
            <div class="form-row">
                <input type="submit" class="btn-cadastrar" value="Salvar">
                <button type="button" class="btn-pesquisar" onclick="limpaCadastro(); window.location.href='index.php?pg=minha-conta';">Cancelar</button>
            </div>
        </form>
        <!-- Mensagem de erro -->
        <div id="error-message" class="error"></div>
    </div>
    <script type="module" src="./scr/validacoes.js"></script>
    <script type="module" src="./scr/cadastro-usuario.js"></script>

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

    // Função de validação específica para alteração de conta
    function validateForm() {
        const tipoPessoa = document.querySelector('input[name="tipo_pessoa"]:checked');
        if (!tipoPessoa) {
            mostrarMensagem('Aviso', 'Selecione se você é Pessoa Física (CPF) ou Jurídica (CNPJ).', 'alerta');
            return false;
        }

        let Nome     = (tipoPessoa.value === "cpf") ? "Nome" : "Razão Social";
        let Cpf_Cnpj = (tipoPessoa.value === "cpf") ? "CPF"  : "CNPJ";

        const nome = document.getElementById("nome").value.trim();
        if (!nome) {
            mostrarMensagem('Aviso', `O campo ${Nome} é obrigatório!`, 'alerta');
            return false;
        }

        const cpf_cnpj = document.getElementById("cpf_cnpj").value.trim();
        if (!cpf_cnpj) {
            mostrarMensagem('Aviso', `O campo ${Cpf_Cnpj} é obrigatório!`, 'alerta');
            return false;
        }

        const nascimento = document.getElementById("data_nascimento").value;
        if (!nascimento && tipoPessoa.value === "cpf") {
            mostrarMensagem('Aviso', 'O campo Data de Nascimento é obrigatório.', 'alerta');
            return false;
        }

        // Validação de CPF/CNPJ
        if (tipoPessoa.value === "cpf") {
            if (!validaCPF(cpf_cnpj)) {
                mostrarMensagem('Aviso', 'CPF inválido.', 'alerta');
                return false;
            }
        } else {
            if (!validarCNPJ(cpf_cnpj)) {
                mostrarMensagem('Aviso', 'CNPJ inválido.', 'alerta');
                return false;
            }
        }

        // Campos obrigatórios
        const camposComuns = [
            { id: "cep", nome: "CEP" },
            { id: "estado", nome: "Estado" },
            { id: "cidade", nome: "Cidade" },
            { id: "bairro", nome: "Bairro" },
            { id: "logradouro", nome: "Logradouro" },
            { id: "numero", nome: "Número do Imóvel" },
            { id: "email", nome: "Email" },
            { id: "confirmEmail", nome: "Confirmação de Email" },
            { id: "num_principal", nome: "Número Principal para Contato" }
        ];
        
        for (let campo of camposComuns) {
            const valor = document.getElementById(campo.id).value.trim();
            if (valor === "") {
                mostrarMensagem('Aviso', `O Campo ${campo.nome} é obrigatório!`, 'alerta');
                return false;
            }
        }

        const email = document.getElementById("email").value;
        const confirmEmail = document.getElementById("confirmEmail").value;
        if (email !== confirmEmail) {
            mostrarMensagem('Aviso', 'Os emails não coincidem!', 'alerta');
            return false;
        }

        // Validação condicional de senha (só se o usuário preencher)
        const senha = document.getElementById("senha").value;
        const confirmSenha = document.getElementById("confirmSenha").value;
        
        if (senha || confirmSenha) {
            if (!senha || !confirmSenha) {
                mostrarMensagem('Aviso', 'Se você deseja alterar a senha, preencha ambos os campos de senha.', 'alerta');
                return false;
            }
            if (senha !== confirmSenha) {
                mostrarMensagem('Aviso', 'As senhas não coincidem!', 'alerta');
                return false;
            }
            // Validação da força da senha
            const regexSenha = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{9,}$/;
            if (!regexSenha.test(senha)) {
                mostrarMensagem('Aviso', 'A senha deve ter no mínimo 9 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial.', 'alerta');
                return false;
            }
        }

        // Validação condicional de pergunta de segurança (só se o usuário preencher)
        const pergunta = document.getElementById("securityQuestion").value;
        const resposta = document.getElementById("securityAnswer").value;
        
        if (pergunta || resposta) {
            if (!pergunta || !resposta) {
                mostrarMensagem('Aviso', 'Se você deseja alterar a pergunta de segurança, preencha tanto a pergunta quanto a resposta.', 'alerta');
                return false;
            }
        }

        return true;
    }
</script>