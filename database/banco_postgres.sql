-- ######################################################
-- # CRIAÇÃO DAS TABELAS DO SISTEMA DE GESTÃO FINANCEIRA (PostgreSQL) #
-- ######################################################

-- 1. Tabela USUARIO
CREATE TABLE Usuario (
    id_usuario SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    config_saldo_oculto BOOLEAN DEFAULT FALSE,
    config_moeda VARCHAR(3) DEFAULT 'BRL',
    config_idioma VARCHAR(5) DEFAULT 'pt-BR',
    config_notificacoes BOOLEAN DEFAULT TRUE,
    config_primeiro_dia_mes SMALLINT DEFAULT 1
);

-- 2. Tabela CONTA
CREATE TABLE Conta (
    id_conta SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo_conta VARCHAR(20) NOT NULL,
    saldo_inicial NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    exibir_no_dashboard BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE
);

-- 3. Tabela CATEGORIA
CREATE TABLE Categoria (
    id_categoria SERIAL PRIMARY KEY,
    id_usuario INT,
    nome VARCHAR(50) NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    icone VARCHAR(10),
    cor_hex VARCHAR(7),
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE
);

-- 4. Tabela TRANSACAO
CREATE TABLE Transacao (
    id_transacao BIGSERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_conta INT NOT NULL,
    id_categoria INT NOT NULL,
    valor NUMERIC(10, 2) NOT NULL,
    tipo_movimentacao VARCHAR(15) NOT NULL,
    data_transacao DATE NOT NULL,
    descricao VARCHAR(255),
    efetuada BOOLEAN DEFAULT TRUE,
    recorrente BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_conta) REFERENCES Conta(id_conta) ON DELETE RESTRICT,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria) ON DELETE RESTRICT
);

-- 5. Tabela RECORRENCIA
CREATE TABLE Recorrencia (
    id_recorrencia SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_conta INT NOT NULL,
    id_categoria INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor NUMERIC(10, 2) NOT NULL,
    frequencia VARCHAR(50) NOT NULL,
    proxima_data DATE NOT NULL,
    data_fim DATE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_conta) REFERENCES Conta(id_conta) ON DELETE RESTRICT,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria) ON DELETE RESTRICT
);

-- 6. Tabela TRANSFERENCIA
CREATE TABLE Transferencia (
    id_transferencia SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_conta_origem INT NOT NULL,
    id_conta_destino INT NOT NULL,
    valor NUMERIC(10, 2) NOT NULL,
    data_transferencia DATE NOT NULL,
    id_transacao_debito BIGINT NOT NULL,
    id_transacao_credito BIGINT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_conta_origem) REFERENCES Conta(id_conta) ON DELETE RESTRICT,
    FOREIGN KEY (id_conta_destino) REFERENCES Conta(id_conta) ON DELETE RESTRICT,
    FOREIGN KEY (id_transacao_debito) REFERENCES Transacao(id_transacao) ON DELETE RESTRICT,
    FOREIGN KEY (id_transacao_credito) REFERENCES Transacao(id_transacao) ON DELETE RESTRICT
);

-- 7. Tabela ORCAMENTO
CREATE TABLE Orcamento (
    id_orcamento SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    valor_limite NUMERIC(10, 2) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria) ON DELETE RESTRICT
);

-- 8. Tabela METAFINANCEIRA
CREATE TABLE MetaFinanceira (
    id_meta SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    valor_alvo NUMERIC(10, 2) NOT NULL,
    data_alvo DATE,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE
);

-- 9. Tabela RECORRENCIA_LOG
CREATE TABLE recorrencia_log (
    id_log SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    mes_ano VARCHAR(7) NOT NULL,
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, mes_ano),
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE
);

-- ######################################################
-- # ÍNDICES DE PERFORMANCE CRUCIAIS                      #
-- ######################################################

CREATE INDEX idx_transacao_usuario ON Transacao (id_usuario);
CREATE INDEX idx_transacao_conta ON Transacao (id_conta);
CREATE INDEX idx_transacao_categoria ON Transacao (id_categoria);
CREATE INDEX idx_transacao_data_usuario ON Transacao (id_usuario, data_transacao);
