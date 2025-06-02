<?php
header('Content-Type: application/json');
require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária

//Valida campo obrigatório utilizando método com json
function campoObrigatorio($campo, $nomeCampo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
        $response['success'] = false;
        $response['error'] = "O campo '$nomeCampo' é obrigatório.";
        echo json_encode($response);
        exit;
    }
    return trim($_POST[$campo]);
}

$response = ['success' => false];

if (!$conn) {
    $response['error'] = 'Erro na conexão com o banco.';
    $response['detalhes'] = sqlsrv_errors();
    echo json_encode($response);
    exit;
}

//Coleta dados de ENDEREÇO
$cep         = campoObrigatorio('cep', 'CEP');
$logradouro  = campoObrigatorio('logradouro', 'Logradouro');
$numero      = campoObrigatorio('numero', 'Número');
$complemento = $_POST['complemento'] ?? null; //Pode ser nulo
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
    $response['error'] = "Erro ao cadastrar endereço ou recuperar ID: " . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}
//Atribui à variável $fk_endereco o valor da coluna endereco_id da linha encontrada
$fk_endereco = $row['endereco_id'];

//Coleta dados de FORNECEDOR
$camposObrigatorios = ['cpf_cnpj', 'nome', 'email', 'num_principal', 'situacao'];
$valores = [];
foreach ($camposObrigatorios as $campo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
        $response['error'] = "Campo obrigatório '$campo' ausente.";
        echo json_encode($response);
        exit;
    }
    $valores[$campo] = $_POST[$campo];
}
$valores['num_secundario'] = $_POST['num_secundario'] ?? null; //Pode ser nulo
$ativo = 1;

//Limpa o CPF/CNPJ (remove pontos, barras e traços)
$valores['cpf_cnpj'] = preg_replace('/\D/', '', $valores['cpf_cnpj']);

//Query para inserir os dados de fornecedor no banco de dados
$sql_fornecedor = "INSERT INTO Fornecedor (
    nome, cpf_cnpj, num_principal, num_secundario, email, fk_endereco, situacao, ativo
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$params_fornecedor = [
    $valores['nome'],
    $valores['cpf_cnpj'],
    $valores['num_principal'],
    $valores['num_secundario'],
    $valores['email'],
    $fk_endereco,
    $valores['situacao'],
    $ativo
];

$stmt_fornecedor = sqlsrv_query($conn, $sql_fornecedor, $params_fornecedor);

//Mostra um erro se houver falha ao registrar fornecedor
if (!$stmt_fornecedor) {
    $response['error'] = 'Erro ao executar SQL.';
    $response['detalhes'] = sqlsrv_errors();
} else {
    $response['success'] = true;
}

echo json_encode($response);
?>