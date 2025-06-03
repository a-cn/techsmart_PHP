//Função para alternar os campos de acordo com a escolha entre CPF ou CNPJ
function toggleCPFCNPJ() {
    const tipoPessoa = document.querySelector('input[name="tipo_pessoa"]:checked')?.value;
    const nrs_Fields = document.getElementById("lbl-nome-razao_social");
    const cc_Fields = document.getElementById("lbl-cpf-cnpj");
    const tipoUsuario = document.getElementById("dvTipoUsuario");
    const dtNasc = document.getElementById("dvDtNasc");

    if (tipoPessoa === "cpf") {
        nrs_Fields.textContent = "Nome:";
        cc_Fields.textContent = "CPF:";
        tipoUsuario.style.display = "flex";
        dtNasc.style.display = "flex"
    } else if (tipoPessoa === "cnpj") {
        nrs_Fields.textContent = "Razão Social:";
        cc_Fields.textContent = "CNPJ:";
        tipoUsuario.style.display = "none";
        dtNasc.style.display = "none";
    }
}

//Restringe entrada a apenas números para CPF/CNPF
document.getElementById("cpf_cnpj").addEventListener("input", function (e) {
    e.target.value = e.target.value.replace(/\D/g, ""); //Remove caracteres não numéricos
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
    if (!tipoPessoa) {
        mostrarMensagem('Aviso', 'Selecione se você é Pessoa Física (CPF) ou Jurídica (CNPJ).', 'alerta');
        return false;
    }

    let Nome     = (tipoPessoa.value === "cpf") ? "Nome" : "Razão Social";
    let Cpf_Cnpj = (tipoPessoa.value === "cpf") ? "CPF"  : "CNPJ";

    const nome = document.getElementById("nome").value.trim();
    if (!nome) {
        mostrarMensagem('Aviso',`O campo ${Nome} é obrigatório!`, 'alerta');
        return false;
    }

    const cpf_cnpj = document.getElementById("cpf_cnpj").value.trim();
    if (!cpf_cnpj) {
        mostrarMensagem('Aviso', `O campo ${Cpf_Cnpj} é obrigatório!`, 'alerta');
        return false;
    }

    const nascimento = document.getElementById("data_nascimento").value;
    if (!nascimento && tipoPessoa.value === "cpf") {
        mostrarMensagem('Aviso', 'O Campo Data de Nascimento é obrigatório.', 'alerta');
        return false;
    }

    const camposComuns = [
        { id: "cep", nome: "CEP" },
        { id: "estado", nome: "Estado" },
        { id: "cidade", nome: "Cidade" },
        { id: "bairro", nome: "Bairro" },
        { id: "logradouro", nome: "Logradouro" },
        { id: "numero", nome: "Número do Imóvel" },
        { id: "email", nome: "Email" },
        { id: "confirmEmail", nome: "Confirmação de Email" },
        { id: "num_celular", nome: "Número de Celular" }
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

    return true;
}
