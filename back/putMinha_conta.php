<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios
    require_once 'validacoes.php'; //Chama a função para validar campos obrigatórios

    $usuario_id = $_SESSION['usuario_id']; //Somente o próprio usuário poderá alterar dados da conta

    $tipo_pessoa = campoObrigatorio('tipo_pessoa', 'Tipo de pessoa');
    if (!in_array($tipo_pessoa, ['cpf', 'cnpj'])) {
        echo "<script>alert('Tipo de pessoa inválido.'); window.history.back();</script>";
        exit;
    }

    //Coleta dados do USUÁRIO
    if ($tipo_pessoa === 'cpf') { //CPF
        $nome = campoObrigatorio('nome', 'Nome');
        $cpf_cnpj = campoObrigatorio('cpf_cnpj', 'CPF');
        $data_nasc = campoObrigatorio('data_nascimento', 'Data de Nascimento');
        if ($data_nasc > date('Y-m-d')) {
            echo "<script>alert('Data de nascimento inválida.'); window.history.back();</script>";
            exit;
        }
    } else { //CNPJ
        $nome = campoObrigatorio('razao_social', 'Razão Social');
        $cpf_cnpj = campoObrigatorio('cpf_cnpj', 'CNPJ');
        $data_nasc = null;
    }

    $email = campoObrigatorio('email', 'Email');
    $confirmEmail = campoObrigatorio('confirmEmail', 'Confirmação de Email');
    if ($email !== $confirmEmail) {
        echo "<script>alert('Os emails não coincidem.'); window.history.back();</script>";
        exit;
    }

    // Verifica duplicidade de e-mail (ignorando o ID do usuário logado)
    $sql_verifica_email = "SELECT COUNT(*) AS total FROM Usuario WHERE email = ? AND usuario_id != ?";
    $params_email = [$email, $usuario_id];
    $stmt_verifica_email = sqlsrv_query($conn, $sql_verifica_email, $params_email);
    $row_email = sqlsrv_fetch_array($stmt_verifica_email, SQLSRV_FETCH_ASSOC);
    if ($row_email && $row_email['total'] > 0) {
        echo "<script>alert('E-mail já cadastrado. Por favor, utilize outro endereço.'); window.history.back();</script>";
        exit;
    }

    $num_principal = campoObrigatorio('num_principal', 'Número Principal para Contato');
    $num_recado = $_POST['num_recado'] ?? null; //Pode ser vazio ou nulo
    $senha = $_POST['senha'] ?? null; //Usuário pode escolher não alterar
    $confirmSenha = $_POST['confirmSenha'] ?? null; //Usuário pode escolher não alterar
    $fk_pergunta = $_POST['securityQuestion'] ?? null; //Usuário pode escolher não alterar
    $resposta = $_POST['securityAnswer'] ?? null; //Usuário pode escolher não alterar
    $ativo = 1;

    $senha_hash = null;
    $resposta_hash = null;

    if (!empty($senha)) {
        if ($senha !== $confirmSenha) {
            echo "<script>alert('As senhas não coincidem.'); window.history.back();</script>";
            exit;
        }
        $regexSenha = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{9,}$/';
        if (!preg_match($regexSenha, $senha)) {
            echo "<script>alert('A senha deve ter no mínimo 9 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial.'); window.history.back();</script>";
            exit;
        }
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT); //Gera hash de senha
    }

    if (!empty($resposta)) {
        $resposta_hash = hash('sha256', $resposta); //Gera hash para a resposta de segurança
    }

    //Coleta dados de ENDEREÇO
    $cep = campoObrigatorio('cep', 'CEP');
    $logradouro = campoObrigatorio('logradouro', 'Logradouro');
    $numero = campoObrigatorio('numero', 'Número');
    $complemento = $_POST['complemento'] ?? null; //Pode ser vazio ou nulo
    $bairro = campoObrigatorio('bairro', 'Bairro');
    $cidade = campoObrigatorio('cidade', 'Cidade');
    $estado = campoObrigatorio('estado', 'Estado');

    //Busca o endereço associado ao usuário
    $sql_endereco = "SELECT fk_endereco FROM Usuario WHERE usuario_id = ?";
    $stmt_endereco = sqlsrv_query($conn, $sql_endereco, [$usuario_id]);
    $row_endereco = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC);
    $fk_endereco = $row_endereco['fk_endereco'] ?? null;

    //Atualiza dados do ENDEREÇO associado ao usuário
    if ($fk_endereco) {
        $sql_update_endereco = "UPDATE Endereco SET
            cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
            WHERE endereco_id = ?";
        $params_update_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $fk_endereco];
        $stmt_update_endereco = sqlsrv_query($conn, $sql_update_endereco, $params_update_endereco);
        if (!$stmt_update_endereco) die("Erro ao atualizar endereço: " . print_r(sqlsrv_errors(), true));
    } else {
        die("Endereço associado ao usuário não encontrado.");
    }

    //Atualiza dados do USUÁRIO
    $sql_usuario = "UPDATE Usuario SET
        nome = ?, cpf_cnpj = ?, data_nascimento = ?, email = ?,
        num_principal = ?, num_recado = ?, fk_endereco = ?, ativo = ?";

    $params_usuario = [
        $nome, $cpf_cnpj, $data_nasc, $email,
        $num_principal, $num_recado, $fk_endereco, $ativo
    ];

    if (!empty($senha_hash)) {
        $sql_usuario .= ", senha = ?";
        $params_usuario[] = $senha_hash;
    }

    if (!empty($fk_pergunta)) {
        $sql_usuario .= ", fk_pergunta_seguranca = ?";
        $params_usuario[] = $fk_pergunta;
    }

    if (!empty($resposta_hash)) {
        $sql_usuario .= ", resposta_seguranca = ?";
        $params_usuario[] = $resposta_hash;
    }

    $sql_usuario .= " WHERE usuario_id = ?";
    $params_usuario[] = $usuario_id;

    $stmt_usuario = sqlsrv_prepare($conn, $sql_usuario, $params_usuario);
    if (sqlsrv_execute($stmt_usuario)) {
        sqlsrv_free_stmt($stmt_usuario);
        sqlsrv_close($conn);
        header("Location: ../front/index.php?pg=minha-conta&msg=sucesso"); //Retorna para a página com os dados da conta
        exit();
    } else {
        die("Erro ao atualizar dados: " . print_r(sqlsrv_errors(), true));
    }
}