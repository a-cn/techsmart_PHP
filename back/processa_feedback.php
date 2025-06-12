<?php
//CÓDIGO PARA GUARDAR OS DADOS DE DADOS DE FEEDBACK NO BANCO

require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco
require_once 'verifica_sessao.php'; //Colocado em todos os arquivos de processamento e recebimento de dados, exceto arquivos públicos ou em que a sessão não é necessária
require_once 'valida_campo_obrigatorio_back.php'; //Chama a função para validar campos obrigatórios

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
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
            throw new Exception("A avaliação deve ser um valor entre 1 e 5");
        }

        if ($observacao !== null && strlen($observacao) > 100) {
            throw new Exception("A observação não pode exceder 100 caracteres");
        }    

        //Verifica se o usuário está logado
        if (!isset($_SESSION['email'])) {
            throw new Exception("Sessão inválida");
        }

        //Verifica se o pedido existe, pertence ao usuário e está com situação "Entregue"
        $sql_verificacao = "SELECT p.pedido_id, p.situacao 
                           FROM Pedido p 
                           WHERE p.pedido_id = ? 
                           AND p.fk_usuario = ? 
                           AND p.ativo = 1";
        $stmt_verificacao = sqlsrv_query($conn, $sql_verificacao, [$fk_pedido, $_SESSION['usuario_id']]);
        
        if (!$stmt_verificacao) {
            throw new Exception("Erro ao verificar o pedido");
        }

        $pedido = sqlsrv_fetch_array($stmt_verificacao, SQLSRV_FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou sem permissão para enviar feedback");
        }

        if ($pedido['situacao'] !== 'Entregue') {
            throw new Exception("Só é possível enviar feedback para pedidos que já foram entregues.");
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
            throw new Exception("Erro ao registrar feedback no banco de dados");
        }

        //Fecha a conexão
        sqlsrv_close($conn);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Feedback registrado com sucesso!'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'erro' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Método não permitido'
    ]);
}
?>