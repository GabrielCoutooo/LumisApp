<?php
// api/controllers/MetaFinanceiraController.php
require_once __DIR__ . '/../models/MetaFinanceiraRepository.php';
require_once __DIR__ . '/../services/MetaFinanceiraService.php';

class MetaFinanceiraController
{
    private $repo;
    private $metaService;

    public function __construct()
    {
        $this->repo = new MetaFinanceiraRepository();
        $this->metaService = new MetaFinanceiraService();
    }

    public function listar()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        // Retorna metas com progresso calculado
        $metasComProgresso = $this->metaService->verificarMetasAtivas($id_usuario);
        echo json_encode($metasComProgresso);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'nome', 'valor_alvo'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }

        try {
            // Criar meta com categoria de contribuição automática
            $resultado = $this->metaService->criarMetaComCategoria(
                $data['id_usuario'],
                $data['nome'],
                $data['valor_alvo'],
                $data['data_alvo'] ?? null,
                $data['status'] ?? 'ATIVA'
            );

            echo json_encode([
                'success' => true,
                'id_meta' => $resultado['id_meta'],
                'id_categoria_contribuicao' => $resultado['id_categoria_contribuicao'],
                'nome_categoria' => $resultado['nome_categoria']
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
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

    public function consultarProgresso($id_meta)
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        $progresso = $this->metaService->calcularProgressoMeta($id_meta, $id_usuario);

        if (isset($progresso['erro'])) {
            http_response_code(404);
            echo json_encode($progresso);
            return;
        }

        echo json_encode($progresso);
    }
}
