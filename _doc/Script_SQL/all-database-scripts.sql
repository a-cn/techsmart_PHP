-- SCRIPTS PARA:
-- > Cria��o de credenciais para o banco de dados (respons�vel por conect�-lo ao back-end);
-- > Cria��o do banco de dados e tabelas do projeto ("TechSmartDB");
-- > Inserts de dados padronizados nas tabelas "Tipo_Usuario" e "Pergunta_Seguranca".

-- Autora: Amanda Caetano Nasser
-- �ltima altera��o em: 01/06/2025


USE master; -- Acessa o banco de dados "master"
GO

-- Dropa (deleta) o banco de dados, se existir
BEGIN
    DECLARE @sql NVARCHAR(MAX)
    IF DB_ID('TechSmartDB') IS NOT NULL
    BEGIN
        SET @sql = 'DROP DATABASE TechSmartDB'
        EXEC(@sql)
    END
END
GO

-- Dropa o Login, se existir
BEGIN
    DECLARE @sql NVARCHAR(MAX)
    IF EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'techsmart_user')
    BEGIN
        SET @sql = 'DROP LOGIN techsmart_user'
        EXEC(@sql)
    END
END
GO

USE master; --Importante come�ar no banco de dados "master"

CREATE LOGIN techsmart_user WITH PASSWORD = 'Teste123!'; -- Cria um login com usu�rio e senha para permitir conex�o com o SQL Server
GO

CREATE DATABASE TechSmartDB; -- Cria um banco de dados chamado "TechSmartDB" para garantir a padroniza��o em todas as m�quinas
GO

USE TechSmartDB; -- Acessa o banco que ser� utilizado. A partir daqui, as linhas seguintes ser�o executadas dentro do banco "TechSmartDB"
GO

CREATE USER techsmart_user FOR LOGIN techsmart_user; -- Cria um usu�rio de banco de dados chamado "techsmart_user" com base no login criado acima
GO

ALTER ROLE db_owner ADD MEMBER techsmart_user; -- Atribui o papel de dono ao usu�rio "techsmart_user", dando permiss�es administrativas completas dentro do banco "TechSmartDB"
GO

-- ================================================================================
-- 1) Tabela: Tipo_Usuario
-- Armazena as categorias de usu�rios do sistema.
-- ================================================================================
CREATE TABLE Tipo_Usuario (
	tipo_usuario_id	INT			NOT NULL	IDENTITY(1,1),
	descricao		VARCHAR(15)	NOT NULL,
	CONSTRAINT PK_Tipo_Usuario	-- Define a chave prim�ria
		PRIMARY KEY CLUSTERED (tipo_usuario_id ASC)
);
GO

INSERT INTO Tipo_Usuario VALUES
	('Administrador'),
	('Colaborador'),
	('Cliente');
GO

-- ================================================================================
-- 2) Tabela: Endereco
-- Armazena os endere�os de usu�rios e fornecedores cadastrados no sistema.
-- ================================================================================
CREATE TABLE Endereco (
	endereco_id	INT				NOT NULL	IDENTITY(1,1),
	cep			VARCHAR(8)		NOT NULL,
	logradouro	VARCHAR(150)	NOT NULL,
	numero		INT				NOT NULL,
	complemento	VARCHAR(100),
	bairro		VARCHAR(50)		NOT NULL,
	cidade		VARCHAR(50)		NOT NULL,
	estado		VARCHAR(50)		NOT NULL,
	CONSTRAINT PK_Endereco
		PRIMARY KEY CLUSTERED (endereco_id ASC)
);
GO

-- ================================================================================
-- 3) Tabela: Pergunta_Seguranca
-- Armazena as perguntas de seguran�a para a redefini��o de senha.
-- ================================================================================
CREATE TABLE Pergunta_Seguranca (
	pergunta_seguranca_id	INT				NOT NULL	IDENTITY(1,1),
	pergunta				VARCHAR(200)	NOT NULL,
	CONSTRAINT PK_Pergunta_Seguranca
		PRIMARY KEY CLUSTERED (pergunta_seguranca_id ASC)
);
GO

