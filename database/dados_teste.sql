-- Script SQL para popular o banco de dados com dados de teste
-- Execute este script após criar as tabelas com banco.sql

USE lumis;

-- 1. Criar usuário de teste
INSERT INTO Usuario (nome, email, senha_hash) 
VALUES ('Teste User', 'teste@lumis.com', 'senha123');

-- Armazenar o ID do usuário criado (assumindo ID = 1)
SET @id_usuario = 1;

-- 2. Criar categorias padrão
INSERT INTO Categoria (id_usuario, nome, tipo, cor_hex) VALUES
(@id_usuario, 'Alimentação', 'DESPESA', '#FF5733'),
(@id_usuario, 'Transporte', 'DESPESA', '#3357FF'),
(@id_usuario, 'Lazer', 'DESPESA', '#FF33F5'),
(@id_usuario, 'Saúde', 'DESPESA', '#33FFF5'),
(@id_usuario, 'Educação', 'DESPESA', '#F5FF33'),
(@id_usuario, 'Salário', 'RECEITA', '#33FF57'),
(@id_usuario, 'Freelance', 'RECEITA', '#57FF33'),
(@id_usuario, 'Investimentos', 'RECEITA', '#33FF99');

-- 3. Criar contas
INSERT INTO Conta (id_usuario, nome, tipo_conta, saldo_inicial, exibir_no_dashboard) VALUES
(@id_usuario, 'Conta Corrente Itaú', 'CORRENTE', 2500.00, TRUE),
(@id_usuario, 'Poupança Banco Inter', 'POUPANCA', 5000.00, TRUE),
(@id_usuario, 'Cartão Nubank', 'CARTAO_CREDITO', 0.00, TRUE),
(@id_usuario, 'Dinheiro', 'DINHEIRO', 300.00, TRUE);

-- 4. Criar transações de exemplo (Novembro 2025)
INSERT INTO Transacao (id_usuario, id_conta, id_categoria, valor, tipo_movimentacao, data_transacao, descricao, efetuada) VALUES
-- Receitas
(@id_usuario, 1, 6, 3500.00, 'RECEITA', '2025-11-01', 'Salário Novembro', TRUE),
(@id_usuario, 1, 7, 800.00, 'RECEITA', '2025-11-10', 'Projeto Freelance', TRUE),
-- Despesas - Alimentação
(@id_usuario, 1, 1, 150.50, 'DESPESA', '2025-11-02', 'Supermercado', TRUE),
(@id_usuario, 1, 1, 45.00, 'DESPESA', '2025-11-05', 'Restaurante', TRUE),
(@id_usuario, 1, 1, 120.30, 'DESPESA', '2025-11-08', 'Feira', TRUE),
(@id_usuario, 1, 1, 80.00, 'DESPESA', '2025-11-12', 'Padaria', TRUE),
-- Despesas - Transporte
(@id_usuario, 1, 2, 50.00, 'DESPESA', '2025-11-03', 'Uber', TRUE),
(@id_usuario, 1, 2, 200.00, 'DESPESA', '2025-11-04', 'Gasolina', TRUE),
(@id_usuario, 1, 2, 35.00, 'DESPESA', '2025-11-09', 'Uber', TRUE),
-- Despesas - Lazer
(@id_usuario, 1, 3, 120.00, 'DESPESA', '2025-11-06', 'Cinema', TRUE),
(@id_usuario, 1, 3, 250.00, 'DESPESA', '2025-11-13', 'Show', TRUE),
-- Despesas - Saúde
(@id_usuario, 1, 4, 150.00, 'DESPESA', '2025-11-07', 'Farmácia', TRUE),
(@id_usuario, 1, 4, 200.00, 'DESPESA', '2025-11-14', 'Consulta Médica', TRUE),
-- Despesas - Educação
(@id_usuario, 1, 5, 300.00, 'DESPESA', '2025-11-11', 'Curso Online', TRUE);

-- 5. Criar orçamentos para o mês
INSERT INTO Orcamento (id_usuario, id_categoria, valor_limite, data_inicio, data_fim, ativo) VALUES
(@id_usuario, 1, 600.00, '2025-11-01', '2025-11-30', TRUE),  -- Alimentação
(@id_usuario, 2, 400.00, '2025-11-01', '2025-11-30', TRUE),  -- Transporte
(@id_usuario, 3, 500.00, '2025-11-01', '2025-11-30', TRUE),  -- Lazer
(@id_usuario, 4, 300.00, '2025-11-01', '2025-11-30', TRUE),  -- Saúde
(@id_usuario, 5, 500.00, '2025-11-01', '2025-11-30', TRUE);  -- Educação

-- 6. Criar uma meta financeira
INSERT INTO MetaFinanceira (id_usuario, nome, valor_alvo, data_alvo, status) VALUES
(@id_usuario, 'Fundo de Emergência', 10000.00, '2026-06-01', 'ATIVA'),
(@id_usuario, 'Viagem Internacional', 5000.00, '2026-01-01', 'ATIVA');

-- 7. Criar recorrências
INSERT INTO Recorrencia (id_usuario, id_conta, id_categoria, descricao, valor, frequencia, proxima_data, data_fim) VALUES
(@id_usuario, 1, 1, 'Supermercado Mensal', 500.00, 'MENSAL', '2025-12-01', NULL),
(@id_usuario, 1, 2, 'Combustível Semanal', 200.00, 'SEMANAL', '2025-11-25', NULL);

-- Verificações
SELECT 'Usuários criados:' as Verificacao, COUNT(*) as Total FROM Usuario;
SELECT 'Categorias criadas:' as Verificacao, COUNT(*) as Total FROM Categoria;
SELECT 'Contas criadas:' as Verificacao, COUNT(*) as Total FROM Conta;
SELECT 'Transações criadas:' as Verificacao, COUNT(*) as Total FROM Transacao;
SELECT 'Orçamentos criados:' as Verificacao, COUNT(*) as Total FROM Orcamento;
SELECT 'Metas criadas:' as Verificacao, COUNT(*) as Total FROM MetaFinanceira;
SELECT 'Recorrências criadas:' as Verificacao, COUNT(*) as Total FROM Recorrencia;

-- Ver resumo financeiro
SELECT 
    'Saldo Total em Contas' as Info,
    CONCAT('R$ ', FORMAT(SUM(saldo_inicial), 2)) as Valor
FROM Conta WHERE id_usuario = @id_usuario;

SELECT 
    'Total de Receitas (Nov/2025)' as Info,
    CONCAT('R$ ', FORMAT(SUM(valor), 2)) as Valor
FROM Transacao 
WHERE id_usuario = @id_usuario 
  AND tipo_movimentacao = 'RECEITA'
  AND DATE_FORMAT(data_transacao, '%Y-%m') = '2025-11';

SELECT 
    'Total de Despesas (Nov/2025)' as Info,
    CONCAT('R$ ', FORMAT(SUM(valor), 2)) as Valor
FROM Transacao 
WHERE id_usuario = @id_usuario 
  AND tipo_movimentacao = 'DESPESA'
  AND DATE_FORMAT(data_transacao, '%Y-%m') = '2025-11';
