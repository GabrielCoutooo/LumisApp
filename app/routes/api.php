<?php
// app/routes/api.php - Definição de rotas da API

// Rotas dinâmicas com parâmetros
$dynamicRoutes = [
    '#^/api/orcamento/status/(\d+)$#' => [
        'controller' => 'OrcamentoController',
        'method' => 'consultarStatus',
        'httpMethod' => 'GET'
    ],
    '#^/api/metas/(\d+)/progresso$#' => [
        'controller' => 'MetaFinanceiraController',
        'method' => 'consultarProgresso',
        'httpMethod' => 'GET'
    ]
];

// Rotas estáticas
$routes = [
    '/api/user/register' => [
        'POST' => ['controller' => 'UserController', 'method' => 'registerUser']
    ],
    '/api/login' => [
        'POST' => ['controller' => 'AuthController', 'method' => 'login']
    ],
    '/api/contas' => [
        'GET' => ['controller' => 'ContaController', 'method' => 'listar'],
        'POST' => ['controller' => 'ContaController', 'method' => 'criar'],
        'PUT' => ['controller' => 'ContaController', 'method' => 'atualizar'],
        'DELETE' => ['controller' => 'ContaController', 'method' => 'excluir']
    ],
    '/api/transacoes' => [
        'GET' => ['controller' => 'TransacaoController', 'method' => 'listar'],
        'POST' => ['controller' => 'TransacaoController', 'method' => 'criar'],
        'PUT' => ['controller' => 'TransacaoController', 'method' => 'atualizar'],
        'DELETE' => ['controller' => 'TransacaoController', 'method' => 'excluir']
    ],
    '/api/categorias' => [
        'GET' => ['controller' => 'CategoriaController', 'method' => 'listar'],
        'POST' => ['controller' => 'CategoriaController', 'method' => 'criar'],
        'PUT' => ['controller' => 'CategoriaController', 'method' => 'atualizar'],
        'DELETE' => ['controller' => 'CategoriaController', 'method' => 'excluir']
    ],
    '/api/dashboard' => [
        'GET' => ['controller' => 'DashboardController', 'method' => 'getDashboard']
    ],
    '/api/relatorios/categorias' => [
        'GET' => ['controller' => 'RelatorioController', 'method' => 'porCategoria']
    ],
    '/api/relatorios/mensal' => [
        'GET' => ['controller' => 'RelatorioController', 'method' => 'mensal']
    ],
    '/api/relatorios/anual' => [
        'GET' => ['controller' => 'RelatorioController', 'method' => 'anual']
    ],
    '/api/orcamento' => [
        'GET' => ['controller' => 'OrcamentoController', 'method' => 'listar'],
        'POST' => ['controller' => 'OrcamentoController', 'method' => 'criar'],
        'PUT' => ['controller' => 'OrcamentoController', 'method' => 'atualizar'],
        'DELETE' => ['controller' => 'OrcamentoController', 'method' => 'excluir']
    ],
    '/api/metas' => [
        'GET' => ['controller' => 'MetaFinanceiraController', 'method' => 'listar'],
        'POST' => ['controller' => 'MetaFinanceiraController', 'method' => 'criar'],
        'PUT' => ['controller' => 'MetaFinanceiraController', 'method' => 'atualizar'],
        'DELETE' => ['controller' => 'MetaFinanceiraController', 'method' => 'excluir']
    ],
    '/api/user/perfil' => [
        'GET' => ['controller' => 'UserController', 'method' => 'getPerfil'],
        'PUT' => ['controller' => 'UserController', 'method' => 'atualizarPerfil']
    ],
    '/api/user/senha' => [
        'PUT' => ['controller' => 'UserController', 'method' => 'alterarSenha']
    ],
    '/api/user/configuracoes' => [
        'PUT' => ['controller' => 'UserController', 'method' => 'atualizarConfiguracoes']
    ],
    '/api/user/conta' => [
        'DELETE' => ['controller' => 'UserController', 'method' => 'excluirConta']
    ],
    '/api/user/exportar' => [
        'GET' => ['controller' => 'UserController', 'method' => 'exportarDados']
    ],
    '/api/extrato' => [
        'GET' => ['controller' => 'TransacaoController', 'method' => 'extrato']
    ],
    '/api/despesas' => [
        'GET' => ['controller' => 'RecorrenciaController', 'method' => 'listarDespesas']
    ],
    '/api/recorrencia' => [
        'POST' => ['controller' => 'RecorrenciaController', 'method' => 'marcarRecorrente']
    ],
    '/api/recorrencia/remover' => [
        'POST' => ['controller' => 'RecorrenciaController', 'method' => 'removerRecorrente']
    ],
    '/api/recorrencias/gerar' => [
        'POST' => ['controller' => 'RecorrenciaController', 'method' => 'gerarRecorrenciasMes']
    ],
    '/api/transferencia' => [
        'POST' => ['controller' => 'TransacaoController', 'method' => 'criarTransferencia']
    ]
];

return [
    'dynamic' => $dynamicRoutes,
    'static' => $routes
];
