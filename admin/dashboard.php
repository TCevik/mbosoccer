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

<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Beheerdersoverzicht</h2>
        <p style="color: var(--text-muted);">Welkom op de beheerdersomgeving van MBO Soccer.</p>
    </div>
    <nav style="display: flex; gap: 0.75rem;" aria-label="Beheersacties">
        <a href="/admin/matches.php?action=create" class="btn btn-primary">+ Wedstrijd Plannen</a>
        <?php if ($user['role'] === 'medewerker'): ?>
            <a href="/admin/members.php" class="btn btn-secondary">👥 Ledenbeheer</a>
        <?php endif; ?>
    </nav>
</header>

<section class="stats-grid" aria-label="Dashboard statistieken">
    <article class="stat-card">
        <span class="stat-icon" aria-hidden="true">👥</span>
        <div>
            <strong class="stat-val"><?= $totalUsers ?></strong>
            <span class="stat-lbl">Totaal Geregistreerde Leden</span>
        </div>
    </article>
    <article class="stat-card">
        <span class="stat-icon" aria-hidden="true">🏆</span>
        <div>
            <strong class="stat-val"><?= count($teams) ?></strong>
            <span class="stat-lbl">Actieve Teams</span>
        </div>
    </article>
    <article class="stat-card">
        <span class="stat-icon" aria-hidden="true">⚽</span>
        <div>
            <strong class="stat-val"><?= $totalMatches ?></strong>
            <span class="stat-lbl">Geplande / Gespeelde Wedstrijden</span>
        </div>
    </article>
</section>

<section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;" aria-label="Wedstrijden en Teams Overzicht">
    <!-- Recente/Komende wedstrijden -->
    <article class="card">
        <header class="card-header">
            <h3>⚽ Komende Wedstrijden & Aanwezigheid</h3>
            <a href="/admin/matches.php" class="btn btn-sm btn-outline">Beheer alles →</a>
        </header>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Datum</th>
                        <th scope="col">Team vs Tegenstander</th>
                        <th scope="col">Aanwezigheid</th>
                        <th scope="col">Acties</th>
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
    </article>

    <!-- Teams Overzicht -->
    <article class="card">
        <header class="card-header">
            <h3>🏆 Teams & Selecties</h3>
            <a href="/admin/teams.php" class="btn btn-sm btn-outline">Beheer teams →</a>
        </header>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Team</th>
                        <th scope="col">Trainer</th>
                        <th scope="col">Spelers</th>
                        <th scope="col">Actie</th>
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
    </article>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
