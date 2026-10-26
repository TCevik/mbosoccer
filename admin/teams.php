<?php
// admin/teams.php
// Beheert de teams en de selecties van spelers.
// Bevat ook de visuele opstelling op het voetbalveld d.m.v. JavaScript.

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Team.php';
require_once __DIR__ . '/../classes/User.php';

// Alleen toegankelijk voor medewerkers en trainers
requireRole(['medewerker', 'trainer']);

$teamModel = new Team();
$userModel = new User();

// POST-acties verwerken (team toevoegen, speler toevoegen/verwijderen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Nieuw team aanmaken (alleen clubmedewerker)
    if ($action === 'create_team') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

        if ($name && $category) {
            $newId = $teamModel->create($name, $category, $trainerId);
            $_SESSION['flash_success'] = 'Team succesvol aangemaakt.';
            header("Location: /admin/teams.php?view_team=" . $newId);
            exit;
        }
    }

    // 2. Speler toevoegen aan de selectie van het gekozen team
    if ($action === 'add_player') {
        $teamId = (int)$_POST['team_id'];
        $userId = (int)$_POST['user_id'];
        $position = trim($_POST['position'] ?? 'Speler');
        $shirtNumber = !empty($_POST['shirt_number']) ? (int)$_POST['shirt_number'] : null;

        if ($teamId > 0 && $userId > 0) {
            $teamModel->addMember($teamId, $userId, $position, $shirtNumber);
            $_SESSION['flash_success'] = 'Speler toegevoegd aan selectie.';
            header("Location: /admin/teams.php?view_team=" . $teamId);
            exit;
        }
    }

    // 3. Speler uit teamselectie verwijderen
    if ($action === 'remove_player') {
        $teamId = (int)$_POST['team_id'];
        $userId = (int)$_POST['user_id'];

        if ($teamId > 0 && $userId > 0) {
            $teamModel->removeMember($teamId, $userId);
            $_SESSION['flash_success'] = 'Speler verwijderd uit team.';
            header("Location: /admin/teams.php?view_team=" . $teamId);
            exit;
        }
    }
}

// Data ophalen voor weergave
$allTeams = $teamModel->getAll();
$selectedTeamId = isset($_GET['view_team']) ? (int)$_GET['view_team'] : 0;

// Standaard het eerste team selecteren als er niets gekozen is
if ($selectedTeamId === 0 && !empty($allTeams)) {
    $selectedTeamId = $allTeams[0]['id'];
}

$currentTeam = $selectedTeamId > 0 ? $teamModel->findById($selectedTeamId) : null;
$teamMembers = $currentTeam ? $teamModel->getMembers($selectedTeamId) : [];
$allUsers = $userModel->getAll('lid');
$allTrainers = $userModel->getAll('trainer');

