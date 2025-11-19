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
        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(['error' => 'id_usuario é obrigatório']);
            return;
        }
        $transacoes = $this->repo->getExtrato($id_usuario, $id_conta);
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
}
