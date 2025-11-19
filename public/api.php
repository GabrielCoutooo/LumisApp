<?php
// public/api.php - Front Controller da API

// Carrega configuração do banco
require_once __DIR__ . '/../app/config/database.php';

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

// Carrega rotas
$routesConfig = require_once __DIR__ . '/../app/routes/api.php';
$dynamicRoutes = $routesConfig['dynamic'];
$staticRoutes = $routesConfig['static'];

// Normalização do path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove /api.php do caminho se existir
if (strpos($path, '/api.php') !== false) {
    $path = substr($path, strpos($path, '/api.php') + strlen('/api.php'));
}

// Remove diretório do script
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

// Remove barra final
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Tenta rotas dinâmicas primeiro
foreach ($dynamicRoutes as $pattern => $route) {
    if (preg_match($pattern, $path, $matches)) {
        if ($method === $route['httpMethod']) {
            array_shift($matches); // Remove o match completo
            require_once __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';
            $controller = new $route['controller']();
            call_user_func_array([$controller, $route['method']], $matches);
            exit;
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }
    }
}

// Tenta rotas estáticas
if (isset($staticRoutes[$path])) {
    if (isset($staticRoutes[$path][$method])) {
        $route = $staticRoutes[$path][$method];
        require_once __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';
        $controller = new $route['controller']();
        $controller->{$route['method']}();
        exit;
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        exit;
    }
}

// Rota não encontrada
http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada']);
