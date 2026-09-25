<?php
// admin/matches.php - Wedstrijden CRUD (toevoegen, wijzigen, verwijderen)
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/MatchGame.php';
require_once __DIR__ . '/../classes/Team.php';

requireRole(['medewerker', 'trainer']);

$matchModel = new MatchGame();
$teamModel = new Team();
$teams = $teamModel->getAll();

$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$matchData = null;

if ($editId > 0) {
    $matchData = $matchModel->findById($editId);
}

// POST acties: Opslaan / Bijwerken / Verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['form_action'] ?? '';

    if ($postAction === 'delete') {
        $delId = (int)$_POST['id'];
        $matchModel->delete($delId);
        $_SESSION['flash_success'] = 'Wedstrijd is succesvol verwijderd.';
        header("Location: /admin/matches.php");
        exit;
    }

    if ($postAction === 'save') {
        $teamId = (int)$_POST['team_id'];
        $opponent = trim($_POST['opponent'] ?? '');
        $matchDate = $_POST['match_date'] ?? '';
        $location = $_POST['location'] ?? 'Thuis';
        $fieldNumber = trim($_POST['field_number'] ?? 'Veld 1');
        $status = $_POST['status'] ?? 'Gepland';
        $notes = trim($_POST['notes'] ?? '');

        if ($teamId <= 0 || empty($opponent) || empty($matchDate)) {
            $_SESSION['flash_error'] = 'Vul alle verplichte velden in.';
        } else {
            if ($editId > 0) {
                $matchModel->update($editId, $teamId, $opponent, $matchDate, $location, $fieldNumber, $status, $notes);
                $_SESSION['flash_success'] = 'Wedstrijd succesvol bijgewerkt.';
            } else {
                $matchModel->create($teamId, $opponent, $matchDate, $location, $fieldNumber, $notes);
                $_SESSION['flash_success'] = 'Nieuwe wedstrijd ingepland.';
            }
            header("Location: /admin/matches.php");
            exit;
        }
    }
}

$allMatches = $matchModel->getAll();
$pageTitle = "Wedstrijdbeheer";
require_once __DIR__ . '/../includes/header.php';
?>

<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Wedstrijdbeheer</h2>
        <p style="color: var(--text-muted);">Voeg wedstrijden toe, wijzig tijden/velden of verwijder afgelaste duels.</p>
    </div>
    <nav aria-label="Wedstrijd acties">
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <a href="/admin/matches.php" class="btn btn-outline">← Terug naar overzicht</a>
        <?php else: ?>
            <a href="/admin/matches.php?action=create" class="btn btn-primary">+ Nieuwe Wedstrijd Toevoegen</a>
        <?php endif; ?>
    </nav>
