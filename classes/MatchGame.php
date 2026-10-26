<?php
// classes/MatchGame.php
// Deze class regelt de wedstrijden: ophalen, toevoegen, aanpassen en verwijderen.

require_once __DIR__ . '/../config/Database.php';

class MatchGame {
    private $db;

    // Databaseverbinding starten
    public function __construct() {
        $this->db = Database::connect();
    }

    // Haalt alle wedstrijden op en koppelt de naam van het team
    public function getAll(int $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT m.*, t.name as team_name 
            FROM matches m 
            JOIN teams t ON m.team_id = t.id 
            ORDER BY m.match_date ASC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Haalt alleen toekomstige wedstrijden op. Als $userId is meegegeven,
    // halen we ook op of deze speler al 'aanwezig' of 'afwezig' heeft doorgegeven.
    public function getUpcoming(int $limit = 20, $userId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("
                SELECT m.*, t.name as team_name, ma.status as user_status
                FROM matches m
                JOIN teams t ON m.team_id = t.id
                LEFT JOIN match_attendance ma ON m.id = ma.match_id AND ma.user_id = :user_id
                WHERE m.match_date >= NOW()
                ORDER BY m.match_date ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        // Als er niemand is ingelogd
        $stmt = $this->db->prepare("
            SELECT m.*, t.name as team_name 
            FROM matches m 
            JOIN teams t ON m.team_id = t.id 
            WHERE m.match_date >= NOW() 
            ORDER BY m.match_date ASC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Zoekt één specifieke wedstrijd op ID
    public function findById(int $id) {
        $stmt = $this->db->prepare("
            SELECT m.*, t.name as team_name 
            FROM matches m 
            JOIN teams t ON m.team_id = t.id 
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Voegt een nieuwe wedstrijd toe aan de planning
    public function create(int $teamId, string $opponent, string $matchDate, string $location, string $fieldNumber = 'Veld 1', string $notes = '') {
        $stmt = $this->db->prepare("
            INSERT INTO matches (team_id, opponent, match_date, location, field_number, notes) 
            VALUES (:team_id, :opponent, :match_date, :location, :field_number, :notes)
        ");
        $stmt->execute([
            'team_id' => $teamId,
            'opponent' => $opponent,
            'match_date' => $matchDate,
            'location' => $location,
            'field_number' => $fieldNumber,
            'notes' => $notes
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Werkt een bestaande wedstrijd bij (bijvoorbeeld gewijzigde tijd of uitslag)
    public function update(int $id, int $teamId, string $opponent, string $matchDate, string $location, string $fieldNumber, string $status, string $notes = '') {
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
            'location' => $location,
            'field_number' => $fieldNumber,
            'status' => $status,
            'notes' => $notes
        ]);
    }

    // Verwijdert een wedstrijd uit het schema
    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM matches WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Telt het aantal wedstrijden voor het beheerdersdashboard
    public function getCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    }
}
