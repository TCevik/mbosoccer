<?php
// admin/members.php - Ledenbeheer (CRUD & rollen)
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';

requireRole('medewerker');

$userModel = new User();
$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editUser = null;

if ($editId > 0) {
    $editUser = $userModel->findById($editId);
}

// POST acties
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['form_action'] ?? '';

    if ($postAction === 'delete') {
        $delId = (int)$_POST['id'];
        if ($delId === $_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Je kunt je eigen beheerdersaccount niet verwijderen.';
        } else {
            $userModel->delete($delId);
            $_SESSION['flash_success'] = 'Lid succesvol verwijderd.';
        }
        header("Location: /admin/members.php");
        exit;
    }

    if ($postAction === 'save') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'lid';
        $phone = trim($_POST['phone'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';

        if (empty($name) || empty($email)) {
            $_SESSION['flash_error'] = 'Naam en e-mailadres zijn verplicht.';
        } else {
            if ($editId > 0) {
                $userModel->updateUserAdmin($editId, $name, $email, $role, $phone, $birthdate);
                $_SESSION['flash_success'] = 'Lidgegevens succesvol bijgewerkt.';
            } else {
                $password = $_POST['password'] ?? 'Welkom123!';
                if ($userModel->findByEmail($email)) {
                    $_SESSION['flash_error'] = 'Dit e-mailadres is al in gebruik.';
                    header("Location: /admin/members.php?action=create");
                    exit;
                }
                $userModel->register($name, $email, $password, $role, $phone, $birthdate);
                $_SESSION['flash_success'] = 'Nieuw lid succesvol aangemaakt.';
            }
            header("Location: /admin/members.php");
            exit;
        }
    }
}

$roleFilter = $_GET['role'] ?? '';
$allMembers = $userModel->getAll($roleFilter);
$pageTitle = "Ledenbeheer";
require_once __DIR__ . '/../includes/header.php';
?>

<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Ledenbeheer</h2>
        <p style="color: var(--text-muted);">Beheer alle leden, trainers en medewerkers van VV MBO Soccer.</p>
    </div>
    <nav aria-label="Leden acties">
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <a href="/admin/members.php" class="btn btn-outline">← Terug naar ledenlijst</a>
        <?php else: ?>
            <a href="/admin/members.php?action=create" class="btn btn-primary">+ Nieuw Lid Toevoegen</a>
        <?php endif; ?>
    </nav>
</header>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <article class="card" style="max-width: 650px; margin: 0 auto;">
        <header class="card-header">
            <h3><?= $action === 'edit' ? 'Lid Bewerken' : 'Nieuw Lid Toevoegen' ?></h3>
        </header>

        <form method="POST" action="/admin/members.php<?= $editId ? '?id=' . $editId : '' ?>">
            <input type="hidden" name="form_action" value="save">

            <fieldset style="border: none; padding: 0; margin: 0;">
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
                        <label for="role">Rol in het systeem *</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="lid" <?= ($editUser && $editUser['role'] === 'lid') ? 'selected' : '' ?>>Lid (Speler)</option>
                            <option value="trainer" <?= ($editUser && $editUser['role'] === 'trainer') ? 'selected' : '' ?>>Trainer / Coach</option>
                            <option value="medewerker" <?= ($editUser && $editUser['role'] === 'medewerker') ? 'selected' : '' ?>>Clubmedewerker (Beheerder)</option>
                        </select>
                    </p>

                    <p class="form-group">
                        <label for="phone">Telefoonnummer</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>">
                    </p>
                </div>

                <div class="form-grid">
                    <p class="form-group">
                        <label for="birthdate">Geboortedatum</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e($editUser['birthdate'] ?? '') ?>">
                    </p>

                    <?php if ($action === 'create'): ?>
                        <p class="form-group">
                            <label for="password">Standaard Wachtwoord *</label>
                            <input type="text" id="password" name="password" class="form-control" value="Welkom123!" required>
                            <small style="color: var(--text-muted);">Het lid kan dit zelf wijzigen na inloggen.</small>
                        </p>
                    <?php endif; ?>
                </div>
            </fieldset>

            <footer style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                <a href="/admin/members.php" class="btn btn-outline">Annuleren</a>
                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Opslaan' : 'Toevoegen' ?></button>
            </footer>
        </form>
    </article>

<?php else: ?>
    <!-- Filteren & Ledenlijst -->
    <section class="card" aria-labelledby="membersListTitle">
        <header class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <nav style="display: flex; gap: 0.5rem; align-items: center;" aria-label="Leden filteren op rol">
                <a href="/admin/members.php" class="btn btn-sm <?= empty($roleFilter) ? 'btn-secondary' : 'btn-outline' ?>">Alle (<?= count($allMembers) ?>)</a>
                <a href="/admin/members.php?role=lid" class="btn btn-sm <?= $roleFilter === 'lid' ? 'btn-secondary' : 'btn-outline' ?>">Spelers</a>
                <a href="/admin/members.php?role=trainer" class="btn btn-sm <?= $roleFilter === 'trainer' ? 'btn-secondary' : 'btn-outline' ?>">Trainers</a>
                <a href="/admin/members.php?role=medewerker" class="btn btn-sm <?= $roleFilter === 'medewerker' ? 'btn-secondary' : 'btn-outline' ?>">Medewerkers</a>
            </nav>

            <search style="max-width: 250px;">
                <input type="search" class="form-control" placeholder="Zoek op naam of mail..." data-table-filter="#membersTable" aria-label="Zoek in ledenlijst">
            </search>
        </header>

        <div class="table-responsive">
            <table class="table" id="membersTable">
                <thead>
                    <tr>
                        <th scope="col">Naam</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Telefoon</th>
                        <th scope="col">Geboortedatum</th>
                        <th scope="col">Lid sinds</th>
                        <th scope="col">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allMembers as $m): ?>
                        <tr>
                            <td><strong><?= e($m['name']) ?></strong></td>
                            <td><?= e($m['email']) ?></td>
                            <td><span class="role-badge role-<?= e($m['role']) ?>"><?= e(ucfirst($m['role'])) ?></span></td>
                            <td><?= e($m['phone'] ?: '-') ?></td>
                            <td><?= e($m['birthdate'] ? date('d-m-Y', strtotime($m['birthdate'])) : '-') ?></td>
                            <td><?= date('d-m-Y', strtotime($m['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.4rem;">
                                    <a href="/admin/members.php?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                                    <?php if ($m['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" action="/admin/members.php" onsubmit="return confirm('Weet je zeker dat je dit lid wilt verwijderen?');" style="display:inline;">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">🗑️</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
