<?php
// app/controllers/RecorrenciaController.php
require_once __DIR__ . '/../models/TransacaoRepository.php';
require_once __DIR__ . '/../config/database.php';

class RecorrenciaController
{
    private $db;
    private $transacaoRepo;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->transacaoRepo = new TransacaoRepository();
    }

    /**
     * Lista todas as despesas e receitas do usuário para o modal de gerenciar recorrências
     * Se mes_ano não for fornecido, retorna TODAS as transações (para marcar como recorrente)
     */
    public function listarDespesas()
    {
        try {
            $id_usuario = $_GET['id_usuario'] ?? null;
            $mes_ano = $_GET['mes_ano'] ?? null;

            if (!$id_usuario) {
                http_response_code(400);
                echo json_encode(['error' => 'id_usuario é obrigatório']);
                return;
            }

            // Se mes_ano não foi passado, retorna todas as transações
            if ($mes_ano) {
                $sql = "SELECT t.id_transacao, t.descricao, t.valor, t.data_transacao, t.recorrente, t.tipo_movimentacao, c.nome AS categoria
                        FROM Transacao t
                        LEFT JOIN Categoria c ON t.id_categoria = c.id_categoria
                        WHERE t.id_usuario = :id_usuario 
                        AND t.tipo_movimentacao IN ('DESPESA', 'RECEITA')
                        AND DATE_FORMAT(t.data_transacao, '%Y-%m') = :mes_ano
                        ORDER BY t.recorrente DESC, t.tipo_movimentacao DESC, t.data_transacao DESC";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(':mes_ano', $mes_ano, PDO::PARAM_STR);
            } else {
                // Retorna apenas UMA transação por grupo (evita duplicatas visuais)
                // Agrupa por descricao, valor, categoria e tipo - pega a mais recente de cada grupo
                $sql = "SELECT 
                            MAX(t.id_transacao) as id_transacao,
                            t.descricao, 
                            t.valor, 
                            MAX(t.data_transacao) as data_transacao, 
                            MAX(t.recorrente) as recorrente, 
                            t.tipo_movimentacao, 
                            c.nome AS categoria
                        FROM Transacao t
                        LEFT JOIN Categoria c ON t.id_categoria = c.id_categoria
                        WHERE t.id_usuario = :id_usuario 
                        AND t.tipo_movimentacao IN ('DESPESA', 'RECEITA')
                        GROUP BY t.descricao, t.valor, t.id_categoria, t.tipo_movimentacao
                        ORDER BY MAX(t.recorrente) DESC, t.tipo_movimentacao DESC, MAX(t.data_transacao) DESC
                        LIMIT 100";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }

            $stmt->execute();
            $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($despesas);
        } catch (Exception $e) {
            error_log('Erro em listarDespesas: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao listar despesas: ' . $e->getMessage()]);
        }
    }

    /**
     * Marca uma transação como recorrente
     */
    public function marcarRecorrente()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id_transacao = $data['id_transacao'] ?? null;
            $id_usuario = $data['id_usuario'] ?? null;

            if (!$id_transacao || !$id_usuario) {
                http_response_code(400);
                echo json_encode(['error' => 'id_transacao e id_usuario são obrigatórios']);
                return;
            }

            $sql = "UPDATE Transacao 
                    SET recorrente = 1 
                    WHERE id_transacao = :id_transacao 
                    AND id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_transacao', $id_transacao, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'mensagem' => 'Transação marcada como recorrente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Transação não encontrada ou já é recorrente']);
            }
        } catch (Exception $e) {
            error_log('Erro em marcarRecorrente: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao marcar recorrente: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove a marcação de recorrente de uma transação
     */
    public function removerRecorrente()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id_transacao = $data['id_transacao'] ?? null;
            $id_usuario = $data['id_usuario'] ?? null;

            if (!$id_transacao || !$id_usuario) {
                http_response_code(400);
                echo json_encode(['error' => 'id_transacao e id_usuario são obrigatórios']);
                return;
            }

            $sql = "UPDATE Transacao 
                    SET recorrente = 0 
                    WHERE id_transacao = :id_transacao 
                    AND id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_transacao', $id_transacao, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'mensagem' => 'Recorrência removida com sucesso']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Transação não encontrada ou não é recorrente']);
            }
        } catch (Exception $e) {
            error_log('Erro em removerRecorrente: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao remover recorrência: ' . $e->getMessage()]);
        }
    }

    /**
     * Gera automaticamente transações recorrentes para um mês específico
     */
    public function gerarRecorrenciasMes()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id_usuario = $data['id_usuario'] ?? null;
            $mes_ano = $data['mes_ano'] ?? null;

            if (!$id_usuario || !$mes_ano) {
                http_response_code(400);
                echo json_encode(['error' => 'id_usuario e mes_ano são obrigatórios']);
                return;
            }

            if (!$this->db) {
                throw new Exception('Conexão com banco de dados não estabelecida');
            }

            // 1. Verificar se já foi gerado para esse mês
            if ($this->verificarGeracao($id_usuario, $mes_ano)) {
                echo json_encode(['success' => true, 'mensagem' => 'Recorrências já geradas para este mês']);
                return;
            }

            // 2. Buscar transações recorrentes ativas do usuário
            $recorrentes = $this->buscarTransacoesRecorrentes($id_usuario);

            if (empty($recorrentes)) {
                echo json_encode(['success' => true, 'mensagem' => 'Nenhuma transação recorrente encontrada']);
                return;
            }

            // 3. Gerar novas transações para o mês
            $geradas = 0;
            foreach ($recorrentes as $transacao) {
                try {
                    $this->gerarTransacaoRecorrente($transacao, $mes_ano);
                    $geradas++;
                } catch (Exception $e) {
                    error_log("Erro ao gerar recorrência: " . $e->getMessage());
                }
            }

            // 4. Registrar que as recorrências foram geradas
            $this->registrarGeracao($id_usuario, $mes_ano);

            echo json_encode([
                'success' => true,
                'mensagem' => "Geradas {$geradas} transações recorrentes para {$mes_ano}"
            ]);
        } catch (Exception $e) {
            error_log('Erro em gerarRecorrenciasMes: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao gerar recorrências: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verifica se já foram geradas recorrências para o mês
     */
    private function verificarGeracao($id_usuario, $mes_ano)
    {
        $sql = "SELECT COUNT(*) FROM recorrencia_log 
                WHERE id_usuario = :id_usuario AND mes_ano = :mes_ano";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':mes_ano', $mes_ano, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Busca transações marcadas como recorrentes
     */
    private function buscarTransacoesRecorrentes($id_usuario)
    {
        $sql = "SELECT id_usuario, id_transacao, id_conta, id_categoria, descricao, valor, 
                       tipo_movimentacao, data_transacao
                FROM Transacao
                WHERE id_usuario = :id_usuario 
                AND recorrente = 1
                ORDER BY data_transacao";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gera uma nova transação recorrente para o mês especificado
     */
    private function gerarTransacaoRecorrente($transacaoOriginal, $mes_ano)
    {
        // Impedir geração para meses anteriores ao mês da transação original
        $mesOriginal = date('Y-m', strtotime($transacaoOriginal['data_transacao']));
        $tsMesAlvo = strtotime($mes_ano . '-01');
        $tsMesOriginal = strtotime($mesOriginal . '-01');
        if ($tsMesAlvo < $tsMesOriginal) {
            // Não gerar recorrência retroativa
            return;
        }

        // Extrair o dia da transação original
        $diaOriginal = date('d', strtotime($transacaoOriginal['data_transacao']));

        // Construir a nova data no mês especificado
        $dataNova = $this->construirDataValida($mes_ano, $diaOriginal);

        // Verificar se já existe transação recorrente nessa data
        $jaExiste = $this->verificarTransacaoExistente(
            $transacaoOriginal['id_usuario'] ?? null,
            $transacaoOriginal['id_conta'],
            $transacaoOriginal['id_categoria'],
            $dataNova,
            $transacaoOriginal['descricao']
        );

        if ($jaExiste) {
            return; // Não duplicar
        }

        // Inserir nova transação
        $sql = "INSERT INTO Transacao 
                (id_usuario, id_conta, id_categoria, valor, tipo_movimentacao, 
                 data_transacao, descricao, efetuada, recorrente)
                VALUES 
                (:id_usuario, :id_conta, :id_categoria, :valor, :tipo_movimentacao, 
                 :data_transacao, :descricao, 0, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $transacaoOriginal['id_usuario'],
            ':id_conta' => $transacaoOriginal['id_conta'],
            ':id_categoria' => $transacaoOriginal['id_categoria'],
            ':valor' => $transacaoOriginal['valor'],
            ':tipo_movimentacao' => $transacaoOriginal['tipo_movimentacao'],
            ':data_transacao' => $dataNova,
            ':descricao' => $transacaoOriginal['descricao'],
        ]);
    }

    /**
     * Constrói uma data válida ajustando para o último dia se necessário
     */
    private function construirDataValida($mes_ano, $dia)
    {
        list($ano, $mes) = explode('-', $mes_ano);
        $ultimoDia = date('t', strtotime("$ano-$mes-01"));

        // Ajustar se o dia original não existe no mês (ex: 31 em fevereiro)
        $diaFinal = min((int)$dia, (int)$ultimoDia);

        return sprintf('%s-%s-%02d', $ano, $mes, $diaFinal);
    }

    /**
     * Verifica se já existe uma transação similar (evita duplicação)
     */
    private function verificarTransacaoExistente($id_usuario, $id_conta, $id_categoria, $data, $descricao)
    {
        $sql = "SELECT COUNT(*) FROM Transacao 
                WHERE id_usuario = :id_usuario 
                AND id_conta = :id_conta 
                AND id_categoria = :id_categoria 
                AND data_transacao = :data_transacao 
                AND descricao = :descricao";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_conta' => $id_conta,
            ':id_categoria' => $id_categoria,
            ':data_transacao' => $data,
            ':descricao' => $descricao
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Registra que as recorrências foram geradas para o mês
     */
    private function registrarGeracao($id_usuario, $mes_ano)
    {
        $sql = "INSERT INTO recorrencia_log (id_usuario, mes_ano) 
                VALUES (:id_usuario, :mes_ano)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':mes_ano', $mes_ano, PDO::PARAM_STR);
        $stmt->execute();
    }
}
