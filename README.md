# LumisApp - Documentação Unificada

## Índice

1. Visão Geral do Projeto
2. Instalação e Configuração
3. Estrutura de Pastas (MVC + Mobile)
4. Endpoints da API
5. Modelos de Dados
6. Guia de Testes Rápidos
7. Identidade Visual
8. Configuração PWA e Mobile
9. Relatório de Implementação
10. Atualizações e Roadmap
11. Referências e Suporte

---

## 1. Visão Geral do Projeto

LumisApp é um sistema completo de gestão financeira pessoal, com backend em PHP (MVC + Repository), API RESTful, Progressive Web App (PWA) e aplicativo Android nativo via Capacitor.

**Principais Funcionalidades (2025):**

- ✅ **Progressive Web App (PWA)** - Instalável no celular via navegador
- ✅ **App Android Nativo** - Build completo com Capacitor
- ✅ **Interface Responsiva** - Adaptada para todos os tamanhos de tela
- ✅ **Modo Offline** - Service Worker para cache e funcionamento offline
- ✅ **API RESTful Completa** - Backend PHP com padrão MVC
- ✅ **Sistema de Autenticação** - Login/Cadastro seguros
- ✅ **Tema Claro/Escuro** - Alternância dinâmica de temas
- ✅ **Exportação de Dados** - XLSX e CSV
- ✅ **Gestão Completa** - Contas, Categorias, Orçamentos, Transações e Metas

---

- Navegador moderno

### Passos

1. Clone o projeto para `C:\xampp\htdocs\LumisApp`
2. Instale dependências:

### Pré-requisitos

#### Backend (Obrigatório)

- XAMPP (Apache + MySQL + PHP 8.2+)
- Composer
- Extensão `zip` habilitada no PHP

#### Mobile/PWA (Opcional - para build Android)

- Node.js 18+ e npm
- Android Studio (para build Android)
- JDK 17+

**Importante:**
Para exportação de dados (XLSX/CSV) e funcionamento do pacote PhpSpreadsheet, habilite a extensão `zip` no PHP:

1. Abra o arquivo `C:\xampp\php\php.ini`
2. Procure por `;extension=zip` e remova o ponto e vírgula, ficando `extension=zip`
3. Salve e reinicie o Apache pelo XAMPP
4. Só então rode `composer install`

   ```bash
   cd C:\xampp\htdocs\LumisApp
   composer install
   ```

5. Configure o banco de dados:

   - Crie o banco `lumis` no phpMyAdmin
   - Importe `database/banco.sql`
   - (Opcional) Importe `database/dados_teste.sql` para dados de exemplo

6. Edite `app/config/database.php` com suas credenciais MySQL

7. Inicie o Apache e MySQL pelo XAMPP

8. Acesse a aplicação:
   - **Interface Web:** `http://localhost/LumisApp/public/index.html`
   - **Login:** `http://localhost/LumisApp/public/login.html`
   - **Cadastro:** `http://localhost/LumisApp/public/cadastro.html`
   - **API:** `http://localhost/LumisApp/public/api.php/api/...`

#### 2. PWA (Progressive Web App)

O PWA já está configurado! Basta:

