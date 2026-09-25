<?php
// admin/teams.php - Teambeheer en Opstellingen
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Team.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/MatchGame.php';
require_once __DIR__ . '/../classes/Lineup.php';

requireRole(['medewerker', 'trainer']);

$teamModel = new Team();
$userModel = new User();
$lineupModel = new Lineup();
$matchModel = new MatchGame();

$selectedTeamId = isset($_GET['view_team']) ? (int)$_GET['view_team'] : 0;
$allTeams = $teamModel->getAll();
if ($selectedTeamId === 0 && !empty($allTeams)) {
    $selectedTeamId = $allTeams[0]['id'];
}

$currentTeam = $teamModel->findById($selectedTeamId);
$teamMembers = $currentTeam ? $teamModel->getMembers($selectedTeamId) : [];
$allUsers = $userModel->getAll('lid');
$allTrainers = $userModel->getAll('trainer');

// POST acties: Speler toevoegen / verwijderen / nieuw team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'create_team') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

        if (!empty($name) && !empty($category)) {
            $newTeamId = $teamModel->create($name, $category, $trainerId);
            $_SESSION['flash_success'] = 'Team succesvol aangemaakt.';
            header("Location: /admin/teams.php?view_team=" . $newTeamId);
            exit;
        }
    }

    if ($postAction === 'add_player') {
        $userId = (int)$_POST['user_id'];
        $position = trim($_POST['position'] ?? 'Speler');
        $shirtNumber = !empty($_POST['shirt_number']) ? (int)$_POST['shirt_number'] : null;

        if ($userId > 0 && $selectedTeamId > 0) {
            $teamModel->addMember($selectedTeamId, $userId, $position, $shirtNumber);
            $_SESSION['flash_success'] = 'Speler toegevoegd aan het team.';
            header("Location: /admin/teams.php?view_team=" . $selectedTeamId);
            exit;
        }
    }

    if ($postAction === 'remove_player') {
        $userId = (int)$_POST['user_id'];
        if ($userId > 0 && $selectedTeamId > 0) {
            $teamModel->removeMember($selectedTeamId, $userId);
            $_SESSION['flash_success'] = 'Speler verwijderd uit het team.';
            header("Location: /admin/teams.php?view_team=" . $selectedTeamId);
            exit;
        }
    }
}

$pageTitle = "Teams & Opstellingen";
require_once __DIR__ . '/../includes/header.php';
?>

<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Teams & Opstellingen</h2>
        <p style="color: var(--text-muted);">Beheer teamindelingen, rugnummers en posities.</p>
    </div>
</header>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start;">
    <!-- Teams Sidebar -->
    <aside aria-label="Teams navigatie">
        <section class="card" style="padding: 1.25rem;">
            <header class="card-header" style="padding-bottom: 0.5rem;">
                <h4>🏆 Onze Teams</h4>
            </header>
            <nav style="display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.25rem;" aria-label="Team selectie">
                <?php foreach ($allTeams as $t): ?>
                    <a href="/admin/teams.php?view_team=<?= $t['id'] ?>" 
                       class="btn <?= $t['id'] === $selectedTeamId ? 'btn-primary' : 'btn-outline' ?>"
                       style="justify-content: flex-start; text-align: left;">
                        <?= e($t['name']) ?>
                        <small style="margin-left: auto; opacity: 0.8;"><?= (int)$t['player_count'] ?>p</small>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Nieuw team formulier (alleen medewerker) -->
            <?php if ($user['role'] === 'medewerker'): ?>
                <hr style="border: none; border-top: 1px solid var(--border); margin: 1rem 0;">
                <h5 style="margin-bottom: 0.75rem;">+ Nieuw Team Aanmaken</h5>
                <form method="POST" action="/admin/teams.php">
                    <input type="hidden" name="action" value="create_team">
                    <fieldset style="border: none; padding: 0; margin: 0;">
                        <p class="form-group">
                            <input type="text" name="name" class="form-control" placeholder="Teamnaam (bijv. O17-1)" required aria-label="Teamnaam">
                        </p>
                        <p class="form-group">
                            <input type="text" name="category" class="form-control" placeholder="Klasse (bijv. 2e Klasse)" required aria-label="Klasse">
                        </p>
                        <p class="form-group">
                            <select name="trainer_id" class="form-control" aria-label="Trainer selecteren">
                                <option value="">-- Kies Trainer --</option>
                                <?php foreach ($allTrainers as $tr): ?>
                                    <option value="<?= $tr['id'] ?>"><?= e($tr['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </fieldset>
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Team Toevoegen</button>
                </form>
            <?php endif; ?>
        </section>
    </aside>

    <!-- Team Details & Spelers -->
    <main>
        <?php if ($currentTeam): ?>
            <article class="card">
                <header class="card-header">
                    <div>
                        <h3><?= e($currentTeam['name']) ?> (<?= e($currentTeam['category']) ?>)</h3>
                        <p style="color: var(--text-muted); font-size: 0.88rem;">
                            Trainer / Coach: <strong><?= e($currentTeam['trainer_name'] ?? 'Geen trainer toegewezen') ?></strong>
                        </p>
                    </div>
                </header>

                <!-- Speler toevoegen aan team -->
                <section style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid var(--border);" aria-labelledby="addPlayerTitle">
                    <h4 id="addPlayerTitle" style="margin-bottom: 0.75rem;">Speler toevoegen aan selectie</h4>
                    <form method="POST" action="/admin/teams.php?view_team=<?= $selectedTeamId ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <input type="hidden" name="action" value="add_player">
                        
                        <div style="flex: 2; min-width: 200px;">
                            <select name="user_id" class="form-control" required aria-label="Selecteer speler">
                                <option value="">-- Selecteer Speler --</option>
                                <?php foreach ($allUsers as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="flex: 1; min-width: 130px;">
                            <input type="text" name="position" class="form-control" placeholder="Positie (bv. Spits)" aria-label="Positie">
                        </div>

                        <div style="width: 100px;">
                            <input type="number" name="shirt_number" class="form-control" placeholder="Rugnr" aria-label="Rugnummer">
                        </div>

                        <button type="submit" class="btn btn-primary">+ Toevoegen</button>
                    </form>
                </section>

                <!-- Selectie Tabel -->
                <section aria-labelledby="currentRosterTitle">
                    <h4 id="currentRosterTitle">Huidige Selectie (<?= count($teamMembers) ?> spelers)</h4>
                    <div class="table-responsive" style="margin-top: 0.75rem;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Rugnr</th>
                                    <th scope="col">Spelersnaam</th>
                                    <th scope="col">Positie</th>
                                    <th scope="col">Telefoon</th>
                                    <th scope="col">Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($teamMembers)): ?>
                                    <tr><td colspan="5" style="color: var(--text-muted);">Nog geen spelers toegevoegd aan dit team.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($teamMembers as $m): ?>
                                        <tr>
                                            <td><strong><?= $m['shirt_number'] ? '#' . $m['shirt_number'] : '-' ?></strong></td>
                                            <td><strong><?= e($m['name']) ?></strong></td>
                                            <td><span class="badge badge-info"><?= e($m['position'] ?? 'Speler') ?></span></td>
                                            <td><?= e($m['phone'] ?: '-') ?></td>
                                            <td>
                                                <form method="POST" action="/admin/teams.php?view_team=<?= $selectedTeamId ?>" onsubmit="return confirm('Speler uit team verwijderen?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="remove_player">
                                                    <input type="hidden" name="user_id" value="<?= $m['user_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </article>
        <?php else: ?>
            <p>Geen team geselecteerd.</p>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
