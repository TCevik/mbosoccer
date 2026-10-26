<?php
// includes/auth_check.php
// Handige hulpfuncties voor sessies, inloggen, rollen en beveiliging.

// Start de sessie als die nog niet actief is
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kijkt of de bezoeker is ingelogd (staat user_id in de sessie?)
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Geeft de gegevens van de ingelogde gebruiker terug
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Gebruiker',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'lid'
    ];
}

// Stuur de bezoeker naar het inlogscherm als hij nog niet is ingelogd
function requireLogin(string $redirect = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Je moet eerst inloggen om deze pagina te bekijken.';
        header("Location: $redirect");
        exit;
    }
}

// Controleert of de gebruiker de juiste rol heeft (bijvoorbeeld alleen 'medewerker')
function requireRole($roles, string $redirect = '/index.php'): void {
    requireLogin(); // Eerst checken of iemand überhaupt is ingelogd
    $roles = (array)$roles;
    $currentRole = $_SESSION['user_role'] ?? 'lid';
    
    // Als de rol niet in de toegestane lijst staat, stuur terug naar home
    if (!in_array($currentRole, $roles)) {
        $_SESSION['flash_error'] = 'Je hebt geen toegang tot deze pagina.';
        header("Location: $redirect");
        exit;
    }
}

// XSS-beveiliging: zet speciale tekens om in veilige HTML zodat hackers geen scripts kunnen injecteren
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
