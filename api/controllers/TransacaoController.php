<?php
// api/controllers/TransacaoController.php
require_once __DIR__ . '/../repositories/TransacaoRepository.php';

class TransacaoController
{
    private $repo;
    public function __construct()
    {
        $this->repo = new TransacaoRepository();
    }

    public function extrato()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $id_conta = $_GET['id_conta'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }
        $transacoes = $this->repo->getExtrato($id_usuario, $id_conta);
        echo json_encode($transacoes);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'id_conta', 'id_categoria', 'valor', 'tipo_movimentacao', 'data_transacao', 'descricao', 'efetuada'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->criar($data);
        echo json_encode(['success' => true, 'id_transacao' => $id]);
    }
}
