# Lumis - Sistema de Gestão Financeira

## 📁 Estrutura do Projeto

```
LumisApp/
├── view/                    # Frontend (MVC - View Layer)
│   ├── index.html          # Página principal
│   ├── css/
│   │   └── styles.css      # Estilos CSS
│   └── js/
│       └── app.js          # Lógica JavaScript
├── api/                     # Backend (MVC - Controller + Service)
│   ├── index.php           # Router principal
│   ├── config/
│   │   └── database.php    # Configuração do banco
│   ├── controllers/        # Controladores da API
│   ├── repositories/       # Camada de acesso a dados
│   └── services/           # Lógica de negócios
└── db/                      # Scripts SQL
```

## 🚀 Como Acessar

### Desenvolvimento Local (XAMPP)

1. Acesse: `http://localhost/LumisApp/view/`
2. O sistema carregará automaticamente o dashboard

### Servidor PHP Built-in

```bash
cd c:\xampp\htdocs\LumisApp\view
php -S localhost:8000
```

Acesse: `http://localhost:8000`

## ✨ Novas Funcionalidades Implementadas

### 1. Tela de Registro de Transações

- **Input de valor grande e centralizado** para entrada rápida
- **Toggle entre 3 tipos**: Despesa, Receita, Transferência
- **Seleção dinâmica de categorias** baseada no tipo
- **Campo de transferência** exibido apenas quando necessário
- **Validação em tempo real** com notificações toast

### 2. Sistema de Privacidade

- **Toggle de visibilidade do saldo** (ícone 👁️/🔒)
- Oculta valores sensíveis com `••••••`
- Estado mantido durante a sessão

### 3. Notificações Toast Aprimoradas

- **4 tipos**: success (✅), warning (⚠️), danger (🚨), info (ℹ️)
- **Auto-fechamento configurável** por duração
- **Animações suaves** de entrada/saída
- **Botão de fechar manual**

### 4. Integração com Service Layer Backend

- **Alertas de orçamento** após criar despesa
- **Notificação de meta concluída** após contribuição
- **Recálculo automático de saldo** pós-transação

## 🎨 Arquitetura Frontend

### Separação de Responsabilidades

**index.html** - Estrutura e Markup

- Telas: Dashboard, Extrato, Registrar, Orçamento
- Modais: Orçamento (criar/editar)
- Navegação bottom bar

**styles.css** - Estilos e Design System

- Mobile-first (max-width: 428px)
- Gradient cards e componentes modernos
- Animações e transições
- Sistema de cores: Blue (#3B82F6), Purple (#8B5CF6), Green (#10B981), Red (#EF4444)

**app.js** - Lógica e Interações

- Consumo da API REST
- Gerenciamento de estado (transações, orçamentos, categorias)
- Notificações e validações
- Navegação entre telas

## 📡 Integração com API

### Endpoints Utilizados

```javascript
// Dashboard
GET /api/dashboard?id_usuario=1&mes_ano=2025-11

// Extrato
GET /api/extrato?id_usuario=1

// Orçamentos
GET /api/orcamento?id_usuario=1&mes_ano=2025-11
POST /api/orcamento
PUT /api/orcamento

// Transações
POST /api/transacoes
GET /api/categorias?id_usuario=1&tipo=DESPESA
GET /api/contas?id_usuario=1
```

### Resposta com Alertas (Exemplo)

```json
{
  "success": true,
  "id_transacao": 123,
  "saldo_atual_conta": 1500.0,
  "alerta_orcamento": {
    "tipo": "ESTOURO_ORCAMENTO",
    "mensagem": "Atenção! O orçamento da categoria Alimentação foi estourado em R$ 150,00"
  },
  "alerta_meta": {
    "tipo": "META_CONCLUIDA",
    "mensagem": "🎉 Parabéns! Você atingiu sua meta 'Viagem para a Europa'!"
  }
}
```

## 🔧 Configuração

### Alterar URL da API

Edite o arquivo `view/js/app.js`:

```javascript
const BASE_API = "http://localhost/LumisApp/api/index.php/api";
const ID_USUARIO = 1;
const MES_ANO = "2025-11";
```

## 📱 Funcionalidades por Tela

### Dashboard

- Saldo total com toggle de privacidade
- Resumo de receitas e despesas do mês
- Cards de orçamento com progresso visual
- Próximos pagamentos pendentes

### Extrato

- Listagem de todas as transações
- Filtros: Todas, Receitas, Despesas
- Ícones e cores diferenciadas por tipo

### Registrar

- Input de valor em destaque (48px)
- 3 botões de tipo com cores semânticas
- Formulário adaptativo (categoria vs transferência)
- Validação de campos obrigatórios
- Notificação de sucesso + alertas de orçamento/meta

### Orçamento

- Lista de orçamentos com progresso %
- Cores dinâmicas: Verde (OK), Amarelo (Alerta), Vermelho (Estourado)
- Modal para criar/editar
- Status calculado pelo backend (via OrcamentoService)

## 🎯 Próximas Melhorias

- [ ] Tela de Perfil completa
- [ ] Tela de Metas Financeiras
- [ ] Gráficos de evolução (Chart.js)
- [ ] Filtro de período no extrato
- [ ] Export de relatórios (PDF/CSV)
- [ ] PWA (Progressive Web App)
- [ ] Dark mode
- [ ] Autenticação JWT

## 📄 Licença

Projeto acadêmico - Lumis Financial Management System
