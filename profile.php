<?php
// profile.php - Eigen account beheren
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Team.php';

requireLogin();

$userModel = new User();
$teamModel = new Team();
$currentUserData = $userModel->findById($_SESSION['user_id']);
$myTeams = $teamModel->getTeamsForUser($_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $error = 'Naam mag niet leeg zijn.';
    } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
        $error = 'Nieuw wachtwoord moet minimaal 6 tekens bevatten.';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'Wachtwoorden komen niet overeen.';
    } else {
        $updated = $userModel->updateProfile(
            $_SESSION['user_id'],
            $name,
            $phone ?: null,
            $birthdate ?: null,
            !empty($newPassword) ? $newPassword : null
        );

        if ($updated) {
            $_SESSION['user_name'] = $name;
            $success = 'Je profielgegevens zijn succesvol bijgewerkt.';
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
    <div class="card">
        <div class="card-header">
            <h2>Mijn Account & Profiel</h2>
            <span class="role-badge role-<?= e($currentUserData['role']) ?>"><?= e(strtoupper($currentUserData['role'])) ?></span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <span>⚠</span>
                <p><?= e($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <span>✓</span>
                <p><?= e($success) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/profile.php">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Volledige Naam</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= e($currentUserData['name']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">E-mailadres (onwijzigbaar)</label>
                    <input type="email" id="email" class="form-control" disabled value="<?= e($currentUserData['email']) ?>" style="background: #f1f5f9;">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="phone">Telefoonnummer</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($currentUserData['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="birthdate">Geboortedatum</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e($currentUserData['birthdate'] ?? '') ?>">
                </div>
            </div>

            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border);">
            <h4 style="margin-bottom: 1rem;">Wachtwoord wijzigen (optioneel)</h4>

            <div class="form-grid">
                <div class="form-group">
                    <label for="new_password">Nieuw Wachtwoord</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Leeg laten om te behouden">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Bevestig Nieuw Wachtwoord</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Herhaal nieuw wachtwoord">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Wijzigingen Opslaan</button>
        </form>
    </div>

    <!-- Mijn Teams -->
    <div class="card">
        <div class="card-header">
            <h3>Mijn Teams & Rol in het Veld</h3>
        </div>
        <?php if (empty($myTeams)): ?>
            <p style="color: var(--text-muted);">Je bent momenteel nog niet ingedeeld bij een specifiek team. Neem contact op met de trainer of clubmedewerker.</p>
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
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
