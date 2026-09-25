<?php
// index.php - Homepage Visual Design & Cluboverzicht
$pageTitle = "Welkom bij VV MBO Soccer";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/MatchGame.php';
require_once __DIR__ . '/classes/Team.php';
require_once __DIR__ . '/classes/User.php';

$matchGame = new MatchGame();
$teamModel = new Team();
$userModel = new User();

$upcomingMatches = $matchGame->getUpcoming(3);
$totalMembers = $userModel->getCount();
$totalMatches = $matchGame->getCount();
$allTeams = $teamModel->getAll();
?>

<section class="hero">
    <div class="hero-content">
        <span class="badge badge-warning" style="margin-bottom: 0.75rem;">⚽ Seizoen 2026/2027</span>
        <h1>Verenigd door Voetbal, Gedreven door Passie</h1>
        <p>Welkom bij het officiële clubportaal van MBO Soccer. Bekijk eenvoudig je trainingsschema's, geef direct je wedstrijdaanwezigheid door en blijf op de hoogte van het laatste verenigingsnieuws.</p>
        <div class="hero-actions">
            <?php if (!$user): ?>
                <a href="/register.php" class="btn btn-primary">Word nu lid</a>
                <a href="/login.php" class="btn btn-outline" style="color: #fff; border-color: rgba(255,255,255,0.4);">Direct Inloggen</a>
            <?php else: ?>
                <a href="/schedule.php" class="btn btn-primary">Bekijk mijn programma & aanwezigheid</a>
                <a href="/profile.php" class="btn btn-outline" style="color: #fff; border-color: rgba(255,255,255,0.4);">Mijn Account</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
            <div class="stat-val"><?= $totalMembers ?></div>
            <div class="stat-lbl">Actieve Clubleden</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div>
            <div class="stat-val"><?= count($allTeams) ?></div>
            <div class="stat-lbl">Competitieteams</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div>
            <div class="stat-val"><?= $totalMatches ?></div>
            <div class="stat-lbl">Wedstrijden Gepland</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div>
            <div class="stat-val">100%</div>
            <div class="stat-lbl">Digitaal & Veilig (OOP)</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
    <!-- Eerstvolgende wedstrijden -->
    <div class="card">
        <div class="card-header">
            <h3>⚽ Eerstvolgende Wedstrijden</h3>
            <a href="/schedule.php" class="btn btn-sm btn-outline">Alles bekijken →</a>
        </div>
        <?php if (empty($upcomingMatches)): ?>
            <p style="color: var(--text-muted);">Er staan momenteel geen geplande wedstrijden op het programma.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum & Tijd</th>
                            <th>Team</th>
                            <th>Tegenstander</th>
                            <th>Locatie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMatches as $match): ?>
                            <tr>
                                <td><strong><?= date('d-m-Y H:i', strtotime($match['match_date'])) ?></strong></td>
                                <td><?= e($match['team_name']) ?></td>
                                <td><?= e($match['opponent']) ?></td>
                                <td>
                                    <span class="badge <?= $match['location'] === 'Thuis' ? 'badge-success' : 'badge-info' ?>">
                                        <?= e($match['location']) ?> (<?= e($match['field_number']) ?>)
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onze Teams -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Onze Teams</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Klasse</th>
                        <th>Trainer</th>
                        <th>Spelers</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allTeams as $team): ?>
                        <tr>
                            <td><strong><?= e($team['name']) ?></strong></td>
                            <td><span class="badge badge-info"><?= e($team['category']) ?></span></td>
                            <td><?= e($team['trainer_name'] ?? 'Nog geen trainer') ?></td>
                            <td><?= (int)$team['player_count'] ?> spelers</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
