<?php
// api/index.php

require_once __DIR__ . '/config/database.php';

// Headers globais (JSON + CORS para desenvolvimento)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder pré-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Normalização do path para funcionar em Apache (XAMPP) e php -S
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Ex.: '/LumisApp/api/index.php/api/contas' -> '/api/contas'
if (strpos($path, '/index.php') !== false) {
    $path = substr($path, strpos($path, '/index.php') + strlen('/index.php'));
}

// Caso ainda venha com diretório do script no início
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($scriptName && strpos($path, $scriptName) === 0) {
    $path = substr($path, strlen($scriptName));
} elseif ($scriptDir && $scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}

if ($path === '' || $path === false) {
    $path = '/';
}

// Remover barra final para evitar 404/405 com trailing slash
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Rotas dinâmicas simples (antes do switch)
// GET /api/orcamento/status/:id
if (preg_match('#^/api/orcamento/status/(\d+)$#', $path, $matches)) {
    require_once __DIR__ . '/controllers/OrcamentoController.php';
    $orcamentoController = new OrcamentoController();
    if ($method === 'GET') {
        $orcamentoController->consultarStatus($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
    exit;
}

// GET /api/metas/:id/progresso
if (preg_match('#^/api/metas/(\d+)/progresso$#', $path, $matches)) {
    require_once __DIR__ . '/controllers/MetaFinanceiraController.php';
    $metaController = new MetaFinanceiraController();
    if ($method === 'GET') {
        $metaController->consultarProgresso($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
    exit;
}

// Roteamento simples
switch ($path) {
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
        } elseif ($method === 'PUT') {
            $contaController->atualizar();
        } elseif ($method === 'DELETE') {
            $contaController->excluir();
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
        } elseif ($method === 'PUT') {
            $orcamentoController->atualizar();
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
        } elseif ($method === 'PUT') {
            $categoriaController->atualizar();
        } elseif ($method === 'DELETE') {
            $categoriaController->excluir();
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

    case '/api/user/perfil':
        require_once __DIR__ . '/controllers/UserController.php';
        $userController = new UserController();
        if ($method === 'GET') {
            $userController->getPerfil();
        } elseif ($method === 'PUT') {
            $userController->atualizarPerfil();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;

    case '/api/user/senha':
        require_once __DIR__ . '/controllers/UserController.php';
        $userController = new UserController();
        if ($method === 'PUT') {
            $userController->alterarSenha();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;

    case '/api/user/configuracoes':
        require_once __DIR__ . '/controllers/UserController.php';
        $userController = new UserController();
        if ($method === 'PUT') {
            $userController->atualizarConfiguracoes();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;

    case '/api/user/conta':
        require_once __DIR__ . '/controllers/UserController.php';
        $userController = new UserController();
        if ($method === 'DELETE') {
            $userController->excluirConta();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
        }
        break;

    case '/api/user/exportar':
        require_once __DIR__ . '/controllers/UserController.php';
        $userController = new UserController();
        if ($method === 'GET') {
            $userController->exportarDados();
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
