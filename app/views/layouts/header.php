<?php
/**
 * app/views/layouts/header.php
 *
 * Reusable page header included at the top of every view.
 * Contains: HTML open, Bootstrap CSS, navbar, and opening <main>.
 *
 * The variable $pageTitle can be set by any controller before
 * including this file to give each page a unique <title>.
 */

// Ensure the session is started so $_SESSION is always available
// (the front controller starts it first, this is a safety guard)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Mini Social Network') ?></title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Bootstrap Icons (CDN) -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/SocialNetwork/public/assets/css/style.css">
</head>
<body class="bg-light">

<!-- ── Navbar ──────────────────────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">

        <!-- Brand / Site Name -->
        <a class="navbar-brand fw-bold" href="/SocialNetwork/public/">
            <i class="bi bi-people-fill me-1"></i>
            Mini Social Network
        </a>

        <!-- Mobile toggle button -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- Left side links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/SocialNetwork/public/">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>
            </ul>

            <!-- Right side links — change based on login state -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged-in user: show display name, Profile link, and Logout -->
                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['user_full_name'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/SocialNetwork/public/?url=profile">
                            <i class="bi bi-person-badge me-1"></i>My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/SocialNetwork/public/?url=auth/logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Guest: show Login and Register -->
                    <li class="nav-item">
                        <a class="nav-link" href="/SocialNetwork/public/?url=auth/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/SocialNetwork/public/?url=auth/register">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

        </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
</nav>

<!-- ── Main Content Area ───────────────────────────────────────────────────── -->
<main class="container my-4">
