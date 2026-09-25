<?php
// classes/Lineup.php - OOP Opstellingenbeheer

require_once __DIR__ . '/../config/Database.php';

class Lineup {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getForMatch(int $matchId): array {
        $stmt = $this->db->prepare("
            SELECT l.*, u.name as player_name, tm.position as natural_position, tm.shirt_number
            FROM lineups l
            JOIN users u ON l.user_id = u.id
            JOIN matches m ON l.match_id = m.id
            LEFT JOIN team_members tm ON m.team_id = tm.team_id AND l.user_id = tm.user_id
            WHERE l.match_id = :match_id
            ORDER BY l.is_starter DESC, l.position_on_field ASC
        ");
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetchAll();
    }

    public function setPlayerPosition(int $matchId, int $userId, string $positionOnField, bool $isStarter = true): bool {
        $stmt = $this->db->prepare("
            INSERT INTO lineups (match_id, user_id, position_on_field, is_starter)
            VALUES (:match_id, :user_id, :position_on_field, :is_starter)
            ON DUPLICATE KEY UPDATE position_on_field = :pos_up, is_starter = :starter_up
        ");
        return $stmt->execute([
            'match_id' => $matchId,
            'user_id' => $userId,
            'position_on_field' => $positionOnField,
            'is_starter' => $isStarter ? 1 : 0,
            'pos_up' => $positionOnField,
            'starter_up' => $isStarter ? 1 : 0
        ]);
    }

    public function removePlayer(int $matchId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM lineups WHERE match_id = :match_id AND user_id = :user_id");
        return $stmt->execute([
            'match_id' => $matchId,
            'user_id' => $userId
        ]);
    }
}
