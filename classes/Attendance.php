<?php
// classes/Attendance.php
// Deze class regelt de aanwezigheid van spelers bij wedstrijden.

require_once __DIR__ . '/../config/Database.php';

class Attendance {
    private $db;

    // Databaseverbinding initialiseren
    public function __construct() {
        $this->db = Database::connect();
    }

    // Slaat de status op ('aanwezig' of 'afwezig') van een speler voor een specifieke wedstrijd.
    // ON DUPLICATE KEY UPDATE zorgt ervoor dat een bestaande keuze netjes wordt overschreven.
    public function setStatus(int $matchId, int $userId, string $status, $reason = null) {
        $stmt = $this->db->prepare("
            INSERT INTO match_attendance (match_id, user_id, status, reason)
            VALUES (:match_id, :user_id, :status, :reason)
            ON DUPLICATE KEY UPDATE status = :status_update, reason = :reason_update
        ");
        return $stmt->execute([
            'match_id' => $matchId,
            'user_id' => $userId,
            'status' => $status,
            'reason' => $reason,
            'status_update' => $status,
            'reason_update' => $reason
        ]);
    }

    // Haalt voor een wedstrijd alle spelers van het team op, plus hun aanwezigheidsstatus
    public function getAttendanceForMatch(int $matchId) {
        $stmt = $this->db->prepare("
            SELECT u.id as user_id, u.name, u.email, tm.position, tm.shirt_number,
                   IFNULL(ma.status, 'onbekend') as status,
                   ma.reason, ma.updated_at
            FROM matches m
            JOIN team_members tm ON m.team_id = tm.team_id
            JOIN users u ON tm.user_id = u.id
            LEFT JOIN match_attendance ma ON m.id = ma.match_id AND u.id = ma.user_id
            WHERE m.id = :match_id
            ORDER BY u.name ASC
        ");
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetchAll();
    }

    // Telt snel hoeveel spelers er aanwezig, afwezig of nog onbekend zijn
    public function getStatsForMatch(int $matchId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_players,
                SUM(CASE WHEN ma.status = 'aanwezig' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN ma.status = 'afwezig' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ma.status = 'onbekend' OR ma.status IS NULL THEN 1 ELSE 0 END) as unknown_count
            FROM matches m
            JOIN team_members tm ON m.team_id = tm.team_id
            LEFT JOIN match_attendance ma ON m.id = ma.match_id AND tm.user_id = ma.user_id
            WHERE m.id = :match_id
        ");
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetch() ?: ['present_count' => 0, 'absent_count' => 0, 'unknown_count' => 0];
    }
}
