<?php
// admin/dashboard.php - Beheerdersdashboard
$pageTitle = "Beheerdersdashboard";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Team.php';
require_once __DIR__ . '/../classes/MatchGame.php';

requireRole(['medewerker', 'trainer']);

$userModel = new User();
$teamModel = new Team();
$matchModel = new MatchGame();

$totalUsers = $userModel->getCount();
$totalMatches = $matchModel->getCount();
$teams = $teamModel->getAll();
$upcomingMatches = $matchModel->getUpcoming(5);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Beheerdersoverzicht</h2>
        <p style="color: var(--text-muted);">Welkom op de beheerdersomgeving van MBO Soccer.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="/admin/matches.php?action=create" class="btn btn-primary">+ Wedstrijd Plannen</a>
        <?php if ($user['role'] === 'medewerker'): ?>
            <a href="/admin/members.php" class="btn btn-secondary">👥 Ledenbeheer</a>
        <?php endif; ?>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
            <div class="stat-val"><?= $totalUsers ?></div>
            <div class="stat-lbl">Totaal Geregistreerde Leden</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div>
            <div class="stat-val"><?= count($teams) ?></div>
            <div class="stat-lbl">Actieve Teams</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚽</div>
        <div>
            <div class="stat-val"><?= $totalMatches ?></div>
            <div class="stat-lbl">Geplande / Gespeelde Wedstrijden</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;">
    <!-- Recente/Komende wedstrijden -->
    <div class="card">
        <div class="card-header">
            <h3>⚽ Komende Wedstrijden & Aanwezigheid</h3>
            <a href="/admin/matches.php" class="btn btn-sm btn-outline">Beheer alles →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Team vs Tegenstander</th>
                        <th>Aanwezigheid</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upcomingMatches)): ?>
                        <tr><td colspan="4" style="color: var(--text-muted);">Geen wedstrijden gepland.</td></tr>
                    <?php else: ?>
                        <?php foreach ($upcomingMatches as $m): ?>
                            <tr>
                                <td><?= date('d-m H:i', strtotime($m['match_date'])) ?></td>
                                <td>
                                    <strong><?= e($m['team_name']) ?></strong><br>
                                    <small>vs <?= e($m['opponent']) ?></small>
                                </td>
                                <td>
                                    <a href="/admin/attendance.php?match_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">
                                        Bekijk Spelers
                                    </a>
                                </td>
                                <td>
                                    <a href="/admin/matches.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">Bewerken</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teams Overzicht -->
    <div class="card">
        <div class="card-header">
            <h3>🏆 Teams & Selecties</h3>
            <a href="/admin/teams.php" class="btn btn-sm btn-outline">Beheer teams →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Trainer</th>
                        <th>Spelers</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $t): ?>
                        <tr>
                            <td><strong><?= e($t['name']) ?></strong><br><small><?= e($t['category']) ?></small></td>
                            <td><?= e($t['trainer_name'] ?? 'Niet toegewezen') ?></td>
                            <td><span class="badge badge-info"><?= (int)$t['player_count'] ?> spelers</span></td>
                            <td>
                                <a href="/admin/teams.php?view_team=<?= $t['id'] ?>" class="btn btn-sm btn-outline">Opstelling</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
