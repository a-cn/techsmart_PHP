<?php
//COMPILADO DE FUNÇÕES PARA ESCONDER ELEMENTOS DE ACORDO COM O TIPO DE USUÁRIO

//Verifica se há sessão ativa e, se não houver, inicia uma
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

//Elemento aparece somente para administradores - Utilizado no botão de "manipulação de usuários"
function mostrarSeAdmin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'administrador';
}

//Elemento aparece somente para colaboradores - Provavelmente não será utilizado
function mostrarSeColab() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'colaborador';
}

//Elemento não aparece para clientes - Utilizado na maioria dos botões
function esconderSeCliente() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] !== 'cliente';
}

//Elemento aparece somente para clientes - Utilizado em botões que não devem aparecer para administradores e colaboradores
function mostrarSeCliente() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'cliente';
}

/*
IMPORTANTE:
Incluir no topo de todas as páginas que utilizarem essas funções:
<?php require_once '../../Back/funcoes_sessao.php'; ?>

Exemplo de uso:

<?php if (esconderSeCliente()): ?>
    <button onclick="window.location.href='relatorios.php'">Ver Relatórios</button>
<?php endif; ?>
*/
?>