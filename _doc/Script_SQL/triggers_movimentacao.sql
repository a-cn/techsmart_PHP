-- TRIGGERS PARA AUTOMA��O DA TABELA MOVIMENTACAO
-- Autora: Amanda Caetano Nasser
-- �ltima altera��o em: 23/04/2025

use TechSmartDB

/*
 Esta trigger � respons�vel por incluir dados na tabela "Movimentacao" quando um novo produto � cadastrado no sistema
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
 Esta trigger � respons�vel por incluir dados na tabela "Movimentacao" quando um novo pedido � registrado via banco de dados
 OBS: Rodar o comando de maneira individual
*/
CREATE TRIGGER trg_after_insert_Pedido_ProdutoFinal
ON Pedido_ProdutoFinal
AFTER INSERT
AS
BEGIN
    INSERT INTO Movimentacao (data_hora, tipo_movimentacao, fk_pedido, fk_produtofinal, quantidade)
    SELECT GETDATE(), 'Sa�da', fk_pedido, fk_produtofinal, quantidade_item
    FROM inserted;
END;
GO

select * from ProdutoFinal
select * from Movimentacao