<?php
require_once '../../Back/conexao_sqlserver.php'; //Inclui o arquivo de conexão com o banco de dados
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página

// Inicializa variáveis
$termoBusca = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$resultados = [];
$mensagem = '';

// Processa a busca se houver termo
// Processa a busca se houver termo
if (!empty($termoBusca)) {
    try {
        $query = "SELECT c.componente_id, c.nome, c.especificacao, c.quantidade, 
                         c.nivel_minimo, c.nivel_maximo, f.nome AS fornecedor
                  FROM Componente c
                  LEFT JOIN Fornecedor_Componente fc ON c.componente_id = fc.fk_componente
                  LEFT JOIN Fornecedor f ON fc.fk_fornecedor = f.fornecedor_id
                  WHERE c.nome LIKE ? AND c.ativo = 1
                  ORDER BY c.nome";

        $params = ['%' . $termoBusca . '%'];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            throw new Exception("Erro ao buscar componentes: " . print_r(sqlsrv_errors(), true));
        }

        $resultados = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $resultados[] = $row;
        }

        if (empty($resultados)) {
            $mensagem = "Nenhum componente encontrado com o termo '{$termoBusca}'";
        }
    } catch (Exception $e) {
        $mensagem = "Erro na busca: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localizar Componentes</title>
    <link rel="stylesheet" type="text/css" href="../CSS/cadastro-componente.css">
</head>
<body>
    <?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->
    <div class="container">
        <h1>LOCALIZAR COMPONENTES</h1>
        
        <div class="form-container">
            <form method="get" action="">
                <div class="form-group busca-group">
                    <label for="nome-busca">Nome do Componente:</label>
                    <div class="busca-input-group">
                        <input type="text" id="nome-busca" name="nome" 
                               value="<?= htmlspecialchars($termoBusca) ?>" 
                               placeholder="Digite o nome do componente" required>
                        <button type="submit" class="btn-primary">BUSCAR</button>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem aviso"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($resultados)): ?>
                <div class="resultados-container">
                    <table class="resultado-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Especificação</th>
                                <th>Quantidade</th>
                                <th>Níveis (Min-Max)</th>
                                <th>Fornecedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $componente): ?>
                            <tr>
                                <td><?= htmlspecialchars($componente['componente_id']) ?></td>
                                <td><?= htmlspecialchars($componente['nome']) ?></td>
                                <td><?= htmlspecialchars($componente['especificacao']) ?></td>
                                <td><?= htmlspecialchars($componente['quantidade']) ?></td>
                                <td><?= htmlspecialchars($componente['nivel_minimo']) ?>-<?= htmlspecialchars($componente['nivel_maximo']) ?></td>
                                <td><?= htmlspecialchars($componente['fornecedor'] ?? 'N/D') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="button-group">
                <a href="cadastro-componente.php" class="btn-secondary">VOLTAR</a>
            </div>
        </div>
    </div>
</body>
</html>