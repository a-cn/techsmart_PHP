<?php
//CÓDIGO PARA VERIFICAR SE EXISTE SESSÃO ATIVA (SE ESTÁ LOGADO)

// Verifica se a sessão já foi iniciada antes de iniciar uma nova
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenera o ID da sessão após login bem-sucedido para evitar sequestro de sessão
if (!isset($_SESSION['email'])) {
    session_regenerate_id(true);
}

// Tempo máximo de inatividade (em segundos)
$tempoInatividade = 30 * 60; // 30 minutos

// Verifica se o usuário está logado
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    // Se não houver usuário logado, redireciona para a tela de login
    header("Location: ../index.html");
    exit;
}

// Verifica o tempo de inatividade
if (isset($_SESSION['ultimo_acesso'])) {
    $tempoDesdeUltimoAcesso = time() - $_SESSION['ultimo_acesso'];
    if ($tempoDesdeUltimoAcesso > $tempoInatividade) {
        // Encerra a sessão e redireciona
        session_unset();
        session_destroy();
        header("Location: ../index.html?erro=timeout");
        exit;
    }
}

// Atualiza o tempo do último acesso
$_SESSION['ultimo_acesso'] = time();

// Debugging opcional para verificar variáveis de sessão
// var_dump($_SESSION);
?>