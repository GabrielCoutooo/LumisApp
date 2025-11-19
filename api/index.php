<?php
// api/index.php

require_once __DIR__ . '/config/database.php';

// Roteamento simples
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/api/login':
        if ($method === 'POST') {
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/contas':
        require_once __DIR__ . '/controllers/ContaController.php';
        $contaController = new ContaController();
        if ($method === 'GET') {
            $contaController->listar();
        } elseif ($method === 'POST') {
            $contaController->criar();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;

    case '/api/transacoes':
        require_once __DIR__ . '/controllers/TransacaoController.php';
        $transacaoController = new TransacaoController();
        if ($method === 'POST') {
            $transacaoController->criar();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/extrato':
        require_once __DIR__ . '/controllers/TransacaoController.php';
        $transacaoController = new TransacaoController();
        if ($method === 'GET') {
            $transacaoController->extrato();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/orcamento':
        require_once __DIR__ . '/controllers/OrcamentoController.php';
        $orcamentoController = new OrcamentoController();
        if ($method === 'GET') {
            $orcamentoController->listar();
        } elseif ($method === 'POST') {
            $orcamentoController->criar();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/relatorios/gastos-categoria':
        require_once __DIR__ . '/controllers/RelatorioController.php';
        $relatorioController = new RelatorioController();
        if ($method === 'GET') {
            $relatorioController->gastosPorCategoria();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/dashboard':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $dashboardController = new DashboardController();
        if ($method === 'GET') {
            $dashboardController->getDashboard();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/categorias':
        require_once __DIR__ . '/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        if ($method === 'GET') {
            $categoriaController->listar();
        } elseif ($method === 'POST') {
            $categoriaController->criar();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    case '/api/metas':
        require_once __DIR__ . '/controllers/MetaFinanceiraController.php';
        $metaController = new MetaFinanceiraController();
        if ($method === 'GET') {
            $metaController->listar();
        } elseif ($method === 'POST') {
            $metaController->criar();
        } elseif ($method === 'PUT') {
            $metaController->atualizar();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint não encontrado']);
        break;
}
