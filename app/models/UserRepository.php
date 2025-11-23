<?php
// api/repositories/UserRepository.php

class UserRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function buscarPorEmail($email)
    {
        $sql = "SELECT * FROM Usuario WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criarUsuario($nome, $email, $senha_hash)
    {
        $sql = "INSERT INTO Usuario (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha_hash', $senha_hash);
        return $stmt->execute();
    }

    public function buscarPorId($id_usuario)
    {
        $sql = "SELECT id_usuario, nome, email, data_criacao as data_registro, 
                       config_saldo_oculto, config_moeda, config_idioma, 
                       config_notificacoes, config_primeiro_dia_mes, senha_hash
                FROM Usuario 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarPerfil($id_usuario, $nome, $email)
    {
        $sql = "UPDATE Usuario 
                SET nome = :nome, email = :email 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizarSenha($id_usuario, $senha_hash)
    {
        $sql = "UPDATE Usuario 
                SET senha_hash = :senha_hash 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':senha_hash', $senha_hash);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizarConfiguracoes($id_usuario, $config)
    {
        $campos = [];
        $params = [':id_usuario' => $id_usuario];

        if (isset($config['config_saldo_oculto'])) {
            $campos[] = 'config_saldo_oculto = :config_saldo_oculto';
            $params[':config_saldo_oculto'] = $config['config_saldo_oculto'] ? 1 : 0;
        }
        if (isset($config['config_moeda'])) {
            $campos[] = 'config_moeda = :config_moeda';
            $params[':config_moeda'] = $config['config_moeda'];
        }
        if (isset($config['config_idioma'])) {
            $campos[] = 'config_idioma = :config_idioma';
            $params[':config_idioma'] = $config['config_idioma'];
        }
        if (isset($config['config_notificacoes'])) {
            $campos[] = 'config_notificacoes = :config_notificacoes';
            $params[':config_notificacoes'] = $config['config_notificacoes'] ? 1 : 0;
        }
        if (isset($config['config_primeiro_dia_mes'])) {
            $campos[] = 'config_primeiro_dia_mes = :config_primeiro_dia_mes';
            $params[':config_primeiro_dia_mes'] = $config['config_primeiro_dia_mes'];
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE Usuario SET " . implode(', ', $campos) . " WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function excluirUsuario($id_usuario)
    {
        $sql = "DELETE FROM Usuario WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function exportarTransacoes($id_usuario)
    {
        $sql = "SELECT 
                    t.id_transacao,
                    t.data_transacao,
                    t.tipo_movimentacao AS tipo,
                    t.descricao,
                    t.valor,
                    t.efetuada,
                    cat.nome AS categoria_nome,
                    c.nome AS conta_nome
                FROM Transacao t
                LEFT JOIN Categoria cat ON t.id_categoria = cat.id_categoria
                LEFT JOIN Conta c ON t.id_conta = c.id_conta
                WHERE t.id_usuario = :id_usuario
                ORDER BY t.data_transacao DESC, t.id_transacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
