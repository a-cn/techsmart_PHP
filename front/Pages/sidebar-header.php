<!-- CABEÇALHO E BARRA DE NAVEGAÇÃO COM PERMISSÕES POR TIPO DE USUÁRIO -->

<?php
require_once '../../Back/funcoes_sessao.php'; //Chama as funções salvas nesse arquivo para serem utilizadas e inicia sessão se não estiver ativa

$loginTimestamp = $_SESSION['login_timestamp'] ?? null;
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/sidebar-header.css">
    <title>Navegação</title>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="topbar">
        <div class="menu-icon" onclick="toggleSidebar()">☰</div>
        <span class="logo">TechSmart</span>

        <div class="user-info">
            <?php if (isset($_SESSION['tipo_usuario'])): ?>
                <div class="user-session-box">
                    <span class="user-role"><?php echo ucfirst($_SESSION['tipo_usuario']); ?></span>
                    <span id="session-timer">Sessão: 00:00:00</span>
                </div>
            <?php endif; ?>
            <a href="../../Back/faz_logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <!-- Barra de navegação -->
    <nav class="sidebar" id="sidebar">
        <ul>
            <?php if (esconderSeCliente()): ?>
                <li><a href="dashboard.php">Dashboard</a></li> <!-- Inserir aqui o caminho para a página de relatórios do sistema -->
            <?php endif; ?>

            <li><a href="dados-usuario.php">Minha Conta</a></li> <!-- Inserir aqui o caminho para a página de dados do usuário (perfil) -->
            
            <?php if (mostrarSeAdmin()): ?>
                <li><a href="listagem-usuarios.php">Usuários</a></li>
            <?php endif; ?>

            <?php if (esconderSeCliente()): ?>
                <li><a href="cadastro-fornecedor.php">Fornecedores</a></li>
            <?php endif; ?>
            
            <?php if (esconderSeCliente()): ?>
                <li><a href="cadastro-componente.php">Componentes</a></li>
            <?php endif; ?>
            
            <?php if (esconderSeCliente()): ?>
                <li><a href="cadastro-producao.php">Linhas de Produção</a></li>
            <?php endif; ?>
            
            <?php if (esconderSeCliente()): ?>
                <li><a href="cadastro-produto.php">Produtos</a></li>
            <?php endif; ?>
            
            <li><a href="historico_pedidos.php">Histórico de Pedidos</a></li>

            <li><a href="consulta-feedback.php">Feedbacks Registrados</a></li>

            <?php if (esconderSeCliente()): ?>
                <li><a href="consulta-movimentacao.php">Movimentações de Estoque</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <script>
        //PERMITE ESCONDER/MOSTRAR A BARRA LATERAL
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('shifted');
        }

        //CONTADOR DE TEMPO  DE SESSÃO (SOMENTE VISUAL; MOSTRA COMO HH:MM:SS)
        document.addEventListener("DOMContentLoaded", () => {
            const sessionStart = <?php echo $loginTimestamp || 'null'; ?>;

            if (sessionStart) {
                setInterval(() => {
                    const now = Math.floor(Date.now() / 1000);
                    const secondsElapsed = now - sessionStart;

                    const horas = String(Math.floor(secondsElapsed / 3600)).padStart(2, '0');
                    const minutos = String(Math.floor((secondsElapsed % 3600) / 60)).padStart(2, '0');
                    const segundos = String(secondsElapsed % 60).padStart(2, '0');

                    const timer = document.getElementById("session-timer");
                    if (timer) timer.innerText = `Sessão: ${horas}:${minutos}:${segundos}`;
                }, 1000);
            }
        });
    </script>
</body>
</html>