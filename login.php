<?php
// login.php - Inloggen
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    header("Location: /index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vul zowel je e-mailadres als wachtwoord in.';
    } else {
        $userModel = new User();
        $authenticatedUser = $userModel->authenticate($email, $password);

        if ($authenticatedUser) {
            // Sessie veilig vullen
            $_SESSION['user_id'] = $authenticatedUser['id'];
            $_SESSION['user_name'] = $authenticatedUser['name'];
            $_SESSION['user_email'] = $authenticatedUser['email'];
            $_SESSION['user_role'] = $authenticatedUser['role'];

            $_SESSION['flash_success'] = 'Welkom terug, ' . $authenticatedUser['name'] . '!';
            
            // Redirect medewerkers direct naar dashboard of anders schema
            if ($authenticatedUser['role'] === 'medewerker') {
                header("Location: /admin/dashboard.php");
            } else {
                header("Location: /schedule.php");
            }
            exit;
        } else {
            $error = 'Ongeldige inloggegevens. Controleer je e-mail en wachtwoord.';
        }
    }
}

$pageTitle = "Inloggen";
require_once __DIR__ . '/includes/header.php';
?>

<section style="max-width: 460px; margin: 2rem auto;" aria-labelledby="loginHeading">
    <article class="card">
        <header class="card-header" style="justify-content: center; text-align: center; flex-direction: column;">
            <span style="font-size: 2.5rem; margin-bottom: 0.5rem;" aria-hidden="true">⚽</span>
            <h2 id="loginHeading">Inloggen bij MBO Soccer</h2>
            <p style="color: var(--text-muted); font-size: 0.88rem;">Toegang voor leden, trainers en medewerkers</p>
        </header>

        <?php if ($error): ?>
            <aside class="alert alert-danger" role="alert" style="margin-bottom: 1rem;">
                <span>⚠</span>
                <p><?= e($error) ?></p>
            </aside>
        <?php endif; ?>

        <form method="POST" action="/login.php">
            <p class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="bijv. speler@mbosoccer.nl" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
            </p>

            <p class="form-group">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </p>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.75rem;">Inloggen</button>
        </form>

        <footer style="margin-top: 1.5rem; text-align: center; font-size: 0.88rem; color: var(--text-muted);">
            Nog geen lid van MBO Soccer? <a href="/register.php"><strong>Schrijf je hier in</strong></a>
        </footer>

        <aside style="margin-top: 1.5rem; padding: 0.75rem; background: #f8fafc; border-radius: var(--radius-sm); font-size: 0.8rem; border: 1px dashed var(--border);">
            <strong>Demo testaccounts:</strong><br>
            • Beheerder: <code>admin@mbosoccer.nl</code> / <code>Admin123!</code><br>
            • Trainer: <code>trainer@mbosoccer.nl</code> / <code>Trainer123!</code><br>
            • Speler: <code>speler@mbosoccer.nl</code> / <code>Speler123!</code>
        </aside>
    </article>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
