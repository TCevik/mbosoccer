<?php
// schedule.php
// Toont de komende wedstrijden en trainingen.
// Ingelogde leden kunnen hier direct per wedstrijd op 'Wel' of 'Niet' aanwezig klikken.

$pageTitle = "Programma & Aanwezigheid";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/MatchGame.php';
require_once __DIR__ . '/classes/Training.php';

$matchModel = new MatchGame();
$trainingModel = new Training();

// Als de bezoeker is ingelogd, halen we ook zijn huidige aanwezigheidsstatus op
$userId = $user ? $user['id'] : null;
$matches = $matchModel->getUpcoming(20, $userId);
$trainings = $trainingModel->getUpcoming(20);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Wedstrijd- & Trainingsschema</h2>
        <p style="color: var(--text-muted);">Bekijk de planning en geef direct je beschikbaarheid door.</p>
    </div>

    <!-- Live zoekfilter via JavaScript -->
    <search style="max-width: 280px; width: 100%;">
        <input type="search" class="form-control" placeholder="🔍 Filter op team of club..." data-table-filter="#matchesTable">
    </search>
</div>

<!-- Tip voor niet-ingelogde bezoekers -->
<?php if (!$user): ?>
    <aside class="alert alert-danger" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
        <span>ℹ️</span>
        <p><strong>Tip:</strong> <a href="/login.php" style="text-decoration: underline;">Log in</a> om direct per wedstrijd aan te geven of je wel of niet aanwezig bent.</p>
    </aside>
<?php endif; ?>

<!-- 1. Wedstrijden tabel -->
<section class="card">
    <header class="card-header">
        <h3>⚽ Komende Wedstrijden</h3>
        <span class="badge badge-info"><?= count($matches) ?> gepland</span>
    </header>

    <?php if (empty($matches)): ?>
        <p style="color: var(--text-muted); padding: 1rem;">Geen aankomende wedstrijden gevonden.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="matchesTable">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>Team</th>
                        <th>Tegenstander</th>
                        <th>Locatie</th>
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
                            <td><?= e($m['location']) ?> (<?= e($m['field_number']) ?>)</td>
                            <td><small><?= e($m['notes'] ?: '-') ?></small></td>
                            
                            <!-- Aanwezigheidsknoppen: JavaScript (attendance.js) pakt de clicks hierop af -->
                            <?php if ($user): ?>
                                <td>
                                    <div class="attendance-actions" style="display: flex; align-items: center; gap: 0.5rem;">
                                        <button type="button" class="btn btn-sm btn-attendance <?= ($m['user_status'] ?? '') === 'aanwezig' ? 'btn-primary' : 'btn-outline' ?>"
                                                data-match-id="<?= $m['id'] ?>"
                                                data-status="aanwezig">
                                            ✓ Wel
                                        </button>
                                        <button type="button" class="btn btn-sm btn-attendance <?= ($m['user_status'] ?? '') === 'afwezig' ? 'btn-danger' : 'btn-outline' ?>"
                                                data-match-id="<?= $m['id'] ?>"
                                                data-status="afwezig">
                                            ✕ Niet
                                        </button>
                                        
                                        <!-- Huidige status badge -->
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
</section>

<!-- 2. Trainingsschema tabel -->
<section class="card" style="margin-top: 2rem;">
    <header class="card-header">
        <h3>🏃 Trainingsschema</h3>
        <span class="badge badge-info"><?= count($trainings) ?> trainingen</span>
    </header>

    <?php if (empty($trainings)): ?>
        <p style="color: var(--text-muted); padding: 1rem;">Geen trainingen gepland.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>Team</th>
                        <th>Veld</th>
                        <th>Omschrijving</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trainings as $tr): ?>
                        <tr>
                            <td><strong><?= date('d-m-Y H:i', strtotime($tr['training_date'])) ?></strong></td>
                            <td><span class="badge badge-info"><?= e($tr['team_name']) ?></span></td>
                            <td><?= e($tr['field_number']) ?></td>
                            <td><?= e($tr['description'] ?: 'Reguliere training') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
