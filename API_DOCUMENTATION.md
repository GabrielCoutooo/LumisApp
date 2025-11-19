# 📚 Documentação Técnica da API Lumis

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Autenticação](#autenticação)
3. [Endpoints](#endpoints)
4. [Modelos de Dados](#modelos-de-dados)
5. [Códigos de Erro](#códigos-de-erro)
6. [Exemplos de Uso](#exemplos-de-uso)

---

## 🎯 Visão Geral

**Base URL:** `http://localhost:8000/index.php/api`

**Formato de Resposta:** JSON

**Content-Type:** `application/json`

---

## 🔐 Autenticação

### POST /login

Autentica um usuário no sistema.

**Request Body:**

```json
{
  "email": "string",
  "senha": "string"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "usuario": {
    "id_usuario": 1,
    "nome": "string",
    "email": "string",
    "data_criacao": "timestamp"
  }
}
```

**Response (401 Unauthorized):**

```json
{
  "error": "Credenciais inválidas"
}
```

---

## 📌 Endpoints

### 💰 Contas

#### GET /contas

Lista todas as contas de um usuário.

**Query Parameters:**

- `id_usuario` (required): ID do usuário

**Response (200 OK):**

```json
[
  {
    "id_conta": 1,
    "id_usuario": 1,
    "nome": "Conta Corrente",
    "tipo_conta": "CORRENTE",
    "saldo_inicial": "1000.00",
    "exibir_no_dashboard": true
  }
]
```

#### POST /contas

Cria uma nova conta.

**Request Body:**

```json
{
  "id_usuario": 1,
  "nome": "string",
  "tipo_conta": "CORRENTE|POUPANCA|INVESTIMENTO|CARTAO_CREDITO|DINHEIRO",
  "saldo_inicial": 0.0,
  "exibir_no_dashboard": true
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "id_conta": 1
}
```

---

### 💸 Transações

#### POST /transacoes

Cria uma nova transação.

**Request Body:**

```json
{
  "id_usuario": 1,
  "id_conta": 1,
  "id_categoria": 1,
  "valor": 100.0,
  "tipo_movimentacao": "RECEITA|DESPESA|TRANSFERENCIA",
  "data_transacao": "YYYY-MM-DD",
  "descricao": "string",
  "efetuada": true
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "id_transacao": 1
}
```

#### GET /extrato

Retorna o extrato de transações.

**Query Parameters:**

- `id_usuario` (required): ID do usuário
- `id_conta` (optional): Filtrar por conta específica

**Response (200 OK):**

```json
[
  {
    "id_transacao": 1,
    "id_usuario": 1,
    "id_conta": 1,
    "id_categoria": 1,
    "valor": "100.00",
    "tipo_movimentacao": "DESPESA",
    "data_transacao": "2025-11-18",
    "descricao": "Mercado",
    "efetuada": true
  }
]
```

---

### 📊 Orçamentos

#### GET /orcamento

Lista orçamentos de um período específico.

**Query Parameters:**

- `id_usuario` (required): ID do usuário
- `mes_ano` (required): Período no formato YYYY-MM

**Response (200 OK):**

```json
[
  {
    "id_orcamento": 1,
    "id_usuario": 1,
    "id_categoria": 1,
    "valor_limite": "500.00",
    "data_inicio": "2025-11-01",
    "data_fim": "2025-11-30",
    "ativo": true,
    "categoria_nome": "Alimentação"
  }
]
```

#### POST /orcamento

Cria um novo orçamento.

**Request Body:**

```json
{
  "id_usuario": 1,
  "id_categoria": 1,
  "valor_limite": 500.0,
  "data_inicio": "YYYY-MM-DD",
  "data_fim": "YYYY-MM-DD",
  "ativo": true
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "id_orcamento": 1
}
```

---

### 📈 Relatórios

#### GET /relatorios/gastos-categoria

Retorna gastos agrupados por categoria.

**Query Parameters:**

- `id_usuario` (required): ID do usuário
- `mes_ano` (required): Período no formato YYYY-MM

**Response (200 OK):**

```json
[
  {
    "categoria": "Alimentação",
    "total_gasto": "395.80"
  },
  {
    "categoria": "Transporte",
    "total_gasto": "285.00"
  }
]
```

---

## 📦 Modelos de Dados

### Usuario

```json
{
  "id_usuario": "integer (auto)",
  "nome": "string(100)",
  "email": "string(255) unique",
  "senha_hash": "string(255)",
  "data_criacao": "timestamp"
}
```

### Conta

```json
{
  "id_conta": "integer (auto)",
  "id_usuario": "integer (FK)",
  "nome": "string(100)",
  "tipo_conta": "string(20)",
  "saldo_inicial": "decimal(10,2)",
  "exibir_no_dashboard": "boolean"
}
```

### Transacao

```json
{
  "id_transacao": "bigint (auto)",
  "id_usuario": "integer (FK)",
  "id_conta": "integer (FK)",
  "id_categoria": "integer (FK)",
  "valor": "decimal(10,2)",
  "tipo_movimentacao": "string(15)",
  "data_transacao": "date",
  "descricao": "string(255)",
  "efetuada": "boolean"
}
```

### Categoria

```json
{
  "id_categoria": "integer (auto)",
  "id_usuario": "integer (FK) nullable",
  "nome": "string(50)",
  "tipo": "string(10)",
  "cor_hex": "string(7)"
}
```

### Orcamento

```json
{
  "id_orcamento": "integer (auto)",
  "id_usuario": "integer (FK)",
  "id_categoria": "integer (FK)",
  "valor_limite": "decimal(10,2)",
  "data_inicio": "date",
  "data_fim": "date nullable",
  "ativo": "boolean"
}
```

---

## ⚠️ Códigos de Erro

| Código | Descrição                                      |
| ------ | ---------------------------------------------- |
| 200    | Sucesso                                        |
| 400    | Bad Request - Parâmetros obrigatórios faltando |
| 401    | Unauthorized - Credenciais inválidas           |
| 404    | Not Found - Endpoint não encontrado            |
| 405    | Method Not Allowed - Método HTTP inválido      |
| 500    | Internal Server Error - Erro no servidor       |

---

## 💡 Exemplos de Uso

### Fluxo Completo: Criar Conta e Registrar Gasto

```bash
# 1. Login
curl -X POST http://localhost:8000/index.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teste@lumis.com","senha":"senha123"}'

# 2. Criar Conta
curl -X POST http://localhost:8000/index.php/api/contas \
  -H "Content-Type: application/json" \
  -d '{"id_usuario":1,"nome":"Carteira","tipo_conta":"DINHEIRO","saldo_inicial":500,"exibir_no_dashboard":true}'

# 3. Registrar Despesa
curl -X POST http://localhost:8000/index.php/api/transacoes \
  -H "Content-Type: application/json" \
  -d '{"id_usuario":1,"id_conta":1,"id_categoria":1,"valor":50,"tipo_movimentacao":"DESPESA","data_transacao":"2025-11-18","descricao":"Almoço","efetuada":true}'

# 4. Ver Extrato
curl "http://localhost:8000/index.php/api/extrato?id_usuario=1"
```

### Criar Orçamento Mensal

```bash
curl -X POST http://localhost:8000/index.php/api/orcamento \
  -H "Content-Type: application/json" \
  -d '{"id_usuario":1,"id_categoria":1,"valor_limite":800,"data_inicio":"2025-11-01","data_fim":"2025-11-30","ativo":true}'
```

### Ver Relatório de Gastos

```bash
curl "http://localhost:8000/index.php/api/relatorios/gastos-categoria?id_usuario=1&mes_ano=2025-11"
```

---

## 🔧 Notas Técnicas

### Segurança

- ⚠️ **Atenção:** A versão atual armazena senhas em texto plano. Para produção, implemente `password_hash()` e `password_verify()`.
- Todas as queries usam prepared statements (PDO) para prevenir SQL injection.
- Validação de campos obrigatórios implementada em todos os endpoints.

### Performance

- Índices criados nas principais chaves estrangeiras.
- Consultas otimizadas com uso de JOINs.
- Ordenação de transações por data para extrato rápido.

### Extensibilidade

- Arquitetura em camadas facilita adição de novos módulos.
- Pattern Repository permite troca de banco de dados sem alterar controllers.
- Preparado para implementação de Service Layer para lógica de negócios complexa.

---

**Versão da API:** 1.0  
**Última Atualização:** 18 de Novembro de 2025
