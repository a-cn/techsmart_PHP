//Restringe entrada a apenas números para CPF/CNPJ
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

//Função de validação completa do formulário de cadastro de fornecedor
function validateForm() {
    const nome = document.getElementById("nome").value.trim();
    if (!nome) {
        mostrarMensagem('Aviso',`O campo Razão Social ou Nome é obrigatório!`, 'alerta');
        return false;
    }

    const cpf_cnpj = document.getElementById("cpf_cnpj").value.trim();
    if (!cpf_cnpj) {
        mostrarMensagem('Aviso', `O campo CNPJ ou CPF é obrigatório!`, 'alerta');
        return false;
    }

    const camposComuns = [
        { id: "cep", nome: "CEP" },
        { id: "estado", nome: "Estado" },
        { id: "cidade", nome: "Cidade" },
        { id: "bairro", nome: "Bairro" },
        { id: "logradouro", nome: "Logradouro" },
        { id: "numero", nome: "Número do Imóvel" },
        { id: "num_principal", nome: "Número Principal para Contato" }
    ];
    
    for (let campo of camposComuns) {
        const valor = document.getElementById(campo.id).value.trim();
        if (valor === "") {
            mostrarMensagem('Aviso', `O Campo ${campo.nome} é obrigatório!`, 'alerta');
            return false;
        }
    }

    return true;
}
