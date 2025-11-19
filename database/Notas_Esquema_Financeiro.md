Introdução
Este documento descreve as tabelas do banco de dados do Sistema de Gerenciamento Financeiro Pessoal, detalhando sua função e os relacionamentos-chave. O foco está na integridade dos dados e escalabilidade para suportar um alto volume de transações.

1. USUARIO (Tabela Base)
   Função: Armazena os dados básicos e credenciais de cada usuário.

Detalhes-chave:

senha_hash: Campo obrigatório para armazenar senhas criptografadas (nunca a senha em texto simples).

email: Campo único, usado para login e recuperação de conta.

Relacionamento: É a tabela pai para todas as outras, garantindo que cada registro pertença a um usuário (ON DELETE CASCADE garante que, se um usuário for excluído, todos os seus dados também o serão).

2. CONTA (Contas Financeiras)
   Função: Representa onde o dinheiro do usuário está localizado (Conta Corrente, Cartão de Crédito, Dinheiro Físico, Investimentos, etc.).

Detalhes-chave:

tipo_conta: Define a natureza da conta, fundamental para o cálculo de saldo e extratos.

saldo_inicial: Ponto de partida para o rastreamento, útil para conciliar com o saldo real do banco.

Relacionamento: Vinculada à USUARIO e referenciada pela TRANSACAO.

3. CATEGORIA (Classificação de Movimentação)
   Função: Classifica as transações em receitas ou despesas (ex: Alimentação, Salário, Moradia).

Detalhes-chave:

id_usuario: Permite que os usuários criem categorias personalizadas. Se NULL, a categoria é padrão do sistema.

tipo: Fundamental para relatórios e orçamentos (garante que você não tente orçar uma categoria de Receita).

Relacionamento: Vinculada à USUARIO e referenciada pela TRANSACAO e ORCAMENTO.

4. TRANSACAO (Núcleo do Sistema)
   Função: O registro de todo e qualquer movimento de dinheiro (a tabela mais importante e volumosa).

Detalhes-chave para Escalabilidade:

id_transacao usa BIGSERIAL/BIGINT para suportar milhões de registros.

valor: Usa DECIMAL(10, 2) para precisão financeira.

Índices: Possui índices na data e nas chaves estrangeiras (id_usuario, id_conta), cruciais para a velocidade de carregamento de extratos e relatórios.

efetuada: Permite rastrear transações que foram agendadas, mas ainda não ocorreram (dinheiro "a pagar/receber").

Relacionamento: Referencia USUARIO, CONTA e CATEGORIA.

5. RECORRENCIA (Transações Futuras)
   Função: Define o padrão de transações que se repetem (ex: aluguel, assinatura mensal, salário).

Detalhes-chave:

Esta tabela NÃO armazena as transações, mas sim as regras para que o sistema (o backend da sua aplicação) crie novas entradas na tabela TRANSACAO na proxima_data.

Relacionamento: Vinculada a USUARIO, CONTA e CATEGORIA.

6. TRANSFERENCIA (Movimento entre Contas Próprias)
   Função: Rastreia o movimento de dinheiro entre as contas do mesmo usuário (ex: transferir da Conta Corrente para a Poupança).

Detalhes-chave:

Contém dois links (id_transacao_debito e id_transacao_credito) para garantir que ambas as pontas da transferência (o débito na conta A e o crédito na conta B) existam na tabela TRANSACAO. Isso mantém a integridade dos dados e o cálculo de saldo.

7. ORCAMENTO (Controle de Gastos)
   Função: Permite ao usuário definir limites de gastos por categoria em um período.

Detalhes-chave:

É a base para relatórios de "gasto vs. orçado".

Geralmente é consultada pelo sistema para dar alertas ao usuário.

Relacionamento: Vinculada a USUARIO e CATEGORIA.

8. METAFINANCEIRA (Planejamento de Metas)
   Função: Acompanha o progresso do usuário em direção a objetivos de economia (ex: juntar R$ 5.000 para uma viagem).

Detalhes-chave:

valor_alvo: O montante final desejado.

O cálculo do valor_atual será feito pela sua aplicação (backend) consultando as transações marcadas como "depósito para meta X" ou por um campo de saldo na própria tabela (dependendo da sua estratégia).

Relacionamento: Vinculada apenas a USUARIO.
