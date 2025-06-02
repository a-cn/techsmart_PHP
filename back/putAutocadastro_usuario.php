<?php
//CÓDIGO PARA GUARDAR OS DADOS DE CADASTRO DE USUÁRIO NO BANCO, COM CRIPTOGRAFIA SHA256

require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios
session_start();

define('TIPO_CLIENTE', 3); //O número é referente ao id do Cliente na tabela Tipo_Usuario
define('TIPO_COLABORADOR', 2); //O número é referente ao id do Colaborador na tabela Tipo_Usuario

//Verifica o tipo de pessoa que está sendo cadastrada (CPF ou CNPJ)
// $tipo_pessoa = $_POST['tipo_pessoa'] ?? null;
$tipo_pessoa = ($_POST['tipo_pessoa']==null) ? null : $_POST['tipo_pessoa'];
if (!in_array($tipo_pessoa, ['cpf', 'cnpj'])) {
    die("Tipo de pessoa inválido.");
}
// var_dump($_POST); exit;

//Determina o tipo de usuário que está logado (se houver)
// $tipo_usuario_logado = ($_SESSION['tipo_usuario']) ?? null;
$tipo_usuario_logado = ($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$fk_tipo_usuario = ($tipo_usuario_logado === 'administrador') ? TIPO_COLABORADOR : TIPO_CLIENTE;

//Coleta os dados de ENDEREÇO
$cep         = campoObrigatorio('cep', 'CEP');
$logradouro  = campoObrigatorio('logradouro', 'Logradouro');
$numero      = campoObrigatorio('numero', 'Número');
// $complemento = $_POST['complemento'] ?? null; //Pode ser nulo
$complemento = $_POST['complemento'] ?  $_POST['complemento'] : null; //Pode ser nulo
$bairro      = campoObrigatorio('bairro', 'Bairro');
$cidade      = campoObrigatorio('cidade', 'Cidade');
$estado      = campoObrigatorio('estado', 'Estado');

//Insere o endereço na tabela Endereco
$sql_endereco = "INSERT INTO Endereco (cep, logradouro, numero, complemento, bairro, cidade, estado)
                 OUTPUT INSERTED.endereco_id
                 VALUES (?, ?, ?, ?, ?, ?, ?)";

$params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado];
$stmt_endereco = sqlsrv_query($conn, $sql_endereco, $params_endereco);

//Verifica se a query/busca no SQL Server consegue encontrar uma linha na tabela Endereco. Se não, mostra um erro
if (!$stmt_endereco || !($row = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC))) {
    die("Erro ao cadastrar endereço ou recuperar ID: " . print_r(sqlsrv_errors(), true));
}
//Atribui à variável $fk_endereco o valor da coluna endereco_id da linha encontrada
$fk_endereco = $row['endereco_id'];

//Coleta dados do USUÁRIO
if ($tipo_pessoa === 'cpf') { //CPF
    $nome = campoObrigatorio('nome', 'Nome');
    $cpf_cnpj = campoObrigatorio('cpf', 'CPF');
    $data_nasc = campoObrigatorio('data_nascimento', 'Data de Nascimento');

    if ($data_nasc > date('Y-m-d')) {
        die("Data de nascimento inválida: não pode ser futura.");
    }

} else { //CNPJ
    $nome = campoObrigatorio('razao_social', 'Razão Social');
    $cpf_cnpj = campoObrigatorio('cnpj', 'CNPJ');
    $data_nasc = null;
}

$email         = campoObrigatorio('email', 'Email');
$confirmEmail  = campoObrigatorio('confirmEmail', 'Confirmação de Email');
$num_celular   = campoObrigatorio('num_celular', 'Número de Celular');
$num_recado    = $_POST['num_recado'] || null; //Pode ser nulo
$senha         = campoObrigatorio('senha', 'Senha');
$confirmSenha  = campoObrigatorio('confirmSenha', 'Confirmação de Senha');
$fk_pergunta   = campoObrigatorio('securityQuestion', 'Pergunta de Segurança');
$resposta      = campoObrigatorio('securityAnswer', 'Resposta de Segurança');
$ativo = 1;

//Verificação extra para e-mail e senha
if ($email !== $confirmEmail) {
    die("Os emails não coincidem.");
}
if ($senha !== $confirmSenha) {
    die("As senhas não coincidem.");
}

//Regras de senha (igual ao JS)
$regexSenha = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{9,}$/';
if (!preg_match($regexSenha, $senha)) {
    die("A senha deve ter no mínimo 9 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial.");
}

//Criptografa a senha e a resposta de segurança do usuário
$senha_hash = password_hash($senha, PASSWORD_DEFAULT); //Gera um hash seguro e recomendado para senhas. Usa o algoritmo bcrypt (ou argon2 em versões mais recentes do PHP).
$resposta_hash = hash('sha256', $resposta); //Gera um hash fixo usando o algoritmo SHA-256.

//Query para inserir o usuário no banco de dados
$sql_usuario = "INSERT INTO Usuario (
    fk_tipo_usuario, nome, cpf_cnpj, data_nascimento, email,
    num_principal, num_recado, fk_endereco,
    senha, fk_pergunta_seguranca, resposta_seguranca, ativo
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$params_usuario = [
    $fk_tipo_usuario, $nome, $cpf_cnpj, $data_nasc, $email,
    $num_celular, $num_recado, $fk_endereco,
    $senha_hash, $fk_pergunta, $resposta_hash, $ativo
];

$stmt_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);

//Se o tipo cadastrado for Cliente (usuário externo) → volta para a tela de login e recebe uma mensagem de sucesso
//Se for Colaborador → permanece na tela de cadastro e recebe uma mensagem simples (Administrador poderá continuar cadastrando outros usuários)
if ($stmt_usuario) {
    if ($fk_tipo_usuario === TIPO_CLIENTE) {
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = '../index.html';</script>";
        exit;
    } else {
        echo "<script>alert('Colaborador cadastrado com sucesso!'); window.location.href = '../front/cadastro-usuario.php';</script>";
        exit;
    }
} else {
    echo "Erro ao cadastrar usuário: " . print_r(sqlsrv_errors(), true);
}
?>