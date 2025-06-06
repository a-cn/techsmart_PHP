-- PADR�ES DE CONSULTA
-- Autora: Amanda Caetano Nasser
-- �ltima altera��o em: 06/06/2025

USE TechSmartDB;

-- CONSULTAR TELA DE USU�RIOS
select * from Tipo_Usuario
select * from Usuario
select * from Endereco
select * from Pergunta_Seguranca

-- CONSULTAR TELA DE PEDIDOS E FEEDBACK
select * from Usuario
select * from Pedido
select * from ProdutoFinal
select * from Pedido_ProdutoFinal
select * from Feedback

-- CONSULTAR TELA DE FORNECEDOR
select * from Fornecedor
select * from Endereco

-- CONSULTAR TELA DE COMPONENTE
select * from Componente
select * from Fornecedor
select * from Fornecedor_Componente

-- CONSULTAR TELA DE PRODU��O
select * from Producao
select * from Etapa_Producao
select * from Componente
select * from ProdutoFinal

-- CONSULTAR TELA DE PRODUTOS
select * from ProdutoFinal
select * from Producao

-- CONSULTAR TELA DE ACOMPANHAMENTO DA PRODU��O
select * from Producao
select * from Etapa_Producao
select * from Historico_Producao

-- CONSULTAR MOVIMENTA��ES
select * from Movimentacao
select * from Pedido
select * from ProdutoFinal