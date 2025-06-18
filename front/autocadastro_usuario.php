<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts (Material Symbols) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,700,0,200&icon_names=chevron_left,chevron_right,first_page,last_page" />
    <!-- Estilos do sistema -->
    <link rel="stylesheet" type="text/css" href="css/_styles.css">
    <link rel="stylesheet" type="text/css" href="css/janelas.css">
    <style>
        #public-topbar {
            height: 60px;
            background-color: #004b87;
            color: white;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: left;
            font-size: 20px;
            align-items: center;
        }
    </style>
    <title>Cadastro de Usuário</title>
</head>
<body>
<header id="public-topbar">
    <div class="menu-icon" onclick="toggleSidebar()">☰</div>
    <span class="logo">TechSmart</span>
</header>
<div id="container">
    <nav id="sidebar">
        <ul>
            <li><a href="../index.html">Página de Login</a></li>
            <li><a href="autocadastro_usuario.php">Cadastro de Usuário</a></li>
            <li><a href="recuperar-senha.html">Redefinição de Senha</a></li>
        </ul>
    </nav>
    <main id="main">
        <?php
        //Conecta ao banco e busca as perguntas de segurança da tabela Pergunta_Seguranca
        require_once '../back/conexao_sqlserver.php';
        $perguntas = [];
        $sql = "SELECT pergunta_seguranca_id, pergunta FROM Pergunta_Seguranca";
        $result = sqlsrv_query($conn, $sql);
        if ($result) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $perguntas[] = $row;
            }
        }
        ?>
        <div class="janela-cadastro">
            <span class="titulo-janela">Cadastro de Usuário</span>
            <form class="form-content" action="../back/putAutocadastro_usuario.php" method="POST" onsubmit="return validateForm()">
                <!-- Seleção entre CPF e CNPJ -->
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="radio" name="tipo_pessoa" value="cpf" onclick="toggleFields()" checked required> CPF
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="tipo_pessoa" value="cnpj" onclick="toggleFields()"> CNPJ
                        </label>
                    </div>
                </div>
                <!-- Campos para CPF -->
                <div id="cpf-fields" style="display: block;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome completo">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf">CPF:</label>
                            <input type="number" id="cpf" name="cpf" maxlength="11" placeholder="Digite seu CPF">
                        </div>
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
                <!-- Campos para CNPJ -->
                <div id="cnpj-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="razao_social">Razão Social:</label>
                            <input type="text" id="razao_social" name="razao_social" maxlength="100" placeholder="Digite sua Razão Social">
                        </div>
                        <div class="form-group">
                            <label for="cnpj">CNPJ:</label>
                            <input type="number" id="cnpj" name="cnpj" maxlength="14" placeholder="Digite seu CNPJ">
                        </div>
                    </div>
                </div>
                <!-- Campos de Endereço -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="cep">CEP:</label>
                        <input type="text" id="cep" name="cep" placeholder="Ex.: 80000000" class="form-control" maxlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" maxlength="50" placeholder="Ex.: Paraná" class="form-control" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top: -20px;">
                    <small id="erroCep" style="display: none; color: #1976d2; font-weight: 500;"></small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" maxlength="50" placeholder="Ex.: Curitiba" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" maxlength="50" placeholder="Ex.: Portão" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="logradouro">Logradouro:</label>
                        <input type="text" id="logradouro" name="logradouro" maxlength="150" placeholder="Ex.: Rua Itajubá" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="numero">Número:</label>
                        <input type="number" id="numero" name="numero" placeholder="Digite o número do imóvel" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" maxlength="100" placeholder="Ex.: Bloco 1" class="form-control">
                    </div>
                </div>
                <!-- Outros campos do formulário -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" maxlength="50" placeholder="Ex.: email@dominio.com" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmEmail">Confirmar Email:</label>
                        <input type="email" id="confirmEmail" name="confirmEmail" maxlength="50" placeholder="Confirme seu email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="num_principal">Número Principal para Contato:</label>
                        <input type="text" id="num_principal" name="num_principal" maxlength="15" placeholder="Ex.: (41) 99876-5432" required>
                    </div>
                    <div class="form-group">
                        <label for="num_recado">Número de Recado:</label>
                        <input type="text" id="num_recado" name="num_recado" maxlength="15" placeholder="Ex.: (41) 3333-3333">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" maxlength="15" placeholder="Digite sua senha" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmSenha">Confirmar Senha:</label>
                        <input type="password" id="confirmSenha" name="confirmSenha" maxlength="15" placeholder="Confirme sua senha" required>
                    </div>
                </div>
                <div class="form-group" id="regraSenha" style="display: none; margin-top: -20px;">
                    <small style="color: #1976d2; font-weight: 500;">As senhas devem ter entre 9 e 15 caracteres, conter pelo menos uma letra maiúscula, um número e um caractere especial.</small>
                </div>
                <!-- Campo para selecionar perguntas de segurança, puxando-as do banco de dados -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="securityQuestion">Escolha uma pergunta de segurança:</label>
                        <select id="securityQuestion" name="securityQuestion" class="form-control" required>
                            <option value="">Selecione uma pergunta</option>
                            <?php foreach ($perguntas as $p): ?>
                                <option value="<?= $p['pergunta_seguranca_id'] ?>">
                                    <?= htmlspecialchars($p['pergunta']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Resposta da pergunta -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="securityAnswer">Resposta para a pergunta escolhida:</label>
                        <input type="text" id="securityAnswer" name="securityAnswer" maxlength="100" placeholder="Digite sua resposta" required>
                    </div>
                </div>
                <!-- Mensagem de erro -->
                <div id="error-message" class="error"></div>
                <!-- Botões -->
                <div class="form-row">
                    <input type="submit" class="btn-cadastrar" value="Cadastrar">
                    <button type="button" class="btn-pesquisar" onclick="window.location.href='../index.html';">Cancelar</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="./scr/autocadastro-usuario.js"></script>
<script>
//PERMITE ESCONDER/MOSTRAR A BARRA LATERAL
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}
</script>
<!-- Importante lembrar que scripts que manipulam elementos devem vir depois que eles são renderizados -->
<script src="scr/janelas.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>