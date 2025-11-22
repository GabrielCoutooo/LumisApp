<?php
// api/repositories/TransacaoRepository.php
require_once __DIR__ . '/../config/database.php';

class TransacaoRepository
{
    private $db;
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getExtrato($id_usuario, $id_conta = null, $data_inicio = null, $data_fim = null)
    {
        $sql = 'SELECT * FROM Transacao WHERE id_usuario = :id_usuario';

        if ($id_conta) {
            $sql .= ' AND id_conta = :id_conta';
        }

        if ($data_inicio && $data_fim) {
            $sql .= ' AND data_transacao BETWEEN :data_inicio AND :data_fim';
        }

        $sql .= ' ORDER BY data_transacao DESC, id_transacao DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);

        if ($id_conta) {
            $stmt->bindParam(':id_conta', $id_conta);
        }

        if ($data_inicio && $data_fim) {
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($data)
    {
        $stmt = $this->db->prepare('INSERT INTO Transacao (id_usuario, id_conta, id_categoria, valor, tipo_movimentacao, data_transacao, descricao, efetuada) VALUES (:id_usuario, :id_conta, :id_categoria, :valor, :tipo_movimentacao, :data_transacao, :descricao, :efetuada)');
        $stmt->bindParam(':id_usuario', $data['id_usuario']);
        $stmt->bindParam(':id_conta', $data['id_conta']);
        $stmt->bindParam(':id_categoria', $data['id_categoria']);
        $stmt->bindParam(':valor', $data['valor']);
        $stmt->bindParam(':tipo_movimentacao', $data['tipo_movimentacao']);
        $stmt->bindParam(':data_transacao', $data['data_transacao']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':efetuada', $data['efetuada']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    // Retorna todas as despesas do usuário
    public function getDespesas($id_usuario)
    {
        $sql = 'SELECT * FROM Transacao WHERE id_usuario = :id_usuario AND tipo_movimentacao = "DESPESA" ORDER BY data_transacao DESC, id_transacao DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marca uma transação como recorrente
    public function setRecorrente($id_transacao, $id_usuario)
    {
        // Adiciona/atualiza campo recorrente na tabela Transacao
        $sql = 'UPDATE Transacao SET recorrente = 1 WHERE id_transacao = :id_transacao AND id_usuario = :id_usuario';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_transacao', $id_transacao);
        $stmt->bindParam(':id_usuario', $id_usuario);
        return $stmt->execute();
    }

    // Atualiza o status de efetuada de uma transação
    public function atualizarEfetuada($id_transacao, $efetuada)
    {
        $sql = 'UPDATE Transacao SET efetuada = :efetuada WHERE id_transacao = :id_transacao';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':efetuada', $efetuada, PDO::PARAM_INT);
        $stmt->bindParam(':id_transacao', $id_transacao, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir($id_transacao)
    {
        $sql = 'DELETE FROM Transacao WHERE id_transacao = :id_transacao';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_transacao', $id_transacao, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
