// Lumis - Sistema de Gestão Financeira - JavaScript

// Configuração da API
const BASE_API = "http://localhost/LumisApp/public/api.php/api";
const ID_USUARIO = 1;
let MES_ANO = new Date().toISOString().slice(0, 7); // Mês atual dinâmico (YYYY-MM)

let todasTransacoes = [];
let filtroAtual = "all";
let categoriasDespesa = [];
let categoriasReceita = [];
let contasUsuario = [];
let orcamentosAtuais = [];
let saldoVisivel = true;
let categoriasAtuais = []; // Armazena todas as categorias

// ==================== MOEDA / CONVERSÃO ====================
const BASE_CURRENCY = "BRL"; // Moeda base armazenada no backend
let CURRENT_CURRENCY = "BRL"; // Moeda exibida na UI
let currencyFactor = 1; // Quanto vale 1 BRL na moeda destino
let currencyFormatter = new Intl.NumberFormat("pt-BR", {
  style: "currency",
  currency: CURRENT_CURRENCY,
});

// ==================== UTILIDADES ====================

function atualizarSaudacao() {
  const hora = new Date().getHours();
  const elementoSaudacao = document.getElementById("saudacao-dinamica");

  let icone, texto;

  if (hora >= 5 && hora < 12) {
    icone = '<i class="fa-solid fa-sun"></i>';
    texto = "Bom dia";
  } else if (hora >= 12 && hora < 18) {
    icone = '<i class="fa-solid fa-sun"></i>';
    texto = "Boa tarde";
  } else {
    icone = '<i class="fa-solid fa-moon"></i>';
    texto = "Boa noite";
  }

  if (elementoSaudacao) {
    elementoSaudacao.innerHTML = `${icone} ${texto}`;
  }
}

function formatarMoeda(valor) {
  if (!saldoVisivel) return "••••••";
  const valorNum = Number(valor) || 0;
  const fator = currencyFactor || 1;
  const convertido = valorNum * fator;
  return currencyFormatter.format(convertido);
}

function formatarData(dataStr) {
  const data = new Date(dataStr + "T00:00:00");
  return data.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit" });
}

function mostrarNotificacao(tipo, titulo, mensagem) {
  // Notificação simples usando alert
  // tipo: 'success', 'danger', 'warning', 'info'
  const icones = {
    success: "✅",
    danger: "❌",
    warning: "⚠️",
    info: "ℹ️",
  };
  const icone = icones[tipo] || "ℹ️";
  alert(`${icone} ${titulo}\n${mensagem}`);
}

function corPorPercentual(percentual) {
  if (percentual >= 0 && percentual <= 60) return "#2ecc71";
  if (percentual > 60 && percentual <= 85) return "#F59E0B";
  return "#EF4444";
}

function intervaloDoMes(mesAno) {
  const [y, m] = mesAno.split("-").map(Number);
  const first = new Date(y, m - 1, 1);
  const last = new Date(y, m, 0);
  const toISO = (d) => d.toISOString().slice(0, 10);
  return { inicio: toISO(first), fim: toISO(last) };
}

function formatarMesAno(mesAno) {
  const [y, m] = mesAno.split("-");
  const meses = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho",
    "Julho",
    "Agosto",
    "Setembro",
    "Outubro",
    "Novembro",
    "Dezembro",
  ];
  return `${meses[parseInt(m) - 1]} ${y}`;
}

function navegarMes(direcao) {
  const [y, m] = MES_ANO.split("-").map(Number);
  const data = new Date(y, m - 1, 1);
  data.setMonth(data.getMonth() + direcao);
  MES_ANO = data.toISOString().slice(0, 7);
  atualizarTituloMes();
  const telaAtiva = document.querySelector(
    '[id$="-screen"]:not([style*="display: none"])'
  );
  if (telaAtiva) {
    const telaId = telaAtiva.id.replace("-screen", "");
    if (telaId === "dashboard") carregarDashboard();
    else if (telaId === "extrato") carregarExtrato();
    else if (telaId === "orcamento") carregarOrcamento();
  }
}

function voltarMesAtual() {
  MES_ANO = new Date().toISOString().slice(0, 7);
  atualizarTituloMes();
  const telaAtiva = document.querySelector(
    '[id$="-screen"]:not([style*="display: none"])'
  );
  if (telaAtiva) {
    const telaId = telaAtiva.id.replace("-screen", "");
    if (telaId === "dashboard") carregarDashboard();
    else if (telaId === "extrato") carregarExtrato();
    else if (telaId === "orcamento") carregarOrcamento();
  }
}

function atualizarTituloMes() {
  const mesFormatado = formatarMesAno(MES_ANO);
  const mesAtual = new Date().toISOString().slice(0, 7);
  const ehMesAtual = MES_ANO === mesAtual;

  // Atualizar todos os títulos de mês
  [
    "titulo-mes-atual",
    "titulo-mes-atual-extrato",
    "titulo-mes-atual-orcamento",
  ].forEach((id) => {
    const elemento = document.getElementById(id);
    if (elemento) elemento.textContent = mesFormatado;
  });

  // Atualizar botões "Hoje"
  ["btn-mes-atual", "btn-mes-atual-extrato", "btn-mes-atual-orcamento"].forEach(
    (id) => {
      const btn = document.getElementById(id);
      if (btn) btn.style.display = ehMesAtual ? "none" : "inline-block";
    }
  );
}

window.navegarMes = navegarMes;
window.voltarMesAtual = voltarMesAtual;
// ==================== PRIVACIDADE ====================

function toggleVisibilidadeSaldo() {
  saldoVisivel = !saldoVisivel;
  const saldoElement = document.getElementById("saldo-total");
  const toggleButton = document.getElementById("btn-privacy");

  if (saldoVisivel) {
    carregarDashboardSaldo();
    toggleButton.innerHTML = '<i class="fa-solid fa-eye"></i>';
  } else {
    saldoElement.textContent = formatarMoeda(0);
    toggleButton.innerHTML = '<i class="fa-solid fa-lock"></i>';
  }
}

async function carregarDashboardSaldo() {
  try {
    const resposta = await fetch(
      `${BASE_API}/dashboard?id_usuario=${ID_USUARIO}&mes_ano=${MES_ANO}`
    );
    const dados = await resposta.json();
    document.getElementById("saldo-total").textContent = formatarMoeda(
      dados.saldo_total || 0
    );
  } catch (error) {
    document.getElementById("saldo-total").textContent = "Erro";
  }
}

// ==================== DASHBOARD ====================

async function carregarDashboard() {
  try {
    atualizarTituloMes();

    const resposta = await fetch(
      `${BASE_API}/dashboard?id_usuario=${ID_USUARIO}&mes_ano=${MES_ANO}`
    );
    const dados = await resposta.json();

    document.getElementById("saldo-total").textContent = formatarMoeda(
      dados.saldo_total || 0
    );
    document.getElementById("receitas-mes").textContent = formatarMoeda(
      dados.receitas_mes || 0
    );
    document.getElementById("despesas-mes").textContent = formatarMoeda(
      dados.despesas_mes || 0
    );

    // Mostrar aviso se houver pagamentos pendentes (recorrências não efetuadas)
    // O backend já retorna apenas pendentes do mês atual (mes_ano)
    const pendentes = dados.proximos_pagamentos || [];
    const saldoCard = document.querySelector(".saldo-card");
    const avisoExistente = document.getElementById("aviso-pendentes");

    // Remover aviso antigo se existir
    if (avisoExistente) {
      avisoExistente.remove();
    }

    // Criar novo aviso apenas se houver pendentes
    if (pendentes.length > 0) {
      const aviso = document.createElement("div");
      aviso.id = "aviso-pendentes";
      aviso.style.cssText =
        "background:#FEF3C7;color:#92400E;padding:8px 12px;border-radius:8px;font-size:12px;margin-top:8px;text-align:center;";
      const qtd = pendentes.length;
      const textoTransacao =
        qtd === 1 ? "transação pendente" : "transações pendentes";
      aviso.innerHTML = `⚠️ Você tem ${qtd} ${textoTransacao} neste mês. Marque como efetuada para atualizar o saldo.`;
      saldoCard.appendChild(aviso);
    }

    renderizarOrcamentos(dados.orcamentos || []);
    renderizarPagamentos(dados.proximos_pagamentos || []);
  } catch (error) {
    document.getElementById("saldo-total").textContent = "Erro ao carregar";
  }
}

