<?php
// classes/User.php - OOP Gebruikersmodel

require_once __DIR__ . '/../config/Database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, email, role, phone, birthdate, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    public function register(string $name, string $email, string $password, string $role = 'lid', ?string $phone = null, ?string $birthdate = null): int {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, phone, birthdate) 
            VALUES (:name, :email, :password, :role, :phone, :birthdate)
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
            'role' => in_array($role, ['lid', 'trainer', 'medewerker']) ? $role : 'lid',
            'phone' => $phone,
            'birthdate' => $birthdate ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $name, ?string $phone, ?string $birthdate, ?string $newPassword = null): bool {
        if ($newPassword) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = :name, phone = :phone, birthdate = :birthdate, password = :password
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $name,
                'phone' => $phone,
                'birthdate' => $birthdate ?: null,
                'password' => $hashed,
                'id' => $id
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = :name, phone = :phone, birthdate = :birthdate
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $name,
                'phone' => $phone,
                'birthdate' => $birthdate ?: null,
                'id' => $id
            ]);
        }
    }

    public function getAll(string $roleFilter = ''): array {
        if ($roleFilter && in_array($roleFilter, ['lid', 'trainer', 'medewerker'])) {
            $stmt = $this->db->prepare("SELECT id, name, email, role, phone, birthdate, created_at FROM users WHERE role = :role ORDER BY name ASC");
            $stmt->execute(['role' => $roleFilter]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->query("SELECT id, name, email, role, phone, birthdate, created_at FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function updateUserAdmin(int $id, string $name, string $email, string $role, ?string $phone, ?string $birthdate): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = :name, email = :email, role = :role, phone = :phone, birthdate = :birthdate
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'phone' => $phone,
            'birthdate' => $birthdate ?: null
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
