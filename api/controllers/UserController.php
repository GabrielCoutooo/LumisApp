<?php
// api/controllers/UserController.php

require_once __DIR__ . '/../repositories/UserRepository.php';

class UserController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    // GET /api/user/perfil?id_usuario=X
    public function getPerfil()
    {
        $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;

        if ($id_usuario <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuário inválido']);
            return;
        }

        $usuario = $this->userRepository->buscarPorId($id_usuario);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            return;
        }

        // Remover senha do retorno
        unset($usuario['senha_hash']);

        http_response_code(200);
        echo json_encode($usuario);
    }

    // PUT /api/user/perfil (atualizar nome e email)
    public function atualizarPerfil()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;
        $nome = isset($input['nome']) ? trim($input['nome']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $senha_confirmacao = isset($input['senha_confirmacao']) ? $input['senha_confirmacao'] : '';

        if ($id_usuario <= 0 || empty($nome) || empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        // Validar senha atual
        $usuario = $this->userRepository->buscarPorId($id_usuario);
        if (!$usuario || !password_verify($senha_confirmacao, $usuario['senha_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Senha atual incorreta']);
            return;
        }

        $atualizado = $this->userRepository->atualizarPerfil($id_usuario, $nome, $email);

        if ($atualizado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar perfil']);
        }
    }

    // PUT /api/user/senha
    public function alterarSenha()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;
        $senha_atual = isset($input['senha_atual']) ? $input['senha_atual'] : '';
        $senha_nova = isset($input['senha_nova']) ? $input['senha_nova'] : '';

        if ($id_usuario <= 0 || empty($senha_atual) || empty($senha_nova)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $usuario = $this->userRepository->buscarPorId($id_usuario);
        if (!$usuario || !password_verify($senha_atual, $usuario['senha_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Senha atual incorreta']);
            return;
        }

        $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
        $atualizado = $this->userRepository->atualizarSenha($id_usuario, $senha_hash);

        if ($atualizado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao alterar senha']);
        }
    }

    // PUT /api/user/configuracoes
    public function atualizarConfiguracoes()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;
        $config = [];

        if (isset($input['config_saldo_oculto'])) {
            $config['config_saldo_oculto'] = (bool) $input['config_saldo_oculto'];
        }
        if (isset($input['config_moeda'])) {
            $config['config_moeda'] = trim($input['config_moeda']);
        }
        if (isset($input['config_idioma'])) {
            $config['config_idioma'] = trim($input['config_idioma']);
        }
        if (isset($input['config_notificacoes'])) {
            $config['config_notificacoes'] = (bool) $input['config_notificacoes'];
        }
        if (isset($input['config_primeiro_dia_mes'])) {
            $config['config_primeiro_dia_mes'] = intval($input['config_primeiro_dia_mes']);
        }

        if ($id_usuario <= 0 || empty($config)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $atualizado = $this->userRepository->atualizarConfiguracoes($id_usuario, $config);

        if ($atualizado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Configurações atualizadas']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar configurações']);
        }
    }

    // DELETE /api/user/conta
    public function excluirConta()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;
        $senha_confirmacao = isset($input['senha_confirmacao']) ? $input['senha_confirmacao'] : '';

        if ($id_usuario <= 0 || empty($senha_confirmacao)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $usuario = $this->userRepository->buscarPorId($id_usuario);
        if (!$usuario || !password_verify($senha_confirmacao, $usuario['senha_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Senha incorreta']);
            return;
        }

        $excluido = $this->userRepository->excluirUsuario($id_usuario);

        if ($excluido) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Conta excluída com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir conta']);
        }
    }

    // GET /api/user/exportar?id_usuario=X&formato=csv
    public function exportarDados()
    {
        $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;
        $formato = isset($_GET['formato']) ? strtolower($_GET['formato']) : 'csv';

        if ($id_usuario <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuário inválido']);
            return;
        }

        if ($formato !== 'csv') {
            http_response_code(400);
            echo json_encode(['error' => 'Formato não suportado. Use: csv']);
            return;
        }

        $dados = $this->userRepository->exportarTransacoes($id_usuario);

        if (!$dados) {
            http_response_code(404);
            echo json_encode(['error' => 'Nenhum dado encontrado']);
            return;
        }

        // Gerar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lumis_export_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8 no Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalho
        fputcsv($output, ['ID', 'Data', 'Tipo', 'Descrição', 'Valor', 'Categoria', 'Conta', 'Efetuada']);

        // Dados
        foreach ($dados as $row) {
            fputcsv($output, [
                $row['id_transacao'],
                $row['data_transacao'],
                $row['tipo'],
                $row['descricao'],
                $row['valor'],
                $row['categoria_nome'] ?? 'N/A',
                $row['conta_nome'] ?? 'N/A',
                $row['efetuada'] ? 'Sim' : 'Não'
            ]);
        }

        fclose($output);
        exit;
    }
}
