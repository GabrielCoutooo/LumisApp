<?php
// api/controllers/ContaController.php
require_once __DIR__ . '/../repositories/ContaRepository.php';

class ContaController
{
    private $repo;
    public function __construct()
    {
        $this->repo = new ContaRepository();
    }

    public function listar()
    {
        // Em produção, pegue o id_usuario autenticado
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }
        $contas = $this->repo->getAllByUsuario($id_usuario);
        echo json_encode($contas);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'nome', 'tipo_conta', 'saldo_inicial', 'exibir_no_dashboard'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->create($data);
        echo json_encode(['success' => true, 'id_conta' => $id]);
    }
}
