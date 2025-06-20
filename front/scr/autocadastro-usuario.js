// Importar funções de validação
import { validaCPF, validarCNPJ } from './validacoes.js';

//Função para mostrar/esconder os campos de acordo com a escolha entre CPF ou CNPJ
export function toggleFields() {
    const tipoPessoa = document.querySelector('input[name="tipo_pessoa"]:checked')?.value;
    const cpfFields = document.getElementById("cpf-fields");
    const cnpjFields = document.getElementById("cnpj-fields");

    if (tipoPessoa === "cpf") {
        cpfFields.style.display = "block";
        cnpjFields.style.display = "none";
    } else if (tipoPessoa === "cnpj") {
        cnpjFields.style.display = "block";
        cpfFields.style.display = "none";
    }
}

// Tornar a função global para compatibilidade com onclick no HTML
window.toggleFields = toggleFields;

//Restringe entrada a apenas números para CPF
document.getElementById("cpf").addEventListener("input", function (e) {
    e.target.value = e.target.value.replace(/\D/g, ""); //Remove caracteres não numéricos
    
    // Limpa o erro quando o usuário começa a digitar
    const erroCpfCnpj = document.getElementById('erroCpf');
    erroCpfCnpj.style.display = 'none';
    erroCpfCnpj.textContent = '';
});

//Restringe entrada a apenas números para CNPJ
document.getElementById("cnpj").addEventListener("input", function (e) {
    e.target.value = e.target.value.replace(/\D/g, ""); //Remove caracteres não numéricos
    
    // Limpa o erro quando o usuário começa a digitar
    const erroCpfCnpj = document.getElementById('erroCnpj');
    erroCpfCnpj.style.display = 'none';
    erroCpfCnpj.textContent = '';
});

//Limpa o erro quando o usuário começa a digitar o CEP
document.getElementById('cep')?.addEventListener('input', function () {
    // Limpa o erro quando o usuário começa a digitar
    const erroCep = document.getElementById('erroCep');
    erroCep.style.display = 'none';
    erroCep.textContent = '';
});

//Função chamada ao buscar pelo CEP
document.getElementById('cep')?.addEventListener('blur', function () {
    const cep = this.value.replace(/\D/g, '');
    const erroCep = document.getElementById('erroCep');
    erroCep.style.display = 'none';
    erroCep.textContent = '';
    if (cep.length !== 8) {
        erroCep.textContent = 'CEP inválido.';
        erroCep.style.display = 'block';
        return;
    }
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => res.json())
        .then(data => {
            if (data.erro) {
                erroCep.textContent = 'CEP não encontrado.';
                erroCep.style.display = 'block';
                return;
            }
            document.getElementById('logradouro').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('estado').value = data.uf || '';
        })
        .catch(() => {
            erroCep.textContent = 'Erro ao buscar o CEP.';
            erroCep.style.display = 'block';
        });
});

//Função de validação do CPF
document.getElementById('cpf').addEventListener('blur', function () {
    const cpf = this.value.replace(/\D/g, '');
    const erroCpf = document.getElementById('erroCpf');
    
    // Sempre limpa o erro primeiro
    erroCpf.style.display = 'none';
    erroCpf.textContent = '';
    
    // Se o campo está vazio, não mostra erro
    if (!cpf) {
        return;
    }
    
    if (!validaCPF(cpf)) {
        erroCpf.textContent = 'CPF inválido.';
        erroCpf.style.display = 'block';
        return;
    }
});

//Função de validação do CNPJ
document.getElementById('cnpj').addEventListener('blur', function () {
    const cnpj = this.value.replace(/\D/g, '');
    const erroCnpj = document.getElementById('erroCnpj');
    
    // Sempre limpa o erro primeiro
    erroCnpj.style.display = 'none';
    erroCnpj.textContent = '';
    
    // Se o campo está vazio, não mostra erro
    if (!cnpj) {
        return;
    }
    
    if (!validarCNPJ(cnpj)) {
        erroCnpj.textContent = 'CNPJ inválido.';
        erroCnpj.style.display = 'block';
        return;
    }
});