1. Acessar `http://localhost/LumisApp/public/index.html` no Chrome/Edge
   │ ├── controllers/ # Controllers (MVC)
   │ │ ├── AuthController.php # Autenticação
   │ │ ├── CategoriaController.php # Gestão de categorias
   │ │ ├── ContaController.php # Gestão de contas
   │ │ ├── DashboardController.php # Dashboard/resumo
   │ │ ├── MetaFinanceiraController.php # Metas financeiras
   │ │ ├── OrcamentoController.php # Orçamentos
   │ │ ├── RecorrenciaController.php # Despesas recorrentes
   │ │ ├── RelatorioController.php # Relatórios
   │ │ ├── TransacaoController.php # Transações
   │ │ └── UserController.php # Perfil de usuário
   │ ├── models/ # Models/Repositories (MVC)
   │ │ ├── CategoriaRepository.php
   │ │ ├── ContaRepository.php
   │ │ ├── MetaFinanceiraRepository.php
   │ │ ├── OrcamentoRepository.php
   │ │ ├── RelatorioRepository.php
   │ │ ├── TransacaoRepository.php
   │ │ └── UserRepository.php
   │ ├── routes/ # Rotas da API
   │ │ └── api.php # Definição centralizada de rotas
   │ ├── services/ # Services (Lógica de Negócio)
   │ │ ├── DashboardService.php
   │ │ ├── MetaFinanceiraService.php
   │ │ ├── OrcamentoService.php
   │ │ └── SaldoService.php
   │ └── config/ # Configurações
   │ └── database.php # Configuração do banco de dados
   │
   ├── 📁 public/ # ARQUIVOS PÚBLICOS (Ponto de Entrada)
   │ ├── api.php # Front Controller da API
   │ ├── index.html # Interface principal (Dashboard)
   │ ├── login.html # Tela de login
   │ ├── cadastro.html # Tela de cadastro
   │ ├── manifest.json # Configuração PWA
   │ ├── sw.js # Service Worker (cache offline)
   │ ├── .htaccess # Regras Apache
   │ ├── css/ # Estilos
   │ │ ├── styles.css # Estilos principais do dashboard
   │ │ ├── login.css # Estilos da tela de login
   │ │ └── cadastro.css # Estilos da tela de cadastro
   │ ├── js/ # JavaScript
   │ │ ├── app.js # Lógica principal do dashboard
   ├── 📁 docs/ # DOCUMENTAÇÃO
   │ ├── README_UNICO.md # Documentação unificada (este arquivo)
   │ └── PWA_SETUP.md # Guia de configuração PWA e publicação
   │
   ├── 📁 android/ # PROJETO ANDROID (Capacitor)
   │ ├── app/ # Código do aplicativo Android
   │ │ ├── build.gradle # Configurações de build
   │ │ ├── src/ # Código-fonte Android
   │ │ └── build/ # Arquivos compilados (APK/AAB)
   │ ├── gradle/ # Sistema de build Gradle
   │ └── capacitor.settings.gradle # Configurações Capacitor
   │
   ├── 📁 vendor/ # DEPENDÊNCIAS PHP (Composer)
   │ ├── phpoffice/phpspreadsheet/ # Exportação XLSX
   │ └── ... # Outras libs PHP
   │
   ├── 📁 node_modules/ # DEPENDÊNCIAS NODE (npm)
   │ ├── @capacitor/core/ # Core do Capacitor
   │ ├── @capacitor/android/ # Plugin Android
   │ └── ... # Outras libs Node
   │
   ├── 📄 composer.json # Dependências PHP
   ├── 📄 composer.lock # Lock de dependências PHP
   ├── 📄 package.json # Dependências Node/Capacitor
   ├── 📄 package-lock.json # Lock de dependências Node
   ├── 📄 capacitor.config.json # Configuração do Capacitor
   ├── 📄 .gitignore # Arquivos ignorados pelo Git
   └── 📄 .htaccess # Configurações Apache (raiz)

