-- PADRÕES DE CONSULTA
-- Responsável: Amanda Caetano Nasser
-- Última alteração em: 16/06/2025

-- Definindo a codificação UTF-8
SET NAMES 'utf8';
SET CHARACTER SET utf8;

USE TechSmartDB;

-- CONSULTAR TELA DE USUÁRIOS
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

-- CONSULTAR TELA DE PRODUÇÃO
select * from Producao
select * from Etapa_Producao
select * from Componente
select * from ProdutoFinal

-- CONSULTAR TELA DE PRODUTOS
select * from ProdutoFinal
select * from Producao

-- CONSULTAR TELA DE ACOMPANHAMENTO DA PRODUÇÃO
select * from Producao
select * from Etapa_Producao
select * from Componente
select * from Fornecedor_Componente
select * from Historico_Producao

-- CONSULTAR MOVIMENTAÇÕES
select * from Movimentacao
select * from Pedido
select * from ProdutoFinal