//Esconde o texto de regras de senha até que o usuário clique no campo correspondente
const campoSenha = document.getElementById("senha");
const regraSenha = document.getElementById("regraSenha");
campoSenha.addEventListener("focus", function () {
    regraSenha.style.display = "block";
});
campoSenha.addEventListener("blur", function () {
    regraSenha.style.display = "none";
});

//Função de validação completa do formulário de cadastro de usuário
function validateForm() {
    const tipoPessoa = document.querySelector('input[name="tipo_pessoa"]:checked');
    const errorMsg = document.getElementById("error-message");

    if (!tipoPessoa) {
        errorMsg.textContent = "Selecione se você é Pessoa Física (CPF) ou Jurídica (CNPJ).";
        return false;
    }

    const tipo = tipoPessoa.value;
    let mensagensErro = [];

    //Verificação de campos para CPF - Pessoa Física
    if (tipo === "cpf") {
        const nome = document.getElementById("nome").value.trim();
        const cpf = document.getElementById("cpf").value.trim();
        const nascimento = document.getElementById("data_nascimento").value;

        if (nome === "") mensagensErro.push("O campo Nome é obrigatório.");
        if (cpf === "") mensagensErro.push("O campo CPF é obrigatório.");
        if (nascimento === "") mensagensErro.push("O campo Data de Nascimento é obrigatório.");
    }

    //Verificação de campos para CNPJ - Pessoa Jurídica
    if (tipo === "cnpj") {
        const razao = document.getElementById("razao_social").value.trim();
        const cnpj = document.getElementById("cnpj").value.trim();

        if (razao === "") mensagensErro.push("O campo Razão Social é obrigatório.");
        if (cnpj === "") mensagensErro.push("O campo CNPJ é obrigatório.");
    }

    if (tipo === "cpf") {
        if (!validaCPF(cpf)) mensagensErro.push("CPF inválido.");
    } else {
        if (!validarCNPJ(cnpj)) mensagensErro.push("CNPJ inválido.");
    }

    //Verificação de campos comuns obrigatórios
    const camposComuns = [
        { id: "cep", nome: "CEP" },
        { id: "estado", nome: "Estado" },
        { id: "cidade", nome: "Cidade" },
        { id: "bairro", nome: "Bairro" },
        { id: "logradouro", nome: "Logradouro" },
        { id: "numero", nome: "Número do Imóvel" },
        { id: "email", nome: "Email" },
        { id: "confirmEmail", nome: "Confirmação de Email" },
        { id: "num_principal", nome: "Número Principal para Contato" },
        { id: "senha", nome: "Senha" },
        { id: "confirmSenha", nome: "Confirmação de Senha" },
        { id: "securityQuestion", nome: "Pergunta de Segurança" },
        { id: "securityAnswer", nome: "Resposta de Segurança" }
    ];

    camposComuns.forEach(campo => {
        const valor = document.getElementById(campo.id).value.trim();
        if (valor === "") mensagensErro.push(`O campo ${campo.nome} é obrigatório.`);
    });

    //Verifica se os emails são idênticos
    const email = document.getElementById("email").value;
    const confirmEmail = document.getElementById("confirmEmail").value;
    if (email !== confirmEmail) mensagensErro.push("Os emails não coincidem.");

    //Verifica se as senhas são idênticas
    const senha = document.getElementById("senha").value;
    const confirmSenha = document.getElementById("confirmSenha").value;
    if (senha !== confirmSenha) mensagensErro.push("As senhas não coincidem.");

    //Validação de complexidade de senha
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{9,}$/;
    if (!passwordRegex.test(senha)) {
        mensagensErro.push("A senha deve ter no mínimo 9 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um caractere especial.");
    }

    //Exibição de erros (se houver)
    if (mensagensErro.length > 0) {
        errorMsg.innerHTML = mensagensErro.join("<br>");
        return false;
    }

    //Limpa mensagens anteriores
    errorMsg.textContent = "";
    return true;
}