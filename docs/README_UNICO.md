# LumisApp - Documentação Unificada

## Índice

1. Visão Geral do Projeto
2. Instalação e Configuração
3. Estrutura de Pastas (MVC)
4. Endpoints da API
5. Modelos de Dados
6. Guia de Testes Rápidos
7. Implementação Mobile
8. Identidade Visual
9. Relatório de Implementação
10. Atualizações e Roadmap
11. Referências e Suporte

---

## 1. Visão Geral do Projeto

LumisApp é um sistema de gestão financeira pessoal, com backend em PHP (MVC + Repository), API RESTful, frontend mobile planejado e documentação completa.

**Principais Funcionalidades (2025):**

- ✅ Integração mobile (acesso via navegador do celular na rede local)
- ✅ Pronto para empacotamento como app mobile (Cordova/Capacitor)
- ✅ Interface responsiva e moderna

---

- Navegador moderno

### Passos

1. Clone o projeto para `C:\xampp\htdocs\LumisApp`
2. **Habilite a extensão GD do PHP (necessária para exportação XLSX):**

- Abra o arquivo `C:\xampp\php\php.ini`.
- Procure por `;extension=gd` e remova o ponto e vírgula, ficando `extension=gd`.
- Salve o arquivo e reinicie o Apache pelo XAMPP.

3. Instale dependências:

```bash
cd C:\xampp\htdocs\LumisApp
composer install
```

- Se aparecer erro relacionado à extensão GD, repita o passo 2.

4. Configure o banco de dados:

- Crie o banco `lumis` no phpMyAdmin
- Importe `database/banco.sql` e (opcional) `database/dados_teste.sql`

5. Edite `app/config/database.php` com suas credenciais
6. Inicie o Apache pelo XAMPP
7. Acesse:

- Interface: `http://localhost/LumisApp/public/index.html`
- API: `http://localhost/LumisApp/public/api.php/api/...`

---

## 3. Estrutura de Pastas (MVC)

```
LumisApp/
│
├── 📁 app/                           # NÚCLEO DA APLICAÇÃO
│   ├── config/                       # Configurações
│   │   └── database.php             # Conexão com banco de dados
│   ├── controllers/                  # Controllers (MVC)
│   │   ├── AuthController.php       # Autenticação
│   │   ├── CategoriaController.php  # Gestão de categorias
│   │   ├── ContaController.php      # Gestão de contas
│   │   ├── DashboardController.php  # Dashboard/resumo
│   │   ├── MetaFinanceiraController.php  # Metas financeiras
│   │   ├── OrcamentoController.php  # Orçamentos
│   │   ├── RelatorioController.php  # Relatórios
│   │   ├── TransacaoController.php  # Transações
│   │   └── UserController.php       # Perfil de usuário
│   ├── models/                       # Models/Repositories (MVC)
│   │   ├── CategoriaRepository.php
│   │   ├── ContaRepository.php
│   │   ├── MetaFinanceiraRepository.php
│   │   ├── OrcamentoRepository.php
│   │   ├── RelatorioRepository.php
│   │   ├── TransacaoRepository.php
│   │   └── UserRepository.php
│   ├── routes/                       # Rotas da API
│   │   └── api.php                  # Definição centralizada de rotas
│   └── services/                     # Services (Lógica de Negócio)
│       ├── DashboardService.php
│       ├── MetaFinanceiraService.php
│       ├── OrcamentoService.php
│       └── SaldoService.php
│
├── 📁 public/                        # ARQUIVOS PÚBLICOS (Ponto de Entrada)
│   ├── api.php                      # Front Controller da API
│   ├── index.html                   # Interface principal
│   ├── .htaccess                    # Regras Apache
│   ├── css/                         # Estilos
│   │   └── styles.css              # Estilos principais
│   ├── js/                          # JavaScript
│   │   ├── app.js                  # Lógica principal
│   │   └── requests.js             # Requisições HTTP
│   └── assets/                      # Recursos estáticos (imagens, etc)
│
├── 📁 database/                      # BANCO DE DADOS
│   ├── banco.sql                    # Schema do banco
│   ├── dados_teste.sql              # Dados para testes
│   ├── Notas_Esquema_Financeiro.md  # Documentação do schema
│   └── README.md                    # Informações do banco
│
├── 📁 docs/                          # DOCUMENTAÇÃO
│   └── README_UNICO.md              # Documentação unificada
│
├── 📁 vendor/                        # DEPENDÊNCIAS (Composer)
│   └── ...                          # PhpSpreadsheet e outras libs
│
├── 📄 composer.json                  # Configuração do Composer
├── 📄 composer.lock                  # Lock de dependências
├── 📄 .gitignore                     # Arquivos ignorados pelo Git
└── 📄 prototipo.html                 # Protótipo inicial
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
├── api/              # Duplicado
├── view/             # Duplicado
├── db/               # Duplicado
└── *.md (na raiz)    # Desorganizado
```

