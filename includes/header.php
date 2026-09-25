<?php
// includes/header.php
require_once __DIR__ . '/auth_check.php';
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>MBO Soccer</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <a href="/index.php" class="brand">
                <span class="brand-badge">⚽</span>
                <span class="brand-text">MBO <strong>SOCCER</strong></span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Menu openen">
                <span></span><span></span><span></span>
            </button>

            <nav class="nav-links" id="navLinks">
                <a href="/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
                <a href="/schedule.php" class="<?= $currentPage === 'schedule.php' ? 'active' : '' ?>">Programma & Schema</a>

                <?php if ($user): ?>
                    <a href="/profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">Mijn Profiel</a>

                    <?php if (in_array($user['role'], ['medewerker', 'trainer'])): ?>
                        <div class="dropdown">
                            <button class="dropdown-btn <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin') ? 'active' : '' ?>">
                                Beheer ▾
                            </button>
                            <nav class="dropdown-content" aria-label="Beheermenu">
                                <a href="/admin/dashboard.php">Overzicht / Dashboard</a>
                                <a href="/admin/matches.php">Wedstrijden</a>
                                <a href="/admin/teams.php">Teams & Opstellingen</a>
                                <a href="/admin/attendance.php">Aanwezigheid</a>
                                <?php if ($user['role'] === 'medewerker'): ?>
                                    <a href="/admin/members.php">Ledenbeheer</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>

                    <div class="nav-user">
                        <span class="user-chip">
                            <strong><?= e($user['name']) ?></strong> 
                            <small class="role-badge role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></small>
                        </span>
                        <a href="/logout.php" class="btn btn-outline-danger btn-sm">Uitloggen</a>
                    </div>
                <?php else: ?>
                    <div class="nav-auth">
                        <a href="/login.php" class="btn btn-outline">Inloggen</a>
                        <a href="/register.php" class="btn btn-primary">Lid Worden</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main-wrapper">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <aside class="alert alert-success" role="alert">
                <span>✓</span>
                <p><?= e($_SESSION['flash_success']) ?></p>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Sluiten">×</button>
            </aside>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <aside class="alert alert-danger" role="alert">
                <span>⚠</span>
                <p><?= e($_SESSION['flash_error']) ?></p>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Sluiten">×</button>
            </aside>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
