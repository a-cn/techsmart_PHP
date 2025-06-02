<?php
require_once '../../Back/conexao_sqlserver.php'; //Chama a coonexão com o banco de dados
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página

//Consulta SQL para listar as movimentações
$sql_movimentacao = "SELECT 
            M.movimentacao_id,
            M.fk_pedido AS pedido_id,
            M.fk_produtofinal AS produtofinal_id,
            PF.nome AS nome_produto,
            M.quantidade,
            M.data_hora,
            M.tipo_movimentacao
        FROM Movimentacao M
        JOIN ProdutoFinal PF ON PF.produtofinal_id = M.fk_produtofinal
        ORDER BY M.data_hora DESC";

$stmt_movimentacao = sqlsrv_query($conn, $sql_movimentacao);

if ($stmt_movimentacao !== false) {
  //Atribui na variável de array $dados o conteúdo selecionado da tabela
  while ($row = sqlsrv_fetch_array($stmt_movimentacao, SQLSRV_FETCH_ASSOC)) {
      //Formata a data e hora em "dd/mm/aaaa, hh:mm:ss" para melhor visualização na tela
      if (!empty($row['data_hora'])) {
          $row['data_hora'] = $row['data_hora']->format('d/m/Y, H:i:s');
      }
      $dados[] = $row;
  }
}
?> 

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Movimentações de Estoque</title>
  <link rel="stylesheet" type="text/css" href="../CSS/consulta-movimentacao.css">
</head>

<body>
  <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->

  <div class="container">
    <h1 class="titulo-pagina">Movimentações de Estoque</h1>
      <table class="tabela-movimentacao">
          <thead>
              <tr>
                  <th>ID</th>
                  <th>PEDIDO ID</th>
                  <th>PRODUTO ID</th>
                  <th>NOME DO PRODUTO</th>
                  <th>QTD</th>
                  <th>DATA/HORA</th>
                  <th>TIPO</th>
              </tr>
          </thead>
          <tbody>
            <?php
              if (!empty($dados)) {
                foreach ($dados as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['movimentacao_id']) ?></td>
                        <td><?= htmlspecialchars($row['pedido_id']) ?></td>
                        <td><?= htmlspecialchars($row['produtofinal_id']) ?></td>
                        <td><?= htmlspecialchars($row['nome_produto']) ?></td>
                        <td><?= htmlspecialchars($row['quantidade']) ?></td>
                        <td><?= htmlspecialchars($row['data_hora']) ?></td>
                        <td><?= htmlspecialchars($row['tipo_movimentacao']) ?></td>
                    </tr>
                <?php endforeach;
              } else {
                echo "<tr><td colspan='7'>Nenhum dado encontrado</td></tr>";
              }
            ?>
          </tbody>
      </table>
  </div>
</body>
</html>