function renderizarOrcamentos(orcamentos) {
  const container = document.getElementById("orcamentos-list");

  if (orcamentos.length === 0) {
    container.innerHTML =
      '<div class="empty-state"><div class="empty-icon">📊</div><div>Nenhum orçamento cadastrado</div></div>';
    return;
  }

  container.innerHTML = orcamentos
    .map((orc) => {
      const percentual = parseFloat(orc.percentual_utilizado || 0);
      const cor = corPorPercentual(percentual);
      const gasto = parseFloat(orc.gasto_realizado || 0);
      const limite = parseFloat(orc.valor_limite || 0);
      const restante = parseFloat(orc.gasto_restante || 0);
      const status = orc.status || "OK";

      return `
            <div class="orcamento-card">
                <div class="orcamento-header">
                    <div class="categoria-name">${
                      orc.nome_categoria || orc.categoria || "Sem categoria"
                    }</div>
                    <div class="progress-text" style="color: ${cor}">${percentual.toFixed(
        0
      )}%</div>
                </div>
                <div class="orcamento-values">${formatarMoeda(
                  gasto
                )} de ${formatarMoeda(limite)}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${Math.min(
                      percentual,
                      100
                    )}%; background: ${cor}"></div>
                </div>
                <div class="progress-text" style="color: ${cor}">
                    ${
                      status === "ESTOURADO"
                        ? `⚠️ Excedeu em ${formatarMoeda(Math.abs(restante))}`
                        : `Restam ${formatarMoeda(restante)}`
                    }
                </div>
            </div>
        `;
    })
    .join("");
}

function renderizarPagamentos(pagamentos) {
  const container = document.getElementById("pagamentos-list");

  if (pagamentos.length === 0) {
    container.innerHTML =
      '<div class="empty-state"><div class="empty-icon">📅</div><div>Nenhum pagamento pendente</div></div>';
    return;
  }

  container.innerHTML = pagamentos
    .map(
      (pag) => `
        <div class="pagamento-item" style="cursor:pointer;" onclick="marcarComoEfetuada(${
          pag.id_transacao
        })">
            <div class="pagamento-info">
                <div class="pagamento-desc">${pag.descricao}</div>
                <div class="pagamento-data">${formatarData(
                  pag.data_transacao
                )}</div>
            </div>
            <div class="pagamento-valor ${
              pag.tipo_movimentacao === "RECEITA" ? "receita" : "despesa"
            }">${formatarMoeda(pag.valor)}</div>
        </div>
    `
    )
    .join("");
}

// ==================== EXTRATO ====================

async function carregarExtrato() {
  try {
    const { inicio, fim } = intervaloDoMes(MES_ANO);
    const resposta = await fetch(
      `${BASE_API}/extrato?id_usuario=${ID_USUARIO}&data_inicio=${inicio}&data_fim=${fim}`
    );
    const dados = await resposta.json();

    // Atualizar todas as transações do mês atual
    todasTransacoes = dados;

    // Aplicar filtro atual
    aplicarFiltroAtual();
    atualizarTituloMes();
  } catch (error) {
    document.getElementById("transacoes-list").innerHTML =
      '<div class="empty-state"><div class="empty-icon">❌</div><div>Erro ao carregar transações</div></div>';
  }
}

function aplicarFiltroAtual() {
  let transacoesParaExibir = todasTransacoes;

  if (filtroAtual && filtroAtual !== "all") {
    transacoesParaExibir = todasTransacoes.filter(
      (t) => t.tipo_movimentacao === filtroAtual
    );
  }

  renderizarTransacoes(transacoesParaExibir);
  atualizarBotoesFiltro();
}

function atualizarBotoesFiltro() {
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
    const btnFiltro = btn.getAttribute("onclick");
    if (btnFiltro && btnFiltro.includes(`'${filtroAtual}'`)) {
      btn.classList.add("active");
    }
  });
}

function renderizarTransacoes(transacoes) {
  const container = document.getElementById("transacoes-list");

  if (transacoes.length === 0) {
    container.innerHTML =
      '<div class="empty-state"><div class="empty-icon">📋</div><div>Nenhuma transação encontrada</div></div>';
    return;
  }

  container.innerHTML = transacoes
    .map((trans) => {
      const isReceita = trans.tipo_movimentacao === "RECEITA";
      const icone = isReceita ? "💰" : "🛒";
      const classeCor = isReceita ? "receita" : "despesa";
      const prefixo = isReceita ? "+" : "-";

      return `
            <div class="transacao-item">
                <div class="transacao-icon">${icone}</div>
                <div class="transacao-info">
                    <div class="transacao-desc">${trans.descricao}</div>
                    <div class="transacao-categoria">${
                      trans.categoria || "Sem categoria"
                    }</div>
                    <div class="transacao-data">${formatarData(
                      trans.data_transacao
                    )}</div>
                </div>
                <div class="transacao-valor ${classeCor}">
                    ${prefixo} ${formatarMoeda(trans.valor)}
                </div>
            </div>
        `;
    })
    .join("");
}

function filtrarTransacoes(tipo) {
  filtroAtual = tipo;
  aplicarFiltroAtual();
}

// ==================== ORÇAMENTO ====================

async function carregarCategoriasDespesa() {
  const resposta = await fetch(
    `${BASE_API}/categorias?id_usuario=${ID_USUARIO}&tipo=DESPESA`
  );
  categoriasDespesa = await resposta.json();
  return categoriasDespesa;
}

async function carregarOrcamento() {
  try {
    // Buscar orçamentos do mês
    const resposta = await fetch(
      `${BASE_API}/orcamento?id_usuario=${ID_USUARIO}&mes_ano=${MES_ANO}`
    );
    const orcamentos = await resposta.json();
    orcamentosAtuais = orcamentos || [];

    // Buscar todas as categorias de despesa
    await carregarCategoriasDespesa();

    // Copiar orçamentos do mês anterior para categorias que não existem
    await copiarOrcamentoMesAnterior();

    renderizarListaOrcamento(orcamentosAtuais);
  } catch (e) {
    console.error("Erro ao carregar orçamento:", e);
    document.getElementById("orcamento-list").innerHTML =
      '<div class="empty-state"><div class="empty-icon">❌</div><div>Erro ao carregar orçamentos</div></div>';
  }
}

async function copiarOrcamentoMesAnterior() {
  try {
    // Calcular mês anterior
    const [ano, mes] = MES_ANO.split("-").map(Number);
    const mesAnterior = new Date(ano, mes - 2, 1); // mes-2 porque Date usa 0-11
    const mesAnteriorStr = `${mesAnterior.getFullYear()}-${String(
      mesAnterior.getMonth() + 1
    ).padStart(2, "0")}`;

    // Buscar orçamentos do mês anterior
    const resposta = await fetch(
      `${BASE_API}/orcamento?id_usuario=${ID_USUARIO}&mes_ano=${mesAnteriorStr}`
    );
    const orcamentosMesAnterior = await resposta.json();

    if (!orcamentosMesAnterior || orcamentosMesAnterior.length === 0) {
      return; // Não há orçamentos no mês anterior para copiar
    }

    // Criar mapa de categorias que já existem no mês atual
    const categoriasExistentes = new Set(
      orcamentosAtuais.map((orc) => orc.id_categoria)
    );

    // Copiar apenas orçamentos de categorias que NÃO existem no mês atual
    const { inicio, fim } = intervaloDoMes(MES_ANO);
    const promessas = orcamentosMesAnterior
      .filter((orc) => !categoriasExistentes.has(orc.id_categoria))
      .map(async (orc) => {
        try {
          const res = await fetch(`${BASE_API}/orcamento`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              id_usuario: ID_USUARIO,
              id_categoria: orc.id_categoria,
              valor_limite: orc.valor_limite,
              data_inicio: inicio,
              data_fim: fim,
              ativo: true,
            }),
          });
          return await res.json();
        } catch (e) {
          console.error(
            `Erro ao copiar orçamento da categoria ${orc.id_categoria}:`,
            e
          );
          return null;
        }
      });

    if (promessas.length > 0) {
      await Promise.all(promessas);

      // Recarregar orçamentos atualizados
      const respostaAtualizada = await fetch(
        `${BASE_API}/orcamento?id_usuario=${ID_USUARIO}&mes_ano=${MES_ANO}`
      );
      orcamentosAtuais = (await respostaAtualizada.json()) || [];
    }
  } catch (e) {
    console.error("Erro ao copiar orçamentos do mês anterior:", e);
  }
}

