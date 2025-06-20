-- TRIGGERS PARA AUTOMAÇÃO DA TABELA MOVIMENTACAO
-- Responsável: Amanda Caetano Nasser
-- Última alteração em: 20/06/2025

USE TechSmartDB;
GO

/*
 Esta trigger é responsável por incluir dados na tabela "Movimentacao" quando um novo produto é cadastrado no sistema
 OBS: Rodar o comando de maneira individual
*/
CREATE TRIGGER trg_after_insert_ProdutoFinal
ON ProdutoFinal
AFTER INSERT
AS
BEGIN
    INSERT INTO Movimentacao (data_hora, tipo_movimentacao, fk_pedido, fk_produtofinal, quantidade)
    SELECT GETDATE(), 'Entrada', NULL, produtofinal_id, quantidade
    FROM inserted;
END;
GO

/*
 Esta trigger é responsável por incluir dados na tabela "Movimentacao" quando um novo pedido é registrado via banco de dados
 OBS: Rodar o comando de maneira individual
*/
CREATE TRIGGER trg_after_insert_Pedido_ProdutoFinal
ON Pedido_ProdutoFinal
AFTER INSERT
AS
BEGIN
    INSERT INTO Movimentacao (data_hora, tipo_movimentacao, fk_pedido, fk_produtofinal, quantidade)
    SELECT GETDATE(), 'Saída', fk_pedido, fk_produtofinal, quantidade_item
    FROM inserted;
END;
GO

select * from ProdutoFinal
select * from Movimentacao