$pageTitle = "Teams & Opstellingen";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Teams & Opstellingen</h2>
        <p style="color: var(--text-muted);">Beheer teamselecties en bekijk de visuele opstelling op het veld.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; align-items: start;">
    <!-- Linker kolom: Teams lijst -->
    <aside class="card" style="padding: 1rem;">
        <h4 style="margin-bottom: 0.75rem;">🏆 Kies Team</h4>
        <div style="display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.5rem;">
            <?php foreach ($allTeams as $t): ?>
                <a href="/admin/teams.php?view_team=<?= $t['id'] ?>" 
                   class="btn <?= $t['id'] === $selectedTeamId ? 'btn-primary' : 'btn-outline' ?>"
                   style="text-align: left; justify-content: flex-start;">
                    <?= e($t['name']) ?> (<?= e($t['category']) ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Alleen clubmedewerkers mogen nieuwe teams toevoegen -->
        <?php if ($user['role'] === 'medewerker'): ?>
            <hr style="border: none; border-top: 1px solid var(--border); margin: 1rem 0;">
            <h5 style="margin-bottom: 0.75rem;">+ Nieuw Team</h5>
            <form method="POST" action="/admin/teams.php">
                <input type="hidden" name="action" value="create_team">
                <p class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Teamnaam (bijv. O17-1)" required>
                </p>
                <p class="form-group">
                    <input type="text" name="category" class="form-control" placeholder="Klasse (bijv. 2e Klasse)" required>
                </p>
                <p class="form-group">
                    <select name="trainer_id" class="form-control">
                        <option value="">-- Trainer kiezen --</option>
                        <?php foreach ($allTrainers as $tr): ?>
                            <option value="<?= $tr['id'] ?>"><?= e($tr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Team Opslaan</button>
            </form>
        <?php endif; ?>
    </aside>

    <!-- Rechter kolom: Selectie & Visuele Opstelling -->
    <main>
        <?php if ($currentTeam): ?>
            <article class="card" style="margin-bottom: 1.5rem;">
                <header class="card-header">
                    <div>
                        <h3><?= e($currentTeam['name']) ?> - <?= e($currentTeam['category']) ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.88rem;">
                            Trainer: <strong><?= e($currentTeam['trainer_name'] ?? 'Geen trainer toegewezen') ?></strong>
                        </p>
                    </div>
                </header>

                <!-- Speler toevoegen aan team -->
                <form method="POST" action="/admin/teams.php?view_team=<?= $selectedTeamId ?>" style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="add_player">
                    <input type="hidden" name="team_id" value="<?= $selectedTeamId ?>">

                    <select name="user_id" class="form-control" required style="flex: 2; min-width: 180px;">
                        <option value="">-- Kies speler om toe te voegen --</option>
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <select name="position" class="form-control" style="flex: 1; min-width: 140px;">
                        <option value="Keeper">Keeper</option>
                        <option value="Verdediger">Verdediger</option>
                        <option value="Middenvelder" selected>Middenvelder</option>
                        <option value="Aanvaller">Aanvaller</option>
                    </select>

                    <input type="number" name="shirt_number" class="form-control" placeholder="Rugnr" style="width: 85px;">

                    <button type="submit" class="btn btn-primary">+ Toevoegen</button>
                </form>

                <!-- Tabel met huidige selectie van het team -->
                <div class="table-responsive" style="margin-top: 1rem;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Spelersnaam</th>
                                <th>Positie</th>
                                <th>Telefoon</th>
                                <th>Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teamMembers)): ?>
                                <tr><td colspan="5" style="color: var(--text-muted);">Nog geen spelers in dit team.</td></tr>
                            <?php else: ?>
                                <?php foreach ($teamMembers as $m): ?>
                                    <tr>
                                        <td><strong><?= $m['shirt_number'] ? '#' . $m['shirt_number'] : '-' ?></strong></td>
                                        <td><strong><?= e($m['name']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= e($m['position']) ?></span></td>
                                        <td><?= e($m['phone'] ?: '-') ?></td>
                                        <td>
                                            <form method="POST" action="/admin/teams.php?view_team=<?= $selectedTeamId ?>" onsubmit="return confirm('Speler verwijderen uit team?');" style="display:inline;">
                                                <input type="hidden" name="action" value="remove_player">
                                                <input type="hidden" name="team_id" value="<?= $selectedTeamId ?>">
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
            </article>

            <!-- Visuele opstelling van het team op het veld -->
            <article class="card">
                <header class="card-header">
                    <div>
                        <h3>⚽ Visuele Opstelling (Veldweergave)</h3>
                        <p style="color: var(--text-muted); font-size: 0.88rem;">Spelers worden automatisch per linie op het veld geplaatst.</p>
                    </div>
                </header>

                <!-- Het groene veld -->
                <div class="soccer-pitch">
                    <!-- Aanvallerslijn bovenaan -->
                    <div class="pitch-line" id="line-aanval"></div>

                    <!-- Middenveld in het midden -->
                    <div class="pitch-line" id="line-midden"></div>

                    <!-- Verdedigingslinie onderin -->
                    <div class="pitch-line" id="line-verdediging"></div>

                    <!-- Doelman in het doel -->
                    <div class="pitch-line" id="line-keeper"></div>
                </div>

                <!-- Wisselspelers onder het veld -->
                <div style="margin-top: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: var(--radius-sm);">
                    <strong>Wisselspelers / Overig:</strong>
                    <div id="line-wissel" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;"></div>
                </div>
            </article>

            <!-- JavaScript dat de spelers op het veld tekent -->
            <script>
                // 1. Spelerslijst vanuit PHP omzetten naar een JavaScript array
                const currentPlayers = <?= json_encode($teamMembers) ?>;

                function renderPitchLineup() {
                    const lineAanval = document.getElementById('line-aanval');
                    const lineMidden = document.getElementById('line-midden');
                    const lineVerdediging = document.getElementById('line-verdediging');
                    const lineKeeper = document.getElementById('line-keeper');
                    const lineWissel = document.getElementById('line-wissel');

                    // Lijnen eerst leegmaken
                    lineAanval.innerHTML = '';
                    lineMidden.innerHTML = '';
                    lineVerdediging.innerHTML = '';
                    lineKeeper.innerHTML = '';
                    lineWissel.innerHTML = '';

                    // 2. Loop door alle spelers en plaats ze in de juiste linie
                    currentPlayers.forEach(player => {
                        const playerEl = document.createElement('div');
                        playerEl.className = 'pitch-player';
                        const rugnr = player.shirt_number ? player.shirt_number : '?';
                        playerEl.innerHTML = `<span class="shirt">${rugnr}</span> ${player.name}`;

                        const pos = (player.position || '').toLowerCase();

                        // Kijken op welke positie de speler staat
                        if (pos.includes('keeper') || pos.includes('doelman')) {
                            lineKeeper.appendChild(playerEl);
                        } else if (pos.includes('verdedig') || pos.includes('back')) {
                            lineVerdediging.appendChild(playerEl);
                        } else if (pos.includes('midden')) {
                            lineMidden.appendChild(playerEl);
                        } else if (pos.includes('aanval') || pos.includes('spits')) {
                            lineAanval.appendChild(playerEl);
                        } else {
                            lineWissel.appendChild(playerEl);
                        }
                    });

                    // 3. Tekstje tonen als een linie nog leeg is
                    if (!lineKeeper.children.length) lineKeeper.innerHTML = '<small style="color:rgba(255,255,255,0.7)">Geen keeper</small>';
                    if (!lineVerdediging.children.length) lineVerdediging.innerHTML = '<small style="color:rgba(255,255,255,0.7)">Geen verdediging</small>';
                    if (!lineMidden.children.length) lineMidden.innerHTML = '<small style="color:rgba(255,255,255,0.7)">Geen middenveld</small>';
                    if (!lineAanval.children.length) lineAanval.innerHTML = '<small style="color:rgba(255,255,255,0.7)">Geen aanval</small>';
                    if (!lineWissel.children.length) lineWissel.innerHTML = '<small style="color:var(--text-muted)">Geen wissels</small>';
                }

                // Aanroepen zodra de HTML klaar is met laden
                document.addEventListener('DOMContentLoaded', renderPitchLineup);
            </script>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
