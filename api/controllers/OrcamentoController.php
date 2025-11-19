<?php
// api/controllers/OrcamentoController.php
require_once __DIR__ . '/../repositories/OrcamentoRepository.php';

class OrcamentoController
{
    private $repo;
    public function __construct()
    {
        $this->repo = new OrcamentoRepository();
    }

    public function listar()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $mes_ano = $_GET['mes_ano'] ?? null; // formato 'YYYY-MM'
        if (!$id_usuario || !$mes_ano) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario e mes_ano são obrigatórios']);
            return;
        }
        $orcamentos = $this->repo->getByMesAno($id_usuario, $mes_ano);
        echo json_encode($orcamentos);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'id_categoria', 'valor_limite', 'data_inicio', 'data_fim', 'ativo'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->criar($data);
        echo json_encode(['success' => true, 'id_orcamento' => $id]);
    }
}
