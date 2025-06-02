<?php
//CÓDIGO PARA GUARDAR OS DADOS DE DADOS DE FEEDBACK NO BANCO

require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Captura os dados do formulário
    $fk_pedido = $_POST["fk_pedido"] ?? null; //Recebido via campo oculto no formulário
    $avaliacao = campoObrigatorio('avaliacao', 'Avaliação');
    $observacao = $_POST['observacao'] ?? null; //Pode ser nulo
    $ativo = 1; //Coluna "ativo" do banco deve ser sempre 1 por padrão
    
    //Define a data e hora atuais, no fuso horário adequado
    date_default_timezone_set('America/Sao_Paulo');
    $data_hora = date("Y-m-d H:i:s");

    //Validação para que a avaliação seja preenchida e seja um valor somente entre 1 e 5
    if (!$avaliacao || !in_array($avaliacao, ['1','2','3','4','5'])) {
        die("Erro: Avaliação inválida.");
    }

    if ($observacao !== null && strlen($observacao) > 100) {
        die("Erro: observação excede 100 caracteres.");
    }    

    //Descobre o usuário logado por meio do e-mail
    $email = $_SESSION['email'] ?? null;
    if (!$email) {
        die("Erro: Sessão inválida.");
    }
    
    //Query para inserir o feedback no banco de dados
    $sql_feedback = "INSERT INTO Feedback (data_hora, fk_pedido, avaliacao, observacao, ativo)
                 VALUES (CONVERT(datetime, ?, 120), ?, ?, ?, ?)";

    $params_feedback = [
        $data_hora,            // string no formato ISO (yyyy-mm-dd hh:mi:ss)
        (int)$fk_pedido,       // int
        (int)$avaliacao,       // int
        $observacao,           // string ou null
        $ativo                 // int (bit)
    ];

    $stmt_feedback = sqlsrv_query($conn, $sql_feedback, $params_feedback);

    if ($stmt_feedback === false) {
        echo "Erro ao registrar feedback: " . print_r(sqlsrv_errors(), true);
    }

    //Fecha a conexão
    sqlsrv_close($conn);

    echo "<script>alert('Feedback registrado com sucesso!'); window.location.href = '../Front/Pages/historico_pedidos.php';</script>";
    exit;
}
?>