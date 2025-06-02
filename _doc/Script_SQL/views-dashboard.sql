-- VIEWS (lógicas de consulta reutilizáveis) PARA RELATÓRIOS DO SISTEMA
-- Autora: Amanda Caetano Nasser
-- Última alteração em: 29/05/2025

USE TechSmartDB;
GO

-- ================================================================================
-- 1) Registrar o consumo de componentes por item do pedido
-- ================================================================================
CREATE VIEW vw_Consumo_Componentes_Por_Pedido AS
SELECT 
    pp.fk_pedido AS PedidoID,
    pf.nome AS Produto,
    c.nome AS Componente,
    SUM(pp.quantidade_item) AS QtdeProdutosPedido,
    COUNT(*) AS QtdeEtapasComponente, -- quantas vezes esse componente aparece na estrutura
    SUM(pp.quantidade_item * 1) AS TotalConsumido -- total consumido = qtd * vezes na estrutura
FROM Pedido_ProdutoFinal pp
JOIN ProdutoFinal pf ON pf.produtofinal_id = pp.fk_produtofinal
JOIN Producao p ON p.producao_id = pf.fk_producao
JOIN Etapa_Producao ep ON ep.fk_producao = p.producao_id
JOIN Componente c ON c.componente_id = ep.fk_componente
WHERE pf.ativo = 1
  AND ep.ativo = 1
  AND c.ativo = 1
  AND pp.quantidade_item > 0
GROUP BY pp.fk_pedido, pf.nome, c.nome;
GO

-- ================================================================================
-- 2) Movimentação de entrada e saída de produtos com indicador de estoque mínimo e máximo
-- ================================================================================
CREATE VIEW vw_Estoque_Produtos_Alerta AS
SELECT 
    pf.nome AS Produto,
    pf.quantidade,
    pf.nivel_minimo,
    pf.nivel_maximo,
    CASE 
        WHEN pf.quantidade < pf.nivel_minimo THEN 'Estoque Baixo'
        WHEN pf.quantidade > pf.nivel_maximo THEN 'Estoque Alto'
        ELSE 'Estoque Normal'
    END AS Alerta
FROM ProdutoFinal pf
WHERE pf.ativo = 1;
GO

-- ================================================================================
-- 3) Estoque mínimo e máximo de componentes com indicador
-- ================================================================================
CREATE VIEW vw_Estoque_Componentes_Alerta AS
SELECT 
    c.nome AS Componente,
    c.quantidade,
    c.nivel_minimo,
    c.nivel_maximo,
    CASE 
        WHEN c.quantidade < c.nivel_minimo THEN 'Estoque Baixo'
        WHEN c.quantidade > c.nivel_maximo THEN 'Estoque Alto'
        ELSE 'Estoque Normal'
    END AS Alerta
FROM Componente c
WHERE c.ativo = 1;
GO

-- ================================================================================
-- 4) Previsões de demandas futuras com base no histórico de movimentações
-- ================================================================================
CREATE VIEW vw_Previsao_Demanda AS
SELECT 
    FORMAT(data_hora, 'yyyy-MM') AS Mes,
    pf.nome AS Produto,
    SUM(CASE WHEN m.tipo_movimentacao = 'Saída' THEN m.quantidade ELSE 0 END) AS Total_Saida
FROM Movimentacao m
JOIN ProdutoFinal pf ON pf.produtofinal_id = m.fk_produtofinal
WHERE pf.ativo = 1	-- Evita considerar movimentações relacionadas a produtos descontinuados
GROUP BY FORMAT(data_hora, 'yyyy-MM'), pf.nome;
GO

-- ================================================================================
-- 5) Relatório de produtos semiacabados VS acabados
-- ================================================================================
CREATE VIEW vw_Status_Produtos_Producao AS
SELECT 
    pf.produtofinal_id,
    pf.nome AS Produto,
    pf.quantidade,
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM Movimentacao m 
            WHERE m.fk_produtofinal = pf.produtofinal_id 
              AND m.tipo_movimentacao = 'Entrada'
        )
        THEN 'Acabado'
        ELSE 'Semiacabado'
    END AS StatusProducao
FROM ProdutoFinal pf
WHERE pf.ativo = 1;
GO

-- ================================================================================
-- 6) Relatório de feedback do cliente por pedido
-- ================================================================================
CREATE VIEW vw_Feedback_Por_Pedido AS
SELECT 
    f.feedback_id,
    f.data_hora,
    u.nome AS Cliente,
    p.pedido_id,
    f.avaliacao,
    f.observacao
FROM Feedback f
JOIN Pedido p ON p.pedido_id = f.fk_pedido
JOIN Usuario u ON u.usuario_id = p.fk_usuario
WHERE f.ativo = 1;
GO

-- ================================================================================
-- 7) Proporção total entre avaliações positivas, negativas e neutras
-- ================================================================================
CREATE VIEW vw_Resumo_Avaliacoes_Geral AS
SELECT
    COUNT(*) AS TotalAvaliacoes,
    SUM(CASE WHEN f.avaliacao >= 4 THEN 1 ELSE 0 END) AS AvaliacoesPositivas,
    SUM(CASE WHEN f.avaliacao <= 2 THEN 1 ELSE 0 END) AS AvaliacoesNegativas,
    SUM(CASE WHEN f.avaliacao = 3 THEN 1 ELSE 0 END) AS AvaliacoesNeutras,
    AVG(CAST(f.avaliacao AS FLOAT)) AS MediaGeral
FROM Feedback f
WHERE f.ativo = 1;
GO

-- ================================================================================
-- 8) Proporção mensal entre avaliações positivas, negativas e neutras
-- ================================================================================
CREATE VIEW vw_Resumo_Avaliacoes_Mensal AS
SELECT
    FORMAT(f.data_hora, 'yyyy-MM') AS Mes,
    COUNT(*) AS TotalAvaliacoes,
    SUM(CASE WHEN f.avaliacao >= 4 THEN 1 ELSE 0 END) AS AvaliacoesPositivas,
    SUM(CASE WHEN f.avaliacao <= 2 THEN 1 ELSE 0 END) AS AvaliacoesNegativas,
    SUM(CASE WHEN f.avaliacao = 3 THEN 1 ELSE 0 END) AS AvaliacoesNeutras,
    AVG(CAST(f.avaliacao AS FLOAT)) AS MediaMensal
FROM Feedback f
WHERE f.ativo = 1
GROUP BY FORMAT(f.data_hora, 'yyyy-MM');
-- Utilizar "ORDER BY Mes" ao fazer o SELECT externo desta View.
GO