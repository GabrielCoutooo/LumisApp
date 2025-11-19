<?php
// app/services/DashboardService.php
// Ajuste de paths após reorganização para MVC definitivo
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ContaRepository.php';
require_once __DIR__ . '/../models/TransacaoRepository.php';
require_once __DIR__ . '/../models/OrcamentoRepository.php';
require_once __DIR__ . '/SaldoService.php';

class DashboardService
{
    private $contaRepo;
    private $transacaoRepo;
    private $orcamentoRepo;
    private $db;
    private $saldoService;

    public function __construct()
    {
        $this->contaRepo = new ContaRepository();
        $this->transacaoRepo = new TransacaoRepository();
        $this->orcamentoRepo = new OrcamentoRepository();
        $database = new Database();
        $this->db = $database->getConnection();
        $this->saldoService = new SaldoService();
    }

    public function getDashboardData($id_usuario, $mes_ano)
    {
        // 1. Saldo Total (soma de todas as contas)
        $saldoTotal = $this->calcularSaldoTotal($id_usuario);

        // 2. Gastos do Mês (total de despesas)
        $gastosMes = $this->calcularGastosMes($id_usuario, $mes_ano);

        // 3. Receitas do Mês
        $receitasMes = $this->calcularReceitasMes($id_usuario, $mes_ano);

        // 4. Resumo de Orçamentos vs. Gastos
        $orcamentosVsGastos = $this->compararOrcamentosGastos($id_usuario, $mes_ano);

        // 5. Próximos Pagamentos (transações não efetuadas)
        $proximosPagamentos = $this->getProximosPagamentos($id_usuario);

        // 6. Contas com saldos
        $contas = $this->getContasComSaldo($id_usuario);

        return [
            'saldo_total' => $saldoTotal,
            // Mantém chave antiga e adiciona alias esperado pelo protótipo
            'gastos_mes' => $gastosMes,
            'despesas_mes' => $gastosMes,
            'receitas_mes' => $receitasMes,
            'saldo_mes' => $receitasMes - $gastosMes,
            'orcamentos' => $orcamentosVsGastos,
            'proximos_pagamentos' => $proximosPagamentos,
            'contas' => $contas
        ];
    }