```├── OrcamentoController.php # Orçamentos
│ │ ├── RelatorioController.php # Relatórios
│ │ ├── TransacaoController.php # Transações
│ │ └── UserController.php # Perfil de usuário
│ ├── models/ # Models/Repositories (MVC)
│ │ ├── CategoriaRepository.php
│ │ ├── ContaRepository.php
│ │ ├── MetaFinanceiraRepository.php
│ │ ├── OrcamentoRepository.php
│ │ ├── RelatorioRepository.php
│ │ ├── TransacaoRepository.php
│ │ └── UserRepository.php
│ ├── routes/ # Rotas da API
│ │ └── api.php # Definição centralizada de rotas
│ └── services/ # Services (Lógica de Negócio)
│ ├── DashboardService.php
│ ├── MetaFinanceiraService.php
│ ├── OrcamentoService.php
│ └── SaldoService.php
│
├── 📁 public/ # ARQUIVOS PÚBLICOS (Ponto de Entrada)
│ ├── api.php # Front Controller da API
│ ├── index.html # Interface principal
│ ├── .htaccess # Regras Apache
│ ├── css/ # Estilos
│ │ └── styles.css # Estilos principais
│ ├── js/ # JavaScript
│ │ ├── app.js # Lógica principal
│ │ └── requests.js # Requisições HTTP
│ └── assets/ # Recursos estáticos (imagens, etc)
│
├── 📁 database/ # BANCO DE DADOS
│ ├── banco.sql # Schema do banco
│ ├── dados_teste.sql # Dados para testes
│ ├── Notas_Esquema_Financeiro.md # Documentação do schema
│ └── README.md # Informações do banco
│
├── 📁 docs/ # DOCUMENTAÇÃO
│ └── README_UNICO.md # Documentação unificada
│
├── 📁 vendor/ # DEPENDÊNCIAS (Composer)
│ └── ... # PhpSpreadsheet e outras libs
│
├── 📄 composer.json # Configuração do Composer
├── 📄 composer.lock # Lock de dependências
├── 📄 .gitignore # Arquivos ignorados pelo Git
└── 📄 prototipo.html # Protótipo inicial

```

---

### Benefícios da Nova Estrutura

- **Organização:** Separação clara entre lógica (`app/`) e interface (`public/`), MVC bem definido, rotas centralizadas, documentação organizada.
- **Segurança:** Apenas `public/` é acessível via web, arquivos sensíveis protegidos, front controller único.
- **Escalabilidade:** Fácil adicionar novos controllers/models, padrão reconhecido, estrutura preparada para crescimento.
- **Manutenção:** Código organizado, fácil de localizar, facilita onboarding de novos desenvolvedores.

---

### Comparação: Antes vs Depois

**❌ ANTES (Estrutura Antiga):**

```

LumisApp/
├── api/ # Duplicado
├── view/ # Duplicado
├── db/ # Duplicado
└── \*.md (na raiz) # Desorganizado

```

**✅ AGORA (Estrutura MVC):**

```

LumisApp/
├── app/ # Lógica centralizada
├── public/ # Interface pública
├── database/ # SQL organizado
├── docs/ # Docs separadas
└── README_UNICO.md # Limpo e claro

```

---

### Próximos Passos

1. ✅ Testar todas as funcionalidades
2. ✅ Validar exportação XLSX
3. ✅ Verificar todas as rotas
4. ✅ Implementar filtros dinâmicos por mês
5. ✅ Sistema de orçamentos fixos por categoria
6. ✅ Gestão completa de contas (CRUD)
7. ✅ Confirmações em ações críticas
8. ✅ Formatação de datas em português
9. 📝 Adicionar testes automatizados (PHPUnit)
10. 🔐 Implementar autenticação JWT
11. 🎨 Melhorar interface (se necessário)

---

---

## 4. Endpoints da API

### Principais Endpoints Resumidos

| Método | Endpoint                 | Descrição             |
| ------ | ------------------------ | --------------------- |
| POST   | `/api/login`             | Autenticação          |
| GET    | `/api/dashboard`         | Dashboard resumo      |
| GET    | `/api/contas`            | Listar contas         |
| POST   | `/api/contas`            | Criar conta           |
| GET    | `/api/transacoes`        | Listar transações     |
| POST   | `/api/transacoes`        | Criar transação       |
| GET    | `/api/categorias`        | Listar categorias     |
| GET    | `/api/relatorios/mensal` | Relatório mensal      |
| GET    | `/api/user/perfil`       | Perfil do usuário     |
| GET    | `/api/user/exportar`     | Exportar dados (XLSX) |