</header>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <!-- Formulier Nieuw / Bewerken -->
    <article class="card" style="max-width: 700px; margin: 0 auto;">
        <header class="card-header">
            <h3><?= $action === 'edit' ? 'Wedstrijd Wijzigen' : 'Nieuwe Wedstrijd Inplannen' ?></h3>
        </header>

        <form method="POST" action="/admin/matches.php<?= $editId ? '?id=' . $editId : '' ?>">
            <input type="hidden" name="form_action" value="save">

            <fieldset style="border: none; padding: 0; margin: 0;">
                <div class="form-grid">
                    <p class="form-group">
                        <label for="team_id">Eigen Team *</label>
                        <select name="team_id" id="team_id" class="form-control" required>
                            <option value="">-- Selecteer Team --</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($matchData && $matchData['team_id'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= e($t['name']) ?> (<?= e($t['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p class="form-group">
                        <label for="opponent">Tegenstander *</label>
                        <input type="text" id="opponent" name="opponent" class="form-control" required 
                               value="<?= e($matchData['opponent'] ?? '') ?>" placeholder="bijv. FC Amsterdam">
                    </p>
                </div>

                <div class="form-grid">
                    <p class="form-group">
                        <label for="match_date">Datum en Tijd *</label>
                        <input type="datetime-local" id="match_date" name="match_date" class="form-control" required
                               value="<?= $matchData ? date('Y-m-d\TH:i', strtotime($matchData['match_date'])) : '' ?>">
                    </p>

                    <p class="form-group">
                        <label for="location">Locatie (Thuis/Uit) *</label>
                        <select name="location" id="location" class="form-control" required>
                            <option value="Thuis" <?= ($matchData && $matchData['location'] === 'Thuis') ? 'selected' : '' ?>>Thuis</option>
                            <option value="Uit" <?= ($matchData && $matchData['location'] === 'Uit') ? 'selected' : '' ?>>Uit</option>
                        </select>
                    </p>
                </div>

                <div class="form-grid">
                    <p class="form-group">
                        <label for="field_number">Veldnummer / Accommodatie</label>
                        <input type="text" id="field_number" name="field_number" class="form-control" 
                               value="<?= e($matchData['field_number'] ?? 'Veld 1') ?>">
                    </p>

                    <?php if ($action === 'edit'): ?>
                        <p class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="Gepland" <?= $matchData['status'] === 'Gepland' ? 'selected' : '' ?>>Gepland</option>
                                <option value="Gespeeld" <?= $matchData['status'] === 'Gespeeld' ? 'selected' : '' ?>>Gespeeld</option>
                                <option value="Afgelast" <?= $matchData['status'] === 'Afgelast' ? 'selected' : '' ?>>Afgelast</option>
                            </select>
                        </p>
                    <?php endif; ?>
                </div>

                <p class="form-group">
                    <label for="notes">Opmerkingen / Wedstrijdinformatie</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="bijv. Verzamelen om 13:00 in de kantine..."><?= e($matchData['notes'] ?? '') ?></textarea>
                </p>
            </fieldset>

            <footer style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                <a href="/admin/matches.php" class="btn btn-outline">Annuleren</a>
                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Wijzigingen Opslaan' : 'Wedstrijd Aanmaken' ?></button>
            </footer>
        </form>
    </article>

<?php else: ?>
    <!-- Overzichtstabel -->
    <section class="card" aria-labelledby="allMatchesTitle">
        <header class="card-header">
            <h3 id="allMatchesTitle">Geplande & Gespeelde Wedstrijden</h3>
            <search style="max-width: 250px;">
                <input type="search" class="form-control" placeholder="Zoek wedstrijd..." data-table-filter="#adminMatchesTable" aria-label="Wedstrijden doorzoeken">
            </search>
        </header>

        <div class="table-responsive">
            <table class="table" id="adminMatchesTable">
                <thead>
                    <tr>
                        <th scope="col">Datum</th>
                        <th scope="col">Team</th>
                        <th scope="col">Tegenstander</th>
                        <th scope="col">Locatie / Veld</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aanwezig</th>
                        <th scope="col">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allMatches)): ?>
                        <tr><td colspan="7" style="color: var(--text-muted);">Er zijn nog geen wedstrijden aangemaakt.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allMatches as $m): ?>
                            <tr>
                                <td><strong><?= date('d-m-Y H:i', strtotime($m['match_date'])) ?></strong></td>
                                <td><?= e($m['team_name']) ?></td>
                                <td><strong><?= e($m['opponent']) ?></strong></td>
                                <td><?= e($m['location']) ?> (<?= e($m['field_number']) ?>)</td>
                                <td>
                                    <span class="badge <?= $m['status'] === 'Gepland' ? 'badge-info' : ($m['status'] === 'Gespeeld' ? 'badge-success' : 'badge-danger') ?>">
                                        <?= e($m['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?= (int)$m['present_count'] ?> wel</span>
                                    <span class="badge badge-danger"><?= (int)$m['absent_count'] ?> niet</span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem;">
                                        <a href="/admin/attendance.php?match_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline" title="Aanwezigheid inzien">👥</a>
                                        <a href="/admin/matches.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                                        <form method="POST" action="/admin/matches.php" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');" style="display:inline;">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