**✅ AGORA (Estrutura MVC):**

```
LumisApp/
├── app/              # Lógica centralizada
├── public/           # Interface pública
├── database/         # SQL organizado
├── docs/             # Docs separadas
└── README_UNICO.md   # Limpo e claro
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

```bash
# Login
curl -X POST http://localhost/LumisApp/public/api.php/api/login \
  -H "Content-Type: application/json" \
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
```

### Checklist de Validação

- [ ] Login retorna dados do usuário
- [ ] Conta criada e listada
- [ ] Transação registrada e aparece no extrato
- [ ] Orçamento criado e listado
- [ ] Relatório exibe gastos por categoria

---

# 7. Implementação Mobile - Guia Completo

## 🎯 Visão Geral das Telas

Este guia detalha a implementação das 4 telas principais do app Lumis, com foco em UX intuitiva e design focado em clareza.

---

## 🏠 TELA A: DASHBOARD (Clareza)

### Objetivo

Fornecer visão geral rápida da saúde financeira do usuário em poucos segundos.

### API Endpoint

```
GET /api/dashboard?id_usuario={ID}&mes_ano={YYYY-MM}
```

### Response JSON

```json
{
  "saldo_total": 7800.0,
  "gastos_mes": 1700.5,
  "receitas_mes": 4300.0,
  "saldo_mes": 2599.5,
  "orcamentos": [
    {
      "id_orcamento": 1,
      "valor_limite": "600.00",
      "categoria": "Alimentação",
      "cor_hex": "#FF5733",
      "gasto_atual": "480.00",
      "percentual_gasto": "80.00"
    }
  ],
  "proximos_pagamentos": [
    {
      "id_transacao": 10,
      "valor": "45.90",
      "descricao": "Netflix",
      "data_transacao": "2025-11-22",
      "categoria": "Lazer"
    }
  ],
  "contas": [
    {
      "id_conta": 1,
      "nome": "Conta Corrente",
      "tipo_conta": "CORRENTE",
      "saldo_atual": "2500.00"
    }
  ]
}
```

### Componentes Principais

#### 1. Header com Saudação

```jsx
<Header gradient={true}>
  <Text size="small">Olá, {usuario.nome}</Text>
  <Text size="large" bold>
    ☀️ Boa tarde
  </Text>
</Header>
```

#### 2. Card de Saldo Total (Destaque Principal)

```jsx
<CardGradient colors={["#3B82F6", "#8B5CF6"]}>
  <Text size="small" color="white">
    SALDO TOTAL
  </Text>
  <Text size="huge" bold color="white">
    R$ {formatCurrency(dashboard.saldo_total)}
  </Text>
</CardGradient>
```

#### 3. Grid de Resumo Mensal

```jsx
<Grid columns={2} gap={16}>
  <Card>
    <Icon name="trending-up" color="#10B981" />
    <Text size="small">Receitas</Text>
    <Text size="large" color="#10B981">
      + R$ {formatCurrency(dashboard.receitas_mes)}
    </Text>
  </Card>

  <Card>
    <Icon name="trending-down" color="#EF4444" />
    <Text size="small">Despesas</Text>
    <Text size="large" color="#EF4444">
      - R$ {formatCurrency(dashboard.gastos_mes)}
    </Text>
  </Card>
</Grid>
```

#### 4. Seção de Orçamentos

```jsx
<Section title="📊 Orçamento do Mês">
  {dashboard.orcamentos.map((orcamento) => (
    <OrcamentoCard key={orcamento.id_orcamento}>
      <Text>{orcamento.categoria}</Text>
      <ProgressBar
        percentage={orcamento.percentual_gasto}
        color={getColorByPercentage(orcamento.percentual_gasto)}
      />
      <Text size="small">
        R$ {orcamento.gasto_atual} / R$ {orcamento.valor_limite}
      </Text>
    </OrcamentoCard>
  ))}
