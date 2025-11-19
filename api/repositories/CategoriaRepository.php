<?php
// api/repositories/CategoriaRepository.php
require_once __DIR__ . '/../config/database.php';

class CategoriaRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllByUsuario($id_usuario, $tipo = null)
    {
        $sql = 'SELECT * FROM Categoria WHERE (id_usuario = :id_usuario OR id_usuario IS NULL)';
        if ($tipo) {
            $sql .= ' AND tipo = :tipo';
        }
        $sql .= ' ORDER BY nome';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        if ($tipo) {
            $stmt->bindParam(':tipo', $tipo);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare('INSERT INTO Categoria (id_usuario, nome, tipo, cor_hex) VALUES (:id_usuario, :nome, :tipo, :cor_hex)');
        $stmt->bindParam(':id_usuario', $data['id_usuario']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':cor_hex', $data['cor_hex']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
}