Consulte a seção abaixo para detalhes completos de cada endpoint e exemplos de uso.

### Autenticação

- `POST /api/login` — Autentica usuário

### Contas

- `GET /api/contas?id_usuario=ID`
- `POST /api/contas`

### Transações

- `POST /api/transacoes` — Criar transação
- `GET /api/extrato?id_usuario=ID[&id_conta=ID]&data_inicio=YYYY-MM-DD&data_fim=YYYY-MM-DD` — Listar extrato com filtros
- `PUT /api/transacoes` — Atualizar transação (marcar como efetuada)
- `GET /api/despesas?id_usuario=ID` — Listar todas as despesas

### Orçamentos

- `GET /api/orcamento?id_usuario=ID&mes_ano=YYYY-MM` — Listar orçamentos do mês
- `POST /api/orcamento` — Criar orçamento
- `PUT /api/orcamento` — Atualizar orçamento existente
- `DELETE /api/orcamento` — Excluir orçamento

**Novidade:** Orçamentos agora são exibidos como categorias fixas na interface, permitindo edição rápida por mês.

### Relatórios

- `GET /api/relatorios/gastos-categoria?id_usuario=ID&mes_ano=YYYY-MM`

### Dashboard

- `GET /api/dashboard?id_usuario=ID&mes_ano=YYYY-MM` — Dashboard completo

**Recursos Dinâmicos:**

- Saldo total calculado por mês
- Receitas e despesas do mês atual
- Orçamentos com percentual de uso
- Próximos pagamentos filtrados por mês
- Aviso de transações pendentes no mês

### Categorias

- `GET /api/categorias?id_usuario=ID[&tipo=RECEITA|DESPESA]` — Listar categorias
- `POST /api/categorias` — Criar categoria
- `PUT /api/categorias` — Atualizar categoria
- `DELETE /api/categorias` — Excluir categoria

**Gestão Completa:** Interface permite criar, editar e excluir categorias personalizadas com cores e ícones.

### Metas Financeiras

- `GET /api/metas?id_usuario=ID`
- `POST /api/metas`
- `PUT /api/metas?id_meta=ID`

### Perfil do Usuário

- `GET /api/user/perfil?id_usuario=ID`
- `PUT /api/user/perfil`
- `PUT /api/user/senha`
- `PUT /api/user/configuracoes`
- `DELETE /api/user/conta`
- `GET /api/user/exportar?id_usuario=ID&formato=csv|xlsx`

#### Exemplos de uso e payloads estão detalhados nas seções seguintes.

---

## 5. Modelos de Dados (Principais)

### Usuário

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

### Transação

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

### Orçamento

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

## 6. Guia de Testes Rápidos

### Configuração Inicial

```bash
# Criar banco
mysql -u root -p
CREATE DATABASE lumis;
USE lumis;
exit;
# Importar schema
mysql -u root -p lumis < database/banco.sql
# Popular com dados de teste (opcional)
mysql -u root -p lumis < database/dados_teste.sql
```

### Testes de API

````bash
---

## 8. Configuração PWA e Mobile

### Progressive Web App (PWA)

O LumisApp é um PWA completo com as seguintes funcionalidades:

**Arquivos PWA:**
- `public/manifest.json` - Metadados do app (nome, ícones, cores)
- `public/sw.js` - Service Worker para cache offline
- `public/icons/` - Ícones em várias resoluções (72px a 512px)

**Recursos PWA Implementados:**
- ✅ Instalável na tela inicial (Android/iOS/Desktop)
- ✅ Funciona offline (cache de páginas e assets)
- ✅ Splash screen personalizada
- ✅ Ícone personalizado
- ✅ Tema de cores consistente
- ✅ Modo standalone (sem barra do navegador)

**Como Testar:**
1. Acesse o app via HTTPS ou localhost
2. No Chrome/Edge: ícone de instalação (+) aparecerá na barra de endereço
3. Clique para instalar na tela inicial
4. Abra como app independente

