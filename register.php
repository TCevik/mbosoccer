<?php
// register.php - Zelfregistratie voor nieuwe leden
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    header("Location: /index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Vul alle verplichte velden in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Voer een geldig e-mailadres in.';
    } elseif (strlen($password) < 6) {
        $error = 'Het wachtwoord moet minimaal 6 tekens lang zijn.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'De wachtwoorden komen niet overeen.';
    } else {
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $error = 'Dit e-mailadres is al geregistreerd bij onze club.';
        } else {
            $newId = $userModel->register($name, $email, $password, 'lid', $phone, $birthdate);
            if ($newId) {
                // Automatisch inloggen
                $_SESSION['user_id'] = $newId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'lid';

                $_SESSION['flash_success'] = 'Welkom bij MBO Soccer! Je inschrijving is succesvol voltooid.';
                header("Location: /schedule.php");
                exit;
            } else {
                $error = 'Er is een fout opgetreden bij de inschrijving. Probeer het opnieuw.';
            }
        }
    }
}

$pageTitle = "Inschrijven als Lid";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 540px; margin: 2rem auto;">
    <div class="card">
        <div class="card-header" style="justify-content: center; text-align: center; flex-direction: column;">
            <h2>Inschrijven als lid</h2>
            <p style="color: var(--text-muted); font-size: 0.88rem;">Meld je aan bij VV MBO Soccer</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <span>⚠</span>
                <p><?= e($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register.php">
            <div class="form-group">
                <label for="name">Volledige Naam *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="bijv. Jan Jansen">
            </div>

            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="naam@voorbeeld.nl">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="phone">Telefoonnummer</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="0612345678">
                </div>

                <div class="form-group">
                    <label for="birthdate">Geboortedatum</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e($_POST['birthdate'] ?? '') ?>">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Wachtwoord (min. 6 tekens) *</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Bevestig Wachtwoord *</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Inschrijving Voltooien</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.88rem; color: var(--text-muted);">
            Heb je al een account? <a href="/login.php"><strong>Hier inloggen</strong></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
