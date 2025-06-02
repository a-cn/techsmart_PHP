<?php
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../../Back/funcoes_sessao.php'; //Chama as funções salvas nesse arquivo para serem utilizadas e inicia sessão se não estiver ativa
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/consulta-feedback.css">
    <title>Feedbacks Registrados</title>
</head>
<body>
    <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->
    <h1>Feedbacks Registrados</h1>
    <table>
        <thead>
            <tr>
                <th>Feedback ID</th>
                <th>Pedido ID</th>
                <th>Avaliação</th>
                <th>Observação</th>
                <th>Data/Hora</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $dados = include '../../Back/consulta_feedback.php'; //Chama o código de consulta na tabela do banco de dados

            if (!empty($dados)) {
                foreach ($dados as $row) {
                    if ($row['ativo'] == 1) { //Exibir apenas registros ativos
                        $status = "Ativo";
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['feedback_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fk_pedido']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['avaliacao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['observacao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['data_hora']) . "</td>";
                        echo "<td class='action-buttons'>"; //Classe para centralizar os botões
                        //Botão CRUD - Arquivar (apenas desativa o registro)
                        if (esconderSeCliente()):
                            echo "<form action='../../Back/desativar_feedback.php' method='POST'>";
                            echo "<input type='hidden' name='feedback_id' value='" . htmlspecialchars($row['feedback_id']) . "'>";
                            echo "<button type='submit'>Arquivar</button>";
                            echo "</form>";
                        endif;
                        //Botão CRUD - Alterar (só para Cliente)
                        if (mostrarSeCliente()):
                            echo "<form action='../Pages/alterar-feedback.php' method='POST'>";
                            echo "<input type='hidden' name='feedback_id' value='" . htmlspecialchars($row['feedback_id']) . "'>";
                            echo "<button type='submit' class='btn-edit'>Alterar</button>";
                            echo "</form>";
                        endif;
                        // Botão CRUD - Deletar (só para Cliente)
                        if (mostrarSeCliente()):
                            echo "<form action='../../Back/deletar_feedback.php' method='POST'>";
                            echo "<input type='hidden' name='feedback_id' value='" . htmlspecialchars($row['feedback_id']) . "'>";
                            echo "<button type='submit' class='btn-delete'>Excluir</button>";
                            echo "</form>";
                        endif;
                        echo "</td>";
                        echo "</tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='5'>Nenhum dado encontrado</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
