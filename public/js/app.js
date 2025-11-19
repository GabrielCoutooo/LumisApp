// Lumis - Sistema de Gestão Financeira - JavaScript

// Configuração da API
const BASE_API = "http://localhost/LumisApp/public/api.php/api";
const ID_USUARIO = 1;
const MES_ANO = "2025-11";

let todasTransacoes = [];
let filtroAtual = "all";
let categoriasDespesa = [];
let categoriasReceita = [];
let contasUsuario = [];
let orcamentosAtuais = [];
let saldoVisivel = true;
let categoriasAtuais = []; // Armazena todas as categorias

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
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(valor);
}

function formatarData(dataStr) {
  const data = new Date(dataStr + "T00:00:00");
  return data.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit" });
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

// ==================== SISTEMA DE NOTIFICAÇÕES ====================

function mostrarNotificacao(tipo, titulo, mensagem, duracao = 5000) {
  const container = document.getElementById("toast-container");
  const toast = document.createElement("div");
  toast.className = `toast ${tipo}`;

  const icones = {
    warning: '<i class="fa-solid fa-triangle-exclamation"></i>',
    danger: '<i class="fa-solid fa-circle-exclamation"></i>',
    success: '<i class="fa-solid fa-circle-check"></i>',
    info: '<i class="fa-solid fa-circle-info"></i>',
  };

  toast.innerHTML = `
        <div class="toast-icon">${icones[tipo] || icones.info}</div>
        <div class="toast-content">
            <div class="toast-title">${titulo}</div>
            <div class="toast-message">${mensagem}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

  container.appendChild(toast);

  if (duracao > 0) {
    setTimeout(() => {
      toast.style.animation = "slideIn 0.3s ease-out reverse";
      setTimeout(() => toast.remove(), 300);
    }, duracao);
  }
}

function processarAlertaOrcamento(alerta) {
  if (!alerta) return;

  if (alerta.tipo === "ESTOURO_ORCAMENTO") {
    mostrarNotificacao("danger", "Orçamento Estourado!", alerta.mensagem, 8000);
  } else if (alerta.tipo === "ALERTA_ORCAMENTO") {
    mostrarNotificacao(
      "warning",
      "Atenção ao Orçamento",
      alerta.mensagem,
      6000
    );
  }
}

function processarAlertaMeta(alerta) {
  if (!alerta) return;

  if (alerta.tipo === "META_CONCLUIDA") {
    mostrarNotificacao("success", "Meta Concluída!", alerta.mensagem, 10000);
  } else if (alerta.tipo === "META_PROXIMA") {
    mostrarNotificacao("info", "Quase lá!", alerta.mensagem, 7000);
  }
}

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

    renderizarOrcamentos(dados.orcamentos || []);
    renderizarPagamentos(dados.proximos_pagamentos || []);
  } catch (error) {
    console.error("Erro ao carregar dashboard:", error);
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
        <div class="pagamento-item">
            <div class="pagamento-info">
                <div class="pagamento-desc">${pag.descricao}</div>
                <div class="pagamento-data">${formatarData(
                  pag.data_transacao
                )}</div>
            </div>
            <div class="pagamento-valor despesa">${formatarMoeda(
              pag.valor
            )}</div>
        </div>
    `
    )
    .join("");
}

// ==================== EXTRATO ====================

async function carregarExtrato() {
  try {
    const resposta = await fetch(
      `${BASE_API}/extrato?id_usuario=${ID_USUARIO}`
    );
    const dados = await resposta.json();
    todasTransacoes = dados;
    renderizarTransacoes(dados);
  } catch (error) {
    console.error("Erro ao carregar extrato:", error);
    document.getElementById("transacoes-list").innerHTML =
      '<div class="empty-state"><div class="empty-icon">❌</div><div>Erro ao carregar transações</div></div>';
  }
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

  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
  });
  event.target.classList.add("active");

  let filtradas = todasTransacoes;
  if (tipo !== "all") {
    filtradas = todasTransacoes.filter((t) => t.tipo_movimentacao === tipo);
  }

  renderizarTransacoes(filtradas);
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
    const resposta = await fetch(
      `${BASE_API}/orcamento?id_usuario=${ID_USUARIO}&mes_ano=${MES_ANO}`
    );
    const orcamentos = await resposta.json();
    orcamentosAtuais = orcamentos || [];
    renderizarListaOrcamento(orcamentosAtuais);
  } catch (e) {
    console.error("Erro ao carregar orçamento:", e);
    document.getElementById("orcamento-list").innerHTML =
      '<div class="empty-state"><div class="empty-icon">❌</div><div>Erro ao carregar orçamentos</div></div>';
  }
}

