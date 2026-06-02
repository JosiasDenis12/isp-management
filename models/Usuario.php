<?php
require_once 'config/database.php';

class Usuario {
    private $conn;
    private $table_name = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findByUsername($username) {
        $query = "SELECT * FROM {$this->table_name} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function verifyPassword(array $user, string $password): bool {
        if (!isset($user['password'])) return false;
        $stored = (string)$user['password'];
        // Si parece un hash moderno, usar password_verify
        if (preg_match('/^(\$2y\$|\$argon2i\$|\$argon2id\$)/', $stored)) {
            return password_verify($password, $stored);
        }
        // Comparación directa (temporal) si guardado en texto plano
        return hash_equals($stored, $password);
    }

    public function updatePasswordHash(int $id, string $newHash): bool {
        $query = "UPDATE {$this->table_name} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $newHash);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
