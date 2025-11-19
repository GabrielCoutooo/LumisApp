# 💡 LumisApp

> **Lumis: A luz que faltava em sua vida financeira**

Aplicativo mobile de gerenciamento financeiro pessoal com API RESTful em PHP.

---

## 🚀 Quick Start

### 1️⃣ Configurar Banco de Dados

```bash
mysql -u root -p
CREATE DATABASE lumis;
USE lumis;
exit;

# Importar schema
mysql -u root -p lumis < db/banco.sql

# Popular com dados de teste (opcional)
mysql -u root -p lumis < db/dados_teste.sql
```

### 2️⃣ Configurar API

Edite `api/config/database.php` com suas credenciais:

```php
private $db_name = 'lumis';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 3️⃣ Iniciar Servidor

```bash
cd api
php -S localhost:8000
```

### 4️⃣ Testar API

```bash
curl "http://localhost:8000/index.php/api/contas?id_usuario=1"
```

---

## 📁 Estrutura do Projeto

```
LumisApp/
├── api/                          # Backend PHP (API RESTful)
│   ├── config/                   # Configurações
│   ├── controllers/              # Controladores (MVC)
│   ├── repositories/             # Acesso a dados
│   ├── models/                   # Modelos de dados
│   ├── services/                 # Lógica de negócios
│   └── index.php                 # Ponto de entrada
├── db/                           # Banco de dados
│   ├── banco.sql                 # Schema do banco
│   └── dados_teste.sql           # Dados para testes
├── RELATORIO_IMPLEMENTACAO.md    # Documentação completa
├── GUIA_TESTES_RAPIDO.md         # Testes rápidos
├── API_DOCUMENTATION.md          # Documentação da API
└── Lumis_API_Postman_Collection.json  # Coleção Postman

```

---

## 🔌 Endpoints Disponíveis

| Método | Endpoint                           | Descrição           |
| ------ | ---------------------------------- | ------------------- |
| POST   | `/api/login`                       | Autenticação        |
| GET    | `/api/contas`                      | Listar contas       |
| POST   | `/api/contas`                      | Criar conta         |
| POST   | `/api/transacoes`                  | Registrar transação |
| GET    | `/api/extrato`                     | Ver extrato         |
| GET    | `/api/orcamento`                   | Listar orçamentos   |
| POST   | `/api/orcamento`                   | Criar orçamento     |
| GET    | `/api/relatorios/gastos-categoria` | Relatório de gastos |

---

## 📚 Documentação

- **[Relatório de Implementação](RELATORIO_IMPLEMENTACAO.md)** - Documentação técnica completa
- **[Guia de Testes](GUIA_TESTES_RAPIDO.md)** - Testes passo a passo
- **[API Documentation](API_DOCUMENTATION.md)** - Referência da API
- **[Postman Collection](Lumis_API_Postman_Collection.json)** - Importar no Postman

---

## 🛠️ Tecnologias

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL/PostgreSQL
- **Arquitetura:** MVC + Repository Pattern
- **API:** RESTful JSON

---

## ✅ Status do Projeto

- [x] Estrutura de camadas (MVC)
- [x] Módulo de Autenticação
- [x] Módulo de Contas (CRUD)
- [x] Módulo de Transações
- [x] Módulo de Orçamentos
- [x] Módulo de Relatórios
- [x] Documentação completa
- [x] Testes unitários (scripts)
- [ ] Autenticação JWT
- [ ] Middleware de segurança
- [ ] Frontend Mobile (em desenvolvimento)

---

## 🧪 Testes

### Teste Rápido

```bash
# Login
curl -X POST http://localhost:8000/index.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teste@lumis.com","senha":"senha123"}'

# Listar contas
curl "http://localhost:8000/index.php/api/contas?id_usuario=1"
```

Para testes completos, consulte o [Guia de Testes](GUIA_TESTES_RAPIDO.md).

---

## 👨‍💻 Desenvolvedor

**Gabriel Couto**  
GitHub: [@GabrielCoutooo](https://github.com/GabrielCoutooo)

---

## 📄 Licença

Este projeto está em desenvolvimento para fins educacionais e pessoais.

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Para mudanças importantes, abra uma issue primeiro para discutir o que você gostaria de alterar.

---

## 📞 Suporte

Para dúvidas ou problemas, consulte a [documentação](RELATORIO_IMPLEMENTACAO.md) ou abra uma issue no repositório
