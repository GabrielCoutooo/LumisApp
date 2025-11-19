# 🎨 COMO VISUALIZAR O PROTÓTIPO LUMIS

## ✅ Pré-requisitos

1. **Banco de dados configurado** com dados de teste
2. **Servidor PHP rodando** na porta 8000

---

## 🚀 Passos para Visualizar

### 1. Inicie o Servidor PHP

Abra um terminal e execute:

```bash
cd c:\Users\Gabriel Couto\Desktop\Projetos\LumisApp\api
php -S localhost:8000
```

Deixe este terminal aberto. Você verá:

```
PHP 8.x Development Server (http://localhost:8000) started
```

---

### 2. Abra o Protótipo no Navegador

**Opção A - Duplo clique:**

- Navegue até: `c:\Users\Gabriel Couto\Desktop\Projetos\LumisApp\`
- Dê duplo clique no arquivo **`prototipo.html`**

**Opção B - Arrastar:**

- Arraste o arquivo `prototipo.html` para o navegador (Chrome, Edge, Firefox)

**Opção C - Via URL:**

- Abra o navegador e digite:

```
file:///c:/Users/Gabriel Couto/Desktop/Projetos/LumisApp/prototipo.html
```

---

## 📱 O Que Você Verá

### ✅ Tela 1: Dashboard (Página Inicial)

- **Saldo Total** com gradiente azul/roxo
- **Receitas e Despesas** do mês em cards
- **Orçamentos** com barras de progresso coloridas:
  - 0-60% = Azul ✅
  - 61-85% = Amarelo ⚠️
  - 86-100% = Vermelho 🔴
- **Próximos Pagamentos** pendentes

### ✅ Tela 2: Extrato

- Lista completa de transações
- Filtros: Todas | Receitas | Despesas
- Ícones e cores por tipo
- Datas e categorias

### 🎯 Navegação Inferior

- **🏠 Início** - Dashboard
- **📋 Extrato** - Transações
- **➕ Registrar** - Modal (em desenvolvimento)
- **🎯 Orçamento** - (em desenvolvimento)
- **👤 Perfil** - (em desenvolvimento)

---

## 🎨 Recursos Visuais

✅ **Design fiel à identidade visual:**

- Gradientes azul/roxo (feixe de luz)
- Cores de status (verde, vermelho, amarelo)
- Cards com sombras suaves
- Bordas arredondadas
- Tipografia hierárquica

✅ **Responsivo:**

- Layout otimizado para mobile (428px)
- Visualiza bem em qualquer navegador

✅ **Interativo:**

- Navegação entre telas
- Filtros funcionais
- Dados reais da API

---

## 🔧 Solução de Problemas

### ❌ "Erro ao carregar" ou dados não aparecem

**Causa:** API não está rodando ou banco de dados vazio.

**Solução:**

1. Verifique se o servidor PHP está rodando:

   ```bash
   cd api
   php -S localhost:8000
   ```

2. Certifique-se de que importou os dados de teste:

   ```bash
   mysql -u root -p lumis < db/dados_teste.sql
   ```

3. Teste a API manualmente:
   ```bash
   curl "http://localhost:8000/index.php/api/dashboard?id_usuario=1&mes_ano=2025-11"
   ```

---

### ❌ Erro de CORS no console

**Causa:** Navegador bloqueando requisições locais.

**Solução:** Abra o Chrome com flag de segurança desabilitada (apenas para desenvolvimento):

**Windows:**

```bash
chrome.exe --disable-web-security --user-data-dir="C:/temp/chrome-dev"
```

Ou adicione headers CORS no `api/index.php`:

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

---

### ❌ Página em branco

**Solução:**

1. Abra o Console do navegador (F12)
2. Veja se há erros JavaScript
3. Verifique se o arquivo `prototipo.html` foi salvo corretamente

---

## 📊 Teste Rápido da API

Antes de abrir o protótipo, teste se a API está funcionando:

```bash
# Dashboard
curl "http://localhost:8000/index.php/api/dashboard?id_usuario=1&mes_ano=2025-11"

# Extrato
curl "http://localhost:8000/index.php/api/extrato?id_usuario=1"
```

Deve retornar JSON com dados.

---

## 🎯 Próximos Passos

Depois de visualizar o protótipo, você pode:

1. **Adicionar mais dados** no banco para ver mais transações
2. **Customizar cores** editando o CSS no `prototipo.html`
3. **Implementar tela de Registro** (modal para criar transações)
4. **Desenvolver app mobile real** usando React Native/Flutter

---

## 💡 Dica

Para uma melhor experiência:

- Abra o **DevTools** (F12) no navegador
- Ative o **modo responsivo** (Ctrl+Shift+M)
- Selecione dispositivo: **iPhone 14 Pro** ou **Pixel 5**
- Agora você vê exatamente como ficaria no celular!

---

**Aproveite o protótipo!** 🚀
