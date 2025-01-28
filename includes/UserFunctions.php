<?php
class UserFunctions {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createUser($data) {
        $sql = "INSERT INTO users (nom, prenoms, emails, passwords, roles, ages) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['prenoms'],
            $data['emails'],
            password_hash($data['passwords'], PASSWORD_DEFAULT),
            $data['roles'],
            $data['ages']
        ]);
    }

    public function updateUser($id, $data) {
        $sql = "UPDATE users SET nom = ?, prenoms = ?, emails = ?, roles = ?, ages = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['prenoms'],
            $data['emails'],
            $data['roles'],
            $data['ages'],
            $id
        ]);
    }

    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getUser($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY nom, prenoms";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE emails = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['passwords'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['roles'];
            return true;
        }
        return false;
    }
}