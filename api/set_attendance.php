<?php
// api/set_attendance.php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Attendance.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Niet ingelogd']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Ongeldige request methode']);
    exit;
}

$matchId = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
$status = $_POST['status'] ?? '';
$reason = $_POST['reason'] ?? null;
$userId = $_SESSION['user_id'];

if ($matchId <= 0 || !in_array($status, ['aanwezig', 'afwezig', 'onbekend'])) {
    echo json_encode(['success' => false, 'error' => 'Ongeldige parameters']);
    exit;
}

$attendance = new Attendance();
$ok = $attendance->setStatus($matchId, $userId, $status, $reason);

if ($ok) {
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    echo json_encode(['success' => false, 'error' => 'Kon aanwezigheid niet opslaan']);
}