INSERT INTO Pergunta_Seguranca VALUES
	('Qual � a sua comida favorita?'),
	('Qual foi o primeiro local para onde voc� viajou?'),
	('Qual � a sua cor favorita?'),
	('Qual foi o modelo do seu primeiro celular?'),
	('Qual o nome do seu primeiro animal de estima��o?');
GO

-- ================================================================================
-- 4) Tabela: Usuario
-- Armazena os dados de usu�rios cadastrados no sistema.
-- ================================================================================
CREATE TABLE Usuario (
	usuario_id				INT				NOT NULL	IDENTITY(1,1),
	fk_tipo_usuario			INT				NOT NULL,
	nome					VARCHAR(100)	NOT NULL,
	cpf_cnpj				VARCHAR(14)		NOT NULL,
	data_nascimento			DATE,
	email					VARCHAR(50)		NOT NULL,
	num_principal			VARCHAR(15)		NOT NULL,
	num_recado				VARCHAR(15),
	fk_endereco				INT				NOT NULL,
	senha					VARCHAR(100)	NOT NULL,
	fk_pergunta_seguranca	INT				NOT NULL,
	resposta_seguranca		VARCHAR(100)	NOT NULL,
	ativo					BIT				NOT NULL	DEFAULT 1,	-- Novos registros estar�o com "ativo = 1" (ativo por padr�o). Para desativar o registro, dever� ser "ativo = 0"
	CONSTRAINT PK_Usuario
		PRIMARY KEY CLUSTERED (usuario_id ASC),
	CONSTRAINT FK_Usuario_Tipo_Usuario	-- Define a chave estrangeira e sua refer�ncia
		FOREIGN KEY (fk_tipo_usuario) REFERENCES Tipo_Usuario (tipo_usuario_id)
		ON DELETE NO ACTION		-- N�o permite excluir se houver registros dependentes
		ON UPDATE NO ACTION,	-- N�o propaga atualiza��o de chaves
	CONSTRAINT FK_Usuario_Endereco
		FOREIGN KEY (fk_endereco) REFERENCES Endereco (endereco_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT FK_Usuario_Pergunta_Seguranca
		FOREIGN KEY (fk_pergunta_seguranca) REFERENCES Pergunta_Seguranca (pergunta_seguranca_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 5) Tabela: Fornecedor
-- Armazena os dados de fornecedores registrados no sistema.
-- ================================================================================
CREATE TABLE Fornecedor (
	fornecedor_id	INT				NOT NULL	IDENTITY(1,1),
	nome			VARCHAR(100)	NOT NULL,
	cpf_cnpj		VARCHAR(14)		NOT NULL,
	num_principal	VARCHAR(15)		NOT NULL,
	num_secundario	VARCHAR(15),
	email			VARCHAR(50)		NOT NULL,
	fk_endereco		INT				NOT NULL,
	situacao		VARCHAR(15)		NOT NULL,
	ativo			BIT				NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Fornecedor
		PRIMARY KEY CLUSTERED (fornecedor_id ASC),
	CONSTRAINT FK_Fornecedor_Endereco
		FOREIGN KEY (fk_endereco) REFERENCES Endereco (endereco_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 6) Tabela: Componente
-- Armazena os dados de componentes registrados no sistema.
-- ================================================================================
CREATE TABLE Componente (
	componente_id	INT				NOT NULL	IDENTITY(1,1),
	nome			VARCHAR(50)		NOT NULL,
	especificacao	VARCHAR(100),
	quantidade		INT				NOT NULL,
	nivel_minimo	INT				NOT NULL,
	nivel_maximo	INT				NOT NULL,
	ativo			BIT				NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Componente
		PRIMARY KEY CLUSTERED (componente_id ASC)
);
GO

-- ================================================================================
-- 7) Tabela: Producao
-- Armazena as linhas de produ��o registradas.
-- ================================================================================
CREATE TABLE Producao (
	producao_id	INT			NOT NULL	IDENTITY(1,1),
	nome		VARCHAR(50)	NOT NULL,
	ativo		BIT			NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Producao
		PRIMARY KEY CLUSTERED (producao_id ASC)
);
GO

-- ================================================================================
-- 8) Tabela: Etapa_Producao
-- Armazena os dados das etapas de produ��o cadastradas.
-- ================================================================================
CREATE TABLE Etapa_Producao (
	etapa_producao_id	INT			NOT NULL	IDENTITY(1,1),
	fk_producao			INT			NOT NULL,
	ordem				INT			NOT NULL,
	nome				VARCHAR(50)	NOT NULL,
	fk_componente		INT			NOT NULL,
	ativo				BIT			NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Etapa_Producao
		PRIMARY KEY CLUSTERED (etapa_producao_id ASC),
	CONSTRAINT FK_Etapa_Producao_Producao
		FOREIGN KEY (fk_producao) REFERENCES Producao (producao_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT FK_Etapa_Producao_Componente
		FOREIGN KEY (fk_componente) REFERENCES Componente (componente_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 9) Tabela: ProdutoFinal
-- Armazena os dados de produtos finais montados pela empresa.
-- ================================================================================
CREATE TABLE ProdutoFinal (
	produtofinal_id		INT				NOT NULL	IDENTITY(1,1),
	fk_producao			INT				NOT NULL,
	nome				VARCHAR(50)		NOT NULL,
	descricao			VARCHAR(100),
	valor_venda			FLOAT			NOT NULL,
	quantidade			INT				NOT NULL,
	nivel_minimo		INT				NOT NULL,
	nivel_maximo		INT				NOT NULL,
	tempo_producao_dias	INT				NOT NULL,	-- Quantos dias leva para montar tal produto (n�mero inteiro)
	ativo				BIT				NOT NULL	DEFAULT 1,
	CONSTRAINT PK_ProdutoFinal
		PRIMARY KEY CLUSTERED (produtofinal_id ASC),
	CONSTRAINT FK_ProdutoFinal_Producao
		FOREIGN KEY (fk_producao) REFERENCES Producao (producao_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 10) Tabela: Pedido
-- Armazena os dados de pedidos solicitados por clientes da TechSmart.
-- ================================================================================
CREATE TABLE Pedido (
	pedido_id	INT			NOT NULL	IDENTITY(1,1),
	data_hora	DATETIME	NOT NULL,
	fk_usuario	INT			NOT NULL,
	valor_total	FLOAT		NOT NULL,
	situacao	VARCHAR(50),
	ativo		BIT			NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Pedido
		PRIMARY KEY CLUSTERED (pedido_id ASC),
	CONSTRAINT FK_Pedido_Usuario
		FOREIGN KEY (fk_usuario) REFERENCES Usuario (usuario_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 11) Tabela: Pedido_ProdutoFinal
-- Indica a quantidade de cada produto final selecionado nos pedidos de clientes.
-- ================================================================================
CREATE TABLE Pedido_ProdutoFinal (
	pedido_produtofinal_id	INT NOT NULL IDENTITY(1,1),
	fk_pedido				INT NOT NULL,
	fk_produtofinal			INT NOT NULL,
	quantidade_item			INT	NOT NULL,
	CONSTRAINT PK_Pedido_ProdutoFinal
		PRIMARY KEY CLUSTERED (pedido_produtofinal_id ASC),
	CONSTRAINT FK_Pedido_ProdutoFinal_Pedido
		FOREIGN KEY (fk_pedido) REFERENCES Pedido (pedido_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT FK_Pedido_ProdutoFinal_ProdutoFinal
		FOREIGN KEY (fk_produtofinal) REFERENCES ProdutoFinal (produtofinal_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 12) Tabela: Feedback
-- Armazena as avalia��es deixadas por clientes sobre seus pedidos.
-- ================================================================================
CREATE TABLE Feedback (
	feedback_id	INT				NOT NULL	IDENTITY(1,1),
	data_hora	DATETIME		NOT NULL,
	fk_pedido	INT				NOT NULL,
	avaliacao	INT				NOT NULL,
	observacao	VARCHAR(100),
	ativo		BIT				NOT NULL	DEFAULT 1,
	CONSTRAINT PK_Feedback
		PRIMARY KEY CLUSTERED (feedback_id ASC),
	CONSTRAINT FK_Feedback_Pedido
		FOREIGN KEY (fk_pedido) REFERENCES Pedido (pedido_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 13) Tabela: Movimentacao
-- Armazena as movimenta��es de estoque, isto �, se o produto terminou de ser montado pela empresa ou se foi comprado por um cliente (entrou ou saiu).
-- ================================================================================
CREATE TABLE Movimentacao (
	movimentacao_id		INT			NOT NULL	IDENTITY(1,1),
	data_hora			DATETIME	NOT NULL,
	tipo_movimentacao	VARCHAR(50)	NOT NULL,
	fk_pedido			INT,
	fk_produtofinal		INT			NOT NULL,
	quantidade			INT			NOT NULL,
	CONSTRAINT PK_Movimentacao
		PRIMARY KEY CLUSTERED (movimentacao_id ASC),
	CONSTRAINT FK_Movimentacao_Pedido
		FOREIGN KEY (fk_pedido) REFERENCES Pedido (pedido_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT FK_Movimentacao_ProdutoFinal
		FOREIGN KEY (fk_produtofinal) REFERENCES ProdutoFinal (produtofinal_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 14) Tabela: Fornecedor_Componente
-- Indica quais fornecedores est�o associados a quais componentes.
-- ================================================================================
CREATE TABLE Fornecedor_Componente (
	fornecedor_componente_id	INT	NOT NULL	IDENTITY(1,1),
	fk_fornecedor				INT	NOT NULL,
	fk_componente				INT NOT NULL,
	custo_componente			FLOAT,
	CONSTRAINT PK_Fornecedor_Componente
		PRIMARY KEY CLUSTERED (fornecedor_componente_id ASC),
	CONSTRAINT FK_Fornecedor_Componente_Fornecedor
		FOREIGN KEY (fk_fornecedor) REFERENCES Fornecedor (fornecedor_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT FK_Fornecedor_Componente_Componente
		FOREIGN KEY (fk_componente) REFERENCES Componente (componente_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);
GO

-- ================================================================================
-- 15) Tabela: Historico_Producao
-- Mant�m um hist�rico das linhas de produ��o iniciadas, em andamento e conclu�das.
-- ================================================================================
CREATE TABLE Historico_Producao (
    historico_producao_id	INT			NOT NULL	IDENTITY(1,1),
    fk_producao				INT			NOT NULL,
    data_inicio				DATETIME	NOT NULL,
    data_previsao			DATETIME	NOT NULL,
    data_conclusao			DATETIME	NULL,	-- Ser� nula, at� que seja registrada a conclus�o da �ltima etapa da linha de produ��o.
    ultima_etapa			INT			NULL,	-- Pode ser nula, pois existe a possibilidade de iniciar uma produ��o e n�o marcar uma etapa como conclu�da na mesma sess�o. A partir do momento em que a 1� etapa for conclu�da, isso ser� registrado.
    CONSTRAINT PK_Historico_Producao
        PRIMARY KEY CLUSTERED (historico_producao_id ASC),
    CONSTRAINT FK_Historico_Producao_Producao
        FOREIGN KEY (fk_producao) REFERENCES Producao (producao_id)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION,
);
GO