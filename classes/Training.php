<?php
// classes/Training.php
// Deze class regelt het trainingsschema van de vereniging.

require_once __DIR__ . '/../config/Database.php';

class Training {
    private $db;

    // Database koppelen
    public function __construct() {
        $this->db = Database::connect();
    }

    // Haalt alle toekomstige trainingen op, gesorteerd op datum
    public function getUpcoming(int $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT tr.*, t.name as team_name 
            FROM trainings tr 
            JOIN teams t ON tr.team_id = t.id 
            WHERE tr.training_date >= NOW() 
            ORDER BY tr.training_date ASC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Voegt een nieuwe trainingssessie toe
    public function create(int $teamId, string $trainingDate, string $fieldNumber = 'Veld 2', string $description = '') {
        $stmt = $this->db->prepare("
            INSERT INTO trainings (team_id, training_date, field_number, description) 
            VALUES (:team_id, :training_date, :field_number, :description)
        ");
        return $stmt->execute([
            'team_id' => $teamId,
            'training_date' => $trainingDate,
            'field_number' => $fieldNumber,
            'description' => $description
        ]);
    }

    // Verwijdert een training
    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM trainings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
