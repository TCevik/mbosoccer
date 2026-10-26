<?php
// admin/members.php
// Ledenbeheer: leden, trainers en medewerkers toevoegen, aanpassen of verwijderen.
// Alleen toegankelijk voor clubmedewerkers.

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';

// Strikte controle: alleen 'medewerker' mag leden beheren
requireRole('medewerker');

$userModel = new User();
$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editUser = $editId > 0 ? $userModel->findById($editId) : null;

// POST acties: opslaan of verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    // 1. Lid verwijderen
    if ($formAction === 'delete') {
        $delId = (int)$_POST['id'];
        
        // Voorkom dat de beheerder zijn eigen account per ongeluk wist
        if ($delId === $_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Je kunt jezelf niet verwijderen.';
        } else {
            $userModel->delete($delId);
            $_SESSION['flash_success'] = 'Lid verwijderd.';
        }
        header("Location: /admin/members.php");
        exit;
    }

    // 2. Lid toevoegen of bewerken
    if ($formAction === 'save') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'lid';
        $phone = trim($_POST['phone'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';

        if ($name && $email) {
            if ($editId > 0) {
                // Bestaand lid bijwerken
                $userModel->updateUserAdmin($editId, $name, $email, $role, $phone, $birthdate);
                $_SESSION['flash_success'] = 'Lid bijgewerkt.';
            } else {
                // Nieuw lid aanmaken
                $password = $_POST['password'] ?? 'Welkom123!';
                if ($userModel->findByEmail($email)) {
                    $_SESSION['flash_error'] = 'Dit e-mailadres bestaat al.';
                    header("Location: /admin/members.php?action=create");
                    exit;
                }
                $userModel->register($name, $email, $password, $role, $phone, $birthdate);
                $_SESSION['flash_success'] = 'Nieuw lid aangemaakt.';
            }
            header("Location: /admin/members.php");
            exit;
        } else {
            $_SESSION['flash_error'] = 'Naam en e-mailadres zijn verplicht.';
        }
    }
}

// Alle leden ophalen voor de tabel
$roleFilter = $_GET['role'] ?? '';
$allMembers = $userModel->getAll($roleFilter);
$pageTitle = "Ledenbeheer";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Ledenbeheer</h2>
        <p style="color: var(--text-muted);">Overzicht van alle spelers, trainers en medewerkers.</p>
    </div>
    <div>
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <a href="/admin/members.php" class="btn btn-outline">← Terug naar ledenlijst</a>
        <?php else: ?>
            <a href="/admin/members.php?action=create" class="btn btn-primary">+ Nieuw Lid Toevoegen</a>
        <?php endif; ?>
    </div>
</div>

<!-- Formulier nieuw / bewerken -->
<?php if ($action === 'create' || $action === 'edit'): ?>
    <article class="card" style="max-width: 600px; margin: 0 auto;">
        <header class="card-header">
            <h3><?= $action === 'edit' ? 'Lid Bewerken' : 'Nieuw Lid Inschrijven' ?></h3>
        </header>

        <form method="POST" action="/admin/members.php<?= $editId ? '?id=' . $editId : '' ?>">
            <input type="hidden" name="form_action" value="save">

            <div class="form-grid">
                <p class="form-group">
                    <label for="name">Volledige Naam *</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= e($editUser['name'] ?? '') ?>">
                </p>

                <p class="form-group">
                    <label for="email">E-mailadres *</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= e($editUser['email'] ?? '') ?>">
                </p>
            </div>

            <div class="form-grid">
                <p class="form-group">
                    <label for="role">Rol</label>
                    <select name="role" id="role" class="form-control">
                        <option value="lid" <?= ($editUser['role'] ?? '') === 'lid' ? 'selected' : '' ?>>Lid (Speler)</option>
                        <option value="trainer" <?= ($editUser['role'] ?? '') === 'trainer' ? 'selected' : '' ?>>Trainer</option>
                        <option value="medewerker" <?= ($editUser['role'] ?? '') === 'medewerker' ? 'selected' : '' ?>>Clubmedewerker (Admin)</option>
                    </select>
                </p>

                <p class="form-group">
                    <label for="phone">Telefoonnummer</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>">
                </p>
            </div>

            <p class="form-group">
                <label for="birthdate">Geboortedatum</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e($editUser['birthdate'] ?? '') ?>">
            </p>

            <?php if ($action === 'create'): ?>
                <p class="form-group">
                    <label for="password">Standaard Wachtwoord</label>
                    <input type="password" id="password" name="password" class="form-control" value="Welkom123!">
                </p>
            <?php endif; ?>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <a href="/admin/members.php" class="btn btn-outline">Annuleren</a>
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </div>
        </form>
    </article>

<!-- Tabel met alle geregistreerde leden -->
<?php else: ?>
    <article class="card">
        <header class="card-header">
            <h3>Ledenlijst (<?= count($allMembers) ?>)</h3>
            <input type="search" class="form-control" placeholder="Zoek op naam of e-mail..." data-table-filter="#membersTable" style="max-width: 250px;">
        </header>

        <div class="table-responsive">
            <table class="table" id="membersTable">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mailadres</th>
                        <th>Rol</th>
                        <th>Telefoon</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allMembers)): ?>
                        <tr><td colspan="5" style="color: var(--text-muted);">Geen leden gevonden.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allMembers as $m): ?>
                            <tr>
                                <td><strong><?= e($m['name']) ?></strong></td>
                                <td><?= e($m['email']) ?></td>
                                <td><span class="role-badge role-<?= e($m['role']) ?>"><?= ucfirst(e($m['role'])) ?></span></td>
                                <td><?= e($m['phone'] ?: '-') ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.3rem;">
                                        <a href="/admin/members.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">Bewerken</a>
                                        <?php if ($m['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" action="/admin/members.php" onsubmit="return confirm('Lid verwijderen?');" style="display:inline;">
                                                <input type="hidden" name="form_action" value="delete">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                                            </form>
                                        <?php endif; ?>
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