function renderizarListaOrcamento(itens) {
  const container = document.getElementById("orcamento-list");

  // Se não há categorias carregadas, mostrar mensagem
  if (!categoriasDespesa || categoriasDespesa.length === 0) {
    container.innerHTML =
      '<div class="empty-state"><div class="empty-icon">📋</div><div>Carregando categorias...</div></div>';
    return;
  }

  // Criar um mapa de orçamentos por categoria
  const orcamentoPorCategoria = {};
  itens.forEach((orc) => {
    orcamentoPorCategoria[orc.id_categoria] = orc;
  });

  // Renderizar todas as categorias
  container.innerHTML = categoriasDespesa
    .map((cat) => {
      const orc = orcamentoPorCategoria[cat.id_categoria];
      const temOrcamento = !!orc;
      const valorLimite = temOrcamento ? orc.valor_limite : 0;
      const ativo = temOrcamento
        ? String(orc.ativo) === "1" || orc.ativo === true
        : true;

      return `
        <div class="orcamento-card" style="${
          !temOrcamento || valorLimite == 0 ? "opacity: 0.6;" : ""
        }">
          <div class="orcamento-header">
            <div class="categoria-name">${cat.nome}</div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button class="btn" style="padding:6px 10px;" onclick="editarOrcamentoRapido('${
                cat.id_categoria
              }', '${cat.nome}', ${valorLimite}, ${
        temOrcamento ? orc.id_orcamento : "null"
      })">Editar</button>
              ${
                temOrcamento && valorLimite > 0
                  ? `<button class="btn btn-danger" style="padding:6px 10px;" onclick="limparOrcamento(${orc.id_orcamento}, '${cat.nome}')">Limpar</button>`
                  : ""
              }
            </div>
          </div>
          <div class="orcamento-values">Limite: ${formatarMoeda(
            valorLimite
          )}</div>
          ${
            temOrcamento && valorLimite > 0
              ? `<div class="orcamento-values" style="font-size:0.85em;opacity:0.7;">Período: ${formatarData(
                  orc.data_inicio
                )} a ${formatarData(orc.data_fim)}</div>`
              : '<div class="orcamento-values" style="font-size:0.85em;opacity:0.7;">Nenhum orçamento definido</div>'
          }
        </div>
      `;
    })
    .join("");
}

function abrirModalOrcamento() {
  const backdrop = document.getElementById("orcamento-modal");
  backdrop.style.display = "flex";
  document.getElementById("orcamento-modal").dataset.mode = "create";
  const title = document.getElementById("orcamento-modal-title");
  if (title) title.textContent = "Novo Orçamento";
  const periodo = document.getElementById("periodo-text");
  if (periodo) periodo.value = MES_ANO;
  document.getElementById("hidden-id-orcamento").value = "";
  const ativoEl = document.getElementById("input-ativo");
  if (ativoEl) ativoEl.checked = true;
  const select = document.getElementById("select-categoria");
  select.innerHTML = '<option value="">Carregando...</option>';
  carregarCategoriasDespesa()
    .then((cats) => {
      select.innerHTML =
        '<option value="">Selecione...</option>' +
        cats
          .map((c) => `<option value="${c.id_categoria}">${c.nome}</option>`)
          .join("");
      select.disabled = false;
    })
    .catch(() => {
      select.innerHTML = '<option value="">Erro ao carregar</option>';
    });
}

function fecharModalOrcamento() {
  document.getElementById("orcamento-modal").style.display = "none";
  document.getElementById("input-valor-limite").value = "";
  document.getElementById("select-categoria").value = "";
}

function abrirModalEditarOrcamentoPorIndice(indice) {
  const orc = orcamentosAtuais[indice];
  if (!orc) return;
  const backdrop = document.getElementById("orcamento-modal");
  backdrop.style.display = "flex";
  document.getElementById("orcamento-modal").dataset.mode = "edit";
  const title = document.getElementById("orcamento-modal-title");
  if (title) title.textContent = "Editar Orçamento";
  const periodo = document.getElementById("periodo-text");
  if (periodo) periodo.value = MES_ANO;
  document.getElementById("hidden-id-orcamento").value = orc.id_orcamento || "";
  document.getElementById("input-valor-limite").value = parseFloat(
    orc.valor_limite || 0
  );
  const ativoEl = document.getElementById("input-ativo");
  if (ativoEl)
    ativoEl.checked = String(orc.ativo) === "1" || orc.ativo === true;
  const select = document.getElementById("select-categoria");
  select.innerHTML = '<option value="">Carregando...</option>';
  carregarCategoriasDespesa()
    .then((cats) => {
      select.innerHTML =
        '<option value="">Selecione...</option>' +
        cats
          .map((c) => `<option value="${c.id_categoria}">${c.nome}</option>`)
          .join("");
      select.value = String(orc.id_categoria || "");
      select.disabled = true;
    })
    .catch(() => {
      select.innerHTML = `<option value="${orc.id_categoria || ""}">${
        orc.categoria_nome || ""
      }</option>`;
      select.disabled = true;
    });
}

async function enviarOrcamento() {
  const id_categoria = document.getElementById("select-categoria").value;
  const valor_limite = parseFloat(
    document.getElementById("input-valor-limite").value || "0"
  );
  const ativo = document.getElementById("input-ativo").checked;
  if (!id_categoria || !valor_limite || valor_limite <= 0) {
    alert("Selecione a categoria e informe um valor válido.");
    return;
  }
  const { inicio, fim } = intervaloDoMes(MES_ANO);
  const mode =
    document.getElementById("orcamento-modal").dataset.mode || "create";
  try {
    const payload =
      mode === "edit"
        ? {
            id_orcamento: Number(
              document.getElementById("hidden-id-orcamento").value
            ),
            valor_limite: valor_limite,
            ativo: ativo,
          }
        : {
            id_usuario: ID_USUARIO,
            id_categoria: Number(id_categoria),
            valor_limite: valor_limite,
            data_inicio: inicio,
            data_fim: fim,
            ativo: ativo,
          };
    const res = await fetch(`${BASE_API}/orcamento`, {
      method: mode === "edit" ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok || !data.success) {
      throw new Error(data.error || "Falha ao criar orçamento");
    }
    fecharModalOrcamento();
    carregarOrcamento();
  } catch (e) {
    alert(
      mode === "edit" ? "Erro ao editar orçamento." : "Erro ao criar orçamento."
    );
  }
}

async function excluirOrcamentoPorIndice(indice) {
  const orc = orcamentosAtuais[indice];
  if (!orc || !orc.id_orcamento) {
    alert("Orçamento não encontrado");
    return;
  }

  if (
    !confirm(`Deseja realmente excluir o orçamento "${orc.nome_categoria}"?`)
  ) {
    return;
  }

  try {
    const res = await fetch(`${BASE_API}/orcamento`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_orcamento: Number(orc.id_orcamento) }),
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(data.error || "Falha ao excluir orçamento");
    }

    mostrarNotificacao("success", "Sucesso!", "Orçamento excluído com sucesso");
    carregarOrcamento();
  } catch (e) {
    alert("Erro ao excluir orçamento: " + e.message);
  }
}

// Edição rápida de orçamento (sem modal complexo)
async function editarOrcamentoRapido(
  idCategoria,
  nomeCategoria,
  valorAtual,
  idOrcamento
) {
  const novoValor = prompt(
    `Defina o limite de orçamento para "${nomeCategoria}" no mês ${formatarMesAno(
      MES_ANO
    )}:`,
    valorAtual
  );

  if (novoValor === null) return; // Cancelou

  const valor = parseFloat(novoValor);
  if (isNaN(valor) || valor < 0) {
    alert("Valor inválido!");
    return;
  }

  try {
    const { inicio, fim } = intervaloDoMes(MES_ANO);

    // Se já existe orçamento, atualizar; senão, criar
    if (idOrcamento && idOrcamento !== "null") {
      const res = await fetch(`${BASE_API}/orcamento`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id_orcamento: Number(idOrcamento),
          valor_limite: valor,
          ativo: true,
        }),
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.error || "Falha ao atualizar orçamento");
      }
    } else {
      const res = await fetch(`${BASE_API}/orcamento`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id_usuario: ID_USUARIO,
          id_categoria: Number(idCategoria),
          valor_limite: valor,
          data_inicio: inicio,
          data_fim: fim,
          ativo: true,
        }),
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.error || "Falha ao criar orçamento");
      }
    }

    mostrarNotificacao("success", "Sucesso!", "Orçamento atualizado");
    carregarOrcamento();
    carregarDashboard();
  } catch (e) {
    alert("Erro ao salvar orçamento: " + e.message);
  }
}

