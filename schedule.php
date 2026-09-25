<?php
// schedule.php - Trainingsschema en Wedstrijdoverzicht + Aanwezigheid doorgeven
$pageTitle = "Programma & Aanwezigheid";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/MatchGame.php';
require_once __DIR__ . '/classes/Training.php';

$matchModel = new MatchGame();
$trainingModel = new Training();

$userId = $user ? $user['id'] : null;
$matches = $matchModel->getUpcoming(20, $userId);
$trainings = $trainingModel->getUpcoming(20, $userId);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Wedstrijd- & Trainingsschema</h2>
        <p style="color: var(--text-muted);">Bekijk de geplande wedstrijden en trainingen. Geef direct je beschikbaarheid door!</p>
    </div>

    <div style="max-width: 280px; width: 100%;">
        <input type="text" class="form-control" placeholder="🔍 Filter op team of tegenstander..." data-table-filter="#matchesTable">
    </div>
</div>

<?php if (!$user): ?>
    <div class="alert alert-danger" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
        <span>ℹ️</span>
        <p><strong>Tip:</strong> <a href="/login.php" style="text-decoration: underline;">Log in</a> om direct per wedstrijd aan te geven of je wel of niet aanwezig bent.</p>
    </div>
<?php endif; ?>

<!-- Wedstrijden -->
<div class="card">
    <div class="card-header">
        <h3>⚽ Komende Wedstrijden</h3>
        <span class="badge badge-info"><?= count($matches) ?> gepland</span>
    </div>

    <?php if (empty($matches)): ?>
        <p style="color: var(--text-muted); padding: 1rem;">Er zijn momenteel geen aankomende wedstrijden gevonden.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="matchesTable">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>Team</th>
                        <th>Tegenstander</th>
                        <th>Locatie / Veld</th>
                        <th>Opmerking</th>
                        <?php if ($user): ?>
                            <th style="min-width: 220px;">Mijn Aanwezigheid</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $m): ?>
                        <tr>
                            <td><strong><?= date('d-m-Y H:i', strtotime($m['match_date'])) ?></strong></td>
                            <td><span class="badge badge-info"><?= e($m['team_name']) ?></span></td>
                            <td><strong><?= e($m['opponent']) ?></strong></td>
                            <td>
                                <span class="badge <?= $m['location'] === 'Thuis' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= e($m['location']) ?>
                                </span>
                                <small style="display: block; color: var(--text-muted);"><?= e($m['field_number']) ?></small>
                            </td>
                            <td><small><?= e($m['notes'] ?: '-') ?></small></td>
                            <?php if ($user): ?>
                                <td>
                                    <div class="attendance-actions" style="display: flex; align-items: center; gap: 0.5rem;">
                                        <button class="btn btn-sm btn-attendance <?= ($m['user_status'] ?? '') === 'aanwezig' ? 'btn-primary' : 'btn-outline' ?>"
                                                data-match-id="<?= $m['id'] ?>"
                                                data-status="aanwezig">
                                            ✓ Wel
                                        </button>
                                        <button class="btn btn-sm btn-attendance <?= ($m['user_status'] ?? '') === 'afwezig' ? 'btn-danger' : 'btn-outline' ?>"
                                                data-match-id="<?= $m['id'] ?>"
                                                data-status="afwezig">
                                            ✕ Niet
                                        </button>
                                        
                                        <?php if (!empty($m['user_status']) && $m['user_status'] !== 'onbekend'): ?>
                                            <span class="status-indicator badge <?= $m['user_status'] === 'aanwezig' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $m['user_status'] === 'aanwezig' ? '✓ Aanwezig' : '✕ Afwezig' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-indicator badge badge-warning">? Nog opgeven</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Trainingsschema -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3>🏃 Trainingsschema</h3>
        <span class="badge badge-info"><?= count($trainings) ?> trainingen</span>
    </div>

    <?php if (empty($trainings)): ?>
        <p style="color: var(--text-muted); padding: 1rem;">Geen komende trainingen gepland.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>Team</th>
                        <th>Veld</th>
                        <th>Focus / Omschrijving</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trainings as $tr): ?>
                        <tr>
                            <td><strong><?= date('d-m-Y H:i', strtotime($tr['training_date'])) ?></strong></td>
                            <td><span class="badge badge-info"><?= e($tr['team_name']) ?></span></td>
                            <td><?= e($tr['field_number']) ?></td>
                            <td><?= e($tr['description'] ?: 'Reguliere teamtraining') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
