<?php
// api/repositories/MetaFinanceiraRepository.php
require_once __DIR__ . '/../config/database.php';

class MetaFinanceiraRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllByUsuario($id_usuario)
    {
        $stmt = $this->db->prepare('SELECT * FROM MetaFinanceira WHERE id_usuario = :id_usuario ORDER BY data_alvo');
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare('INSERT INTO MetaFinanceira (id_usuario, nome, valor_alvo, data_alvo, status) VALUES (:id_usuario, :nome, :valor_alvo, :data_alvo, :status)');
        $stmt->bindParam(':id_usuario', $data['id_usuario']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':valor_alvo', $data['valor_alvo']);
        $stmt->bindParam(':data_alvo', $data['data_alvo']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function update($id_meta, $data)
    {
        $stmt = $this->db->prepare('UPDATE MetaFinanceira SET status = :status WHERE id_meta = :id_meta');
        $stmt->bindParam(':id_meta', $id_meta);
        $stmt->bindParam(':status', $data['status']);
        return $stmt->execute();
    }
}
