<?php
// api/controllers/RelatorioController.php
require_once __DIR__ . '/../repositories/RelatorioRepository.php';

class RelatorioController
{
    private $repo;
    public function __construct()
    {
        $this->repo = new RelatorioRepository();
    }

    public function gastosPorCategoria()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $mes_ano = $_GET['mes_ano'] ?? null; // formato 'YYYY-MM'
        if (!$id_usuario || !$mes_ano) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario e mes_ano são obrigatórios']);
            return;
        }
        $dados = $this->repo->gastosPorCategoria($id_usuario, $mes_ano);
        echo json_encode($dados);
    }
}
