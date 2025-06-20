/**
 * Arquivo de validações centralizadas
 * Contém funções para validação de documentos brasileiros e outros campos
 */

//Função para validar CPF
export function validaCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');
    
    // Verifica se tem 11 dígitos
    if (cpf.length !== 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    // Validação do primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = soma % 11;
    let digitoVerificador1 = (resto < 2) ? 0 : (11 - resto);
    
    if (parseInt(cpf.charAt(9)) !== digitoVerificador1) {
        return false;
    }
    
    // Validação do segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = soma % 11;
    let digitoVerificador2 = (resto < 2) ? 0 : (11 - resto);
    
    if (parseInt(cpf.charAt(10)) !== digitoVerificador2) {
        return false;
    }
    
    return true;
}

//Função para validar CNPJ
export function validarCNPJ(cnpj) {
    // Remove caracteres não numéricos
    cnpj = cnpj.replace(/\D/g, '');
    
    // Verifica se tem 14 dígitos
    if (cnpj.length !== 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{13}$/.test(cnpj)) {
        return false;
    }
    
    // Validação do primeiro dígito verificador
    let soma = 0;
    let peso = 2;
    for (let i = 11; i >= 0; i--) {
        soma += parseInt(cnpj.charAt(i)) * peso;
        peso = (peso === 9) ? 2 : peso + 1;
    }
    let resto = soma % 11;
    let digitoVerificador1 = (resto < 2) ? 0 : 11 - resto;
    
    if (parseInt(cnpj.charAt(12)) !== digitoVerificador1) {
        return false;
    }
    
    // Validação do segundo dígito verificador
    soma = 0;
    peso = 2;
    for (let i = 12; i >= 0; i--) {
        soma += parseInt(cnpj.charAt(i)) * peso;
        peso = (peso === 9) ? 2 : peso + 1;
    }
    resto = soma % 11;
    let digitoVerificador2 = (resto < 2) ? 0 : 11 - resto;
    
    if (parseInt(cnpj.charAt(13)) !== digitoVerificador2) {
        return false;
    }
    
    return true;
}

//Função para validar email
export function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

//Função para validar CEP
export function validarCEP(cep) {
    const cepLimpo = cep.replace(/\D/g, '');
    return cepLimpo.length === 8;
}

//Função para validar telefone
export function validarTelefone(telefone) {
    const telefoneLimpo = telefone.replace(/\D/g, '');
    return telefoneLimpo.length >= 10 && telefoneLimpo.length <= 11;
}

//Função para validar senha forte
export function validarSenhaForte(senha) {
    // Mínimo 8 caracteres, pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return regex.test(senha);
}