<?php
// classes/Training.php - OOP Trainingsbeheer

require_once __DIR__ . '/../config/Database.php';

class Training {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(int $limit = 50, ?int $teamId = null): array {
        $sql = "
            SELECT tr.*, t.name as team_name, t.category
            FROM trainings tr
            JOIN teams t ON tr.team_id = t.id
        ";
        if ($teamId) {
            $sql .= " WHERE tr.team_id = :team_id ";
        }
        $sql .= " ORDER BY tr.training_date ASC LIMIT " . (int)$limit;

        if ($teamId) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['team_id' => $teamId]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }

    public function getUpcoming(int $limit = 10, ?int $userId = null): array {
        if ($userId) {
            $stmt = $this->db->prepare("
                SELECT tr.*, t.name as team_name
                FROM trainings tr
                JOIN teams t ON tr.team_id = t.id
                JOIN team_members tm ON t.id = tm.team_id AND tm.user_id = :user_id
                WHERE tr.training_date >= NOW()
                ORDER BY tr.training_date ASC
                LIMIT " . (int)$limit
            );
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("
            SELECT tr.*, t.name as team_name
            FROM trainings tr
            JOIN teams t ON tr.team_id = t.id
            WHERE tr.training_date >= NOW()
            ORDER BY tr.training_date ASC
            LIMIT " . (int)$limit
        );
        return $stmt->fetchAll();
    }

    public function create(int $teamId, string $trainingDate, string $fieldNumber, string $description = ''): int {
        $stmt = $this->db->prepare("
            INSERT INTO trainings (team_id, training_date, field_number, description)
            VALUES (:team_id, :training_date, :field_number, :description)
        ");
        $stmt->execute([
            'team_id' => $teamId,
            'training_date' => $trainingDate,
            'field_number' => $fieldNumber,
            'description' => $description
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM trainings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
