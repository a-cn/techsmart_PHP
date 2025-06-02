<?php
header('Content-Type: application/json');

require_once 'conexao_sqlserver.php';

try {
    $search = $_GET['search'] ?? '';
    
    // Consulta usando a sintaxe de JOIN mais compatível
    $sql = "SELECT 
                u.usuario_id,
                u.nome,
                u.cpf_cnpj,
                u.email,
                u.num_principal,
                u.num_recado,
                e.cep,
                e.logradouro,
                e.complemento,
                e.numero,
                e.bairro,
                e.cidade,
                e.estado,
                tu.descricao as permissao_user
            FROM Usuario u
            JOIN Endereco e ON u.fk_endereco = e.endereco_id
            JOIN Tipo_Usuario tu ON u.fk_tipo_usuario = tu.tipo_usuario_id
            WHERE u.ativo = 1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (u.nome LIKE ? OR u.cpf_cnpj LIKE ? OR u.email LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        throw new Exception('Erro na consulta: ' . print_r(sqlsrv_errors(), true));
    }
    
    $usuarios = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $usuarios[] = [
            'usuario_id' => $row['usuario_id'],
            'nome' => $row['nome'],
            'cpf_cnpj' => $row['cpf_cnpj'],
            'email' => $row['email'],
            'num_principal' => $row['num_principal'],
            'num_recado' => $row['num_recado'],
            'cep' => $row['cep'],
            'logradouro' => $row['logradouro'],
            'complemento' => $row['complemento'],
            'numero_endereco' => $row['numero'], // Mapeado para o nome esperado no front
            'permissao_user' => $row['permissao_user']
        ];
    }
    
    echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}