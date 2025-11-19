<?php
// api/repositories/ContaRepository.php
require_once __DIR__ . '/../config/database.php';

class ContaRepository
{
    private $db;
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllByUsuario($id_usuario)
    {
        $stmt = $this->db->prepare('SELECT * FROM Conta WHERE id_usuario = :id_usuario');
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare('INSERT INTO Conta (id_usuario, nome, tipo_conta, saldo_inicial, exibir_no_dashboard) VALUES (:id_usuario, :nome, :tipo_conta, :saldo_inicial, :exibir_no_dashboard)');
        $stmt->bindParam(':id_usuario', $data['id_usuario']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':tipo_conta', $data['tipo_conta']);
        $stmt->bindParam(':saldo_inicial', $data['saldo_inicial']);
        $stmt->bindParam(':exibir_no_dashboard', $data['exibir_no_dashboard']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function update($data)
    {
        $sql = 'UPDATE Conta SET nome = :nome, tipo_conta = :tipo_conta';

        if (isset($data['saldo_inicial'])) {
            $sql .= ', saldo_inicial = :saldo_inicial';
        }
        if (isset($data['exibir_no_dashboard'])) {
            $sql .= ', exibir_no_dashboard = :exibir_no_dashboard';
        }

        $sql .= ' WHERE id_conta = :id_conta';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_conta', $data['id_conta']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':tipo_conta', $data['tipo_conta']);

        if (isset($data['saldo_inicial'])) {
            $stmt->bindParam(':saldo_inicial', $data['saldo_inicial']);
        }
        if (isset($data['exibir_no_dashboard'])) {
            $stmt->bindParam(':exibir_no_dashboard', $data['exibir_no_dashboard']);
        }

        return $stmt->execute();
    }

    public function delete($id_conta)
    {
        $stmt = $this->db->prepare('DELETE FROM Conta WHERE id_conta = :id_conta');
        $stmt->bindParam(':id_conta', $id_conta);
        return $stmt->execute();
    }
}
