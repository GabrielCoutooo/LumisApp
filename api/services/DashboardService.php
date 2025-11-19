<?php
// api/services/DashboardService.php
require_once __DIR__ . '/../repositories/ContaRepository.php';
require_once __DIR__ . '/../repositories/TransacaoRepository.php';
require_once __DIR__ . '/../repositories/OrcamentoRepository.php';
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
        $sql = 'SELECT 
                    o.id_orcamento,
                    o.valor_limite,
                    c.nome as categoria,
                    c.cor_hex,
                    COALESCE(SUM(t.valor), 0) as gasto_atual,
                    ROUND((COALESCE(SUM(t.valor), 0) / o.valor_limite) * 100, 2) as percentual_gasto
                FROM Orcamento o
                JOIN Categoria c ON o.id_categoria = c.id_categoria
                LEFT JOIN Transacao t ON t.id_categoria = o.id_categoria 
                    AND t.id_usuario = o.id_usuario
                    AND DATE_FORMAT(t.data_transacao, "%Y-%m") = :mes_ano
                    AND t.efetuada = 1
                WHERE o.id_usuario = :id_usuario
                    AND o.ativo = 1
                    AND DATE_FORMAT(o.data_inicio, "%Y-%m") = :mes_ano
                GROUP BY o.id_orcamento, o.valor_limite, c.nome, c.cor_hex';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
