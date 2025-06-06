<?php
function validarCNPJ($cnpj) {
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    // Verifica se foi informado e tem 14 caracteres
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Elimina CNPJs inválidos conhecidos
    if (in_array($cnpj, array(
        '00000000000000', '11111111111111', '22222222222222', 
        '33333333333333', '44444444444444', '55555555555555',
        '66666666666666', '77777777777777', '88888888888888', 
        '99999999999999'
    ))) {
        return false;
    }
    
    // Valida primeiro dígito verificador
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Valida segundo dígito verificador
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem com os informados
    return ($cnpj[12] == $dv1 && $cnpj[13] == $dv2);
}

// Função para validar e formatar o e-mail
function validarEmail($email) {
    // Remove espaços em branco no início e fim
    $email = trim($email);
    
    // Valida a estrutura básica
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Validação adicional com regex (opcional)
    $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($regex, $email);
}

// Função para validar CEP
function validarCEP($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    // Verifica se tem 8 dígitos
    return (strlen($cep) === 8);
}

// Função para buscar endereço via API ViaCEP
function buscarEndereco($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    
    // Inicia cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $dados = json_decode($response, true);
    
    return (isset($dados['erro'])) ? false : $dados;
}


?>