    private function calcularSaldoTotal($id_usuario)
    {
        $sql = 'SELECT SUM(saldo_inicial) as total FROM Conta WHERE id_usuario = :id_usuario AND exibir_no_dashboard = 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total'] ?? 0);
    }

    private function calcularGastosMes($id_usuario, $mes_ano)
    {
        $sql = 'SELECT SUM(valor) as total FROM Transacao 
                WHERE id_usuario = :id_usuario 
                AND tipo_movimentacao = "DESPESA" 
                AND DATE_FORMAT(data_transacao, "%Y-%m") = :mes_ano
                AND efetuada = 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total'] ?? 0);
    }

    private function calcularReceitasMes($id_usuario, $mes_ano)
    {
        $sql = 'SELECT SUM(valor) as total FROM Transacao 
                WHERE id_usuario = :id_usuario 
                AND tipo_movimentacao = "RECEITA" 
                AND DATE_FORMAT(data_transacao, "%Y-%m") = :mes_ano
                AND efetuada = 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total'] ?? 0);
    }

    private function compararOrcamentosGastos($id_usuario, $mes_ano)
    {
        // Buscar todos os orçamentos ativos que abrangem o mês
        $inicio_mes = $mes_ano . '-01';
        $fim_mes = date('Y-m-t', strtotime($inicio_mes));
        $sqlOrcamentos = "SELECT o.*, c.nome as categoria, c.cor_hex FROM Orcamento o JOIN Categoria c ON o.id_categoria = c.id_categoria WHERE o.id_usuario = :id_usuario AND o.ativo = 1 AND o.data_inicio <= :fim_mes AND o.data_fim >= :inicio_mes";
        $stmt = $this->db->prepare($sqlOrcamentos);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':inicio_mes', $inicio_mes);
        $stmt->bindParam(':fim_mes', $fim_mes);
        $stmt->execute();
        $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];
        foreach ($orcamentos as $orc) {
            // Calcular gasto realizado no período do orçamento
            $gasto_realizado = $this->calcularGastoRealizado(
                $id_usuario,
                $orc['id_categoria'],
                $orc['data_inicio'],
                $orc['data_fim']
            );
            $valor_limite = (float)$orc['valor_limite'];
            $gasto_restante = $valor_limite - $gasto_realizado;
            $percentual_utilizado = $valor_limite > 0 ? round(($gasto_realizado / $valor_limite) * 100, 2) : 0;
            // Status visual igual ao OrcamentoService
            $status = 'OK';
            if ($gasto_realizado >= $valor_limite) {
                $status = 'ESTOURADO';
            } elseif ($gasto_realizado >= $valor_limite * 0.8) {
                $status = 'ALERTA';
            }
            $resultado[] = [
                'id_orcamento' => $orc['id_orcamento'],
                'id_categoria' => $orc['id_categoria'],
                'nome_categoria' => $orc['categoria'],
                'valor_limite' => $valor_limite,
                'gasto_realizado' => $gasto_realizado,
                'gasto_restante' => $gasto_restante,
                'percentual_utilizado' => $percentual_utilizado,
                'status' => $status,
                'cor_hex' => $orc['cor_hex'],
                'data_inicio' => $orc['data_inicio'],
                'data_fim' => $orc['data_fim']
            ];
        }
        return $resultado;
    }

    // Copiado de OrcamentoService para evitar dependência cruzada
    private function calcularGastoRealizado($id_usuario, $id_categoria_alvo, $data_inicio_orcamento, $data_fim_orcamento)
    {
        $sql = "SELECT COALESCE(SUM(T.valor), 0.00) AS gasto_realizado_periodo FROM Transacao T WHERE T.id_usuario = :id_usuario AND T.id_categoria = :id_categoria_alvo AND T.tipo_movimentacao = 'DESPESA' AND T.efetuada = TRUE AND T.data_transacao >= :data_inicio AND T.data_transacao <= :data_fim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_categoria_alvo', $id_categoria_alvo);
        $stmt->bindParam(':data_inicio', $data_inicio_orcamento);
        $stmt->bindParam(':data_fim', $data_fim_orcamento);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['gasto_realizado_periodo'] ?? 0.00);
    }

    private function getProximosPagamentos($id_usuario)
    {
        $sql = 'SELECT t.*, c.nome as categoria
                FROM Transacao t
                JOIN Categoria c ON t.id_categoria = c.id_categoria
                WHERE t.id_usuario = :id_usuario
                AND t.efetuada = 0
                AND t.data_transacao >= CURDATE()
                ORDER BY t.data_transacao ASC
                LIMIT 5';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getContasComSaldo($id_usuario)
    {
        // Busca contas exibidas no dashboard
        $sqlContas = 'SELECT c.id_conta, c.nome, c.tipo_conta, c.saldo_inicial
                      FROM Conta c
                      WHERE c.id_usuario = :id_usuario AND c.exibir_no_dashboard = 1
                      ORDER BY c.tipo_conta, c.nome';
        $stmt = $this->db->prepare($sqlContas);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcula saldo atual (A) e projetado (B) até o fim do mês atual de mes_ano
        $resultado = [];
        foreach ($contas as $c) {
            $saldoAtual = $this->saldoService->calcularSaldoAtualConta($c['id_conta']);
            $resultado[] = [
                'id_conta' => (int)$c['id_conta'],
                'nome' => $c['nome'],
                'tipo_conta' => $c['tipo_conta'],
                'saldo_inicial' => (float)$c['saldo_inicial'],
                'saldo_atual' => $saldoAtual
            ];
        }
        return $resultado;
    }
}
