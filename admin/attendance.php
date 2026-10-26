<?php
// admin/attendance.php
// Toont per wedstrijd wie van de teamleden aanwezig of afgemeld is.

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/MatchGame.php';
require_once __DIR__ . '/../classes/Attendance.php';

// Alleen medewerkers en trainers
requireRole(['medewerker', 'trainer']);

$matchModel = new MatchGame();
$attendanceModel = new Attendance();

// Haal recente wedstrijden op voor in het keuzemenu
$allMatches = $matchModel->getAll(20);
$selectedMatchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : (empty($allMatches) ? 0 : $allMatches[0]['id']);

// Haal de gekozen wedstrijd, de spelerslijst en de samenvattende statistieken op
$currentMatch = $selectedMatchId > 0 ? $matchModel->findById($selectedMatchId) : null;
$playerAttendance = $selectedMatchId > 0 ? $attendanceModel->getAttendanceForMatch($selectedMatchId) : [];
$stats = $selectedMatchId > 0 ? $attendanceModel->getStatsForMatch($selectedMatchId) : null;

$pageTitle = "Aanwezigheid Inzien";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Aanwezigheid van Leden</h2>
        <p style="color: var(--text-muted);">Inzicht in wie wel en niet aanwezig is bij de wedstrijden.</p>
    </div>
</div>

<!-- Dropdown om een wedstrijd te kiezen -->
<section class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
    <form method="GET" action="/admin/attendance.php" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <label for="match_id" style="font-weight: 700;">Kies Wedstrijd:</label>
        <select name="match_id" id="match_id" class="form-control" style="max-width: 450px;" onchange="this.form.submit()">
            <?php foreach ($allMatches as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $m['id'] === $selectedMatchId ? 'selected' : '' ?>>
                    <?= date('d-m-Y H:i', strtotime($m['match_date'])) ?> | <?= e($m['team_name']) ?> vs <?= e($m['opponent']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</section>

<?php if ($currentMatch): ?>
    <!-- Drie tellers: Aanwezig, Afwezig, Nog geen reactie -->
    <section class="stats-grid">
        <article class="stat-card">
            <span class="stat-icon" style="background: var(--primary-light); color: var(--primary-dark);">✓</span>
            <div>
                <strong class="stat-val"><?= (int)($stats['present_count'] ?? 0) ?></strong>
                <span class="stat-lbl">Aanwezig</span>
            </div>
        </article>
        <article class="stat-card">
            <span class="stat-icon" style="background: var(--danger-light); color: var(--danger);">✕</span>
            <div>
                <strong class="stat-val"><?= (int)($stats['absent_count'] ?? 0) ?></strong>
                <span class="stat-lbl">Afgemeld</span>
            </div>
        </article>
        <article class="stat-card">
            <span class="stat-icon" style="background: #fef3c7; color: #b45309;">?</span>
            <div>
                <strong class="stat-val"><?= (int)($stats['unknown_count'] ?? 0) ?></strong>
                <span class="stat-lbl">Nog geen reactie</span>
            </div>
        </article>
    </section>

    <!-- Spelerslijst met de status per speler -->
    <article class="card">
        <header class="card-header">
            <h3>Spelers: <?= e($currentMatch['team_name']) ?> vs <?= e($currentMatch['opponent']) ?></h3>
            <span class="badge badge-info"><?= e($currentMatch['location']) ?> (<?= e($currentMatch['field_number']) ?>)</span>
        </header>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Speler</th>
                        <th>Positie</th>
                        <th>Status</th>
                        <th>Reden</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($playerAttendance)): ?>
                        <tr><td colspan="5" style="color: var(--text-muted);">Geen teamleden gevonden voor dit team.</td></tr>
                    <?php else: ?>
                        <?php foreach ($playerAttendance as $row): ?>
                            <tr>
                                <td><strong><?= $row['shirt_number'] ? '#' . $row['shirt_number'] : '-' ?></strong></td>
                                <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['email']) ?></small></td>
                                <td><?= e($row['position'] ?? 'Speler') ?></td>
                                <td>
                                    <?php if ($row['status'] === 'aanwezig'): ?>
                                        <span class="badge badge-success">✓ Aanwezig</span>
                                    <?php elseif ($row['status'] === 'afwezig'): ?>
                                        <span class="badge badge-danger">✕ Afwezig</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">? Onbekend</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($row['reason'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