// Limpar/zerar orçamento
async function limparOrcamento(idOrcamento, nomeCategoria) {
  if (!confirm(`Deseja remover o orçamento de "${nomeCategoria}"?`)) {
    return;
  }

  try {
    const res = await fetch(`${BASE_API}/orcamento`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_orcamento: Number(idOrcamento) }),
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(data.error || "Falha ao excluir orçamento");
    }

    mostrarNotificacao("success", "Sucesso!", "Orçamento removido");
    carregarOrcamento();
    carregarDashboard();
  } catch (e) {
    alert("Erro ao excluir orçamento: " + e.message);
  }
}

// ==================== REGISTRO DE TRANSAÇÕES ====================

async function carregarDadosRegistro() {
  try {
    const [contasRes, despesaRes, receitaRes] = await Promise.all([
      fetch(`${BASE_API}/contas?id_usuario=${ID_USUARIO}`),
      fetch(`${BASE_API}/categorias?id_usuario=${ID_USUARIO}&tipo=DESPESA`),
      fetch(`${BASE_API}/categorias?id_usuario=${ID_USUARIO}&tipo=RECEITA`),
    ]);

    contasUsuario = await contasRes.json();
    categoriasDespesa = await despesaRes.json();
    categoriasReceita = await receitaRes.json();

    const selectContaOrigem = document.getElementById("select-conta-origem");
    const selectContaDestino = document.getElementById("select-conta-destino");
    selectContaOrigem.innerHTML = '<option value="">Selecione...</option>';
    selectContaDestino.innerHTML = '<option value="">Selecione...</option>';

    contasUsuario.forEach((conta) => {
      const option = `<option value="${conta.id_conta}">${conta.nome}</option>`;
      selectContaOrigem.innerHTML += option;
      selectContaDestino.innerHTML += option;
    });

    renderizarCategoriasRegistro("DESPESA");
  } catch (error) {
    mostrarNotificacao(
      "danger",
      "Erro de Carregamento",
      "Não foi possível carregar contas e categorias."
    );
  }
}

function renderizarCategoriasRegistro(tipo) {
  const select = document.getElementById("select-categoria-registro");
  const categorias = tipo === "RECEITA" ? categoriasReceita : categoriasDespesa;

  select.innerHTML = '<option value="">Selecione...</option>';
  categorias.forEach((cat) => {
    select.innerHTML += `<option value="${cat.id_categoria}">${cat.nome}</option>`;
  });
}

function prepararTelaRegistro() {
  document.getElementById("form-transacao").reset();
  document.getElementById("input-data").valueAsDate = new Date();
  document.getElementById("input-efetuada").checked = false;
  setTipoRegistro("DESPESA");
  carregarDadosRegistro();
}

function setTipoRegistro(tipo) {
  ["btn-despesa", "btn-receita", "btn-transferencia"].forEach((id) => {
    const btn = document.getElementById(id);
    btn.classList.remove("active", "despesa", "receita", "transferencia");
  });

  const btnAtivo = document.getElementById(`btn-${tipo.toLowerCase()}`);
  if (btnAtivo) {
    btnAtivo.classList.add("active", tipo.toLowerCase());
  }

  document.getElementById("registro-tipo").value = tipo;

  const grupoCategoria = document.getElementById("grupo-categoria");
  const grupoContaDestino = document.getElementById("grupo-conta-destino");

  if (tipo === "TRANSFERENCIA") {
    grupoCategoria.style.display = "none";
    grupoContaDestino.style.display = "block";
    document
      .getElementById("select-categoria-registro")
      .removeAttribute("required");
    document
      .getElementById("select-conta-destino")
      .setAttribute("required", "required");
  } else {
    grupoCategoria.style.display = "block";
    grupoContaDestino.style.display = "none";
    document
      .getElementById("select-categoria-registro")
      .setAttribute("required", "required");
    document.getElementById("select-conta-destino").removeAttribute("required");
    renderizarCategoriasRegistro(tipo);
  }
}

// ==================== NAVEGAÇÃO ====================

function mostrarTela(tela) {
  document.getElementById("dashboard-screen").style.display = "none";
  document.getElementById("extrato-screen").style.display = "none";
  document.getElementById("orcamento-screen").style.display = "none";
  document.getElementById("registrar-screen").style.display = "none";
  document.getElementById("perfil-screen").style.display = "none";

  document.querySelectorAll(".nav-item").forEach((item) => {
    item.classList.remove("active");
    item.classList.remove("highlight");
  });

  const navItems = document.querySelectorAll(".bottom-nav .nav-item");
  if (tela === "dashboard") {
    document.getElementById("dashboard-screen").style.display = "block";
    navItems[0].classList.add("active");
    carregarDashboard();
  } else if (tela === "extrato") {
    document.getElementById("extrato-screen").style.display = "block";
    navItems[1].classList.add("active");
    carregarExtrato();
  } else if (tela === "registrar") {
    document.getElementById("registrar-screen").style.display = "block";
    navItems[2].classList.add("highlight");
    prepararTelaRegistro();
  } else if (tela === "orcamento") {
    document.getElementById("orcamento-screen").style.display = "block";
    navItems[3].classList.add("active");
    carregarOrcamento();
  } else if (tela === "perfil") {
    document.getElementById("perfil-screen").style.display = "block";
    navItems[4].classList.add("active");
    carregarPerfil();
  }
}

// ==================== EVENTOS ====================

document.addEventListener("DOMContentLoaded", async () => {
  atualizarSaudacao();
  initTheme();
  await carregarConfiguracoesIniciais();
  carregarDashboard();

  // Event listener para formulário de transação
  const formTransacao = document.getElementById("form-transacao");
  if (formTransacao) {
    formTransacao.addEventListener("submit", async function (event) {
      event.preventDefault();

      const valor = parseFloat(
        document.getElementById("input-valor-registro").value
      );
      const tipo = document.getElementById("registro-tipo").value;
      const id_conta_origem = document.getElementById(
        "select-conta-origem"
      ).value;
      const id_conta_destino = document.getElementById(
        "select-conta-destino"
      ).value;
      const id_categoria = document.getElementById(
        "select-categoria-registro"
      ).value;
      const descricao = document.getElementById("input-descricao").value;
      const data_transacao = document.getElementById("input-data").value;
      const efetuada = document.getElementById("input-efetuada").checked;

      if (valor <= 0 || !id_conta_origem) {
        mostrarNotificacao(
          "warning",
          "Dados Inválidos",
          "Verifique o valor e a conta de origem."
        );
        return;
      }

      let endpoint = `${BASE_API}/transacoes`;
      let payload = {
        id_usuario: ID_USUARIO,
        valor: valor,
        descricao: descricao,
        data_transacao: data_transacao,
        efetuada: efetuada,
      };

      if (tipo === "TRANSFERENCIA") {
        if (id_conta_origem === id_conta_destino) {
          mostrarNotificacao(
            "warning",
            "Erro",
            "A conta de origem e destino devem ser diferentes."
          );
          return;
        }
        endpoint = `${BASE_API}/transferencia`;
        payload = {
          ...payload,
          id_conta_origem,
          id_conta_destino,
          tipo_movimentacao: "TRANSFERENCIA",
        };
      } else {
        payload = {
          ...payload,
          id_conta: id_conta_origem,
          id_categoria: id_categoria,
          tipo_movimentacao: tipo,
        };
      }

      try {
        const res = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
          throw new Error(data.error || "Falha ao registrar.");
        }

        mostrarNotificacao(
          "success",
          "Sucesso!",
          `${tipo} registrada com sucesso.`,
          5000
        );

        // Resetar todos os campos do formulário
        document.getElementById("form-transacao").reset();
        document.getElementById("input-efetuada").checked = false;
        document.getElementById("input-data").valueAsDate = new Date();
        setTipoRegistro("DESPESA");

        mostrarTela("dashboard");
      } catch (e) {
        console.error("Erro de API:", e);
        mostrarNotificacao(
          "danger",
          "Erro no Servidor",
          e.message || "Falha na comunicação com o backend.",
          8000
        );
      }
    });
  }
});