### Build Android com Capacitor

O projeto usa **Capacitor 7** para gerar aplicativo Android nativo.

**Estrutura Capacitor:**
**Telas Implementadas:**

1. **Login/Cadastro:**
   - Design minimalista e moderno
   - Gradientes luminosos
   - Validação em tempo real
   - Mensagens de erro/sucesso

2. **Dashboard:**
   - Saudação dinâmica (Bom dia/Boa tarde/Boa noite)
   - Navegação de mês (anterior/próximo)
   - Saldo total com toggle de privacidade
   - Cards de receitas/despesas
   - Orçamentos do mês com barra de progresso
   - Próximos pagamentos

3. **Extrato:**
   - Lista de transações por mês
   - Filtros (Todas/Receitas/Despesas)
   - Ícones por categoria
**Destaques Técnicos:**

- ✅ **Arquitetura MVC** - Controllers, Models, Services separados
- ✅ **Repository Pattern** - Acesso a dados isolado
- ✅ **Service Layer** - Lógica de negócio centralizada
- ✅ **API RESTful** - Endpoints padronizados
- ✅ **Prepared Statements** - Proteção contra SQL Injection
- ✅ **Exportação de Dados** - CSV e XLSX com PhpSpreadsheet
- ✅ **PWA Completo** - Service Worker, manifest, offline-first
- ✅ **Capacitor** - Build Android nativo
- ✅ **Validação** - Backend e frontend

**Destaques de UX:**

- ✅ **Interface Intuitiva** - Navegação por abas inferior
- ✅ **Tema Claro/Escuro** - Alternância dinâmica com persistência
- ✅ **Responsividade Total** - Mobile-first, adaptado a todas as telas
- ✅ **Filtros por Mês** - Navegação temporal intuitiva
- ✅ **Feedback Visual** - Toasts para ações importantes
- ✅ **Confirmações** - Em ações críticas (exclusões)
- ✅ **CRUD Completo** - Contas, Categorias, Transações, Orçamentos
- ✅ **Formatação BR** - Valores monetários e datas em português
- ✅ **Privacidade** - Toggle para ocultar saldos
- ✅ **Offline-First** - Funciona sem internet (PWA)

**Destaques Mobile:**
**Últimas Atualizações:**

### Versão 3.0 (24/11/2025) - Mobile & PWA
- ✅ Progressive Web App completo (manifest.json, service worker)
- ✅ Build Android com Capacitor 7
- ✅ Tema claro/escuro com persistência
- ✅ Responsividade total (mobile-first)
- ✅ Suporte a safe-area (notch/home indicator)
- ✅ Media queries para telas pequenas (340px+)
- ✅ Navegação inferior otimizada
- ✅ Telas de login/cadastro separadas
- ✅ Service Worker para cache offline
- ✅ Ícones PWA em múltiplas resoluções

### Versão 2.5 (21/11/2025) - Gestão Completa
- ✅ CRUD de contas bancárias
- ✅ CRUD de categorias personalizadas
- ✅ Gestão de despesas recorrentes
- ✅ Sistema de orçamentos por categoria
- ✅ Navegação dinâmica por mês
- ✅ Exportação de dados (XLSX/CSV)
- ✅ Confirmações em ações críticas
- ✅ Formatação de valores/datas em PT-BR

### Versão 2.0 (19/11/2025) - Estrutura MVC
- ✅ Reestruturação completa em MVC
- ✅ Repository Pattern implementado
- ✅ Service Layer para lógica de negócio
- ✅ API RESTful padronizada
- ✅ Front Controller único
- ✅ Segurança aprimorada (prepared statements)

**Próximas Etapas (Roadmap):**

### Versão 3.1 (Previsto: Dezembro 2025)
- 📝 Autenticação JWT na API
- 📝 Refresh tokens
- 📝 Rate limiting
- 📝 Testes automatizados (PHPUnit)
- 📝 CI/CD com GitHub Actions

