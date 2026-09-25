<?php
// classes/Team.php - OOP Teambeheer

require_once __DIR__ . '/../config/Database.php';

class Team {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT t.*, u.name as trainer_name,
                   (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) as player_count
            FROM teams t
            LEFT JOIN users u ON t.trainer_id = u.id
            ORDER BY t.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as trainer_name 
            FROM teams t 
            LEFT JOIN users u ON t.trainer_id = u.id 
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $team = $stmt->fetch();
        return $team ?: null;
    }

    public function create(string $name, string $category, ?int $trainerId): int {
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

    public function update(int $id, string $name, string $category, ?int $trainerId): bool {
        $stmt = $this->db->prepare("
            UPDATE teams 
            SET name = :name, category = :category, trainer_id = :trainer_id 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'trainer_id' => $trainerId ?: null
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM teams WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getMembers(int $teamId): array {
        $stmt = $this->db->prepare("
            SELECT tm.id as member_id, tm.position, tm.shirt_number, u.id as user_id, u.name, u.email, u.phone
            FROM team_members tm
            JOIN users u ON tm.user_id = u.id
            WHERE tm.team_id = :team_id
            ORDER BY tm.shirt_number ASC, u.name ASC
        ");
        $stmt->execute(['team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function addMember(int $teamId, int $userId, string $position = 'Speler', ?int $shirtNumber = null): bool {
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

    public function removeMember(int $teamId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id");
        return $stmt->execute([
            'team_id' => $teamId,
            'user_id' => $userId
        ]);
    }

    public function getTeamsForUser(int $userId): array {
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