</Section>
```

#### 5. Próximos Pagamentos

```jsx
<Section title="📅 Próximos Pagamentos">
  {dashboard.proximos_pagamentos.map((pagamento) => (
    <ListItem key={pagamento.id_transacao}>
      <Text>{pagamento.descricao}</Text>
      <Text>R$ {formatCurrency(pagamento.valor)}</Text>
      <Text size="small" color="gray">
        {formatDate(pagamento.data_transacao)}
      </Text>
    </ListItem>
  ))}
</Section>
```

### Lógica de Cores para Barra de Progresso

```javascript
function getColorByPercentage(percentage) {
  if (percentage >= 0 && percentage <= 60) return "#3B82F6"; // Azul
  if (percentage > 60 && percentage <= 85) return "#F59E0B"; // Amarelo
  if (percentage > 85) return "#EF4444"; // Vermelho
}
```

---

## 📊 TELA B: EXTRATO/CONTAS (Detalhe)

### Objetivo

Permitir visualização detalhada de todas as movimentações e saldos das contas.

### APIs Necessárias

#### Listar Contas

```
GET /api/contas?id_usuario={ID}
```

#### Extrato Completo ou Filtrado

```
GET /api/extrato?id_usuario={ID}&id_conta={ID_CONTA}
```

### Componentes Principais

#### 1. Tabs de Contas

```jsx
<TabView>
  <Tab title="Todas">
    {contas.map((conta) => (
      <ContaCard
        key={conta.id_conta}
        onClick={() => filterByConta(conta.id_conta)}
      >
        <Icon name={getIconByConta(conta.tipo_conta)} />
        <Text>{conta.nome}</Text>
        <Text size="large" bold>
          R$ {conta.saldo_atual}
        </Text>
      </ContaCard>
    ))}
  </Tab>
</TabView>
```

#### 2. Filtros Rápidos

```jsx
<FilterBar>
  <FilterButton active={filter === "all"} onClick={() => setFilter("all")}>
    Todas
  </FilterButton>
  <FilterButton
    active={filter === "RECEITA"}
    onClick={() => setFilter("RECEITA")}
  >
    Receitas
  </FilterButton>
  <FilterButton
    active={filter === "DESPESA"}
    onClick={() => setFilter("DESPESA")}
  >
    Despesas
  </FilterButton>
</FilterBar>
```

#### 3. Lista de Transações

```jsx
<TransactionList>
  {extrato.map((transacao) => (
    <TransactionItem key={transacao.id_transacao}>
      <CategoryIcon icon={transacao.categoria} />
      <View>
        <Text bold>{transacao.descricao}</Text>
        <Text size="small" color="gray">
          {transacao.categoria}
        </Text>
        <Text size="small" color="gray">
          {formatDate(transacao.data_transacao)}
        </Text>
      </View>
      <Text
        size="large"
        bold
        color={
          transacao.tipo_movimentacao === "RECEITA" ? "#10B981" : "#EF4444"
        }
      >
        {transacao.tipo_movimentacao === "RECEITA" ? "+" : "-"}R${" "}
        {formatCurrency(transacao.valor)}
      </Text>
    </TransactionItem>
  ))}
</TransactionList>
```

---

## ➕ TELA C: REGISTRO RÁPIDO (Facilidade)

### Objetivo

Tornar o registro de transações o mais rápido e intuitivo possível (3-5 toques).

### API Endpoint

```
POST /api/transacoes
```

### Request Body

```json
{
  "id_usuario": 1,
  "id_conta": 1,
  "id_categoria": 5,
  "valor": 150.5,
  "tipo_movimentacao": "DESPESA",
  "data_transacao": "2025-11-18",
  "descricao": "Supermercado",
  "efetuada": true
}
```

### APIs de Suporte

#### Listar Categorias

```
GET /api/categorias?id_usuario={ID}&tipo={RECEITA|DESPESA}
```

### Componentes Principais

#### 1. Modal/Sheet de Registro

```jsx
<BottomSheet>
  <Header>
    <Text size="large" bold>
      Novo Registro
    </Text>
    <CloseButton />
  </Header>

  {/* Campo de Valor em DESTAQUE */}
  <ValueInput
    value={valor}
    onChange={setValor}
    placeholder="R$ 0,00"
    fontSize={36}
    autoFocus={true}
  />

  {/* Seletor de Tipo */}
  <TypeSelector>
    <TypeButton
      active={tipo === "DESPESA"}
      onClick={() => setTipo("DESPESA")}
      color="#EF4444"
    >
      Despesa
    </TypeButton>
    <TypeButton
      active={tipo === "RECEITA"}
      onClick={() => setTipo("RECEITA")}
      color="#10B981"
    >
      Receita
    </TypeButton>
    <TypeButton
      active={tipo === "TRANSFERENCIA"}
      onClick={() => setTipo("TRANSFERENCIA")}
      color="#3B82F6"
    >
      Transferência
    </TypeButton>
  </TypeSelector>

  {/* Seleção de Conta */}
  <Dropdown
    label="Conta"
    value={contaSelecionada}
    onChange={setContaSelecionada}
    options={contas}
  />

  {/* Seleção de Categoria */}
  <CategoryGrid>
    {categorias
      .filter((c) => c.tipo === tipo)
      .map((categoria) => (
        <CategoryButton
          key={categoria.id_categoria}
          active={categoriaSelecionada === categoria.id_categoria}
          onClick={() => setCategoriaSelecionada(categoria.id_categoria)}
          color={categoria.cor_hex}
        >
          <Icon name={categoria.icone} />
          <Text size="small">{categoria.nome}</Text>
        </CategoryButton>
      ))}
  </CategoryGrid>

  {/* Campos Opcionais */}
  <Input
    label="Descrição"
    value={descricao}
    onChange={setDescricao}
    placeholder="Ex: Supermercado"
  />
  <DatePicker label="Data" value={data} onChange={setData} />

  {/* Botão de Salvar */}
  <GradientButton onClick={handleSalvar}>Salvar Transação</GradientButton>
