<?php
// api/controllers/MetaFinanceiraController.php
require_once __DIR__ . '/../repositories/MetaFinanceiraRepository.php';

class MetaFinanceiraController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new MetaFinanceiraRepository();
    }

    public function listar()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }
        $metas = $this->repo->getAllByUsuario($id_usuario);
        echo json_encode($metas);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'nome', 'valor_alvo', 'data_alvo', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->create($data);
        echo json_encode(['success' => true, 'id_meta' => $id]);
    }

    public function atualizar()
    {
        $id_meta = $_GET['id_meta'] ?? null;
        if (!$id_meta) {
            http_response_code(400);
            echo json_encode(['error' => 'id_meta é obrigatório']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'status é obrigatório']);
            return;
        }

        $result = $this->repo->update($id_meta, $data);
        echo json_encode(['success' => $result]);
    }
}
