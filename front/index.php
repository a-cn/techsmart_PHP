<!-- Página principal estrutural do site resposivo ao tipo do usuário -->
<?php
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/funcoes_sessao.php'; //Chama as funções salvas nesse arquivo para serem utilizadas e inicia sessão se não estiver ativa
$loginTimestamp = $_SESSION['login_timestamp'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'];
$body_class = 'body-background-fit background-' . substr($tipo_usuario, 0, 3);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- DataTables : https://datatables.net/  -->
    <!-- DataTables Personalização: https://datatables.net/download/  -->
    <!-- Tradução DataTables : https://diegomariano.com/datatables-em-portugues/ -->
    <link
        href="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.0.4/date-1.5.5/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.2/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css"
        rel="stylesheet" integrity="sha384-a+StyOLQiEaYS/laq/DITBpYX64P+2aT81Tk3kqGwdCotkppjr8nkKIxQ8wy0qD0"
        crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
        integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
        integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n"
        crossorigin="anonymous"></script>
    <script
        src="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.0.4/date-1.5.5/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.2/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.js"
        integrity="sha384-3AahuaUhHb3d/pxER58ULhJ1kNnGqaEytbGeqjhkWXxFKB+v++ZsQYtuQX/L3nbY"
        crossorigin="anonymous"></script>
    <!-- Final arquivos do DataTables -->
    <!-- Google Fonts  -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,700,0,200&icon_names=chevron_left,chevron_right,first_page,last_page" />

    <!-- bootstrap para o dashboard inicialmente -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/_styles.css">
    <link rel="stylesheet" type="text/css" href="css/borderglass.css">
    <title>TechSmart - Controle de Produção e Vendas</title>
</head>

<!--body> Gerado automaticamente pelo php identificando visualmente o tipo do usuário e definido pela classe com o nome do tipo do usuario-->
<!-- E que será mudado para Javascript, ficando aqui, por enquanto, como exemplo de ordem de rederização -->
    <?php print '<body class="' . $body_class . '">'; ?>

    <!-- Overlay -->
    <div id="overlay" class="overlay" style="display: none;"></div>

    <!-- Janela de Mensagens/Dialogo -->
    <div id="janela-mensagens" class="popup-bordeglass" style="display: none;">
        <div class="conteudo">
            <h2 id="popup-titulo">Mensagem</h2>
            <p id="popup-texto">Texto da mensagem ou pergunta.</p>
            <div class="botoes-popup">
                <button id="btn-sim" style="display: none;">Sim</button>
                <button id="btn-nao" style="display: none;">Não</button>
                <button id="btn-fechar">Fechar</button>
            </div>
        </div>
    </div>

    <header id="topbar">
        <div class="menu-icon" onclick="toggleSidebar()">☰</div>
        <span class="logo">TechSmart</span>
        <div class="user-info">
            <?php if (isset($_SESSION['tipo_usuario'])): ?>
                <div class="user-session-box">
                    <span class="user-role"><?php echo ucfirst($tipo_usuario); ?></span>
                    <span id="session-timer">Sessão: 00:00:00</span>
                </div>
            <?php endif; ?>
            <a href="../back/faz_logout.php" class="logout-btn">Sair</a>
        </div>
    </header>
    <div id="container">
        <nav id="sidebar">
            <?php
            /*/ Adionando o trecho de código do menu conforme o tipo do usuário /*/
            include_once 'menu_' . $tipo_usuario . '.html';
            ?>
        </nav>
        <main id="main">
            <?php
            /*/ De mesma forma ao menu em sidebar, vamos adicionar o trecho do código da opção selecionada /*/

            // Lista de arquivos permitidos para evitar acessos indevidos
            $paginas_permitidas = [
                "dashboard",
                "minha-conta",
                "usuarios",
                "fornecedores",
                "componentes",
                "producao",
                "produtos",
                "iniciar-producao",
                "pedidos",
                "feedbacks",
                "alterar-conta",
                "alterar-feedback",
                "formulario-feedback",
                "historico-producao"
            ];

            // Obtém o parâmetro e filtra
            $pagina = $_GET['pg'] ?? "minha-conta"; // Página padrão
            $pagina = basename($pagina); // Remove caminhos indesejados (exemplo: "../arquivo")
            
            // Verifica se a página está na lista de permitidos
            if (in_array($pagina, $paginas_permitidas)) {
                include_once $pagina . ".php";
            } else {
                include_once "dashboard.php";
            }
            ?>
            <!-- "mostrarMensagem('Título', 'Mensagem','Tipo')"
            <button onclick="mostrarMensagem('Aviso', 'Este é um teste de mensagem.')">Testar Mensagem</button>
            <button onclick="mostrarMensagem('Aviso-Erro', 'Este é um teste de mensagem.', 'erro')">Mensagem Erro</button>
            <button onclick="mostrarMensagem('Aviso-Alerta', 'Este é um teste de mensagem.', 'alerta')">Mensagem Alerta</button>
            <button onclick="mostrarMensagem('Aviso-Sucesso', 'Este é um teste de mensagem.', 'sucesso')">Mensagem Sucesso</button>
            -->
            <!-- "mostrarDialogo('Título', 'Mensagem', () => função para ('Sim!'), () => função para ('Não!'),'Tipo')" 
            <button onclick="mostrarDialogo('Confirmação', 'Deseja continuar?', () => alert('Sim!'), () => alert('Não!'))">Testar Diálogo</button>
            <button onclick="mostrarDialogo('Teste Erro', 'Deseja continuar?', () => alert('Sim!'), () => alert('Não!'),'erro')">Diálogo Erro</button>
            <button onclick="mostrarDialogo('Teste Alerta', 'Deseja continuar?', () => alert('Sim!'), () => alert('Não!'),'alerta')">Diálogo Alerta</button>
            <button onclick="mostrarDialogo('Teste Sucesso', 'Deseja continuar?', () => alert('Sim!'), () => alert('Não!'),'sucesso')">Diálogo Sucesso</button>
            -->
        </main>
    </div>
    <script>
        //PERMITE ESCONDER/MOSTRAR A BARRA LATERAL
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        //CONTADOR DE TEMPO  DE SESSÃO (SOMENTE VISUAL; MOSTRA COMO HH:MM:SS)
        document.addEventListener("DOMContentLoaded", () => {
            const sessionStart = <?php echo isset($loginTimestamp) ? intval($loginTimestamp) : 'null'; ?>;

            if (sessionStart) {
                setInterval(() => {
                    const now = Math.floor(Date.now() / 1000);
                    const secondsElapsed = now - sessionStart;

                    const hr = String(Math.floor(secondsElapsed / 3600)).padStart(2, '0');
                    const mn = String(Math.floor((secondsElapsed % 3600) / 60)).padStart(2, '0');
                    const sg = String(secondsElapsed % 60).padStart(2, '0');

                    const timer = document.getElementById("session-timer");
                    if (timer) timer.innerText = `Sessão: ${hr}:${mn}:${sg}`;
                }, 1000);
            }
        });
    </script>
    <!-- Importante lembrar que scripts que manipulam elementos devem vir depois que eles são renderizados -->
    <script src="scr/janelas.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>