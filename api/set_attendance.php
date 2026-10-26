<?php
// api/set_attendance.php
// Dit endpoint ontvangt verzoeken vanuit JavaScript (Fetch API)
// om de aanwezigheid van een ingelogde speler direct op te slaan zonder pagina-herlaad.

// Geef aan de browser aan dat we een JSON-antwoord terugsturen
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Attendance.php';

// Stap 1: Controleer of iemand überhaupt is ingelogd
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Je bent niet ingelogd']);
    exit;
}

// Stap 2: Parameters ophalen uit het POST-verzoek
$matchId = (int)($_POST['match_id'] ?? 0);
$status = $_POST['status'] ?? '';
$userId = $_SESSION['user_id'];

// Stap 3: Valideren en opslaan in de database via de Attendance class
if ($matchId > 0 && in_array($status, ['aanwezig', 'afwezig'])) {
    $attendance = new Attendance();
    $attendance->setStatus($matchId, $userId, $status);

    // Succesmelding teruggeven aan JavaScript
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    // Foutmelding als gegevens ontbreken
    echo json_encode(['success' => false, 'error' => 'Ongeldige aanvraag']);
}
