-- SCRIPTS PARA:
-- > Criação de credenciais para o banco de dados (responsável por conectá-lo ao back-end);
-- > Criação do banco de dados e tabelas do projeto ("TechSmartDB");
-- > Inserts de dados padronizados nas tabelas "Tipo_Usuario" e "Pergunta_Seguranca".

-- Autora: Amanda Caetano Nasser
-- Última alteração em: 01/06/2025


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

USE master; --Importante começar no banco de dados "master"

CREATE LOGIN techsmart_user WITH PASSWORD = 'Teste123!'; -- Cria um login com usuário e senha para permitir conexão com o SQL Server
GO

CREATE DATABASE TechSmartDB; -- Cria um banco de dados chamado "TechSmartDB" para garantir a padronização em todas as máquinas
GO

USE TechSmartDB; -- Acessa o banco que será utilizado. A partir daqui, as linhas seguintes serão executadas dentro do banco "TechSmartDB"
GO

CREATE USER techsmart_user FOR LOGIN techsmart_user; -- Cria um usuário de banco de dados chamado "techsmart_user" com base no login criado acima
GO

ALTER ROLE db_owner ADD MEMBER techsmart_user; -- Atribui o papel de dono ao usuário "techsmart_user", dando permissões administrativas completas dentro do banco "TechSmartDB"
GO

-- ================================================================================
-- 1) Tabela: Tipo_Usuario
-- Armazena as categorias de usuários do sistema.
-- ================================================================================
CREATE TABLE Tipo_Usuario (
	tipo_usuario_id	INT			NOT NULL	IDENTITY(1,1),
	descricao		VARCHAR(15)	NOT NULL,
	CONSTRAINT PK_Tipo_Usuario	-- Define a chave primária
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
-- Armazena os endereços de usuários e fornecedores cadastrados no sistema.
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
-- Armazena as perguntas de segurança para a redefinição de senha.
-- ================================================================================
CREATE TABLE Pergunta_Seguranca (
	pergunta_seguranca_id	INT				NOT NULL	IDENTITY(1,1),
	pergunta				VARCHAR(200)	NOT NULL,
	CONSTRAINT PK_Pergunta_Seguranca
		PRIMARY KEY CLUSTERED (pergunta_seguranca_id ASC)
);
GO

INSERT INTO Pergunta_Seguranca VALUES
	('Qual é a sua comida favorita?'),
	('Qual foi o primeiro local para onde você viajou?'),
	('Qual é a sua cor favorita?'),
	('Qual foi o modelo do seu primeiro celular?'),
	('Qual o nome do seu primeiro animal de estimação?');
GO

-- ================================================================================
-- 4) Tabela: Usuario
-- Armazena os dados de usuários cadastrados no sistema.
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
	ativo					BIT				NOT NULL	DEFAULT 1,	-- Novos registros estarão com "ativo = 1" (ativo por padrão). Para desativar o registro, deverá ser "ativo = 0"
	CONSTRAINT PK_Usuario
		PRIMARY KEY CLUSTERED (usuario_id ASC),
	CONSTRAINT FK_Usuario_Tipo_Usuario	-- Define a chave estrangeira e sua referência
		FOREIGN KEY (fk_tipo_usuario) REFERENCES Tipo_Usuario (tipo_usuario_id)
		ON DELETE NO ACTION		-- Não permite excluir se houver registros dependentes
		ON UPDATE NO ACTION,	-- Não propaga atualização de chaves
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
-- Armazena as linhas de produção registradas.
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
-- Armazena os dados das etapas de produção cadastradas.
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
	tempo_producao_dias	INT				NOT NULL,	-- Quantos dias leva para montar tal produto (número inteiro)
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
-- Armazena as avaliações deixadas por clientes sobre seus pedidos.
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
-- Armazena as movimentações de estoque, isto é, se o produto terminou de ser montado pela empresa ou se foi comprado por um cliente (entrou ou saiu).
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
-- Indica quais fornecedores estão associados a quais componentes.
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
-- Mantém um histórico das linhas de produção iniciadas, em andamento e concluídas.
-- ================================================================================
CREATE TABLE Historico_Producao (
    historico_producao_id	INT			NOT NULL	IDENTITY(1,1),
    fk_producao				INT			NOT NULL,
    data_inicio				DATETIME	NOT NULL,
    data_previsao			DATETIME	NOT NULL,
    data_conclusao			DATETIME	NULL,	-- Será nula, até que seja registrada a conclusão da última etapa da linha de produção.
    ultima_etapa			INT			NULL,	-- Pode ser nula, pois existe a possibilidade de iniciar uma produção e não marcar uma etapa como concluída na mesma sessão. A partir do momento em que a 1ª etapa for concluída, isso será registrado.
    CONSTRAINT PK_Historico_Producao
        PRIMARY KEY CLUSTERED (historico_producao_id ASC),
    CONSTRAINT FK_Historico_Producao_Producao
        FOREIGN KEY (fk_producao) REFERENCES Producao (producao_id)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION,
);
GO