</BottomSheet>
```

#### 2. Validação e Feedback

```javascript
async function handleSalvar() {
  // Validação
  if (!valor || valor <= 0) {
    showToast("Informe um valor válido", "error");
    return;
  }

  if (!contaSelecionada) {
    showToast("Selecione uma conta", "error");
    return;
  }

  if (!categoriaSelecionada) {
    showToast("Selecione uma categoria", "error");
    return;
  }

  // Enviar para API
  try {
    await api.post("/transacoes", {
      id_usuario: usuario.id,
      id_conta: contaSelecionada,
      id_categoria: categoriaSelecionada,
      valor: parseFloat(valor),
      tipo_movimentacao: tipo,
      data_transacao: formatDateToSQL(data),
      descricao: descricao,
      efetuada: true,
    });

    showToast("Transação salva com sucesso!", "success");
    closeModal();
    refreshDashboard();
  } catch (error) {
    showToast("Erro ao salvar transação", "error");
  }
}
```

---

## 🎯 TELA D: ORÇAMENTO/METAS (Planejamento)

### Objetivo

Acompanhar progresso de orçamentos e metas financeiras com visualização clara.

### APIs Necessárias

#### Listar Orçamentos

```
GET /api/orcamento?id_usuario={ID}&mes_ano={YYYY-MM}
```

#### Listar Metas

```
GET /api/metas?id_usuario={ID}
```

#### Criar Meta

```
POST /api/metas
```

### Componentes Principais

#### 1. Seção de Orçamentos

```jsx
<Section title="💰 Orçamento Mensal">
  <MonthSelector value={mesAtual} onChange={setMesAtual} />

  {orcamentos.map((orcamento) => (
    <OrcamentoCard key={orcamento.id_orcamento}>
      <Header>
        <CategoryIcon color={orcamento.cor_hex} />
        <Text bold>{orcamento.categoria}</Text>
      </Header>

      <ValueRow>
        <Text size="large" bold>
          R$ {orcamento.gasto_atual}
        </Text>
        <Text size="small" color="gray">
          de R$ {orcamento.valor_limite}
        </Text>
      </ValueRow>

      <ProgressBar
        percentage={orcamento.percentual_gasto}
        color={getColorByPercentage(orcamento.percentual_gasto)}
        height={12}
        animated={true}
      />

      <Footer>
        <Text
          size="small"
          color={getColorByPercentage(orcamento.percentual_gasto)}
        >
          {orcamento.percentual_gasto >= 100
            ? `Excedeu em R$ ${orcamento.gasto_atual - orcamento.valor_limite}`
            : `Restam R$ ${orcamento.valor_limite - orcamento.gasto_atual}`}
        </Text>
      </Footer>
    </OrcamentoCard>
  ))}