### Versão 3.2 (Previsto: Janeiro 2026)
- 📝 Notificações push (PWA)
- 📝 Compartilhamento de contas (multi-usuário)
- 📝 Anexos em transações (comprovantes)
- 📝 Reconhecimento de voz para registro rápido
- 📝 Gráficos interativos (Chart.js)

### Versão 4.0 (Previsto: Fevereiro 2026)
- 📝 Sincronização em nuvem (Firebase/Supabase)
- 📝 App iOS (via Capacitor)
- 📝 Machine Learning para previsões
- 📝 Integração bancária (Open Banking)
- 📝 Modo família (múltiplos usuários)

---

## 12. Referências e Suportep

**Versão Atual:** 3.0 (24/11/2025)
**Responsividade:**
- Design mobile-first
- Adaptado para telas pequenas (340px+)
- Media queries para diferentes resoluções
- Navegação inferior otimizada
- Suporte a safe-area (notch/home indicator)

---

## 10. Relatório de Implementação (Resumo)
**Comandos Úteis:**

```bash
# Sincronizar código web com Android
npx cap sync android

# Abrir no Android Studio
npx cap open android

# Copiar assets atualizados
npx cap copy android

# Atualizar plugins Capacitor
npm install @capacitor/core@latest @capacitor/android@latest
npx cap sync
````

**Gerar APK/AAB:**

1. `npx cap open android`
2. No Android Studio: `Build > Build Bundle(s) / APK(s)`
3. Escolher entre:
   - **APK** - Para testar/instalar diretamente
   - **AAB** - Para publicar na Play Store

**Publicação na Play Store:**

Consulte `docs/PWA_SETUP.md` para instruções detalhadas sobre:

- Gerar APK assinado
- Criar listing na Play Store
- Screenshots e assets necessários
- Processo de revisão

### Configuração de URL da API

**Desenvolvimento Local:**

```javascript
// public/js/app.js
const BASE_API = "http://localhost/LumisApp/public";
```

**Produção (servidor remoto):**

```javascript
// public/js/app.js
const BASE_API = "https://api.lumisapp.me";
```

**Importante:** Ao fazer build para produção, atualize a `BASE_API` para apontar para seu servidor real.

---

## 9. Identidade Visuallication/json" \

-d '{"email":"teste@lumis.com","senha":"senha123"}'

# Listar contas

curl "http://localhost/LumisApp/public/api.php/api/contas?id_usuario=1"

# Criar transação

curl -X POST http://localhost/LumisApp/public/api.php/api/transacoes \
 -H "Content-Type: application/json" \
 -d '{"id_usuario":1,"id_conta":1,"id_categoria":1,"valor":150.00,"tipo_movimentacao":"DESPESA","data_transacao":"2025-11-18","descricao":"Mercado","efetuada":true}'

# Ver extrato

curl "http://localhost/LumisApp/public/api.php/api/extrato?id_usuario=1"

# Criar orçamento

curl -X POST http://localhost/LumisApp/public/api.php/api/orcamento \
 -H "Content-Type: application/json" \
 -d '{"id_usuario":1,"id_categoria":1,"valor_limite":500.00,"data_inicio":"2025-11-01","data_fim":"2025-11-30","ativo":true}'

# Relatório de gastos

curl "http://localhost/LumisApp/public/api.php/api/relatorios/gastos-categoria?id_usuario=1&mes_ano=2025-11"

````

### Checklist de Validação

- [ ] Login retorna dados do usuário
- [ ] Conta criada e listada
- [ ] Transação registrada e aparece no extrato
- [ ] Orçamento criado e listado
- [ ] Relatório exibe gastos por categoria

---

## 7. Identidade Visual

**Paleta de Cores (Foco em Clareza):**

- 🔵 Índigo Principal: #4F46E5 (Indigo-600)
- 💜 Roxo Profundo: #7C3AED (Purple-600)
- 🌟 Índigo Claro: #6366F1 (Indigo-500)
- 💎 Violeta: #8B5CF6 (Violet-500)
- ✅ Verde (Receita): #10B981 (Emerald-500)
- ❌ Vermelho (Despesa): #EF4444 (Red-500)
- ⚠️ Amarelo (Atenção): #F59E0B (Amber-500)

**Gradiente Principal (Clareza Luminosa):**

```css
background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
````

