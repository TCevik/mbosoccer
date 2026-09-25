<?php
// admin/attendance.php - Aanwezigheidsoverzicht per wedstrijd en team
$pageTitle = "Aanwezigheid Inzien";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/MatchGame.php';
require_once __DIR__ . '/../classes/Attendance.php';

requireRole(['medewerker', 'trainer']);

$matchModel = new MatchGame();
$attendanceModel = new Attendance();

$allMatches = $matchModel->getAll(20);
$selectedMatchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : (empty($allMatches) ? 0 : $allMatches[0]['id']);

$currentMatch = $selectedMatchId > 0 ? $matchModel->findById($selectedMatchId) : null;
$playerAttendance = $selectedMatchId > 0 ? $attendanceModel->getAttendanceForMatch($selectedMatchId) : [];
$stats = $selectedMatchId > 0 ? $attendanceModel->getStatsForMatch($selectedMatchId) : null;
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Aanwezigheid van Leden</h2>
        <p style="color: var(--text-muted);">Inzicht in wie wel en niet aanwezig is bij de wedstrijden.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="/admin/attendance.php" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <label for="match_id" style="font-weight: 700;">Selecteer Wedstrijd:</label>
        <select name="match_id" id="match_id" class="form-control" style="max-width: 450px;" onchange="this.form.submit()">
            <?php foreach ($allMatches as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $m['id'] === $selectedMatchId ? 'selected' : '' ?>>
                    <?= date('d-m-Y H:i', strtotime($m['match_date'])) ?> | <?= e($m['team_name']) ?> vs <?= e($m['opponent']) ?> (<?= e($m['location']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($currentMatch): ?>
    <!-- Wedstrijd Samenvatting & KPI's -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-light); color: var(--primary-dark);">✓</div>
            <div>
                <div class="stat-val"><?= (int)($stats['present_count'] ?? 0) ?></div>
                <div class="stat-lbl">Spelers Aanwezig</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-light); color: var(--danger);">✕</div>
            <div>
                <div class="stat-val"><?= (int)($stats['absent_count'] ?? 0) ?></div>
                <div class="stat-lbl">Spelers Afgemeld</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #b45309;">?</div>
            <div>
                <div class="stat-val"><?= (int)($stats['unknown_count'] ?? 0) ?></div>
                <div class="stat-lbl">Nog Geen Reactie</div>
            </div>
        </div>
    </div>

    <!-- Aanwezigheidslijst -->
    <div class="card">
        <div class="card-header">
            <h3>Spelerslijst: <?= e($currentMatch['team_name']) ?> vs <?= e($currentMatch['opponent']) ?></h3>
            <span class="badge <?= $currentMatch['location'] === 'Thuis' ? 'badge-success' : 'badge-warning' ?>">
                <?= e($currentMatch['location']) ?> - <?= e($currentMatch['field_number']) ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rugnr</th>
                        <th>Spelersnaam</th>
                        <th>Positie</th>
                        <th>Status</th>
                        <th>Reden / Notitie</th>
                        <th>Laatst Bijgewerkt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($playerAttendance)): ?>
                        <tr><td colspan="6" style="color: var(--text-muted);">Er zijn geen teamleden gevonden voor dit team.</td></tr>
                    <?php else: ?>
                        <?php foreach ($playerAttendance as $row): ?>
                            <tr>
                                <td><strong><?= $row['shirt_number'] ? '#' . $row['shirt_number'] : '-' ?></strong></td>
                                <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['email']) ?></small></td>
                                <td><?= e($row['position'] ?? 'Speler') ?></td>
                                <td>
                                    <?php if ($row['status'] === 'aanwezig'): ?>
                                        <span class="badge badge-success">✓ Wel aanwezig</span>
                                    <?php elseif ($row['status'] === 'afwezig'): ?>
                                        <span class="badge badge-danger">✕ Afwezig</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">? Onbekend</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($row['reason'] ?: '-') ?></td>
                                <td><?= $row['updated_at'] ? date('d-m-Y H:i', strtotime($row['updated_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <p>Geen wedstrijd geselecteerd of beschikbaar.</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
