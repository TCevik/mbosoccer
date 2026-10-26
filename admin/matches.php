<?php
// admin/matches.php
// Beheert de wedstrijden: toevoegen, wijzigen en verwijderen.

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/MatchGame.php';
require_once __DIR__ . '/../classes/Team.php';

// Alleen medewerkers en trainers mogen bij wedstrijdbeheer
requireRole(['medewerker', 'trainer']);

$matchModel = new MatchGame();
$teamModel = new Team();
$teams = $teamModel->getAll();

$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$matchData = null;

// Als we een wedstrijd bewerken, halen we eerst de bestaande gegevens op
if ($editId > 0) {
    $matchData = $matchModel->findById($editId);
}

// POST-verzoeken afhandelen (opslaan of verwijderen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    // 1. Wedstrijd verwijderen
    if ($formAction === 'delete') {
        $id = (int)$_POST['id'];
        $matchModel->delete($id);
        $_SESSION['flash_success'] = 'Wedstrijd verwijderd.';
        header("Location: /admin/matches.php");
        exit;
    }

    // 2. Wedstrijd opslaan (nieuw of bewerken)
    if ($formAction === 'save') {
        $teamId = (int)$_POST['team_id'];
        $opponent = trim($_POST['opponent'] ?? '');
        $matchDate = $_POST['match_date'] ?? '';
        $location = $_POST['location'] ?? 'Thuis';
        $fieldNumber = trim($_POST['field_number'] ?? 'Veld 1');
        $status = $_POST['status'] ?? 'Gepland';
        $notes = trim($_POST['notes'] ?? '');

        // Validatie van verplichte velden
        if ($teamId > 0 && $opponent && $matchDate) {
            if ($editId > 0) {
                // Bestaande wedstrijd updaten
                $matchModel->update($editId, $teamId, $opponent, $matchDate, $location, $fieldNumber, $status, $notes);
                $_SESSION['flash_success'] = 'Wedstrijd bijgewerkt.';
            } else {
                // Nieuwe wedstrijd aanmaken
                $matchModel->create($teamId, $opponent, $matchDate, $location, $fieldNumber, $notes);
                $_SESSION['flash_success'] = 'Nieuwe wedstrijd aangemaakt.';
            }
            header("Location: /admin/matches.php");
            exit;
        } else {
            $_SESSION['flash_error'] = 'Vul alle verplichte velden in.';
        }
    }
}

// Alle wedstrijden ophalen voor de tabel
$allMatches = $matchModel->getAll();
$pageTitle = "Wedstrijdbeheer";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Wedstrijdbeheer</h2>
        <p style="color: var(--text-muted);">Wedstrijden inplannen, aanpassen of verwijderen.</p>
    </div>
    <div>
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <a href="/admin/matches.php" class="btn btn-outline">← Terug naar lijst</a>
        <?php else: ?>
            <a href="/admin/matches.php?action=create" class="btn btn-primary">+ Nieuwe Wedstrijd</a>
        <?php endif; ?>
    </div>
</div>

<!-- Scherm voor aanmaken of bewerken -->
<?php if ($action === 'create' || $action === 'edit'): ?>
    <article class="card" style="max-width: 650px; margin: 0 auto;">
        <header class="card-header">
            <h3><?= $action === 'edit' ? 'Wedstrijd Bewerken' : 'Nieuwe Wedstrijd Plannen' ?></h3>
        </header>

        <form method="POST" action="/admin/matches.php<?= $editId ? '?id=' . $editId : '' ?>">
            <input type="hidden" name="form_action" value="save">

            <div class="form-grid">
                <p class="form-group">
                    <label for="team_id">Team *</label>
                    <select name="team_id" id="team_id" class="form-control" required>
                        <option value="">-- Kies Team --</option>
                        <?php foreach ($teams as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($matchData && $matchData['team_id'] == $t['id']) ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> (<?= e($t['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="form-group">
                    <label for="opponent">Tegenstander *</label>
                    <input type="text" id="opponent" name="opponent" class="form-control" required value="<?= e($matchData['opponent'] ?? '') ?>" placeholder="bijv. AFC Ajax">
                </p>
            </div>

            <div class="form-grid">
                <p class="form-group">
                    <label for="match_date">Datum & Tijd *</label>
                    <input type="datetime-local" id="match_date" name="match_date" class="form-control" required 
                           value="<?= $matchData ? date('Y-m-d\TH:i', strtotime($matchData['match_date'])) : '' ?>">
                </p>

                <p class="form-group">
                    <label for="location">Locatie (Thuis / Uit)</label>
                    <select name="location" id="location" class="form-control">
                        <option value="Thuis" <?= ($matchData && $matchData['location'] === 'Thuis') ? 'selected' : '' ?>>Thuis</option>
                        <option value="Uit" <?= ($matchData && $matchData['location'] === 'Uit') ? 'selected' : '' ?>>Uit</option>
                    </select>
                </p>
            </div>

            <div class="form-grid">
                <p class="form-group">
                    <label for="field_number">Veld</label>
                    <input type="text" id="field_number" name="field_number" class="form-control" value="<?= e($matchData['field_number'] ?? 'Veld 1') ?>">
                </p>

                <?php if ($action === 'edit'): ?>
                    <p class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="Gepland" <?= ($matchData['status'] ?? '') === 'Gepland' ? 'selected' : '' ?>>Gepland</option>
                            <option value="Gespeeld" <?= ($matchData['status'] ?? '') === 'Gespeeld' ? 'selected' : '' ?>>Gespeeld</option>
                            <option value="Afgelast" <?= ($matchData['status'] ?? '') === 'Afgelast' ? 'selected' : '' ?>>Afgelast</option>
                        </select>
                    </p>
                <?php endif; ?>
            </div>

            <p class="form-group">
                <label for="notes">Opmerking</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Bijv. verzamelen om 13:00 uur..."><?= e($matchData['notes'] ?? '') ?></textarea>
            </p>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <a href="/admin/matches.php" class="btn btn-outline">Annuleren</a>
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </div>
        </form>
    </article>

<!-- Scherm met overzichtstabel -->
<?php else: ?>
    <article class="card">
        <header class="card-header">
            <h3>Overzicht Wedstrijden</h3>
            <input type="search" class="form-control" placeholder="Zoek op team of tegenstander..." data-table-filter="#matchesTable" style="max-width: 250px;">
        </header>

        <div class="table-responsive">
            <table class="table" id="matchesTable">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Team</th>
                        <th>Tegenstander</th>
                        <th>Locatie</th>
                        <th>Status</th>
                        <th>Aanwezigheid</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allMatches)): ?>
                        <tr><td colspan="7" style="color: var(--text-muted);">Geen wedstrijden gevonden.</td></tr>
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
                                    <a href="/admin/attendance.php?match_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">Inzien</a>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.3rem;">
                                        <a href="/admin/matches.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">Bewerken</a>
                                        <form method="POST" action="/admin/matches.php" onsubmit="return confirm('Wedstrijd verwijderen?');" style="display:inline;">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