**Conceito Visual:**
O esquema de cores frias (azuis e roxos) representa clareza, confiança e tranquilidade financeira. Os gradientes remetem a um feixe de luz que "acende" a clareza sobre suas finanças.

**Componentes de UI:**

- Botões com gradientes luminosos
- Cards com sombras suaves
- Gráficos e barras de progresso em tons frios
- Tipografia hierárquica clara
- Layout responsivo mobile-first
- Ícones que remetem a luz e clareza (💡✨🌟)

**Wireframes:**

- Dashboard, Extrato, Registro, Orçamento/Metas, Perfil

---

## 8. Relatório de Implementação (Resumo)

O backend foi implementado em PHP seguindo MVC, com controllers, repositories, services e rotas centralizadas. Todas as funcionalidades principais estão cobertas, com prepared statements para segurança, respostas JSON padronizadas e documentação detalhada.

**Destaques Técnicos:**

- ✅ Service Layer para lógica de negócio
- ✅ Repository Pattern para acesso a dados
- ✅ Testes completos de todos os endpoints
- ✅ Exportação de dados (CSV/XLSX)
- ✅ Filtragem dinâmica por intervalo de datas
- ✅ Sistema de alertas e notificações
- ✅ Validação de dados no backend e frontend

**Destaques de UX:**

- ✅ Interface intuitiva com navegação por abas
- ✅ Filtros persistentes ao navegar entre meses
- ✅ Confirmações em ações críticas
- ✅ Feedback visual imediato (toasts)
- ✅ Orçamentos com edição rápida via prompt
- ✅ Categorias fixas sempre visíveis
- ✅ Formatação de valores e datas em português brasileiro

---

## 9. Atualizações e Roadmap

**Versão Atual:** 2.5 (21/11/2025)

**Últimas Atualizações:**

### Versão 2.5 (21/11/2025)

### Versão 2.0 (19/11/2025)

**Próximas Etapas:**

---

## 10. Referências e Suporte

**Tecnologias e Frameworks:**

- [PHP MVC Architecture](https://www.php.net/manual/en/tutorial.php)
- [Repository Pattern](https://designpatternsphp.readthedocs.io/en/latest/More/Repository/README.html)
- [Front Controller Pattern](https://en.wikipedia.org/wiki/Front_controller)
- [Progressive Web Apps (PWA)](https://web.dev/progressive-web-apps/)
- [Capacitor - Build Native Apps](https://capacitorjs.com/)
- [PhpSpreadsheet - Export XLSX](https://phpspreadsheet.readthedocs.io/)

**Documentação do Projeto:**

- `docs/README_UNICO.md` - Este arquivo (documentação completa)
- `docs/PWA_SETUP.md` - Guia de configuração PWA e build Android
- `database/README.md` - Documentação do banco de dados
- `database/Notas_Esquema_Financeiro.md` - Schema e relacionamentos

**Desenvolvedor:** Gabriel Couto  
**GitHub:** [github.com/GabrielCoutooo/LumisApp](https://github.com/GabrielCoutooo/LumisApp)  
**Versão:** 3.0 (24/11/2025)

**Dúvidas ou problemas?**

- Abra uma issue no repositório
- Consulte a documentação em `docs/`
- Revise os exemplos em `database/dados_teste.sql`

---

**© 2025 LumisApp - Gestão Financeira Inteligente 💡**
