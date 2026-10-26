<?php
// profile.php
// Hiermee kan een ingelogd lid zijn eigen gegevens bekijken en aanpassen.

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Team.php';

// Alleen toegankelijk voor ingelogde gebruikers
requireLogin();

$userModel = new User();
$teamModel = new Team();

// Gegevens ophalen van het huidige ingelogde account
$currentUserData = $userModel->findById($_SESSION['user_id']);
$myTeams = $teamModel->getTeamsForUser($_SESSION['user_id']);

$error = '';
$success = '';

// Stap 1: Kijken of het profielformulier is opgeslagen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validatie
    if (empty($name)) {
        $error = 'Naam mag niet leeg zijn.';
    } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
        $error = 'Nieuw wachtwoord moet minimaal 6 tekens bevatten.';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'De nieuwe wachtwoorden komen niet overeen.';
    } else {
        // Stap 2: Gegevens bijwerken via het User model
        $updated = $userModel->updateProfile(
            $_SESSION['user_id'],
            $name,
            $phone ?: null,
            $birthdate ?: null,
            !empty($newPassword) ? $newPassword : null
        );

        if ($updated) {
            $_SESSION['user_name'] = $name; // Sessienaam updaten
            $success = 'Je gegevens zijn succesvol bijgewerkt.';
            $currentUserData = $userModel->findById($_SESSION['user_id']);
        } else {
            $error = 'Kon profiel niet bijwerken. Probeer opnieuw.';
        }
    }
}

$pageTitle = "Mijn Profiel";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <article class="card">
        <header class="card-header">
            <h2>Mijn Account & Profiel</h2>
            <span class="role-badge role-<?= e($currentUserData['role']) ?>"><?= e(strtoupper($currentUserData['role'])) ?></span>
        </header>

        <!-- Meldingen -->
        <?php if ($error): ?>
            <aside class="alert alert-danger" role="alert">
                <span>⚠</span>
                <p><?= e($error) ?></p>
            </aside>
        <?php endif; ?>

        <?php if ($success): ?>
            <aside class="alert alert-success" role="alert">
                <span>✓</span>
                <p><?= e($success) ?></p>
            </aside>
        <?php endif; ?>

        <!-- Formulier eigen gegevens -->
        <form method="POST" action="/profile.php">
            <div class="form-grid">
                <p class="form-group">
                    <label for="name">Volledige Naam</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= e($currentUserData['name']) ?>">
                </p>

                <p class="form-group">
                    <label for="email">E-mailadres (onwijzigbaar)</label>
                    <input type="email" id="email" class="form-control" disabled value="<?= e($currentUserData['email']) ?>" style="background: #f1f5f9;">
                </p>
            </div>

            <div class="form-grid">
                <p class="form-group">
                    <label for="phone">Telefoonnummer</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($currentUserData['phone'] ?? '') ?>">
                </p>

                <p class="form-group">
                    <label for="birthdate">Geboortedatum</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e($currentUserData['birthdate'] ?? '') ?>">
                </p>
            </div>

            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border);">
            
            <h4 style="margin-bottom: 0.75rem;">Wachtwoord wijzigen (optioneel)</h4>
            <div class="form-grid">
                <p class="form-group">
                    <label for="new_password">Nieuw Wachtwoord</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Leeg laten om te behouden">
                </p>

                <p class="form-group">
                    <label for="confirm_password">Herhaal Nieuw Wachtwoord</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Herhaal nieuw wachtwoord">
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Gegevens Opslaan</button>
        </form>
    </article>

    <!-- Overzicht van teams waar de speler in zit -->
    <article class="card">
        <header class="card-header">
            <h3>Mijn Teams</h3>
        </header>
        <?php if (empty($myTeams)): ?>
            <p style="color: var(--text-muted);">Je bent momenteel nog niet ingedeeld bij een team. De trainer of beheerder kan je toevoegen.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Klasse</th>
                            <th>Positie</th>
                            <th>Rugnummer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myTeams as $team): ?>
                            <tr>
                                <td><strong><?= e($team['name']) ?></strong></td>
                                <td><?= e($team['category']) ?></td>
                                <td><?= e($team['position'] ?? 'Speler') ?></td>
                                <td><?= e($team['shirt_number'] ? '#' . $team['shirt_number'] : '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
