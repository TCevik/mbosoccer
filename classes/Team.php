<?php
// classes/Team.php
// Deze class regelt alles rondom teams en de spelers die in een team zitten.

require_once __DIR__ . '/../config/Database.php';

class Team {
    private $db;

    // Databaseverbinding klaarzetten
    public function __construct() {
        $this->db = Database::connect();
    }

    // Haalt alle teams op inclusief de naam van de trainer
    public function getAll() {
        $stmt = $this->db->query("
            SELECT t.*, u.name as trainer_name 
            FROM teams t 
            LEFT JOIN users u ON t.trainer_id = u.id 
            ORDER BY t.name ASC
        ");
        return $stmt->fetchAll();
    }

    // Haalt één team op basis van het ID
    public function findById(int $id) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as trainer_name 
            FROM teams t 
            LEFT JOIN users u ON t.trainer_id = u.id 
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Voegt een nieuw team toe (naam, klasse en eventueel een trainer)
    public function create(string $name, string $category, $trainerId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO teams (name, category, trainer_id) 
            VALUES (:name, :category, :trainer_id)
        ");
        $stmt->execute([
            'name' => $name,
            'category' => $category,
            'trainer_id' => $trainerId ?: null
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Verwijdert een team
    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM teams WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Haalt alle spelers op die gekoppeld zijn aan dit specifieke team
    public function getMembers(int $teamId) {
        $stmt = $this->db->prepare("
            SELECT tm.*, u.name, u.email, u.phone 
            FROM team_members tm 
            JOIN users u ON tm.user_id = u.id 
            WHERE tm.team_id = :team_id 
            ORDER BY tm.shirt_number ASC, u.name ASC
        ");
        $stmt->execute(['team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    // Voegt een speler toe aan een teamselectie (of werkt zijn rugnummer/positie bij)
    public function addMember(int $teamId, int $userId, string $position = 'Speler', $shirtNumber = null) {
        $stmt = $this->db->prepare("
            INSERT INTO team_members (team_id, user_id, position, shirt_number)
            VALUES (:team_id, :user_id, :position, :shirt_number)
            ON DUPLICATE KEY UPDATE position = :position, shirt_number = :shirt_number
        ");
        return $stmt->execute([
            'team_id' => $teamId,
            'user_id' => $userId,
            'position' => $position,
            'shirt_number' => $shirtNumber ?: null
        ]);
    }

    // Verwijdert een speler uit het team
    public function removeMember(int $teamId, int $userId) {
        $stmt = $this->db->prepare("DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id");
        return $stmt->execute([
            'team_id' => $teamId,
            'user_id' => $userId
        ]);
    }

    // Haalt alle teams op waar een specifieke speler lid van is (voor op het profiel)
    public function getTeamsForUser(int $userId) {
        $stmt = $this->db->prepare("
            SELECT t.*, tm.position, tm.shirt_number 
            FROM teams t 
            JOIN team_members tm ON t.id = tm.team_id 
            WHERE tm.user_id = :user_id 
            ORDER BY t.name ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
