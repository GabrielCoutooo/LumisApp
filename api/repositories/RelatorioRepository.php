<?php
// api/repositories/RelatorioRepository.php
require_once __DIR__ . '/../config/database.php';

class RelatorioRepository
{
    private $db;
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function gastosPorCategoria($id_usuario, $mes_ano)
    {
        $sql = 'SELECT c.nome as categoria, SUM(t.valor) as total_gasto
                FROM Transacao t
                JOIN Categoria c ON t.id_categoria = c.id_categoria
                WHERE t.id_usuario = :id_usuario
                  AND c.tipo = "DESPESA"
                  AND DATE_FORMAT(t.data_transacao, "%Y-%m") = :mes_ano
                  AND t.efetuada = 1
                GROUP BY c.nome
                ORDER BY total_gasto DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