// Aplica configurações iniciais do usuário (ex.: ocultar saldo por padrão)
async function carregarConfiguracoesIniciais() {
  try {
    const resposta = await fetch(
      `${BASE_API}/user/perfil?id_usuario=${ID_USUARIO}`
    );
    const dados = await resposta.json();
    if (!resposta.ok) return;

    const ocultarSaldoPorPadrao = !!dados.config_saldo_oculto;
    saldoVisivel = !ocultarSaldoPorPadrao;

    // Moeda preferida do usuário
    const moeda = dados.config_moeda || BASE_CURRENCY;
    await setCurrency(moeda);

    // Atualiza ícone do botão de privacidade imediatamente
    const toggleButton = document.getElementById("btn-privacy");
    if (toggleButton) {
      toggleButton.innerHTML = saldoVisivel
        ? '<i class="fa-solid fa-eye"></i>'
        : '<i class="fa-solid fa-lock"></i>';
    }

    // Se oculto, garante que o valor exibido esteja mascarado até o dashboard recarregar
    if (!saldoVisivel) {
      const saldoElement = document.getElementById("saldo-total");
      if (saldoElement) saldoElement.textContent = "••••••";
    }
  } catch (e) {
    // Em caso de erro de rede/API, mantém padrão (visível)
  }
}

// ==================== TEMA CLARO/ESCURO ====================
function applyTheme(theme) {
  const root = document.documentElement;
  if (theme === "dark") {
    root.setAttribute("data-theme", "dark");
  } else {
    root.removeAttribute("data-theme");
  }

  const btn = document.getElementById("btn-theme-toggle");
  const txt = document.getElementById("theme-toggle-text");
  if (btn && txt) {
    if (theme === "dark") {
      btn.innerHTML =
        '<i class="fa-solid fa-sun"></i> <span id="theme-toggle-text">Tema claro</span>';
    } else {
      btn.innerHTML =
        '<i class="fa-solid fa-moon"></i> <span id="theme-toggle-text">Tema escuro</span>';
    }
  }
}

function initTheme() {
  try {
    const saved = localStorage.getItem("lumis_theme");
    const theme = saved || "light";
    applyTheme(theme);
  } catch (_) {
    applyTheme("light");
  }
}

function toggleTheme() {
  const isDark = document.documentElement.getAttribute("data-theme") === "dark";
  const next = isDark ? "light" : "dark";
  applyTheme(next);
  try {
    localStorage.setItem("lumis_theme", next);
  } catch (_) {}
}

window.toggleTheme = toggleTheme;

// ==================== MOEDA: Helpers ====================
async function setCurrency(currencyCode) {
  try {
    CURRENT_CURRENCY = (currencyCode || BASE_CURRENCY).toUpperCase();
    currencyFormatter = new Intl.NumberFormat("pt-BR", {
      style: "currency",
      currency: CURRENT_CURRENCY,
    });
    await updateCurrencyFactor();
  } catch (e) {
    CURRENT_CURRENCY = BASE_CURRENCY;
    currencyFormatter = new Intl.NumberFormat("pt-BR", {
      style: "currency",
      currency: BASE_CURRENCY,
    });
  }
}

async function updateCurrencyFactor() {
  if (CURRENT_CURRENCY === BASE_CURRENCY) {
    currencyFactor = 1;
    return;
  }

  // Tenta usar cache recente (valido por 6h) - MAS APENAS SE FOR VÁLIDO
  try {
    const cacheKey = `lumis_rate_${BASE_CURRENCY}_${CURRENT_CURRENCY}`;
    const cacheStr = localStorage.getItem(cacheKey);
    if (cacheStr) {
      const cached = JSON.parse(cacheStr);
      const fatorCache = Number(cached.factor);
      const idadeCache = Date.now() - (cached.ts || 0);
      const cacheValido = idadeCache < 1000 * 60 * 60 * 6;

      if (cached && fatorCache > 0 && fatorCache !== 1 && cacheValido) {
        currencyFactor = fatorCache;
        return;
      }
    }
  } catch (e) {}

  // Helper com timeout para evitar travas
  const withTimeout = (promise, ms = 6000) =>
    Promise.race([
      promise,
      new Promise((_, reject) =>
        setTimeout(() => reject(new Error("timeout")), ms)
      ),
    ]);

  // Tenta múltiplas APIs públicas gratuitas, em ordem
  let fator = NaN;

  // 1) open.er-api.com (funciona sem chave API)
  try {
    const url1 = `https://open.er-api.com/v6/latest/${BASE_CURRENCY}`;
    const res1 = await withTimeout(fetch(url1));
    const data1 = await res1.json();
    if (
      data1 &&
      data1.result === "success" &&
      data1.rates &&
      data1.rates[CURRENT_CURRENCY] != null
    ) {
      fator = Number(data1.rates[CURRENT_CURRENCY]);
    }
  } catch (e) {}

  if (!(fator > 0)) {
    try {
      // 2) jsdelivr fawazahmed currency-api
      const baseLc = BASE_CURRENCY.toLowerCase();
      const targetLc = CURRENT_CURRENCY.toLowerCase();
      const url2 = `https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/${baseLc}.json`;
      const res2 = await withTimeout(fetch(url2));
      const data2 = await res2.json();
      if (data2 && data2[baseLc] && data2[baseLc][targetLc] != null) {
        fator = Number(data2[baseLc][targetLc]);
      }
    } catch (e) {}
  }

  currencyFactor = fator > 0 ? fator : 1;

  // Grava cache
  try {
    localStorage.setItem(
      `lumis_rate_${BASE_CURRENCY}_${CURRENT_CURRENCY}`,
      JSON.stringify({ factor: currencyFactor, ts: Date.now() })
    );
  } catch (e) {}
}

function refreshTelaAtual() {
  const telaAtiva = document.querySelector(
    '[id$="-screen"]:not([style*="display: none"])'
  );
  if (!telaAtiva) return;
  const telaId = telaAtiva.id.replace("-screen", "");
  if (telaId === "dashboard") carregarDashboard();
  else if (telaId === "extrato") carregarExtrato();
  else if (telaId === "orcamento") carregarOrcamento();
  else if (telaId === "registrar") prepararTelaRegistro();
  else if (telaId === "perfil") carregarPerfil();
}

// ==================== PERFIL ====================

async function carregarPerfil() {
  try {
    const resposta = await fetch(
      `${BASE_API}/user/perfil?id_usuario=${ID_USUARIO}`
    );
    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.error || "Erro ao carregar perfil");
    }

    // Atualizar informações na tela
    document.getElementById("perfil-nome").textContent = dados.nome || "-";
    document.getElementById("perfil-email").textContent = dados.email || "-";

    const dataRegistro = dados.data_registro
      ? new Date(dados.data_registro).toLocaleDateString("pt-BR")
      : "-";
    document.getElementById("perfil-data-registro").textContent = dataRegistro;

    // Configurações
    document.getElementById("config-saldo-oculto").checked =
      dados.config_saldo_oculto || false;
    document.getElementById("config-notificacoes").checked =
      dados.config_notificacoes !== false;
    document.getElementById("config-moeda").value = dados.config_moeda || "BRL";
    document.getElementById("config-primeiro-dia").value =
      dados.config_primeiro_dia_mes || 1;
  } catch (error) {
    console.error("Erro ao carregar perfil:", error);
    mostrarNotificacao("danger", "Erro", "Não foi possível carregar o perfil");
  }
}

async function salvarConfiguracao(campo, valor) {
  try {
    const payload = {
      id_usuario: ID_USUARIO,
      [campo]: valor,
    };

    const resposta = await fetch(`${BASE_API}/user/configuracoes`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.error || "Erro ao salvar configuração");
    }

    // Aplicar imediatamente mudanças de moeda ANTES de mostrar notificação
    if (campo === "config_moeda") {
      console.log(`[CONFIG] Aplicando nova moeda: ${valor}`);
      await setCurrency(String(valor || BASE_CURRENCY));
      console.log(`[CONFIG] Moeda aplicada, fator: ${currencyFactor}`);
      // Pequeno delay para garantir que a taxa foi obtida
      await new Promise((resolve) => setTimeout(resolve, 100));
      refreshTelaAtual();
    }

    mostrarNotificacao("success", "Salvo!", "Configuração atualizada", 2000);
  } catch (error) {
    console.error("Erro ao salvar configuração:", error);
    mostrarNotificacao("danger", "Erro", error.message);
  }
}

// Modais de Perfil
function abrirModalEditarPerfil() {
  const nome = document.getElementById("perfil-nome").textContent;
  const email = document.getElementById("perfil-email").textContent;

  document.getElementById("input-perfil-nome").value = nome;
  document.getElementById("input-perfil-email").value = email;
  document.getElementById("input-perfil-senha-confirmacao").value = "";

  document.getElementById("modal-editar-perfil").style.display = "flex";
}

