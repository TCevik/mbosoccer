<?php
// classes/Lineup.php
// Deze class regelt de vaste veldposities van spelers bij wedstrijden.

require_once __DIR__ . '/../config/Database.php';

class Lineup {
    private $db;

    // Database koppelen
    public function __construct() {
        $this->db = Database::connect();
    }

    // Haalt de opstelling op van een specifieke wedstrijd
    public function getForMatch(int $matchId) {
        $stmt = $this->db->prepare("
            SELECT l.*, u.name as player_name, tm.shirt_number 
            FROM lineups l
            JOIN users u ON l.user_id = u.id
            JOIN matches m ON l.match_id = m.id
            LEFT JOIN team_members tm ON m.team_id = tm.team_id AND l.user_id = tm.user_id
            WHERE l.match_id = :match_id
        ");
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetchAll();
    }

    // Slaat de veldpositie van een speler op voor een wedstrijd
    public function setPosition(int $matchId, int $userId, string $position) {
        $stmt = $this->db->prepare("
            INSERT INTO lineups (match_id, user_id, position_on_field)
            VALUES (:match_id, :user_id, :position)
            ON DUPLICATE KEY UPDATE position_on_field = :pos_up
        ");
        return $stmt->execute([
            'match_id' => $matchId,
            'user_id' => $userId,
            'position' => $position,
            'pos_up' => $position
        ]);
    }
}
