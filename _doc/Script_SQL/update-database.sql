-- SCRIPTS PARA:
-- > Criação da tabela Historico_Producao;
-- > Incluir o atributo de tempo de produção na tabela ProdutoFinal (coluna "tempo_producao_dias").

-- Autora: Amanda Caetano Nasser
-- Última alteração em: 01/06/2025

USE TechSmartDB

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

-- Adiciona a coluna "tempo_producao_dias" na tabela ProdutoFinal (se já tiver a tabela criada no banco):
ALTER TABLE ProdutoFinal
ADD tempo_producao_dias INT NOT NULL DEFAULT 3;
-- Obs: Vai criar a nova coluna e atribuir valor 3 para todos os registros, pois, como a tabela já existe e esta coluna não pode ser nula, é necessário preencher com algo (tapa buraco).
-- Isso serve somente para este script. No padrão de criação, ela começará vazia.
GO