# Estrutura do Banco de Dados - Lumis

## Arquivos SQL

### 📋 banco.sql

**Arquivo central do sistema** - Contém toda a estrutura base do banco de dados virgem.

Este arquivo inclui:

- ✅ **8 tabelas principais**: Usuario, Conta, Categoria, Transacao, Recorrencia, Transferencia, Orcamento, MetaFinanceira
- ✅ **Colunas de configuração do usuário**: config_saldo_oculto, config_moeda, config_idioma, config_notificacoes, config_primeiro_dia_mes
- ✅ **Coluna icone na tabela Categoria**: Para emojis/ícones das categorias
- ✅ **Índices de performance**: Para otimização de consultas

**Como usar:**

```bash
# Criar banco de dados limpo
mysql -u root -h localhost -e "CREATE DATABASE IF NOT EXISTS lumis;"
mysql -u root -h localhost lumis < banco.sql
```

### 🧪 dados_teste.sql

**Arquivo de dados de teste** - Popula o banco com dados fictícios para desenvolvimento.

Este arquivo contém:

- Usuário de teste (ID: 1)
- Contas de exemplo
- Categorias padrão (Receitas e Despesas)
- Transações de exemplo
- Orçamentos pré-configurados

**Como usar:**

```bash
# Após criar a estrutura com banco.sql, adicionar dados de teste
mysql -u root -h localhost lumis < dados_teste.sql
```

## Estrutura das Tabelas

### Usuario

- Dados pessoais e autenticação
- 5 colunas de configuração personalizável

### Conta

- Contas financeiras do usuário (corrente, poupança, investimento, etc.)

### Categoria

- Categorias de receitas e despesas
- Suporta ícones/emojis e cores personalizadas

### Transacao

- Registro de todas as movimentações financeiras
- Tipos: RECEITA, DESPESA, TRANSFERENCIA

### Recorrencia

- Transações recorrentes (mensais, semanais, anuais)

### Transferencia

- Transferências entre contas do usuário

### Orcamento

- Limites de gastos por categoria

### MetaFinanceira

- Metas financeiras do usuário

## ⚠️ Importante

**Sempre use `banco.sql` como referência da estrutura base do sistema.**

Qualquer alteração no schema deve ser:

1. Testada no banco de desenvolvimento
2. Documentada aqui
3. Incorporada ao `banco.sql`

Não mantenha arquivos ALTER TABLE separados - tudo deve estar no `banco.sql` para facilitar reinstalações limpas do sistema.
