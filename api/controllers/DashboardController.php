<?php
// api/controllers/DashboardController.php
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController
{
    private $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function getDashboard()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $mes_ano = $_GET['mes_ano'] ?? date('Y-m'); // Padrão: mês atual

        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        $data = $this->service->getDashboardData($id_usuario, $mes_ano);
        echo json_encode($data);
    }
}
