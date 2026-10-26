<?php
// admin/dashboard.php - Beheerdersoverzicht

require_once __DIR__ . '/../includes/auth_check.php';
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

$pageTitle = "Beheerdersdashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Beheerdersdashboard</h2>
        <p style="color: var(--text-muted);">Welkom bij het beheerpaneel van VV MBO Soccer.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="/admin/matches.php?action=create" class="btn btn-primary">+ Wedstrijd Plannen</a>
        <?php if ($user['role'] === 'medewerker'): ?>
            <a href="/admin/members.php" class="btn btn-secondary">👥 Ledenbeheer</a>
        <?php endif; ?>
    </div>
</div>

<!-- Statistieken kaarten -->
<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-icon">👥</span>
        <div>
            <strong class="stat-val"><?= $totalUsers ?></strong>
            <span class="stat-lbl">Totaal Leden</span>
        </div>
    </article>
    <article class="stat-card">
        <span class="stat-icon">🏆</span>
        <div>
            <strong class="stat-val"><?= count($teams) ?></strong>
            <span class="stat-lbl">Actieve Teams</span>
        </div>
    </article>
    <article class="stat-card">
        <span class="stat-icon">⚽</span>
        <div>
            <strong class="stat-val"><?= $totalMatches ?></strong>
            <span class="stat-lbl">Totaal Wedstrijden</span>
        </div>
    </article>
</section>

<!-- Komende wedstrijden -->
<article class="card">
    <header class="card-header">
        <h3>Eerstvolgende Wedstrijden</h3>
        <a href="/admin/matches.php" class="btn btn-sm btn-outline">Beheer alles →</a>
    </header>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Wedstrijd</th>
                    <th>Aanwezigheid</th>
                    <th>Actie</th>
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
                                <strong><?= e($m['team_name']) ?></strong> vs <?= e($m['opponent']) ?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
