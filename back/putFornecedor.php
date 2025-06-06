<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
    require_once 'conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
    require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios
    require_once 'validacoes.php'; //Chama a função para validar campos obrigatórios
    
    /*/ IMPORTANTE: Lembrar de gravar o registro das FKs antes da PK correspondente /*/

    /*/ Gravação de Endereço /*/
    $end  = buscarEndereco($_POST['cep']);
    $id_endereco = $_POST['endereco_id']; // campo oculto do form contendo o id do endereço
    $logradouro  = ($end['logradouro'] !== '') ? $end['logradouro'] : die("endereço Invalido");
    $cep         = $logradouro ? $_POST['cep'] : die("CEP invalido.");
    $numero      = campoObrigatorio('numero', 'Número');
    $complemento = $_POST['complemento'] ?? ''; // Pode ser vazio ou nulo
    $bairro      = ($end['bairro'] !== '') ? $end['bairro'] : die("bairro Invalido");
    $cidade      = ($end['localidade'] !== '') ? $end['localidade'] : die("cidade Invalido");
   // var_dump($end); exit;
    $estado      = ($end['estado'] !== '') ? $end['estado'] : die("estado Invalido");

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cep = $_POST['cep'] ?? '';
    
    if (!validarCEP($cep)) {
        $erro = "CEP inválido! Deve conter 8 dígitos.";
    } else {
        $endereco = buscarEndereco($cep);
        
        if (!$endereco) {
            $erro = "CEP não encontrado!";
        }
    }
}

    // Se $id_endereco for vazio inclui o registro, senão vai atualizar os dados do $id_endereco informado
    if (empty($id_endereco)){
        $sql_endereco = "INSERT INTO Endereco (cep, logradouro, numero, complemento, bairro, cidade, estado)
                              OUTPUT INSERTED.endereco_id
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado];
        $stmt_endereco = sqlsrv_query($conn, $sql_endereco, $params_endereco);

        // Por causa do OUTPUT deste insert retornando o ID do endereço criado, devemos usar o sqlsrv_fetch_array
        if (!$stmt_endereco || !($row = sqlsrv_fetch_array($stmt_endereco, SQLSRV_FETCH_ASSOC))) {
            die("Erro ao cadastrar endereço: " . print_r(sqlsrv_errors(), true));
        }
        // Atribui à variável $id_endereco o valor inserido
        $id_endereco = $row['endereco_id'];
    }    
    else {
        $sql_endereco = "UPDATE Endereco
                            SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
                          WHERE endereco_id = ?";
        $params_endereco = [$cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $id_endereco];
        //var_dump($_POST, $sql_endereco, $params_endereco); exit;
        $stmt_endereco = sqlsrv_prepare($conn, $sql_endereco, $params_endereco);
        if (!$stmt_endereco || !sqlsrv_execute($stmt_endereco)) {
            die("Erro ao gravar endereço: " . print_r(sqlsrv_errors(), true));
        }
    }
    sqlsrv_free_stmt($stmt_endereco);    

    /*/ Gravação de Fornecedor /*/
    $ativo=$_POST["ativo"] == 'on' ? '1' :  '0';
    $id=$_POST['fornecedor_id'];
    $nome=campoObrigatorio('nome', 'Nome do Fornecedor');
    $cpf_cnpj=validarCNPJ($_POST['cpf_cnpj'])?$_POST['cpf_cnpj']: die("CNPJ invalido.");
    $num_principal=campoObrigatorio('num_principal', 'Telefone Principal');
    $num_secundario=$_POST['num_secundario'];
    $email=validarEmail($_POST['email'])?$_POST['email']:die("Email invalido.");
    //$id_endereco=campoObrigatorio('id_endereco', 'Endereço'); 
    $situacao=0;//$_POST["situacao"]; //campoObrigatorio('situacao', 'Situacao');
    
    // Se $id for vazio inclui o registro, senão vai atualizar os dados do $id informado
    if (empty($id)) {
        $sql="INSERT INTO Fornecedor (nome, cpf_cnpj, num_principal, num_secundario, email, fk_endereco, situacao, ativo)
                   VALUES (?,?,?,?,?,?,?,?)";
        $params = [$nome,$cpf_cnpj,$num_principal,$num_secundario,$email,$id_endereco,$situacao,$ativo];
    } else {
        $sql= "UPDATE Fornecedor
                  SET nome = ?, cpf_cnpj = ?, num_principal = ?, num_secundario = ?, email = ?, fk_endereco = ?, situacao = ?, ativo = ?
                WHERE fornecedor_id = ?";
        $params = [$nome,$cpf_cnpj,$num_principal,$num_secundario,$email,$id_endereco,$situacao,$ativo,$id];
    }
    //var_dump($_POST,$id, $sql); exit(); // Apenas para verificar o que será gravado (Bom manter)
    
    $stmt = sqlsrv_prepare($conn, $sql,$params);
    if (sqlsrv_execute($stmt)) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Após gravados devemos carregar a página inicial com o parâmetro da página que chamou a gravação
        header("Location: ../front/index.php?pg=fornecedores");
        exit(); // Para evitar execução de código após o redirecionamento
    } else {
        die("Erro ao gravar fornecedor: " . print_r(sqlsrv_errors(), true));
        //die(var_dump(sqlsrv_errors()));  // Bom para debug
    }
}
