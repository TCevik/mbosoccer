<?php
// classes/User.php
// Deze class beheert alle gebruikers (leden, trainers, medewerkers) in de database.

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    // Bij het aanmaken van new User() halen we direct de databaseverbinding op
    public function __construct() {
        $this->db = Database::connect();
    }

    // Zoekt een specifieke gebruiker op basis van ID
    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Zoekt een gebruiker op basis van e-mailadres
    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // Controleert inloggegevens: zoekt gebruiker op en verifieert het gehashte wachtwoord
    public function authenticate(string $email, string $password) {
        $user = $this->findByEmail($email);
        
        // password_verify controleert of het ingetypte wachtwoord past bij de hash in de database
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Haal de hash weg voordat we de gegevens bewaren
            return $user;
        }
        return null;
    }

    // Registreert een nieuw lid en hasht het wachtwoord veilig met bcrypt
    public function register(string $name, string $email, string $password, string $role = 'lid', $phone = null, $birthdate = null) {
        // Wachtwoord beveiligen tegen diefstal
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Gegevens veilig invoegen met prepared statement
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, phone, birthdate) 
            VALUES (:name, :email, :password, :role, :phone, :birthdate)
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'phone' => $phone ?: null,
            'birthdate' => $birthdate ?: null
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Laat een ingelogde gebruiker zijn eigen gegevens of optioneel wachtwoord aanpassen
    public function updateProfile(int $id, string $name, $phone = null, $birthdate = null, $newPassword = null) {
        // Als de gebruiker een nieuw wachtwoord heeft ingevuld, hashen we dat ook
        if ($newPassword) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = :name, phone = :phone, birthdate = :birthdate, password = :password
                WHERE id = :id
            ");
            return $stmt->execute([
                'id' => $id,
                'name' => $name,
                'phone' => $phone ?: null,
                'birthdate' => $birthdate ?: null,
                'password' => $hashed
            ]);
        }

        // Zonder wachtwoordwijziging
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = :name, phone = :phone, birthdate = :birthdate
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'phone' => $phone ?: null,
            'birthdate' => $birthdate ?: null
        ]);
    }

    // Haalt alle gebruikers op, optioneel gefilterd op rol (bijv. alleen 'lid' of 'trainer')
    public function getAll(string $roleFilter = '') {
        if ($roleFilter !== '') {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE role = :role ORDER BY name ASC");
            $stmt->execute(['role' => $roleFilter]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("SELECT * FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    // Clubmedewerker kan gegevens en rol van een lid wijzigen
    public function updateUserAdmin(int $id, string $name, string $email, string $role, $phone = null, $birthdate = null) {
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
            'phone' => $phone ?: null,
            'birthdate' => $birthdate ?: null
        ]);
    }

    // Verwijdert een gebruiker uit de database
    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Telt het totaal aantal gebruikers voor op het dashboard
    public function getCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