function fecharModalEditarPerfil() {
  document.getElementById("modal-editar-perfil").style.display = "none";
}

async function salvarPerfilEditado() {
  const nome = document.getElementById("input-perfil-nome").value.trim();
  const email = document.getElementById("input-perfil-email").value.trim();
  const senha = document.getElementById("input-perfil-senha-confirmacao").value;

  if (!nome || !email || !senha) {
    mostrarNotificacao("warning", "Atenção", "Preencha todos os campos");
    return;
  }

  try {
    const payload = {
      id_usuario: ID_USUARIO,
      nome,
      email,
      senha_confirmacao: senha,
    };

    const resposta = await fetch(`${BASE_API}/user/perfil`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.error || "Erro ao atualizar perfil");
    }

    mostrarNotificacao("success", "Sucesso!", "Perfil atualizado com sucesso");
    fecharModalEditarPerfil();
    carregarPerfil();
  } catch (error) {
    console.error("Erro ao atualizar perfil:", error);
    mostrarNotificacao("danger", "Erro", error.message);
  }
}

function abrirModalAlterarSenha() {
  document.getElementById("input-senha-atual").value = "";
  document.getElementById("input-senha-nova").value = "";
  document.getElementById("input-senha-nova-confirmar").value = "";
  document.getElementById("modal-alterar-senha").style.display = "flex";
}

function fecharModalAlterarSenha() {
  document.getElementById("modal-alterar-senha").style.display = "none";
}

async function salvarNovaSenha() {
  const senhaAtual = document.getElementById("input-senha-atual").value;
  const senhaNova = document.getElementById("input-senha-nova").value;
  const senhaConfirmar = document.getElementById(
    "input-senha-nova-confirmar"
  ).value;

  if (!senhaAtual || !senhaNova || !senhaConfirmar) {
    mostrarNotificacao("warning", "Atenção", "Preencha todos os campos");
    return;
  }

  if (senhaNova !== senhaConfirmar) {
    mostrarNotificacao("warning", "Atenção", "As senhas não coincidem");
    return;
  }

  if (senhaNova.length < 6) {
    mostrarNotificacao(
      "warning",
      "Atenção",
      "A senha deve ter pelo menos 6 caracteres"
    );
    return;
  }

  try {
    const payload = {
      id_usuario: ID_USUARIO,
      senha_atual: senhaAtual,
      senha_nova: senhaNova,
    };

    const resposta = await fetch(`${BASE_API}/user/senha`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.error || "Erro ao alterar senha");
    }

    mostrarNotificacao("success", "Sucesso!", "Senha alterada com sucesso");
    fecharModalAlterarSenha();
  } catch (error) {
    console.error("Erro ao alterar senha:", error);
    mostrarNotificacao("danger", "Erro", error.message);
  }
}

function abrirModalExcluirConta() {
  document.getElementById("input-excluir-senha").value = "";
  document.getElementById("modal-excluir-conta").style.display = "flex";
}

function fecharModalExcluirConta() {
  document.getElementById("modal-excluir-conta").style.display = "none";
}

async function confirmarExclusaoConta() {
  const senha = document.getElementById("input-excluir-senha").value;

  if (!senha) {
    mostrarNotificacao("warning", "Atenção", "Digite sua senha para confirmar");
    return;
  }

  if (!confirm("TEM CERTEZA? Esta ação é IRREVERSÍVEL!")) {
    return;
  }

  try {
    const payload = {
      id_usuario: ID_USUARIO,
      senha_confirmacao: senha,
    };

    const resposta = await fetch(`${BASE_API}/user/conta`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.error || "Erro ao excluir conta");
    }

    mostrarNotificacao(
      "success",
      "Conta Excluída",
      "Sua conta foi excluída com sucesso",
      3000
    );

    setTimeout(() => {
      window.location.href = "/";
    }, 3000);
  } catch (error) {
    console.error("Erro ao excluir conta:", error);
    mostrarNotificacao("danger", "Erro", error.message);
  }
}

async function exportarDados() {
  try {
    const url = `${BASE_API}/user/exportar?id_usuario=${ID_USUARIO}&formato=xlsx`;
    window.open(url, "_blank");
    mostrarNotificacao(
      "info",
      "Exportando...",
      "O download do arquivo XLSX será iniciado"
    );
  } catch (error) {
    console.error("Erro ao exportar dados:", error);
    mostrarNotificacao("danger", "Erro", "Não foi possível exportar os dados");
  }
}

function abrirModalGerenciarContas() {
  document.getElementById("modal-gerenciar-contas").style.display = "flex";
  carregarContasGerenciar();
}

function fecharModalGerenciarContas() {
  document.getElementById("modal-gerenciar-contas").style.display = "none";
}

async function carregarContasGerenciar() {
  const lista = document.getElementById("lista-contas-gerenciar");
  lista.innerHTML =
    '<div class="loading"><div class="spinner"></div> Carregando...</div>';

  try {
    const resposta = await fetch(`${BASE_API}/contas?id_usuario=${ID_USUARIO}`);
    const dados = await resposta.json();

    if (!resposta.ok) throw new Error(dados.error);

    if (!dados.length) {
      lista.innerHTML =
        '<div class="empty-state">Nenhuma conta cadastrada</div>';
      return;
    }

    // Armazenar contas globalmente para uso em edição
    contasUsuario = dados;

    lista.innerHTML = dados
      .map(
        (conta) => `
      <div class="item-gerenciar">
        <div class="item-gerenciar-info">
          <div class="item-gerenciar-nome">${conta.nome}</div>
          <div class="item-gerenciar-detalhes">
            ${conta.tipo_conta} - Saldo: ${formatarMoeda(conta.saldo_inicial)}
          </div>
        </div>
        <div class="item-gerenciar-acoes">
          <button class="btn-icon" onclick="editarConta(${conta.id_conta})">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="btn-icon btn-icon-danger" onclick="excluirContaComConfirmacao(${
            conta.id_conta
          }, '${conta.nome.replace(/'/g, "\\'")}')">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    `
      )
      .join("");
  } catch (error) {
    console.error("Erro ao carregar contas:", error);
    lista.innerHTML = '<div class="empty-state">Erro ao carregar contas</div>';
  }
}

function abrirFormNovaConta() {
  document.getElementById("modal-form-conta").style.display = "flex";
  document.getElementById("conta-modal-title").textContent = "Nova Conta";
  document.getElementById("hidden-id-conta").value = "";
  document.getElementById("input-conta-nome").value = "";
  document.getElementById("select-conta-tipo").value = "CORRENTE";
  document.getElementById("input-conta-saldo").value = "0";
  document.getElementById("input-conta-exibir").checked = true;
  const btnDeletar = document.getElementById("btn-deletar-conta");
  if (btnDeletar) btnDeletar.style.display = "none";
}

function editarConta(idConta) {
  const conta = contasUsuario.find((c) => c.id_conta === idConta);
  if (!conta) {
    mostrarNotificacao("danger", "Erro", "Conta não encontrada.");
    return;
  }

  document.getElementById("modal-form-conta").style.display = "flex";
  document.getElementById("conta-modal-title").textContent = "Editar Conta";
  document.getElementById("hidden-id-conta").value = conta.id_conta;
  document.getElementById("input-conta-nome").value = conta.nome;
  document.getElementById("select-conta-tipo").value = conta.tipo_conta;
  document.getElementById("input-conta-saldo").value = parseFloat(
    conta.saldo_inicial || 0
  );
  document.getElementById("input-conta-exibir").checked =
    conta.exibir_no_dashboard == 1;
  const btnDeletar = document.getElementById("btn-deletar-conta");
  if (btnDeletar) btnDeletar.style.display = "flex";
}

function fecharFormConta() {
  document.getElementById("modal-form-conta").style.display = "none";
}

