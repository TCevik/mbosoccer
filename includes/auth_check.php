<?php
// includes/auth_check.php - Authenticatie & Autorisatie helper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

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

function requireLogin(string $redirect = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Je moet eerst inloggen om deze pagina te bekijken.';
        header("Location: $redirect");
        exit;
    }
}

function requireRole(array|string $roles, string $redirect = '/index.php'): void {
    requireLogin();
    $roles = (array)$roles;
    $currentRole = $_SESSION['user_role'] ?? 'lid';
    
    if (!in_array($currentRole, $roles)) {
        $_SESSION['flash_error'] = 'Je hebt geen toegang tot deze pagina.';
        header("Location: $redirect");
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
