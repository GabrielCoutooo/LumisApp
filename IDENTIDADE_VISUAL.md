# 🎨 GUIA DE IDENTIDADE VISUAL - LUMIS APP

## 💡 Conceito Central

**"A luz que faltava em sua vida financeira"**

O Lumis representa clareza, iluminação e transparência financeira. O design visual transmite a ideia de que o app "acende uma luz" sobre as finanças do usuário, revelando insights e tornando complexo em simples.

---

## 🌈 Paleta de Cores

### Cores Primárias (Frias - Clareza e Confiança)

```
Azul Principal (Primary)
HEX: #3B82F6
RGB: (59, 130, 246)
USO: Botões principais, headers, elementos de destaque
```

```
Roxo Profundo (Secondary)
HEX: #8B5CF6
RGB: (139, 92, 246)
USO: Gradientes, gráficos, elementos secundários
```

```
Azul Escuro (Dark)
HEX: #1E3A8A
RGB: (30, 58, 138)
USO: Textos principais, backgrounds escuros
```

### Cores de Suporte

```
Azul Claro (Light)
HEX: #DBEAFE
RGB: (219, 234, 254)
USO: Backgrounds, cards, áreas de destaque suave
```

```
Roxo Claro (Light Purple)
HEX: #EDE9FE
RGB: (237, 233, 254)
USO: Highlights, badges, notificações
```

### Cores de Status

```
Verde (Receita/Positivo)
HEX: #10B981
RGB: (16, 185, 129)
USO: Receitas, saldo positivo, sucesso
```

```
Vermelho (Despesa/Negativo)
HEX: #EF4444
RGB: (239, 68, 68)
USO: Despesas, alertas, valores negativos
```

```
Amarelo (Atenção)
HEX: #F59E0B
RGB: (245, 158, 11)
USO: Avisos, orçamento próximo do limite
```

### Cores Neutras

```
Branco
HEX: #FFFFFF
RGB: (255, 255, 255)
```

```
Cinza Claro
HEX: #F3F4F6
RGB: (243, 244, 246)
```

```
Cinza Médio
HEX: #6B7280
RGB: (107, 114, 128)
```

```
Cinza Escuro
HEX: #1F2937
RGB: (31, 41, 55)
```

---

## 🎨 Gradientes

### Gradiente Principal (Luz/Claridade)

```css
background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
```

**USO:** Headers, botões de ação principal, splash screen

### Gradiente Secundário (Feixe de Luz)

```css
background: linear-gradient(180deg, #dbeafe 0%, #ede9fe 100%);
```

**USO:** Cards, backgrounds suaves, áreas de conteúdo

### Gradiente de Sucesso

```css
background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
```

**USO:** Metas alcançadas, feedback positivo

---

## 🔤 Tipografia

### Fonte Principal

**Inter** ou **Poppins** (moderna, clean, legível)

```
Títulos (H1):
- Tamanho: 24-32px
- Peso: Bold (700)
- Cor: #1F2937

Subtítulos (H2):
- Tamanho: 18-22px
- Peso: SemiBold (600)
- Cor: #374151

Corpo de Texto:
- Tamanho: 14-16px
- Peso: Regular (400)
- Cor: #6B7280

Valores Monetários:
- Tamanho: 28-36px
- Peso: Bold (700)
- Cor: #1E3A8A (padrão) ou cores de status
```

---

## ✨ Ícones

### Estilo

- **Tipo:** Outlined (linha) para manter leveza
- **Biblioteca recomendada:** Material Icons, Feather Icons, ou Heroicons
- **Espessura:** 2px (stroke)
- **Cor:** Segue a paleta (primário ou cinza médio)

### Ícones Principais

```
🏠 Dashboard:
- Ícone: home / grid
- Feixe de luz saindo do centro

💰 Contas:
- Ícone: wallet / credit-card
- Com brilho/luz no canto

📊 Transações:
- Ícone: trending-up / arrow-up-down
- Setas com gradiente

🎯 Orçamento:
- Ícone: target / pie-chart
- Círculo com raio de luz

⭐ Metas:
- Ícone: star / flag
- Com efeito de brilho

➕ Adicionar:
- Ícone: plus-circle
- Com gradiente primário
```

---

## 📱 Componentes de UI

### Botões

**Botão Primário:**

```css
background: linear-gradient(135deg, #3b82f6, #8b5cf6);
border-radius: 12px;
padding: 16px 24px;
color: #ffffff;
font-weight: 600;
shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
```

**Botão Secundário:**

```css
background: transparent;
border: 2px solid #3b82f6;
border-radius: 12px;
padding: 14px 22px;
color: #3b82f6;
font-weight: 600;
```

### Cards

```css
background: #ffffff;
border-radius: 16px;
padding: 20px;
shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
border: 1px solid #f3f4f6;
```

**Card com Destaque (Saldo Total):**

```css
background: linear-gradient(135deg, #3b82f6, #8b5cf6);
border-radius: 20px;
padding: 24px;
shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
color: #ffffff;
```

### Gráficos de Progresso (Orçamento)

```css
background: #f3f4f6; /* fundo da barra */
fill: linear-gradient(90deg, #3b82f6, #8b5cf6); /* progresso */
border-radius: 8px;
height: 8px;
```

**Estados:**

