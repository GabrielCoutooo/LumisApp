<?php
// api/repositories/OrcamentoRepository.php
require_once __DIR__ . '/../config/database.php';

class OrcamentoRepository
{
    private $db;
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getByMesAno($id_usuario, $mes_ano)
    {
        $sql = 'SELECT o.*, c.nome as categoria_nome FROM Orcamento o JOIN Categoria c ON o.id_categoria = c.id_categoria WHERE o.id_usuario = :id_usuario AND DATE_FORMAT(o.data_inicio, "%Y-%m") = :mes_ano';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':mes_ano', $mes_ano);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($data)
    {
        $stmt = $this->db->prepare('INSERT INTO Orcamento (id_usuario, id_categoria, valor_limite, data_inicio, data_fim, ativo) VALUES (:id_usuario, :id_categoria, :valor_limite, :data_inicio, :data_fim, :ativo)');
        $stmt->bindParam(':id_usuario', $data['id_usuario']);
        $stmt->bindParam(':id_categoria', $data['id_categoria']);
        $stmt->bindParam(':valor_limite', $data['valor_limite']);
        $stmt->bindParam(':data_inicio', $data['data_inicio']);
        $stmt->bindParam(':data_fim', $data['data_fim']);
        $stmt->bindParam(':ativo', $data['ativo']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function atualizar($id_orcamento, $fields)
    {
        $allowed = ['valor_limite', 'data_inicio', 'data_fim', 'ativo', 'id_categoria'];
        $setParts = [];
        $params = [':id_orcamento' => $id_orcamento];
        foreach ($fields as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $param = ':' . $k;
                $setParts[] = "$k = $param";
                $params[$param] = $v;
            }
        }
        if (empty($setParts)) {
            return false;
        }
        $sql = 'UPDATE Orcamento SET ' . implode(', ', $setParts) . ' WHERE id_orcamento = :id_orcamento';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $p => $v) {
            $stmt->bindValue($p, $v);
        }
        return $stmt->execute();
    }

    public function excluir($id_orcamento)
    {
        $stmt = $this->db->prepare('DELETE FROM Orcamento WHERE id_orcamento = :id_orcamento');
        $stmt->bindParam(':id_orcamento', $id_orcamento, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
