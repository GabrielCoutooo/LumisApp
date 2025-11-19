<?php
// api/services/OrcamentoService.php
require_once __DIR__ . '/../config/database.php';

class OrcamentoService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Passo 1: Calcular Gasto Realizado no Período do Orçamento
     * 
     * Consulta SQL otimizada para somar despesas efetivas de uma categoria
     * dentro do período ativo do orçamento.
     * 
     * @param int $id_usuario ID do usuário
     * @param int $id_categoria_alvo ID da categoria sendo verificada
     * @param string $data_inicio_orcamento Data de início (formato: YYYY-MM-DD)
     * @param string $data_fim_orcamento Data de fim (formato: YYYY-MM-DD)
     * @return float Gasto realizado no período
     */
    public function calcularGastoRealizado($id_usuario, $id_categoria_alvo, $data_inicio_orcamento, $data_fim_orcamento)
    {
        $sql = "SELECT
                    COALESCE(SUM(T.valor), 0.00) AS gasto_realizado_periodo
                FROM
                    Transacao T
                WHERE
                    T.id_usuario = :id_usuario
                    AND T.id_categoria = :id_categoria_alvo
                    AND T.tipo_movimentacao = 'DESPESA'  -- Foco apenas em despesas (saídas de dinheiro)
                    AND T.efetuada = TRUE                -- Foco apenas em transações concluídas
                    AND T.data_transacao >= :data_inicio
                    AND T.data_transacao <= :data_fim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_categoria_alvo', $id_categoria_alvo);
        $stmt->bindParam(':data_inicio', $data_inicio_orcamento);
        $stmt->bindParam(':data_fim', $data_fim_orcamento);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['gasto_realizado_periodo'] ?? 0.00);
    }

    /**
     * Passo 2: Calcular Status do Orçamento
     * 
     * Lógica de negócios para determinar:
     * - Gasto restante
     * - Percentual utilizado
     * - Status visual (OK / ALERTA / ESTOURADO)
     * 
     * @param int $id_orcamento ID do orçamento a verificar
     * @param int $id_usuario ID do usuário (para validação)
     * @return array Retorna status completo do orçamento
     */
    public function calcularStatusOrcamento($id_orcamento, $id_usuario)
    {
        // 1. Buscar dados do orçamento ativo
        $sqlOrcamento = "SELECT 
                            O.id_orcamento,
                            O.id_categoria,
                            O.valor_limite,
                            O.data_inicio,
                            O.data_fim,
                            O.ativo,
                            C.nome AS nome_categoria
                         FROM Orcamento O
                         INNER JOIN Categoria C ON O.id_categoria = C.id_categoria
                         WHERE O.id_orcamento = :id_orcamento
                         AND O.id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sqlOrcamento);
        $stmt->bindParam(':id_orcamento', $id_orcamento);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orcamento) {
            return [
                'erro' => 'Orçamento não encontrado',
                'status' => 'INVALIDO'
            ];
        }

        // 2. Calcular gasto realizado no período
        $valor_limite = (float)$orcamento['valor_limite'];
        $gasto_realizado = $this->calcularGastoRealizado(
            $id_usuario,
            $orcamento['id_categoria'],
            $orcamento['data_inicio'],
            $orcamento['data_fim']
        );

        // 3. Cálculos de status
        $gasto_restante = $valor_limite - $gasto_realizado;
        $percentual_utilizado = $valor_limite > 0 ? ($gasto_realizado / $valor_limite) * 100 : 0;

        // 4. Definição do status visual
        $status = 'OK';          // Verde (< 80%)
        if ($gasto_realizado >= $valor_limite) {
            $status = 'ESTOURADO';  // Vermelho (>= 100%)
        } elseif ($gasto_realizado >= $valor_limite * 0.8) {
            $status = 'ALERTA';     // Amarelo (>= 80% e < 100%)
        }

        // 5. Retorno completo
        return [
            'id_orcamento' => $orcamento['id_orcamento'],
            'id_categoria' => $orcamento['id_categoria'],
            'nome_categoria' => $orcamento['nome_categoria'],
            'valor_limite' => $valor_limite,
            'gasto_realizado' => $gasto_realizado,
            'gasto_restante' => $gasto_restante,
            'percentual_utilizado' => round($percentual_utilizado, 2),
            'status' => $status,
            'ativo' => (bool)$orcamento['ativo'],
            'data_inicio' => $orcamento['data_inicio'],
            'data_fim' => $orcamento['data_fim']
        ];
    }

    /**
     * Verificar todos os orçamentos ativos de um usuário
     * 
     * @param int $id_usuario ID do usuário
     * @param string $mes_ano Período no formato YYYY-MM (ex: 2025-11)
     * @return array Lista de orçamentos com seus status
     */
    public function verificarOrcamentosAtivos($id_usuario, $mes_ano)
    {
        // Buscar todos os orçamentos ativos do período
        $sqlOrcamentos = "SELECT id_orcamento 
                          FROM Orcamento
                          WHERE id_usuario = :id_usuario
                          AND ativo = TRUE
                          AND DATE_FORMAT(data_inicio, '%Y-%m') = :mes_ano";

        $stmt = $this->db->prepare($sqlOrcamentos);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];
        foreach ($orcamentos as $orc) {
            $resultado[] = $this->calcularStatusOrcamento($orc['id_orcamento'], $id_usuario);
        }

        return $resultado;
    }

    /**
     * Verificar se uma nova transação causa estouro de orçamento
     * 
     * Deve ser chamado APÓS criar uma transação do tipo DESPESA.
     * Retorna alertas se algum orçamento for estourado.
     * 
     * @param int $id_usuario ID do usuário
     * @param int $id_categoria ID da categoria da transação
     * @param string $data_transacao Data da transação (YYYY-MM-DD)
     * @return array|null Retorna array com alerta se houver estouro, null caso contrário
     */
    public function verificarEstouroAposTransacao($id_usuario, $id_categoria, $data_transacao)
    {
        // Buscar orçamento ativo para esta categoria que engloba a data da transação
        $sqlOrcamento = "SELECT id_orcamento, valor_limite, data_inicio, data_fim
                         FROM Orcamento
                         WHERE id_usuario = :id_usuario
                         AND id_categoria = :id_categoria
                         AND ativo = TRUE
                         AND :data_transacao BETWEEN data_inicio AND data_fim
                         LIMIT 1";

        $stmt = $this->db->prepare($sqlOrcamento);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':data_transacao', $data_transacao);
        $stmt->execute();
        $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orcamento) {
            // Não há orçamento ativo para esta categoria/período
            return null;
        }

        // Calcular status atualizado
        $status = $this->calcularStatusOrcamento($orcamento['id_orcamento'], $id_usuario);

        // Se estourou, retornar alerta
        if ($status['status'] === 'ESTOURADO') {
            $valor_estourado = abs($status['gasto_restante']);
            return [
                'tipo' => 'ESTOURO_ORCAMENTO',
                'mensagem' => "Atenção! O orçamento da categoria {$status['nome_categoria']} foi estourado em R$ " . number_format($valor_estourado, 2, ',', '.'),
                'id_orcamento' => $status['id_orcamento'],
                'id_categoria' => $status['id_categoria'],
                'nome_categoria' => $status['nome_categoria'],
                'valor_limite' => $status['valor_limite'],
                'gasto_realizado' => $status['gasto_realizado'],
                'valor_estourado' => $valor_estourado,
                'percentual_utilizado' => $status['percentual_utilizado']
            ];
        }

        // Se está em alerta (>= 80%), retornar aviso
        if ($status['status'] === 'ALERTA') {
            return [
                'tipo' => 'ALERTA_ORCAMENTO',
                'mensagem' => "Cuidado! Você já utilizou {$status['percentual_utilizado']}% do orçamento de {$status['nome_categoria']}.",
                'id_orcamento' => $status['id_orcamento'],
                'id_categoria' => $status['id_categoria'],
                'nome_categoria' => $status['nome_categoria'],
                'valor_limite' => $status['valor_limite'],
                'gasto_realizado' => $status['gasto_realizado'],
                'gasto_restante' => $status['gasto_restante'],
                'percentual_utilizado' => $status['percentual_utilizado']
            ];
        }

        return null; // Status OK, sem alertas
    }
}