- 0-60%: Azul (#3B82F6)
- 61-85%: Amarelo (#F59E0B)
- 86-100%: Vermelho (#EF4444)

---

## 🖼️ Layouts de Telas

### A. Dashboard (Clareza)

```
┌─────────────────────────────────┐
│  [Header com gradiente]         │
│  Olá, [Nome]                    │
│  ☀️ Boa tarde                    │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│  💰 SALDO TOTAL                 │
│  R$ 7.800,00                    │
│  [Card com gradiente principal] │
└─────────────────────────────────┘

┌──────────────┬──────────────────┐
│ Receitas Mês │  Despesas Mês   │
│ R$ 4.300,00  │  R$ 1.700,00    │
│ [Verde]      │  [Vermelho]     │
└──────────────┴──────────────────┘

┌─────────────────────────────────┐
│ 📊 Orçamento do Mês             │
│                                 │
│ Alimentação     [▓▓▓▓▓░] 80%   │
│ Transporte      [▓▓▓░░░] 45%   │
│ Lazer           [▓▓░░░░] 30%   │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 📅 Próximos Pagamentos          │
│ • Netflix      R$ 45,90  22/11 │
│ • Água         R$ 120,00 25/11 │
└─────────────────────────────────┘
```

### B. Extrato/Contas

```
┌─────────────────────────────────┐
│  Minhas Contas                  │
│  [Filtro: Todas ▾]              │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🏦 Conta Corrente               │
│    R$ 2.500,00                  │
│    [Gradiente suave]            │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 💳 Cartão Nubank                │
│    R$ 1.200,00                  │
└─────────────────────────────────┘

[Filtro: Receitas | Despesas | Todas]

┌─────────────────────────────────┐
│ 🛒 Supermercado                 │
│    Alimentação                  │
│    18/11/2025    - R$ 150,50    │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 💰 Salário                      │
│    Receita                      │
│    01/11/2025    + R$ 3.500,00  │
└─────────────────────────────────┘
```

### C. Registro Rápido (Facilidade)

```
┌─────────────────────────────────┐
│         Novo Registro           │
│         [Fechar X]              │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│          R$ 0,00                │
│   [Campo de valor GRANDE]       │
└─────────────────────────────────┘

┌──────────┬──────────┬───────────┐
│ Despesa  │ Receita  │Transferên.│
│   [✓]    │          │           │
└──────────┴──────────┴───────────┘

┌─────────────────────────────────┐
│ Conta: [Conta Corrente ▾]      │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Categoria: [Alimentação ▾]      │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Descrição: [Supermercado]       │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Data: [18/11/2025]              │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│   [BOTÃO SALVAR - Gradiente]    │
└─────────────────────────────────┘
```

### D. Orçamento/Metas

```
┌─────────────────────────────────┐
│  Orçamento - Novembro 2025      │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🍔 Alimentação                  │
│ R$ 480,00 / R$ 600,00           │
│ [▓▓▓▓▓▓▓▓░░] 80%               │
│ Restam R$ 120,00                │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🚗 Transporte                   │
│ R$ 180,00 / R$ 400,00           │
│ [▓▓▓▓░░░░░░] 45%               │
│ Restam R$ 220,00                │
└─────────────────────────────────┘

[Separador com ícone de estrela]

┌─────────────────────────────────┐
│ ⭐ Minhas Metas                 │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🎯 Viagem Internacional         │
│ R$ 2.400,00 / R$ 5.000,00       │
│ [▓▓▓▓░░░░░░] 48%               │
│ Meta: Jan/2026                  │
└─────────────────────────────────┘
```

---

## ⚡ Efeitos e Animações

### Transições

```css
transition: all 0.3s ease-in-out;
```

### Hover em Cards

```css
transform: translateY(-4px);
shadow: 0 12px 24px rgba(59, 130, 246, 0.2);
```

### Efeito de Luz (Feixe)

```css
/* Adicionar em elementos de destaque */
box-shadow: 0 0 20px rgba(59, 130, 246, 0.3), 0 0 40px rgba(139, 92, 246, 0.2);
```

### Loading

- Skeleton screens com gradiente animado
- Spinner circular com cores do gradiente principal

---

## 📐 Espaçamentos

```
Extra Small: 4px
Small: 8px
Medium: 16px
Large: 24px
Extra Large: 32px

Border Radius:
- Pequeno: 8px
- Médio: 12px
- Grande: 16px
- Cards principais: 20px
```

---

## 🎯 Princípios de Design

1. **Clareza em Primeiro Lugar:** Informações financeiras devem ser imediatamente compreensíveis
2. **Hierarquia Visual:** Valores mais importantes em destaque (tamanho, cor, posição)
3. **Feedback Visual:** Toda ação deve ter resposta visual clara
4. **Consistência:** Mesmos padrões em todas as telas
5. **Minimalismo:** Sem poluição visual, foco no essencial
6. **Acessibilidade:** Contraste adequado, textos legíveis, toque fácil

---

## 🌟 Logo e Marca

### Conceito do Logo

- **Símbolo:** Feixe de luz ou lâmpada estilizada
- **Formas:** Geometria circular (clareza, completude)
- **Gradiente:** Do azul ao roxo (representando iluminação progressiva)

### Variações

1. **Logo Completo:** Símbolo + "Lumis"
2. **Logo Compacto:** Apenas símbolo (para app icon)
3. **Logo Monocromático:** Para fundos coloridos

---

**Versão:** 1.0  
**Data:** Novembro 2025  
**Projeto:** Lumis - Gestão Financeira Pessoal
