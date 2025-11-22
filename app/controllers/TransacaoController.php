<?php
// api/controllers/TransacaoController.php
require_once __DIR__ . '/../models/TransacaoRepository.php';
require_once __DIR__ . '/../services/SaldoService.php';
require_once __DIR__ . '/../services/OrcamentoService.php';
require_once __DIR__ . '/../services/MetaFinanceiraService.php';

class TransacaoController
{
    private $repo;
    private $saldoService;
    private $orcamentoService;
    private $metaService;

    public function __construct()
    {
        $this->repo = new TransacaoRepository();
        $this->saldoService = new SaldoService();
        $this->orcamentoService = new OrcamentoService();
        $this->metaService = new MetaFinanceiraService();
    }

    public function extrato()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        $id_conta = $_GET['id_conta'] ?? null;
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;

        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }

        $transacoes = $this->repo->getExtrato($id_usuario, $id_conta, $data_inicio, $data_fim);
        echo json_encode($transacoes);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['id_usuario', 'id_conta', 'id_categoria', 'valor', 'tipo_movimentacao', 'data_transacao', 'descricao', 'efetuada'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field é obrigatório"]);
                return;
            }
        }
        $id = $this->repo->criar($data);

        // C. Gatilho simples: recalcular saldo atual da conta impactada
        $saldoAtual = $this->saldoService->calcularSaldoAtualConta($data['id_conta']);

        // D. Gatilho de Orçamento: Verificar estouro após criar DESPESA
        $alertaOrcamento = null;
        if ($data['tipo_movimentacao'] === 'DESPESA' && $data['efetuada'] == true) {
            $alertaOrcamento = $this->orcamentoService->verificarEstouroAposTransacao(
                $data['id_usuario'],
                $data['id_categoria'],
                $data['data_transacao']
            );
        }

        // E. Gatilho de Meta: Verificar conclusão após contribuição
        $alertaMeta = null;
        if ($data['tipo_movimentacao'] === 'DESPESA' && $data['efetuada'] == true) {
            $alertaMeta = $this->metaService->verificarConclusaoMetaAposContribuicao(
                $data['id_usuario'],
                $data['id_categoria']
            );
        }

        $response = [
            'success' => true,
            'id_transacao' => $id,
            'saldo_atual_conta' => $saldoAtual
        ];

        if ($alertaOrcamento) {
            $response['alerta_orcamento'] = $alertaOrcamento;
        }

        if ($alertaMeta) {
            $response['alerta_meta'] = $alertaMeta;
        }

        echo json_encode($response);
    }
    // Lista todas as despesas do usuário
    public function listarDespesas()
    {
        $id_usuario = $_GET['id_usuario'] ?? null;
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }
        $despesas = $this->repo->getDespesas($id_usuario);
        echo json_encode($despesas);
    }

    // Marca uma despesa como recorrente
    public function marcarRecorrente()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_transacao = $data['id_transacao'] ?? null;
        $id_usuario = $data['id_usuario'] ?? null;
        if (!$id_transacao || !$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_transacao e id_usuario são obrigatórios']);
            return;
        }
        $result = $this->repo->setRecorrente($id_transacao, $id_usuario);
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Falha ao marcar recorrente']);
        }
    }

    // Atualiza uma transação (principalmente para marcar como efetuada)
    public function atualizar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_transacao = $data['id_transacao'] ?? null;

        if (!$id_transacao) {
            http_response_code(400);
            echo json_encode(['error' => 'id_transacao é obrigatório']);
            return;
        }

        // Permitir atualizar apenas o campo efetuada
        if (isset($data['efetuada'])) {
            $result = $this->repo->atualizarEfetuada($id_transacao, $data['efetuada']);

            if ($result) {
                echo json_encode(['success' => true, 'mensagem' => 'Transação atualizada com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Falha ao atualizar transação']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Nenhum campo para atualizar']);
        }
    }
}
