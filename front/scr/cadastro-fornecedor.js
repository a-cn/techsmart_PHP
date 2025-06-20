//Importar funções de validação
import { validaCPF, validarCNPJ } from './validacoes.js';

//Restringe entrada a apenas números para CPF/CNPJ
document.getElementById("cpf_cnpj").addEventListener("input", function (e) {
    e.target.value = e.target.value.replace(/\D/g, ""); //Remove caracteres não numéricos
    
    // Limpa o erro quando o usuário começa a digitar
    const erroCpfCnpj = document.getElementById('erroCpfCnpj');
    erroCpfCnpj.style.display = 'none';
    erroCpfCnpj.textContent = '';
});

//Função chamada ao buscar pelo CEP
document.getElementById('cep')?.addEventListener('input', function () {
    // Limpa o erro quando o usuário começa a digitar
    const erroCep = document.getElementById('erroCep');
    erroCep.style.display = 'none';
    erroCep.textContent = '';
});

document.getElementById('cep')?.addEventListener('blur', function () {
    const cep = this.value.replace(/\D/g, '');
    const erroCep = document.getElementById('erroCep');
    erroCep.style.display = 'none';
    erroCep.textContent = '';
    
    // Se o campo está vazio, não mostra erro (será validado no submit)
    if (!cep) {
        return;
    }
    
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

//Função de validação do CPF/CNPJ
document.getElementById('cpf_cnpj').addEventListener('blur', function () {
    const cpf_cnpj = this.value.replace(/\D/g, '');
    const erroCpfCnpj = document.getElementById('erroCpfCnpj');
    
    // Sempre limpa o erro primeiro
    erroCpfCnpj.style.display = 'none';
    erroCpfCnpj.textContent = '';
    
    // Se o campo está vazio, não mostra erro (será validado no submit)
    if (!cpf_cnpj) {
        return;
    }
    
    if (cpf_cnpj.length == 11) {
        if (!validaCPF(cpf_cnpj)) {
            erroCpfCnpj.textContent = 'CPF inválido.';
            erroCpfCnpj.style.display = 'block';
            return;
        }
    } else if (cpf_cnpj.length == 14) {
        if (!validarCNPJ(cpf_cnpj)) {
            erroCpfCnpj.textContent = 'CNPJ inválido.';
            erroCpfCnpj.style.display = 'block';
            return;
        }
    } else {
        erroCpfCnpj.textContent = 'CPF/CNPJ inválido.';
        erroCpfCnpj.style.display = 'block';
        return;
    }
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

    //Verifica o tamanho do CPF/CNPJ
    if(cpf_cnpj.length == 11) {
        if (!validaCPF(cpf_cnpj)) {
            mostrarMensagem('Aviso', 'CPF inválido.', 'alerta');
            return false;
        }
    } else if (cpf_cnpj.length == 14) {
        if (!validarCNPJ(cpf_cnpj)) {
            mostrarMensagem('Aviso', 'CNPJ inválido.', 'alerta');
            return false;
        }
    } else {
        mostrarMensagem('Aviso', 'CPF/CNPJ inválido.', 'alerta');
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