async function salvarConta() {
  const id_conta = document.getElementById("hidden-id-conta").value;
  const nome = document.getElementById("input-conta-nome").value.trim();
  const tipo_conta = document.getElementById("select-conta-tipo").value;
  const saldo_inicial = parseFloat(
    document.getElementById("input-conta-saldo").value || 0
  );
  const exibir_no_dashboard = document.getElementById("input-conta-exibir")
    .checked
    ? 1
    : 0;

  if (!nome) {
    mostrarNotificacao("warning", "Atenção", "O nome da conta é obrigatório.");
    return;
  }

  const mode = id_conta ? "edit" : "create";
  const method = mode === "edit" ? "PUT" : "POST";
  const endpoint = `${BASE_API}/contas`;

  const payload = {
    id_usuario: ID_USUARIO,
    nome: nome,
    tipo_conta: tipo_conta,
    saldo_inicial: saldo_inicial,
    exibir_no_dashboard: exibir_no_dashboard,
  };

  if (mode === "edit") {
    payload.id_conta = Number(id_conta);
  }

  try {
    const res = await fetch(endpoint, {
      method: method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(
        data.error || `Falha ao ${mode === "edit" ? "editar" : "criar"} conta.`
      );
    }

    mostrarNotificacao(
      "success",
      "Sucesso!",
      `Conta ${nome} salva com sucesso.`
    );
    fecharFormConta();
    carregarContasGerenciar();
  } catch (e) {
    console.error("Erro ao salvar conta:", e);
    mostrarNotificacao("danger", "Erro", e.message);
  }
}

function confirmarExclusaoConta() {
  const id_conta = document.getElementById("hidden-id-conta").value;
  const nome = document.getElementById("input-conta-nome").value;

  if (
    !confirm(
      `Tem certeza que deseja EXCLUIR a conta "${nome}"? Se houver transações vinculadas, a exclusão falhará.`
    )
  ) {
    return;
  }

  excluirConta(id_conta);
}

function excluirContaComConfirmacao(idConta, nomeConta) {
  if (!confirm(`Tem certeza que deseja excluir a conta "${nomeConta}"?`)) {
    return;
  }
  excluirConta(idConta);
}

async function excluirConta(idConta) {
  try {
    const resposta = await fetch(`${BASE_API}/contas`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_conta: Number(idConta) }),
    });

    const texto = await resposta.text();
    let dados;

    try {
      dados = JSON.parse(texto);
    } catch (e) {
      throw new Error("Erro no servidor. Verifique os logs do PHP.");
    }

    if (!resposta.ok) {
      if (
        dados.error &&
        (dados.error.includes("Integrity constraint") ||
          dados.error.includes("vinculada"))
      ) {
        throw new Error(
          "Não é possível excluir. A conta está vinculada a transações."
        );
      }
      throw new Error(dados.error || "Erro desconhecido ao excluir.");
    }

    mostrarNotificacao(
      "success",
      "Excluída!",
      "Conta removida com sucesso.",
      4000
    );
    fecharFormConta();
    carregarContasGerenciar();
  } catch (error) {
    console.error("Erro ao excluir conta:", error);
    mostrarNotificacao("danger", "Erro de Exclusão", error.message);
  }
}

function abrirModalGerenciarCategorias() {
  document.getElementById("modal-gerenciar-categorias").style.display = "flex";
  carregarCategoriasGerenciar();
}

function fecharModalGerenciarCategorias() {
  document.getElementById("modal-gerenciar-categorias").style.display = "none";
}

async function carregarCategoriasGerenciar() {
  const lista = document.getElementById("lista-categorias-gerenciar");
  if (!lista) return;

  lista.innerHTML =
    '<div class="loading"><div class="spinner"></div> Carregando...</div>';

  try {
    const resposta = await fetch(
      `${BASE_API}/categorias?id_usuario=${ID_USUARIO}`
    );
    const categorias = await resposta.json();

    if (!resposta.ok) {
      lista.innerHTML = `<div class='empty-state'>Erro ao carregar categorias</div>`;
      return;
    }

    if (!categorias || categorias.length === 0) {
      lista.innerHTML =
        '<div class="empty-state"><div class="empty-icon">📋</div><div>Nenhuma categoria cadastrada</div></div>';
      return;
    }

    // Armazenar categorias globalmente para uso em edição
    categoriasAtuais = categorias;

    lista.innerHTML = categorias
      .map(
        (cat) => `
      <div class="categoria-item" style="display:flex;justify-content:space-between;align-items:center;padding:15px;background:#f8f9fa;border-radius:8px;margin-bottom:10px;">
        <div>
          <div style="font-weight:600;margin-bottom:5px;">${cat.nome}</div>
          <div style="font-size:12px;color:#666;">Tipo: ${cat.tipo}</div>
        </div>
        <div style="display:flex;gap:8px;">
          <button class="btn" style="padding:6px 12px;" onclick="editarCategoria(${
            cat.id_categoria
          })">
            <i class="fa-solid fa-edit"></i> Editar
          </button>
          <button class="btn btn-danger" style="padding:6px 12px;" onclick="excluirCategoriaComConfirmacao(${
            cat.id_categoria
          }, '${cat.nome.replace(/'/g, "\\'")}')">
            <i class="fa-solid fa-trash"></i> Excluir
          </button>
        </div>
      </div>
    `
      )
      .join("");
  } catch (error) {
    console.error("Erro ao carregar categorias:", error);
    lista.innerHTML =
      '<div class="empty-state">Erro ao carregar categorias</div>';
  }
}

function abrirFormNovaCategoria() {
  document.getElementById("modal-form-categoria").style.display = "flex";
  document.getElementById("categoria-modal-title").textContent =
    "Nova Categoria";
  document.getElementById("hidden-id-categoria").value = "";
  document.getElementById("input-categoria-nome").value = "";
  document.getElementById("select-categoria-tipo").value = "DESPESA";
  document.getElementById("input-categoria-icone").value = "";
  document.getElementById("input-categoria-cor").value = "#3B82F6";
  const btnDeletar = document.getElementById("btn-deletar-categoria");
  if (btnDeletar) btnDeletar.style.display = "none";
}

function editarCategoria(idCategoria) {
  const categoria = categoriasAtuais.find(
    (c) => c.id_categoria === idCategoria
  );
  if (!categoria) {
    mostrarNotificacao("danger", "Erro", "Categoria não encontrada.");
    return;
  }

  document.getElementById("modal-form-categoria").style.display = "flex";
  document.getElementById("categoria-modal-title").textContent =
    "Editar Categoria";
  document.getElementById("hidden-id-categoria").value = categoria.id_categoria;
  document.getElementById("input-categoria-nome").value = categoria.nome;
  document.getElementById("select-categoria-tipo").value = categoria.tipo;
  document.getElementById("input-categoria-icone").value =
    categoria.icone || "";
  document.getElementById("input-categoria-cor").value =
    categoria.cor_hex || "#3B82F6";
  const btnDeletar = document.getElementById("btn-deletar-categoria");
  if (btnDeletar) btnDeletar.style.display = "flex";
}

async function carregarDespesasRecorrencia() {
  const lista = document.getElementById("lista-despesas-recorrencia");
  if (!lista) return;
  lista.innerHTML =
    '<div class="loading"><div class="spinner"></div> Carregando...</div>';
  try {
    // Buscar transações de TODOS os meses (sem filtro de mes_ano)
    const resposta = await fetch(
      `${BASE_API}/despesas?id_usuario=${ID_USUARIO}`
    );
    const dados = await resposta.json();
    if (!resposta.ok) {
      lista.innerHTML = `<div class='empty-state'>Erro: ${
        dados.error || resposta.status
      }</div>`;
      return;
    }
    if (!Array.isArray(dados)) {
      lista.innerHTML = `<div class='empty-state'>Resposta inesperada</div>`;
      return;
    }
    if (!dados.length) {
      lista.innerHTML =
        '<div class="empty-state">Nenhuma despesa encontrada</div>';
      return;
    }

    // Remover duplicatas baseado em descrição, valor, categoria e tipo
    const transacoesUnicas = [];
    const chaves = new Set();

    for (const d of dados) {
      const chave = `${d.descricao}-${d.valor}-${d.id_categoria}-${d.tipo_movimentacao}`;
      if (!chaves.has(chave)) {
        chaves.add(chave);
        transacoesUnicas.push(d);
      }
    }

    lista.innerHTML = transacoesUnicas
      .map((d) => {
        const jaRecorrente =
          String(d.recorrente) === "1" || d.recorrente === true;
        const tipoLabel =
          d.tipo_movimentacao === "RECEITA" ? "💰 Receita" : "💸 Despesa";
        const tipoClass =
          d.tipo_movimentacao === "RECEITA" ? "tipo-receita" : "tipo-despesa";

        return `
        <div class="item-recorrencia">
          <div class="info">
            <div class="descricao">${
              d.descricao || "Sem descrição"
            } <span class="${tipoClass}" style="font-size:0.85em;opacity:0.7;">${tipoLabel}</span></div>
            <div class="meta">
              <span class="valor ${
                jaRecorrente ? "recorrente" : ""
              }">${formatarMoeda(d.valor || 0)}</span>
              <span class="data">${formatarData(
                d.data_transacao || MES_ANO + "-01"
              )}</span>
              ${
                jaRecorrente
                  ? '<span class="tag-recorrente">Recorrente</span>'
                  : ""
              }
            </div>
          </div>
          <div class="acoes">
            ${
              jaRecorrente
                ? `<button class="btn" style="background:#EF4444;" onclick="removerRecorrencia(${d.id_transacao})">Remover Recorrência</button>`
                : `<button class="btn" onclick="tornarDespesaRecorrente(${d.id_transacao})">Tornar Recorrente</button>`
            }
          </div>
        </div>
      `;
      })
      .join("");
  } catch (e) {
    lista.innerHTML = `<div class='empty-state'>Erro ao carregar despesas</div>`;
    console.error("Erro ao carregar despesas recorrência:", e);
  }
}

