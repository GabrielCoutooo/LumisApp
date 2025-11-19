<?php
// api/services/SaldoService.php
require_once __DIR__ . '/../config/database.php';

class SaldoService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // A. Calcular Saldo Atual da Conta
    // Consulta SQL otimizada com LEFT JOIN para calcular saldo_atual em uma única query
    public function calcularSaldoAtualConta($id_conta)
    {
        $sql = 'SELECT
                    C.nome AS nome_conta,
                    C.saldo_inicial,
                    COALESCE(SUM(
                        CASE
                            WHEN T.tipo_movimentacao = "RECEITA" THEN T.valor
                            WHEN T.tipo_movimentacao = "DESPESA" THEN -T.valor
                            ELSE 0
                        END
                    ), 0.00) AS valor_transacoes_liquido,
                    C.saldo_inicial + COALESCE(SUM(
                        CASE
                            WHEN T.tipo_movimentacao = "RECEITA" THEN T.valor
                            WHEN T.tipo_movimentacao = "DESPESA" THEN -T.valor
                            ELSE 0
                        END
                    ), 0.00) AS saldo_atual_calculado
                FROM Conta C
                LEFT JOIN Transacao T 
                    ON C.id_conta = T.id_conta
                    AND T.efetuada = TRUE
                WHERE C.id_conta = :id_conta
                GROUP BY C.id_conta, C.nome, C.saldo_inicial';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_conta', $id_conta);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return 0.0;
        }

        return (float)($row['saldo_atual_calculado'] ?? 0);
    }

    // B. Calcular Saldo Projetado até uma data (inclui pendentes até a data)
    public function calcularSaldoProjetadoConta($id_conta, $ateData)
    {
        $saldoAtual = $this->calcularSaldoAtualConta($id_conta);

        $sqlPendentes = 'SELECT COALESCE(SUM(CASE WHEN tipo_movimentacao = "RECEITA" THEN valor WHEN tipo_movimentacao = "DESPESA" THEN -valor ELSE 0 END), 0) as total
                         FROM Transacao
                         WHERE id_conta = :id_conta AND efetuada = 0 AND data_transacao <= :ateData';
        $stmt = $this->db->prepare($sqlPendentes);
        $stmt->bindParam(':id_conta', $id_conta);
        $stmt->bindParam(':ateData', $ateData);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $deltaFuturo = (float)($row['total'] ?? 0);

        return $saldoAtual + $deltaFuturo;
    }
}
