<?php
// api/controllers/OrcamentoController.php
require_once __DIR__ . '/../repositories/OrcamentoRepository.php';
require_once __DIR__ . '/../services/OrcamentoService.php';

class OrcamentoController
{
    private $repo;
    private $orcamentoService;

    public function __construct()
    {
        $this->repo = new OrcamentoRepository();
        $this->orcamentoService = new OrcamentoService();
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

        // Retorna orçamentos com status calculado (gasto, restante, percentual, status visual)
        $orcamentosComStatus = $this->orcamentoService->verificarOrcamentosAtivos($id_usuario, $mes_ano);
        echo json_encode($orcamentosComStatus);
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

    public function atualizar()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_orcamento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id_orcamento é obrigatório']);
            return;
        }

        $id = (int)$data['id_orcamento'];
        $fields = [];

        // Campos permitidos para atualização
        $updatable = ['valor_limite', 'data_inicio', 'data_fim', 'ativo', 'id_categoria'];
        foreach ($updatable as $f) {
            if (array_key_exists($f, $data)) {
                $fields[$f] = $data[$f];
            }
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nenhum campo para atualizar']);
            return;
        }

        $ok = $this->repo->atualizar($id, $fields);
        echo json_encode(['success' => (bool)$ok]);
    }

    public function consultarStatus($id_orcamento)
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        $status = $this->orcamentoService->calcularStatusOrcamento($id_orcamento, $id_usuario);

        if (isset($status['erro'])) {
            http_response_code(404);
            echo json_encode($status);
            return;
        }

        echo json_encode($status);
    }
}