</Section>
```

#### 2. Seção de Metas

```jsx
<Section title="⭐ Minhas Metas">
  {metas.map((meta) => (
    <MetaCard key={meta.id_meta} status={meta.status}>
      <Header>
        <Text bold size="large">
          {meta.nome}
        </Text>
        <StatusBadge status={meta.status} />
      </Header>

      <ProgressSection>
        <CircularProgress
          percentage={calcularProgressoMeta(meta)}
          color="#3B82F6"
          size={80}
        />

        <ValueColumn>
          <Text size="small" color="gray">
            Progresso
          </Text>
          <Text bold size="large">
            R$ {calcularValorAtual(meta)}
          </Text>
          <Text size="small">de R$ {meta.valor_alvo}</Text>
        </ValueColumn>
      </ProgressSection>

      <Footer>
        <Icon name="calendar" size={16} color="gray" />
        <Text size="small" color="gray">
          Meta: {formatDate(meta.data_alvo)}
        </Text>
      </Footer>
    </MetaCard>
  ))}

  <AddButton onClick={openNovaMetaModal}>+ Nova Meta</AddButton>
</Section>
```

---

## 🔄 Fluxo de Navegação

```
┌─────────────┐
│  Dashboard  │ ← Tela inicial (padrão)
└──────┬──────┘
       │
   ┌───┴────┬─────────┬──────────┐
   │        │         │          │
┌──▼───┐ ┌─▼────┐ ┌─▼──────┐ ┌─▼────────┐
│Extrato│ │Regis-│ │Orçamen.│ │  Perfil  │
│       │ │tro   │ │/Metas  │ │          │
└───────┘ └──────┘ └────────┘ └──────────┘
```

### Bottom Navigation Bar

```jsx
<BottomTabNavigator>
  <Tab icon="home" label="Início" route="/dashboard" />
  <Tab icon="list" label="Extrato" route="/extrato" />
  <Tab
    icon="plus-circle"
    label="Registrar"
    route="/registro"
    highlight={true}
  />
  <Tab icon="target" label="Orçamento" route="/orcamento" />
  <Tab icon="user" label="Perfil" route="/perfil" />
</BottomTabNavigator>
```

---

## ⚡ Otimizações de UX

### 1. Skeleton Screens

Exibir placeholders durante carregamento:

```jsx
{
  loading ? (
    <Skeleton>
      <SkeletonCard height={120} />
      <SkeletonText width="60%" />
      <SkeletonText width="40%" />
    </Skeleton>
  ) : (
    <DashboardContent data={dashboard} />
  );
}
```

### 2. Pull to Refresh

```jsx
<ScrollView
  refreshControl={
    <RefreshControl
      refreshing={refreshing}
      onRefresh={handleRefresh}
      colors={["#3B82F6", "#8B5CF6"]}
    />
  }
>
  {/* Conteúdo */}
</ScrollView>
```

### 3. Animações de Transição

```javascript
// React Navigation (React Native)
const screenOptions = {
  cardStyleInterpolator: CardStyleInterpolators.forHorizontalIOS,
  transitionSpec: {
    open: { animation: "timing", config: { duration: 300 } },
    close: { animation: "timing", config: { duration: 300 } },
  },
};
```

### 4. Feedback Tátil

```javascript
// Ao tocar em botões importantes
import { Haptics } from "expo-haptics";

function handlePress() {
  Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
  // ação do botão
}
```

---

## 📊 Métricas de Sucesso

### KPIs de UX

- **Tempo médio para registrar transação:** < 10 segundos
- **Taxa de conclusão de registro:** > 95%
- **Tempo de carregamento do dashboard:** < 2 segundos
- **Engajamento diário:** > 60% dos usuários ativos

---

**Versão:** 1.0  
**Data:** Novembro 2025  
**Projeto:** Lumis - Gestão Financeira Pessoal

---

## 8. Identidade Visual

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
```

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

## 9. Relatório de Implementação (Resumo)

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

## 10. Atualizações e Roadmap

**Versão Atual:** 2.5 (21/11/2025)

**Últimas Atualizações:**

### Versão 2.5 (21/11/2025)

### Versão 2.0 (19/11/2025)

**Próximas Etapas:**

---

## 11. Referências e Suporte

- [PHP MVC Architecture](https://www.php.net/manual/en/tutorial.php)
- [Repository Pattern](https://designpatternsphp.readthedocs.io/en/latest/More/Repository/README.html)
- [Front Controller Pattern](https://en.wikipedia.org/wiki/Front_controller)

**Desenvolvedor:** Gabriel Couto ([GitHub](https://github.com/GabrielCoutooo))

**Dúvidas ou problemas?** Abra uma issue no repositório ou consulte este arquivo.

---

**Status:** Backend Completo ✅ | Frontend Web Funcional ✅ | Documentação Atualizada ✅ | Mobile em Planejamento 📱
