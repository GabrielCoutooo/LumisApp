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

    public function getExtrato($id_usuario, $id_conta = null)
    {
        $sql = 'SELECT * FROM Transacao WHERE id_usuario = :id_usuario';
        if ($id_conta) {
            $sql .= ' AND id_conta = :id_conta';
        }
        $sql .= ' ORDER BY data_transacao DESC, id_transacao DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        if ($id_conta) {
            $stmt->bindParam(':id_conta', $id_conta);
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
}
