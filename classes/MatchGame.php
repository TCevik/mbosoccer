<?php
// classes/MatchGame.php - OOP Wedstrijdbeheer (CRUD)

require_once __DIR__ . '/../config/Database.php';

class MatchGame {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(int $limit = 50, ?int $teamId = null): array {
        $sql = "
            SELECT m.*, t.name as team_name, t.category,
                   (SELECT COUNT(*) FROM match_attendance ma WHERE ma.match_id = m.id AND ma.status = 'aanwezig') as present_count,
                   (SELECT COUNT(*) FROM match_attendance ma WHERE ma.match_id = m.id AND ma.status = 'afwezig') as absent_count
            FROM matches m
            JOIN teams t ON m.team_id = t.id
        ";
        if ($teamId) {
            $sql .= " WHERE m.team_id = :team_id ";
        }
        $sql .= " ORDER BY m.match_date ASC LIMIT " . (int)$limit;

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
                SELECT m.*, t.name as team_name,
                       COALESCE(ma.status, 'onbekend') as user_status,
                       ma.reason as user_reason
                FROM matches m
                JOIN teams t ON m.team_id = t.id
                JOIN team_members tm ON t.id = tm.team_id AND tm.user_id = :user_id
                LEFT JOIN match_attendance ma ON m.id = ma.match_id AND ma.user_id = :user_id2
                WHERE m.match_date >= NOW()
                ORDER BY m.match_date ASC
                LIMIT " . (int)$limit
            );
            $stmt->execute(['user_id' => $userId, 'user_id2' => $userId]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("
            SELECT m.*, t.name as team_name
            FROM matches m
            JOIN teams t ON m.team_id = t.id
            WHERE m.match_date >= NOW()
            ORDER BY m.match_date ASC
            LIMIT " . (int)$limit
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, t.name as team_name
            FROM matches m
            JOIN teams t ON m.team_id = t.id
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function create(int $teamId, string $opponent, string $matchDate, string $location, string $fieldNumber, string $notes = ''): int {
        $stmt = $this->db->prepare("
            INSERT INTO matches (team_id, opponent, match_date, location, field_number, notes)
            VALUES (:team_id, :opponent, :match_date, :location, :field_number, :notes)
        ");
        $stmt->execute([
            'team_id' => $teamId,
            'opponent' => $opponent,
            'match_date' => $matchDate,
            'location' => in_array($location, ['Thuis', 'Uit']) ? $location : 'Thuis',
            'field_number' => $fieldNumber,
            'notes' => $notes
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $teamId, string $opponent, string $matchDate, string $location, string $fieldNumber, string $status, string $notes = ''): bool {
        $stmt = $this->db->prepare("
            UPDATE matches 
            SET team_id = :team_id, opponent = :opponent, match_date = :match_date, 
                location = :location, field_number = :field_number, status = :status, notes = :notes
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'team_id' => $teamId,
            'opponent' => $opponent,
            'match_date' => $matchDate,
            'location' => in_array($location, ['Thuis', 'Uit']) ? $location : 'Thuis',
            'field_number' => $fieldNumber,
            'status' => in_array($status, ['Gepland', 'Gespeeld', 'Afgelast']) ? $status : 'Gepland',
            'notes' => $notes
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM matches WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    }
}
