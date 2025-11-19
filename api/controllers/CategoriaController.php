<?php
// api/controllers/CategoriaController.php
require_once __DIR__ . '/../repositories/CategoriaRepository.php';

class CategoriaController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new CategoriaRepository();
    }

    public function listar()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $tipo = $_GET['tipo'] ?? null; // 'RECEITA' ou 'DESPESA'

        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        $categorias = $this->repo->getAllByUsuario($id_usuario, $tipo);
        echo json_encode($categorias);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'nome', 'tipo', 'cor_hex'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->create($data);
        echo json_encode(['success' => true, 'id_categoria' => $id]);
    }

    public function atualizar()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_categoria'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id_categoria é obrigatório']);
            return;
        }

        $atualizado = $this->repo->update($data);

        if ($atualizado) {
            echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar categoria']);
        }
    }

    public function excluir()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_categoria'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id_categoria é obrigatório']);
            return;
        }

        $excluido = $this->repo->delete($data['id_categoria']);

        if ($excluido) {
            echo json_encode(['success' => true, 'message' => 'Categoria excluída com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir categoria. Verifique se não há transações vinculadas.']);
        }
    }
}
