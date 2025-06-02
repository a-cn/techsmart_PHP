<?php
//CÓDIGO PARA VALIDAR CAMPOS OBRIGATÓRIOS NO BACK
//É diferente das validações do front, pois aqui evita que campos vazios sejam enviados ao banco

function campoObrigatorio($campo, $nomeCampo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
        die("O campo '$nomeCampo' é obrigatório.");
    }
    return trim($_POST[$campo]);
}
?>