<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

    define('TIPO_CLIENTE', 3); //O número é referente ao id do Cliente na tabela Tipo_Usuario
    define('TIPO_COLABORADOR', 2); //O número é referente ao id do Colaborador na tabela Tipo_Usuario

    //Determina o tipo de usuário que está logado (se houver)
    $tipo_usuario_logado = ($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    //$fk_tipo_usuario = ($tipo_usuario_logado === 'administrador') ? TIPO_COLABORADOR : TIPO_CLIENTE;

    $usuario_id    = $_POST['usuario_id']; //Identificador (caso seja edição)

    $tipo_pessoa   = campoObrigatorio('tipo_pessoa', 'Tipo de pessoa');
    if (!in_array($tipo_pessoa, ['cpf', 'cnpj'])){
        echo "<script>
                alert('Tipo de pessoa inválido.');
                window.history.back();
            </script>";
        exit;
    }

    //Coleta dados do USUÁRIO
    if ($tipo_pessoa === 'cpf') { //CPF
        $nome = campoObrigatorio('nome', 'Nome');
        $cpf_cnpj = campoObrigatorio('cpf_cnpj', 'CPF');
        $data_nasc = campoObrigatorio('data_nascimento', 'Data de Nascimento');
        if ($data_nasc > date('Y-m-d')){
            echo "<script>
                alert('Data de nascimento inválida: não pode ser futura.');
                window.history.back();
            </script>";
            exit;
        }
    } else { //CNPJ
        $nome = campoObrigatorio('razao_social', 'Razão Social');
        $cpf_cnpj = campoObrigatorio('cpf_cnpj', 'CNPJ');
        $data_nasc = null;
    }
    $fk_tipo_usuario = campoObrigatorio('fk_tipo_usuario','Tipo de usuário');
    $email = campoObrigatorio('email', 'Email');
    $confirmEmail = campoObrigatorio('confirmEmail', 'Confirmação de Email');
    if ($email !== $confirmEmail) {
        echo "<script>
                alert('Os emails não coincidem.');
                window.history.back();
            </script>";
        exit;
    }

    //Verifica duplicidade de e-mail (ignorando o ID do usuário sendo alterado em caso de update)
    $sql_verifica_email = "SELECT COUNT(*) AS total FROM Usuario WHERE email = ? AND usuario_id != ?";
    $params_email = [$email, $usuario_id ?? 0];
    $stmt_verifica_email = sqlsrv_query($conn, $sql_verifica_email, $params_email);
    $row_email = sqlsrv_fetch_array($stmt_verifica_email, SQLSRV_FETCH_ASSOC);
    if ($row_email && $row_email['total'] > 0) {
        echo "<script>
                alert('E-mail já cadastrado. Por favor, utilize outro endereço.');
                history.back();
            </script>";
        exit;
    }

    $num_principal = campoObrigatorio('num_principal', 'Número Principal para Contato');
    $num_recado = !empty($_POST['num_recado']) ? $_POST['num_recado'] : null;
    $senha = $_POST['senha'] ?? null;
    $confirmSenha = $_POST['confirmSenha'] ?? null;
    //$fk_pergunta = $_POST['securityQuestion'] ?? null;
    //$resposta = $_POST['securityAnswer'] ?? null;
    $ativo = 1;

    //Verifica campos sensíveis só se foram preenchidos
    $senha_hash = null;
    //$resposta_hash = null;

    if (!empty($senha)) {
        if ($senha !== $confirmSenha){
            echo "<script>
                alert('As senhas não coincidem.');
                window.history.back();
            </script>";
            exit;
        }
        $regexSenha = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{9,}$/';
        if (!preg_match($regexSenha, $senha)){
            echo "<script>
                    alert('A senha deve ter no mínimo 9 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial.');
                    window.history.back();
                </script>";
            exit;
        }
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    }

    //Coleta dados de ENDEREÇO
    $cep = campoObrigatorio('cep', 'CEP');
    $logradouro = campoObrigatorio('logradouro', 'Logradouro');
    $numero = campoObrigatorio('numero', 'Número');
    $complemento = !empty($_POST['complemento']) ? $_POST['complemento'] : null;
    $bairro = campoObrigatorio('bairro', 'Bairro');
    $cidade = campoObrigatorio('cidade', 'Cidade');
    $estado = campoObrigatorio('estado', 'Estado');

    //Se for UPDATE, busca o fk_endereco atual
    if (!empty($usuario_id)) {
        $sql_busca_endereco = "SELECT fk_endereco FROM Usuario WHERE usuario_id = ?";
        $stmt_busca_endereco = sqlsrv_query($conn, $sql_busca_endereco, [$usuario_id]);
        $row_endereco = sqlsrv_fetch_array($stmt_busca_endereco, SQLSRV_FETCH_ASSOC);
        $fk_endereco = $row_endereco['fk_endereco'] ?? null;

        if ($fk_endereco) {
            //Atualiza o endereço existente
            $sql_update_endereco = "UPDATE Endereco SET
                cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
                WHERE endereco_id = ?";
            $params_update_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $fk_endereco];
            $stmt_update_endereco = sqlsrv_query($conn, $sql_update_endereco, $params_update_endereco);
            if (!$stmt_update_endereco) die("Erro ao atualizar endereço: " . print_r(sqlsrv_errors(), true));
        } else {
            die("Endereço associado ao usuário não encontrado.");
        }
    } else {
        //Insere novo registro de endereço no banco, se for CRIAÇÃO de conta
        $sql_endereco = "INSERT INTO Endereco (cep, logradouro, numero, complemento, bairro, cidade, estado)
                        OUTPUT INSERTED.endereco_id
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado];
        $stmt_endereco = sqlsrv_query($conn, $sql_endereco, $params_endereco);
        if (!$stmt_endereco || !($row = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC))) {
            die("Erro ao cadastrar endereço: " . print_r(sqlsrv_errors(), true));
        }
        $fk_endereco = $row['endereco_id'];
    }

    //INSERT ou UPDATE do USUÁRIO
    if (empty($usuario_id)) {
        $sql_usuario = "INSERT INTO Usuario (
            fk_tipo_usuario, nome, cpf_cnpj, data_nascimento, email,
            num_principal, num_recado, fk_endereco,
            senha, ativo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params_usuario = [
            $fk_tipo_usuario, $nome, $cpf_cnpj, $data_nasc, $email,
            $num_principal, $num_recado, $fk_endereco,
            $senha_hash, $ativo
        ];
    } else {
        //UPDATE
        $sql_usuario = "UPDATE Usuario SET
            fk_tipo_usuario = ?, nome = ?, cpf_cnpj = ?, data_nascimento = ?, email = ?,
            num_principal = ?, num_recado = ?, fk_endereco = ?, ativo = ?";
        
        $params_usuario = [
            $fk_tipo_usuario, $nome, $cpf_cnpj, $data_nasc, $email,
            $num_principal, $num_recado, $fk_endereco, $ativo
        ];

        if (!empty($senha_hash)) {
            $sql_usuario .= ", senha = ?";
            $params_usuario[] = $senha_hash;
        }

        $sql_usuario .= " WHERE usuario_id = ?";
        $params_usuario[] = $usuario_id;
    }

    //Executa a query
    $stmt_usuario = sqlsrv_prepare($conn, $sql_usuario, $params_usuario);
    if (sqlsrv_execute($stmt_usuario)) {
        sqlsrv_free_stmt($stmt_usuario);
        sqlsrv_close($conn);

        //Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=usuarios");
        exit(); //Para evitar execução de código após o redirecionamento
    } else {
        die("Erro ao salvar usuário: " . print_r(sqlsrv_errors(), true));
    }
}
?>
