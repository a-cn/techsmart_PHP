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
    
    $usuario_id    = $_POST['usuario_id'];
    $tipo_pessoa   = campoObrigatorio('tipo_pessoa', 'Tipo de pessoa');

    //Verifica se data de nascimento informada é futura.
    $data_nasc     = ($tipo_pessoa === 'cpf') ? campoObrigatorio('data_nascimento', 'Data de Nascimento') : null;
    if ($data_nasc > date('Y-m-d')) {
        die("Data de nascimento inválida: não pode ser futura.");
    }

    //Coleta os dados de ENDEREÇO
    //Atribui à variável $fk_endereco o valor da coluna endereco_id da linha encontrada

    $id_endereco = campoObrigatorio('endereco_id', 'Id do endereço'); // campo oculto do form contendo o id do endereço
    $cep         = campoObrigatorio('cep', 'CEP');
    $logradouro  = campoObrigatorio('logradouro', 'Logradouro');
    $numero      = campoObrigatorio('numero', 'Número');
    $complemento = $_POST['complemento'] ?? ''; // Pode ser vazio ou nulo
    $bairro      = campoObrigatorio('bairro', 'Bairro');
    $cidade      = campoObrigatorio('cidade', 'Cidade');
    $estado      = campoObrigatorio('estado', 'Estado');
    
    // Altera os dados do endereço informado
    $sql_endereco = "UPDATE [dbo].[Endereco]
                        SET [cep] = ?,
                            [logradouro] = ?,
                            [numero] = ?,
                            [complemento] = ?,
                            [bairro] = ?,
                            [cidade] = ?,
                            [estado] = ?
                    WHERE endereco_id = ?";

    $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $id_endereco];
    //var_dump($_POST, $sql_endereco, $params_endereco); exit;

    $stmt_endereco = sqlsrv_prepare($conn, $sql_endereco, $params_endereco);

    if (!$stmt_endereco || !sqlsrv_execute($stmt_endereco)) {
        die("Erro ao atualizar endereço: " . print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($stmt_endereco);

    //Coleta dados do USUÁRIO
    $fk_tipo_usuario = campoObrigatorio('fk_tipo_usuario','Tipo de usuário');
    $nome            = campoObrigatorio('nome', 'Nome / Razão Social');
    $cpf_cnpj        = campoObrigatorio('cpf_cnpj', 'CPF / CNPJ');
    $email           = campoObrigatorio('email', 'Email');
    //$confirmEmail  = campoObrigatorio('confirmEmail', 'Confirmação de Email');
    $num_celular     = campoObrigatorio('num_principal', 'Número de Celular');
    $num_recado      = $_POST['num_recado'] ?? ''; //Pode ser nulo/vazio
    //$senha         = campoObrigatorio('senha', 'Senha');
    //$confirmSenha  = campoObrigatorio('confirmSenha', 'Confirmação de Senha');
    //$fk_pergunta   = campoObrigatorio('securityQuestion', 'Pergunta de Segurança');
    //$resposta      = campoObrigatorio('securityAnswer', 'Resposta de Segurança');
    $ativo = 1;
    
    $sql= "  UPDATE [dbo].[Usuario]
                SET [fk_tipo_usuario] = ?
                    ,[nome] = ?
                    ,[cpf_cnpj] = ?
                    ,[data_nascimento] = ?
                    ,[email] = ?
                    ,[num_principal] = ?
                    ,[num_recado] = ?
                    ,[fk_endereco] = ?
                    ,[ativo] = ?
              WHERE usuario_id = ?";

    $params_usuario = [
        $fk_tipo_usuario, $nome, $cpf_cnpj, $data_nasc, $email,
        $num_celular, $num_recado, $id_endereco,
        $ativo, $usuario_id 
    ]; 
    //var_dump($_POST, $sql, $params_usuario); exit; // Apenas para verificar o que será gravado (Bom manter)
    
    $stmt = sqlsrv_prepare($conn, $sql, $params_usuario);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=usuarios");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        //die(print_r(sqlsrv_errors(), true)); 
        die(var_dump(sqlsrv_errors(),$sql));  // Bom para debug, comentar essa linha após fase de teste
    }
}
