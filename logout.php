<?php
// logout.php
require_once __DIR__ . '/includes/auth_check.php';
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

session_start();
$_SESSION['flash_success'] = 'Je bent succesvol uitgelogd.';
header("Location: /login.php");
exit;
