# 🧪 GUIA RÁPIDO DE TESTES - LUMIS APP

## ⚡ Configuração Inicial (5 minutos)

### 1. Configurar Banco de Dados

```bash
# Criar banco
mysql -u root -p
CREATE DATABASE lumis;
USE lumis;
exit;

# Importar schema
mysql -u root -p lumis < db/banco.sql
```

### 2. Ajustar Credenciais

Edite `api/config/database.php`:

```php
private $db_name = 'lumis';
private $username = 'root';  // seu usuário
private $password = '';      // sua senha
```

### 3. Iniciar Servidor

```bash
cd api
php -S localhost:8000
```

---

## 🎯 Testes Sequenciais (Copiar e Colar)

### TESTE 1: Criar Usuário

```sql
USE lumis;
INSERT INTO Usuario (nome, email, senha_hash)
VALUES ('Teste User', 'teste@lumis.com', 'senha123');
```

### TESTE 2: Login

```bash
curl -X POST http://localhost:8000/index.php/api/login -H "Content-Type: application/json" -d "{\"email\":\"teste@lumis.com\",\"senha\":\"senha123\"}"
```

### TESTE 3: Criar Conta

```bash
curl -X POST http://localhost:8000/index.php/api/contas -H "Content-Type: application/json" -d "{\"id_usuario\":1,\"nome\":\"Conta Corrente\",\"tipo_conta\":\"CORRENTE\",\"saldo_inicial\":1000.00,\"exibir_no_dashboard\":true}"
```

### TESTE 4: Listar Contas

```bash
curl "http://localhost:8000/index.php/api/contas?id_usuario=1"
```

### TESTE 5: Criar Categorias

```sql
INSERT INTO Categoria (id_usuario, nome, tipo, cor_hex) VALUES
(1, 'Alimentação', 'DESPESA', '#FF5733'),
(1, 'Salário', 'RECEITA', '#33FF57');
```

### TESTE 6: Criar Transação

```bash
curl -X POST http://localhost:8000/index.php/api/transacoes -H "Content-Type: application/json" -d "{\"id_usuario\":1,\"id_conta\":1,\"id_categoria\":1,\"valor\":150.00,\"tipo_movimentacao\":\"DESPESA\",\"data_transacao\":\"2025-11-18\",\"descricao\":\"Mercado\",\"efetuada\":true}"
```

### TESTE 7: Ver Extrato

```bash
curl "http://localhost:8000/index.php/api/extrato?id_usuario=1"
```

### TESTE 8: Criar Orçamento

```bash
curl -X POST http://localhost:8000/index.php/api/orcamento -H "Content-Type: application/json" -d "{\"id_usuario\":1,\"id_categoria\":1,\"valor_limite\":500.00,\"data_inicio\":\"2025-11-01\",\"data_fim\":\"2025-11-30\",\"ativo\":true}"
```

### TESTE 9: Listar Orçamentos

```bash
curl "http://localhost:8000/index.php/api/orcamento?id_usuario=1&mes_ano=2025-11"
```

### TESTE 10: Ver Relatório

```bash
curl "http://localhost:8000/index.php/api/relatorios/gastos-categoria?id_usuario=1&mes_ano=2025-11"
```

---

## ✅ Checklist de Validação

- [ ] Login retornou dados do usuário
- [ ] Conta foi criada (retornou id_conta)
- [ ] Listagem de contas exibiu a conta criada
- [ ] Transação foi registrada
- [ ] Extrato mostrou a transação
- [ ] Orçamento foi criado
- [ ] Relatório exibiu gastos por categoria

**Se todos os testes passaram: ✅ 100% FUNCIONAL!**

---

## 🐛 Troubleshooting

**Erro de conexão com banco:**

- Verifique credenciais em `config/database.php`
- Confirme que o MySQL está rodando

**Erro 404:**

- Verifique se está acessando `http://localhost:8000/index.php/api/...`
- Confirme que o servidor PHP está rodando

**Erro 500:**

- Verifique logs do PHP
- Confirme que as tabelas foram criadas corretamente
