-- INSERTS RANDOMIZADOS PARA TESTE
-- Autora: Amanda Caetano Nasser
-- Última alteração em: 25/04/2025

USE TechSmartDB;

-- ENDERECO: Inserção de registros de endereço na tabela "Endereco" a fim de testar o acesso de usuários no sistema, pois o endereço é obrigatório no cadastro
INSERT INTO Endereco VALUES
('80020000', 'Rua das Palmeiras', 456, 'Casa', 'Batel', 'Curitiba', 'Paraná'),
('80030000', 'Avenida Brasil', 789, NULL, 'Água Verde', 'Curitiba', 'Paraná'),
('80040000', 'Rua Marechal', 321, 'Bloco B', 'Cristo Rei', 'Curitiba', 'Paraná'),
('80010000', 'Rua XV de Novembro', 123, 'Ap 302', 'Centro', 'Curitiba', 'Paraná');

-- USUARIO: Inserção de usuários na tabela "Usuario" a fim de testar o acesso ao sistema. Senha e resposta de segurança são enviadas criptografadas
-- Senha: Carlos@123 | Resposta: Pizza123 | Tipo: Administrador
INSERT INTO Usuario VALUES (
    1, 'Carlos Eduardo', '12345678901', '1995-05-10', 'carlos.edu@gmail.com', '41999998888', '41988887777', 1,
    '$2b$12$QmKMbCWY4coE.QLnHX/M5eIZXFfyY3yAcLms.NzPyJLgMu11gW1KC', 3,
    '858a44e789c923fe5d2594f8996b621ab73e3293d960a7fc0e98194eb8b60499', 1
);

-- Senha: F3r@Lima90 | Resposta: Verde@98 | Tipo: Colaborador
INSERT INTO Usuario VALUES (
    2, 'Fernanda Lima', '98765432100', '1990-09-21', 'fernanda.lima@gmail.com', '41997654321', NULL, 2,
    '$2b$12$B8KSc7U5WQhw9.vMF1Yt3OU9UfrugCuHHZi7raGZjC4DxruaWvcHK', 3,
    '66085f88bdf8adde16d3ed2e4b22ca178ffde6832df020f60f25b6e6a86cb53f', 1
);

-- Senha: Joao123!@# | Resposta: Rex#2020 | Tipo: Cliente
INSERT INTO Usuario VALUES (
    3, 'João Pedro Silva', '11223344556', '2002-12-01', 'joao.pedro@hotmail.com', '41991231234', NULL, 3,
    '$2b$12$3Vc/lW7fecVE1TmagwacoeMmFEfdzGINZPwGoifumiqcpQCUNO2Ui', 3,
    'f29e8c02927096297eea5fdf7095591f570cb8755c7ffcde77ab54f25155cad1', 1
);

-- Senha: Admin123! | Resposta: Lasanha | Tipo: Administrador
INSERT INTO Usuario VALUES	
	(1, 'Admin Teste', '09841489902', '2000-03-04', 'teste_admin@gmail.com', '41998136537', NULL, 4,
	'$2b$12$.zzAIFmixN.etmcsplEtqOiw8Eq8CnQpdM1UVzYy99G394di9L6c2', 1,
	'543f0e64795b37f1bf39acbcb64d8c09676849ff8d4091ca772c869714dd5980', 1
);

-- FORNECEDOR
INSERT INTO Fornecedor VALUES
('Samsung Brasil', '45678912300', '41999990000', NULL, 'samsung@fornecedores.com', 3, 'ATIVO', 1),
('Xiaomi Importados', '12345098765', '41988887766', NULL, 'xiaomi@fornecedores.com', 3, 'ATIVO', 1),
('Apple Distribuição', '78945612300', '41977776666', '41977770000', 'apple@fornecedores.com', 3, 'ATIVO', 1);

-- COMPONENTE
INSERT INTO Componente VALUES
('Tela AMOLED 6.5"', 'Full HD+', 300, 50, 800, 1),
('Processador Snapdragon 888', 'Octa-core 5nm', 150, 30, 300, 1),
('Bateria 5000mAh', 'Lítio polímero', 500, 100, 1000, 1);

-- FORNECEDOR_COMPONENTE
INSERT INTO Fornecedor_Componente VALUES
(1, 1, 560.00),
(2, 2, 780.00),
(3, 3, 220.00);

-- PRODUCAO
INSERT INTO Producao VALUES
('Linha Smartphones', 1),
('Linha Smartwatches', 1),
('Linha Tablets', 1);

-- ETAPA_PRODUCAO
INSERT INTO Etapa_Producao VALUES
(1, 1, 'Montagem da tela', 1, 1),
(1, 2, 'Instalação do processador', 2, 1),
(2, 1, 'Inserção de bateria', 3, 1);

-- PRODUTOFINAL
INSERT INTO ProdutoFinal VALUES
(1, 'Smartphone TechOne', 'Modelo premium com 3 câmeras', 2800.00, 50, 10, 100, 1),
(2, 'Smartwatch FitPro', 'Com monitoramento cardíaco', 950.00, 80, 20, 150, 1),
(3, 'Tablet ProTab 10"', 'Alta performance para trabalho e estudo', 1800.00, 30, 5, 60, 1);

-- PEDIDO (usuario_id = 3)
INSERT INTO Pedido VALUES
(GETDATE(), 3, 2800.00, 'Aguardando pagamento', 1),
(GETDATE(), 3, 950.00, 'Em preparação', 1),
(GETDATE(), 3, 1800.00, 'Cancelado', 1);

-- PEDIDO_PRODUTOFINAL
INSERT INTO Pedido_ProdutoFinal VALUES
(1, 1, 1),
(1, 2, 1),
(2, 2, 1),
(3, 3, 1);

-- FEEDBACK
INSERT INTO Feedback VALUES
(GETDATE(), 1, 4, 'Boa qualidade, mas o prazo foi longo', 1),
(GETDATE(), 2, 5, 'Produto excelente!', 1),
(GETDATE(), 3, 2, 'Comprei errado e precisei cancelar', 1);

/* MOVIMENTACAO: Trecho não utilizado, pois são inseridas automaticamente por meio de trigger
INSERT INTO Movimentacao VALUES
(GETDATE(), 'Entrada', NULL, 1, 50),
(GETDATE(), 'Entrada', NULL, 2, 80),
(GETDATE(), 'Entrada', NULL, 3, 30),
(GETDATE(), 'Saída', 1, 1, 1),
(GETDATE(), 'Saída', 1, 2, 1),
(GETDATE(), 'Saída', 2, 2, 1),
(GETDATE(), 'Saída', 3, 3, 1);
*/