// ==================== MODAL RECORRÊNCIAS (RESTAURADO) ====================
function abrirModalGerenciarRecorrencias() {
  const modal = document.getElementById("modal-gerenciar-recorrencias");
  if (!modal) {
    mostrarNotificacao(
      "danger",
      "Erro",
      "Modal de recorrências não encontrado."
    );
    return;
  }
  modal.style.display = "flex";
  carregarDespesasRecorrencia();
}

function fecharModalGerenciarRecorrencias() {
  const modal = document.getElementById("modal-gerenciar-recorrencias");
  if (modal) modal.style.display = "none";
}

// Expor para uso pelos atributos onclick do HTML
window.abrirModalGerenciarRecorrencias = abrirModalGerenciarRecorrencias;
window.fecharModalGerenciarRecorrencias = fecharModalGerenciarRecorrencias;

// ==================== GERAÇÃO AUTOMÁTICA DE RECORRÊNCIAS ====================

async function gerarRecorrenciasAutomaticas() {
  try {
    const resposta = await fetch(`${BASE_API}/recorrencias/gerar`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_usuario: ID_USUARIO, mes_ano: MES_ANO }),
    });

    const textoResposta = await resposta.text();
    const dados = JSON.parse(textoResposta);

    if (!dados.success) {
      console.error(
        "Erro ao gerar recorrências:",
        dados.mensagem || dados.error
      );
    }
  } catch (e) {
    console.error("Erro ao gerar recorrências:", e.message);
  }
}

function fecharFormCategoria() {
  document.getElementById("modal-form-categoria").style.display = "none";
}

async function salvarCategoria() {
  const id_categoria = document.getElementById("hidden-id-categoria").value;
  const nome = document.getElementById("input-categoria-nome").value.trim();
  const tipo = document.getElementById("select-categoria-tipo").value;
  const icone = document.getElementById("input-categoria-icone").value.trim();
  const cor_hex = document.getElementById("input-categoria-cor").value;

  if (!nome) {
    mostrarNotificacao(
      "warning",
      "Atenção",
      "O nome da categoria é obrigatório."
    );
    return;
  }

  const mode = id_categoria ? "edit" : "create";
  const method = mode === "edit" ? "PUT" : "POST";
  let endpoint = `${BASE_API}/categorias`;

  const payload = {
    id_usuario: ID_USUARIO,
    nome: nome,
    tipo: tipo,
    icone: icone || null,
    cor_hex: cor_hex,
  };

  if (mode === "edit") {
    payload.id_categoria = Number(id_categoria);
  }

  try {
    const res = await fetch(endpoint, {
      method: method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(
        data.error ||
          `Falha ao ${mode === "edit" ? "editar" : "criar"} categoria.`
      );
    }

    mostrarNotificacao(
      "success",
      "Sucesso!",
      `Categoria ${nome} salva com sucesso.`
    );
    fecharFormCategoria();
    carregarCategoriasGerenciar(); // Recarrega a lista
  } catch (e) {
    console.error("Erro ao salvar categoria:", e);
    mostrarNotificacao("danger", "Erro", e.message);
  }
}

function confirmarExclusaoCategoria() {
  const id_categoria = document.getElementById("hidden-id-categoria").value;
  const nome = document.getElementById("input-categoria-nome").value;

  if (
    !confirm(
      `Tem certeza que deseja EXCLUIR a categoria "${nome}"? Se houver transações vinculadas, a exclusão falhará.`
    )
  ) {
    return;
  }

  excluirCategoria(id_categoria);
}

function excluirCategoriaComConfirmacao(idCategoria, nomeCategoria) {
  if (
    !confirm(`Tem certeza que deseja excluir a categoria "${nomeCategoria}"?`)
  ) {
    return;
  }
  excluirCategoria(idCategoria);
}

async function excluirCategoria(idCategoria) {
  try {
    const resposta = await fetch(`${BASE_API}/categorias`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_categoria: Number(idCategoria) }),
    });

    const texto = await resposta.text();
    let dados;

    try {
      dados = JSON.parse(texto);
    } catch (e) {
      throw new Error("Erro no servidor. Verifique os logs do PHP.");
    }

    if (!resposta.ok) {
      // Tratamento para RESTRICT do SQL (transações vinculadas)
      if (
        dados.error &&
        (dados.error.includes("Integrity constraint") ||
          dados.error.includes("vinculada"))
      ) {
        throw new Error(
          "Não é possível excluir. A categoria está vinculada a uma ou mais transações/orçamentos."
        );
      }
      throw new Error(dados.error || "Erro desconhecido ao excluir.");
    }

    mostrarNotificacao(
      "success",
      "Excluída!",
      "Categoria removida com sucesso.",
      4000
    );
    carregarCategoriasGerenciar();
  } catch (error) {
    console.error("Erro ao excluir categoria:", error);
    mostrarNotificacao("danger", "Erro de Exclusão", error.message);
  }
}

function abrirModalSobre() {
  document.getElementById("modal-sobre").style.display = "flex";
}

function fecharModalSobre() {
  document.getElementById("modal-sobre").style.display = "none";
}

window.mostrarTela = mostrarTela;

async function tornarDespesaRecorrente(idTransacao) {
  if (!idTransacao) return;
  try {
    const resposta = await fetch(`${BASE_API}/recorrencia`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_transacao: idTransacao,
        id_usuario: ID_USUARIO,
      }),
    });
    const dados = await resposta.json();
    if (!resposta.ok || !dados.success) {
      throw new Error(dados.error || "Falha ao marcar recorrente");
    }
    mostrarNotificacao(
      "success",
      "Recorrente",
      "Transação marcada como recorrente!"
    );
    carregarDespesasRecorrencia();
  } catch (e) {
    console.error("Erro recorrência:", e);
    mostrarNotificacao("danger", "Erro", e.message);
  }
}

async function removerRecorrencia(idTransacao) {
  if (!idTransacao) return;
  if (!confirm("Deseja realmente remover esta transação da recorrência?"))
    return;

  try {
    const resposta = await fetch(`${BASE_API}/recorrencia/remover`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_transacao: idTransacao,
        id_usuario: ID_USUARIO,
      }),
    });
    const dados = await resposta.json();
    if (!resposta.ok || !dados.success) {
      throw new Error(dados.error || "Falha ao remover recorrência");
    }
    mostrarNotificacao(
      "success",
      "Removido",
      "Recorrência removida com sucesso!"
    );
    carregarDespesasRecorrencia();
  } catch (e) {
    console.error("Erro ao remover recorrência:", e);
    mostrarNotificacao("danger", "Erro", e.message);
  }
}

async function marcarComoEfetuada(idTransacao) {
  if (!idTransacao) return;

  // Confirmação antes de marcar como efetuada
  if (
    !confirm("Tem certeza que deseja marcar este pagamento como concluído?")
  ) {
    return;
  }

  try {
    const resposta = await fetch(`${BASE_API}/transacoes`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id_transacao: idTransacao,
        efetuada: 1,
      }),
    });
    const dados = await resposta.json();
    if (!resposta.ok || !dados.success) {
      throw new Error(dados.error || "Falha ao marcar como efetuada");
    }
    mostrarNotificacao(
      "success",
      "Confirmado",
      "Transação marcada como efetuada!"
    );
    // Recarregar dashboard para atualizar saldo e listas
    carregarDashboard();
  } catch (e) {
    console.error("Erro ao marcar como efetuada:", e);
    mostrarNotificacao("danger", "Erro", e.message);
  }
}
