<?php
// api/services/MetaFinanceiraService.php
require_once __DIR__ . '/../config/database.php';

class MetaFinanceiraService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * A. Criar Meta Financeira com Categoria de Contribuição Automática
     * 
     * Quando uma meta é criada, o sistema automaticamente cria uma categoria
     * de contribuição para rastrear transações vinculadas à meta.
     * 
     * @param int $id_usuario ID do usuário
     * @param string $nome Nome da meta
     * @param float $valor_alvo Valor objetivo
     * @param string|null $data_alvo Data alvo (YYYY-MM-DD)
     * @param string $status Status inicial (default: ATIVA)
     * @return array Retorna id_meta e id_categoria_contribuicao criados
     */
    public function criarMetaComCategoria($id_usuario, $nome, $valor_alvo, $data_alvo = null, $status = 'ATIVA')
    {
        try {
            $this->db->beginTransaction();

            // 1. Criar a meta na tabela MetaFinanceira
            $sqlMeta = "INSERT INTO MetaFinanceira (id_usuario, nome, valor_alvo, data_alvo, status)
                        VALUES (:id_usuario, :nome, :valor_alvo, :data_alvo, :status)";

            $stmtMeta = $this->db->prepare($sqlMeta);
            $stmtMeta->bindParam(':id_usuario', $id_usuario);
            $stmtMeta->bindParam(':nome', $nome);
            $stmtMeta->bindParam(':valor_alvo', $valor_alvo);
            $stmtMeta->bindParam(':data_alvo', $data_alvo);
            $stmtMeta->bindParam(':status', $status);
            $stmtMeta->execute();

            $id_meta = $this->db->lastInsertId();

            // 2. Criar categoria de contribuição vinculada
            $nomeCategoria = "Contribuição para " . $nome;
            $tipoCategoria = "DESPESA"; // Contribuição é vista como despesa da conta principal

            $sqlCategoria = "INSERT INTO Categoria (id_usuario, nome, tipo, icone)
                            VALUES (:id_usuario, :nome, :tipo, '🎯')";

            $stmtCategoria = $this->db->prepare($sqlCategoria);
            $stmtCategoria->bindParam(':id_usuario', $id_usuario);
            $stmtCategoria->bindParam(':nome', $nomeCategoria);
            $stmtCategoria->bindParam(':tipo', $tipoCategoria);
            $stmtCategoria->execute();

            $id_categoria_contribuicao = $this->db->lastInsertId();

            $this->db->commit();

            return [
                'id_meta' => $id_meta,
                'id_categoria_contribuicao' => $id_categoria_contribuicao,
                'nome_categoria' => $nomeCategoria
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erro ao criar meta: " . $e->getMessage());
        }
    }

    /**
     * B. Calcular Progresso Atual da Meta
     * 
     * Consulta SQL otimizada com LEFT JOIN para calcular contribuições acumuladas
     * baseando-se em transações com categoria de contribuição.
     * 
     * @param int $id_meta ID da meta
     * @param int $id_usuario ID do usuário (validação)
     * @return array Status completo da meta com progresso
     */
    public function calcularProgressoMeta($id_meta, $id_usuario)
    {
        $sql = "SELECT
                    M.id_meta,
                    M.nome AS nome_meta,
                    M.valor_alvo,
                    M.data_alvo,
                    M.status,
                    COALESCE(SUM(T.valor), 0.00) AS valor_contribuido_acumulado
                FROM
                    MetaFinanceira M
                LEFT JOIN Categoria C
                    ON C.id_usuario = M.id_usuario
                    AND C.nome = CONCAT('Contribuição para ', M.nome)
                    AND C.tipo = 'DESPESA'
                LEFT JOIN Transacao T
                    ON T.id_usuario = M.id_usuario
                    AND T.id_categoria = C.id_categoria
                    AND T.tipo_movimentacao = 'DESPESA'
                    AND T.efetuada = TRUE
                WHERE
                    M.id_meta = :id_meta
                    AND M.id_usuario = :id_usuario
                GROUP BY
                    M.id_meta, M.nome, M.valor_alvo, M.data_alvo, M.status";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_meta', $id_meta);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'erro' => 'Meta não encontrada',
                'status' => 'INVALIDA'
            ];
        }

        // C. Lógica de Negócios: Calcular percentual e valor restante
        $valor_alvo = (float)$row['valor_alvo'];
        $valor_contribuido = (float)$row['valor_contribuido_acumulado'];

        $percentual_progresso = $valor_alvo > 0 ? ($valor_contribuido / $valor_alvo) * 100 : 0;
        $valor_restante = $valor_alvo - $valor_contribuido;

        // Status visual
        $status_atual = $row['status'];
        $status_progresso = 'EM_ANDAMENTO';

        if ($valor_contribuido >= $valor_alvo) {
            $status_progresso = 'ATINGIDA';
        } elseif ($percentual_progresso >= 75) {
            $status_progresso = 'PROXIMO_OBJETIVO';
        }

        return [
            'id_meta' => $row['id_meta'],
            'nome_meta' => $row['nome_meta'],
            'valor_alvo' => $valor_alvo,
            'valor_contribuido_acumulado' => $valor_contribuido,
            'valor_restante' => $valor_restante,
            'percentual_progresso' => round($percentual_progresso, 2),
            'status' => $status_atual,
            'status_progresso' => $status_progresso,
            'data_alvo' => $row['data_alvo']
        ];
    }

    /**
     * Verificar todas as metas ativas de um usuário
     * 
     * @param int $id_usuario ID do usuário
     * @return array Lista de metas com progresso calculado
     */
    public function verificarMetasAtivas($id_usuario)
    {
        // Buscar todas as metas do usuário
        $sqlMetas = "SELECT id_meta FROM MetaFinanceira WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sqlMetas);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];
        foreach ($metas as $meta) {
            $progresso = $this->calcularProgressoMeta($meta['id_meta'], $id_usuario);
            if (!isset($progresso['erro'])) {
                $resultado[] = $progresso;
            }
        }

        return $resultado;
    }

    /**
     * Verificar se uma contribuição completa a meta (Gatilho)
     * 
     * Deve ser chamado APÓS criar uma transação de contribuição.
     * Se a meta for atingida, atualiza status e retorna notificação.
     * 
     * @param int $id_usuario ID do usuário
     * @param int $id_categoria ID da categoria da transação
     * @return array|null Retorna notificação se meta foi concluída, null caso contrário
     */
    public function verificarConclusaoMetaAposContribuicao($id_usuario, $id_categoria)
    {
        // Buscar meta vinculada à categoria de contribuição
        $sqlMeta = "SELECT M.id_meta, M.nome, M.status
                    FROM MetaFinanceira M
                    INNER JOIN Categoria C
                        ON C.nome = CONCAT('Contribuição para ', M.nome)
                        AND C.id_usuario = M.id_usuario
                    WHERE C.id_categoria = :id_categoria
                    AND M.id_usuario = :id_usuario
                    AND M.status != 'CONCLUIDA'
                    LIMIT 1";

        $stmt = $this->db->prepare($sqlMeta);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $meta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$meta) {
            // Não há meta vinculada ou já está concluída
            return null;
        }

        // Calcular progresso atualizado
        $progresso = $this->calcularProgressoMeta($meta['id_meta'], $id_usuario);

        // Verificar se atingiu o objetivo
        if ($progresso['valor_contribuido_acumulado'] >= $progresso['valor_alvo']) {
            // Atualizar status da meta para CONCLUIDA
            $sqlUpdate = "UPDATE MetaFinanceira 
                         SET status = 'CONCLUIDA' 
                         WHERE id_meta = :id_meta";

            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':id_meta', $meta['id_meta']);
            $stmtUpdate->execute();

            // Retornar notificação de conquista
            return [
                'tipo' => 'META_CONCLUIDA',
                'mensagem' => "🎉 Parabéns! Você atingiu sua meta '{$meta['nome']}'!",
                'id_meta' => $meta['id_meta'],
                'nome_meta' => $meta['nome'],
                'valor_alvo' => $progresso['valor_alvo'],
                'valor_contribuido' => $progresso['valor_contribuido_acumulado']
            ];
        }

        // Se está próximo (>= 75%), retornar alerta motivacional
        if ($progresso['percentual_progresso'] >= 75 && $progresso['percentual_progresso'] < 100) {
            return [
                'tipo' => 'META_PROXIMA',
                'mensagem' => "🎯 Você está quase lá! Faltam apenas {$progresso['percentual_progresso']}% para atingir '{$meta['nome']}'.",
                'id_meta' => $meta['id_meta'],
                'nome_meta' => $meta['nome'],
                'percentual_progresso' => $progresso['percentual_progresso'],
                'valor_restante' => $progresso['valor_restante']
            ];
        }

        return null;
    }

    /**
     * Buscar ID da categoria de contribuição de uma meta
     * 
     * @param int $id_meta ID da meta
     * @param int $id_usuario ID do usuário
     * @return int|null ID da categoria ou null se não encontrada
     */
    public function obterCategoriaContribuicao($id_meta, $id_usuario)
    {
        $sql = "SELECT C.id_categoria
                FROM Categoria C
                INNER JOIN MetaFinanceira M
                    ON C.nome = CONCAT('Contribuição para ', M.nome)
                    AND C.id_usuario = M.id_usuario
                WHERE M.id_meta = :id_meta
                AND M.id_usuario = :id_usuario
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_meta', $id_meta);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_categoria'] : null;
    }
}
