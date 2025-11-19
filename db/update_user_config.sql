-- Atualização do Schema: Adicionar colunas de configuração na tabela Usuario
-- Arquivo: db/update_user_config.sql

USE lumis;

-- Adicionar colunas de configuração se não existirem
ALTER TABLE Usuario 
ADD COLUMN IF NOT EXISTS config_saldo_oculto BOOLEAN DEFAULT FALSE COMMENT 'Configuração para ocultar saldo por padrão',
ADD COLUMN IF NOT EXISTS config_moeda VARCHAR(3) DEFAULT 'BRL' COMMENT 'Moeda preferida (BRL, USD, EUR)',
ADD COLUMN IF NOT EXISTS config_idioma VARCHAR(5) DEFAULT 'pt-BR' COMMENT 'Idioma da interface',
ADD COLUMN IF NOT EXISTS config_notificacoes BOOLEAN DEFAULT TRUE COMMENT 'Ativar/desativar notificações',
ADD COLUMN IF NOT EXISTS config_primeiro_dia_mes TINYINT DEFAULT 1 COMMENT 'Primeiro dia do mês financeiro (1-28)';

-- Atualizar usuário de teste com configurações padrão
UPDATE Usuario 
SET 
    config_saldo_oculto = FALSE,
    config_moeda = 'BRL',
    config_idioma = 'pt-BR',
    config_notificacoes = TRUE,
    config_primeiro_dia_mes = 1
WHERE id_usuario = 1;

-- Verificar estrutura atualizada
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'lumis_db' 
  AND TABLE_NAME = 'Usuario'
ORDER BY ORDINAL_POSITION;
