<?php
require_once __DIR__ . '/../../config/database.php';

class Authentication {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureUsersTableExists();
    }

    public function ensureUsersTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($sql);
    }

    

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function register($email, $password, $name) {
        $sql = "INSERT INTO users (email, password, name) VALUES (:email, :password, :name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name
        ]);

        return $this->login($email, $password);
    }
}

?>