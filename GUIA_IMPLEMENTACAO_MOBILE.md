# 📱 GUIA DE IMPLEMENTAÇÃO MOBILE - LUMIS APP

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
        {transacao.tipo_movimentacao === "RECEITA" ? "+" : "-"}
        R$ {formatCurrency(transacao.valor)}
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
    highlight={true} // Botão em destaque com gradiente
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
