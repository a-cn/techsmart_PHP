-- SCRIPTS PARA:
-- > Cria��o da tabela Historico_Producao;
-- > Incluir o atributo de tempo de produ��o na tabela ProdutoFinal (coluna "tempo_producao_dias").

-- Autora: Amanda Caetano Nasser
-- �ltima altera��o em: 01/06/2025

USE TechSmartDB

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

-- Adiciona a coluna "tempo_producao_dias" na tabela ProdutoFinal (se j� tiver a tabela criada no banco):
ALTER TABLE ProdutoFinal
ADD tempo_producao_dias INT NOT NULL DEFAULT 3;
-- Obs: Vai criar a nova coluna e atribuir valor 3 para todos os registros, pois, como a tabela j� existe e esta coluna n�o pode ser nula, � necess�rio preencher com algo (tapa buraco).
-- Isso serve somente para este script. No padr�o de cria��o, ela come�ar� vazia.
GO