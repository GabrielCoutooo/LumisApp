<?php
// app/controllers/UserController.php
// O autoload do Composer só é necessário para exportação (PhpSpreadsheet).
// Carregamos sob demanda dentro de exportarDados() para evitar erro fatal
// quando a pasta vendor ainda não existe (antes de rodar `composer install`).

require_once __DIR__ . '/../models/UserRepository.php';

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

    // GET /api/user/exportar?id_usuario=X&formato=xlsx  (ou formato=csv)
    public function exportarDados()
    {
        $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;
        $formato = isset($_GET['formato']) ? strtolower($_GET['formato']) : 'xlsx';

        if ($id_usuario <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuário inválido']);
            return;
        }

        if (!in_array($formato, ['xlsx', 'csv'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato não suportado. Use: xlsx ou csv']);
            return;
        }

        // Buscar dados do usuário
        $usuario = $this->userRepository->buscarPorId($id_usuario);
        $transacoes = $this->userRepository->exportarTransacoes($id_usuario);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            return;
        }

        if ($formato === 'xlsx') {
            // Carrega autoload apenas se necessário
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                if (file_exists($autoloadPath)) {
                    require_once $autoloadPath;
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Dependências não instaladas. Execute "composer install" para habilitar exportação XLSX.']);
                    return;
                }
            }
            // Exportação real XLSX com PhpSpreadsheet
            $timestamp = date('Ymd_His');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="lumis_export_' . $timestamp . '.xlsx"');

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Aba 1: Usuário
            $sheetUsuario = $spreadsheet->getActiveSheet();
            $sheetUsuario->setTitle('Usuário');
            $sheetUsuario->fromArray([
                ['INFORMAÇÕES DO USUÁRIO'],
                ['Nome', $usuario['nome']],
                ['Email', $usuario['email']],
                ['Data de Cadastro', isset($usuario['data_registro']) ? date('d/m/Y', strtotime($usuario['data_registro'])) : ''],
                ['Total de Transações', count($transacoes)]
            ]);
            foreach (range('A', 'B') as $col) {
                $sheetUsuario->getColumnDimension($col)->setAutoSize(true);
            }

            // Aba 2: Resumo
            $totalReceitas = 0;
            $totalDespesas = 0;
            $totalEfetuadas = 0;
            $totalPendentes = 0;
            foreach ($transacoes as $t) {
                if ($t['tipo'] === 'RECEITA') {
                    $totalReceitas += (float)$t['valor'];
                } elseif ($t['tipo'] === 'DESPESA') {
                    $totalDespesas += (float)$t['valor'];
                }
                if ($t['efetuada']) {
                    $totalEfetuadas++;
                } else {
                    $totalPendentes++;
                }
            }
            $sheetResumo = $spreadsheet->createSheet();
            $sheetResumo->setTitle('Resumo');
            $sheetResumo->fromArray([
                ['RESUMO FINANCEIRO'],
                ['Total de Receitas', $totalReceitas],
                ['Total de Despesas', $totalDespesas],
                ['Saldo', $totalReceitas - $totalDespesas],
                ['Transações Efetuadas', $totalEfetuadas],
                ['Transações Pendentes', $totalPendentes]
            ]);
            foreach (range('A', 'B') as $col) {
                $sheetResumo->getColumnDimension($col)->setAutoSize(true);
            }

            // Aba 3: Transações
            $sheetTransacoes = $spreadsheet->createSheet();
            $sheetTransacoes->setTitle('Transações');
            $sheetTransacoes->fromArray([
                ['ID', 'Data', 'Tipo', 'Descrição', 'Valor', 'Categoria', 'Conta', 'Status']
            ], null, 'A1');
            $linha = 2;
            foreach ($transacoes as $row) {
                $sheetTransacoes->fromArray([
                    $row['id_transacao'],
                    date('d/m/Y', strtotime($row['data_transacao'])),
                    $row['tipo'],
                    $row['descricao'],
                    (float)$row['valor'],
                    $row['categoria_nome'] ?? 'Sem Categoria',
                    $row['conta_nome'] ?? 'Sem Conta',
                    $row['efetuada'] ? 'Efetuada' : 'Pendente'
                ], null, 'A' . $linha);
                $linha++;
            }
            foreach (range('A', 'H') as $col) {
                $sheetTransacoes->getColumnDimension($col)->setAutoSize(true);
            }

            // Rodapé na última linha da aba Transações
            $sheetTransacoes->setCellValue('A' . $linha, 'Exportado em');
            $sheetTransacoes->setCellValue('B' . $linha, date('d/m/Y H:i:s'));
            $sheetTransacoes->setCellValue('A' . ($linha + 1), 'Sistema');
            $sheetTransacoes->setCellValue('B' . ($linha + 1), 'Lumis - Gestão Financeira Pessoal');

            // Salvar para output
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        // Fallback CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lumis_export_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        $delim = ';';
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // ========== SEÇÃO 1: INFORMAÇÕES DO USUÁRIO ==========
        fputcsv($output, ['INFORMAÇÕES DO USUÁRIO'], $delim);
        fputcsv($output, ['Nome', $usuario['nome']], $delim);
        fputcsv($output, ['Email', $usuario['email']], $delim);
        // buscarPorId() retorna data_criacao como data_registro
        $dataCadastro = isset($usuario['data_registro']) ? date('d/m/Y', strtotime($usuario['data_registro'])) : '';
        fputcsv($output, ['Data de Cadastro', $dataCadastro], $delim);
        fputcsv($output, ['Total de Transações', count($transacoes)], $delim);
        fputcsv($output, [], $delim); // Linha em branco
        fputcsv($output, [], $delim); // Linha em branco

        // ========== SEÇÃO 2: RESUMO FINANCEIRO ==========
        $totalReceitas = 0;
        $totalDespesas = 0;
        $totalEfetuadas = 0;
        $totalPendentes = 0;

        foreach ($transacoes as $t) {
            if ($t['tipo'] === 'RECEITA') {
                $totalReceitas += floatval($t['valor']);
            } elseif ($t['tipo'] === 'DESPESA') {
                $totalDespesas += floatval($t['valor']);
            }

            if ($t['efetuada']) {
                $totalEfetuadas++;
            } else {
                $totalPendentes++;
            }
        }

        fputcsv($output, ['RESUMO FINANCEIRO'], $delim);
        fputcsv($output, ['Total de Receitas', 'R$ ' . number_format($totalReceitas, 2, ',', '.')], $delim);
        fputcsv($output, ['Total de Despesas', 'R$ ' . number_format($totalDespesas, 2, ',', '.')], $delim);
        fputcsv($output, ['Saldo', 'R$ ' . number_format($totalReceitas - $totalDespesas, 2, ',', '.')], $delim);
        fputcsv($output, ['Transações Efetuadas', $totalEfetuadas], $delim);
        fputcsv($output, ['Transações Pendentes', $totalPendentes], $delim);
        fputcsv($output, [], $delim); // Linha em branco
        fputcsv($output, [], $delim); // Linha em branco

        // ========== SEÇÃO 3: TRANSAÇÕES DETALHADAS ==========
        fputcsv($output, ['TRANSAÇÕES DETALHADAS'], $delim);
        fputcsv($output, ['ID', 'Data', 'Tipo', 'Descrição', 'Valor', 'Categoria', 'Conta', 'Status'], $delim);

        // Dados das transações
        foreach ($transacoes as $row) {
            fputcsv($output, [
                $row['id_transacao'],
                date('d/m/Y', strtotime($row['data_transacao'])),
                $row['tipo'],
                $row['descricao'],
                'R$ ' . number_format(floatval($row['valor']), 2, ',', '.'),
                $row['categoria_nome'] ?? 'Sem Categoria',
                $row['conta_nome'] ?? 'Sem Conta',
                $row['efetuada'] ? 'Efetuada' : 'Pendente'
            ], $delim);
        }

        fputcsv($output, [], $delim); // Linha em branco
        fputcsv($output, [], $delim); // Linha em branco

        // ========== SEÇÃO 4: RODAPÉ ==========
        fputcsv($output, ['Exportado em', date('d/m/Y H:i:s')], $delim);
        fputcsv($output, ['Sistema', 'Lumis - Gestão Financeira Pessoal'], $delim);

        fclose($output);
        exit;
    }
}
