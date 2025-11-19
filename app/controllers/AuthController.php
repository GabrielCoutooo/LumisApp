<?php
// api/controllers/AuthController.php
require_once __DIR__ . '/../config/database.php';

class AuthController
{
    public function login()
    {
        // Recebe dados do POST
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $senha = $data['senha'] ?? '';

        if (!$email || !$senha) {
            http_response_code(400);
            echo json_encode(['error' => 'Email e senha são obrigatórios']);
            return;
        }

        // Exemplo de consulta ao banco (ajuste conforme sua tabela de usuários)
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare('SELECT * FROM Usuario WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha_hash'])) {
            // Remove senha_hash da resposta
            unset($user['senha_hash']);
            echo json_encode(['success' => true, 'usuario' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
        }
    }
}