function renderizarListaOrcamento(itens) {
  const container = document.getElementById("orcamento-list");
  if (!itens || itens.length === 0) {
    container.innerHTML =
      '<div class="empty-state"><div class="empty-icon">📊</div><div>Nenhum orçamento cadastrado</div></div>';
    return;
  }
  container.innerHTML = itens
    .map((orc, idx) => {
      return `
        <div class="orcamento-card">
          <div class="orcamento-header">
            <div class="categoria-name">${
              orc.nome_categoria || "Sem categoria"
            }</div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button class="btn" style="padding:6px 10px;" onclick="abrirModalEditarOrcamentoPorIndice(${idx})">Editar</button>
              <button class="btn btn-danger" style="padding:6px 10px;" onclick="excluirOrcamentoPorIndice(${idx})">Excluir</button>
            </div>
          </div>
          <div class="orcamento-values">Limite: ${formatarMoeda(
            orc.valor_limite
          )}</div>
          <div class="orcamento-values">Período: ${orc.data_inicio} a ${
        orc.data_fim
      }</div>
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
    console.error(e);
    alert(
      mode === "edit" ? "Erro ao editar orçamento." : "Erro ao criar orçamento."
    );
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
    console.error("Erro ao carregar dados de registro:", error);
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

document.addEventListener("DOMContentLoaded", () => {
  atualizarSaudacao();
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

        // Processar alertas
        if (data.alerta_orcamento)
          processarAlertaOrcamento(data.alerta_orcamento);
        if (data.alerta_meta) processarAlertaMeta(data.alerta_meta);

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
          <button class="btn-icon" onclick="alert('Editar conta em desenvolvimento')">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="btn-icon btn-icon-danger" onclick="alert('Excluir conta em desenvolvimento')">
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

function abrirModalGerenciarCategorias() {
  document.getElementById("modal-gerenciar-categorias").style.display = "flex";
  carregarCategoriasGerenciar();
}

function fecharModalGerenciarCategorias() {
  document.getElementById("modal-gerenciar-categorias").style.display = "none";
}

async function carregarCategoriasGerenciar() {
  const lista = document.getElementById("lista-categorias-gerenciar");
  lista.innerHTML =
    '<div class="loading"><div class="spinner"></div> Carregando...</div>';

  try {
    const resposta = await fetch(
      `${BASE_API}/categorias?id_usuario=${ID_USUARIO}`
    );
    const dados = await resposta.json();

    if (!resposta.ok) throw new Error(dados.error);
    categoriasAtuais = dados; // Armazena globalmente

    if (!dados.length) {
      lista.innerHTML =
        '<div class="empty-state">Nenhuma categoria cadastrada</div>';
      return;
    }

    lista.innerHTML = dados
      .map(
        (cat) => `
      <div class="item-gerenciar" onclick="abrirFormEditarCategoria(${
        cat.id_categoria
      })" style="cursor: pointer;">
        <div class="item-gerenciar-info">
          <div class="item-gerenciar-nome">${cat.nome}</div>
          <div class="item-gerenciar-detalhes" style="color: ${
            cat.tipo === "DESPESA" ? "#EF4444" : "#10B981"
          };">
            ${cat.tipo} ${cat.icone ? "- " + cat.icone : ""}
          </div>
        </div>
        <div class="item-gerenciar-acoes">
          <button class="btn-icon" onclick="event.stopPropagation(); abrirFormEditarCategoria(${
            cat.id_categoria
          })">
            <i class="fa-solid fa-pen"></i>
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

// ==================== FORMULÁRIO CATEGORIA ====================

function abrirFormNovaCategoria() {
  document.getElementById("modal-form-categoria").style.display = "flex";
  document.getElementById("categoria-modal-title").textContent =
    "Nova Categoria";
  document.getElementById("hidden-id-categoria").value = "";
  document.getElementById("input-categoria-nome").value = "";
  document.getElementById("select-categoria-tipo").value = "DESPESA";
  document.getElementById("input-categoria-icone").value = "";
  document.getElementById("input-categoria-cor").value = "#3B82F6";
  document.getElementById("btn-deletar-categoria").style.display = "none";
}

function abrirFormEditarCategoria(idCategoria) {
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
  document.getElementById("btn-deletar-categoria").style.display = "flex";
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

async function excluirCategoria(idCategoria) {
  try {
    const resposta = await fetch(`${BASE_API}/categorias`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_categoria: Number(idCategoria) }),
    });

    const dados = await resposta.json();

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
    fecharFormCategoria();
    carregarCategoriasGerenciar();
  } catch (error) {
    console.error("Erro ao excluir categoria:", error);
    mostrarNotificacao("danger", "Erro de Exclusão", error.message);
  }
}

function abrirFormNovaConta() {
  alert("Formulário de nova conta em desenvolvimento");
}

function abrirFormNovaCategoria() {
  alert("Formulário de nova categoria em desenvolvimento");
}

function abrirModalSobre() {
  document.getElementById("modal-sobre").style.display = "flex";
}

function fecharModalSobre() {
  document.getElementById("modal-sobre").style.display = "none";
}
