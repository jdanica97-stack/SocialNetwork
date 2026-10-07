<?php

/**
 * public/index.php
 *
 * Front Controller — single entry point for the entire application.
 *
 * Every request is routed here (via .htaccess).
 * This file:
 *   1. Defines BASE_PATH so all require_once calls use absolute paths.
 *   2. Starts the session once, globally.
 *   3. Reads the ?url= parameter to determine which controller/action to call.
 *   4. Dispatches to the correct controller method.
 *   5. Falls back to a 404 page for unknown routes.
 *
 * Supported routes (Step 5 — Authentication):
 *   (empty)          → home page
 *   auth/register    → AuthController::showRegister()  [GET]
 *   auth/register    → AuthController::register()      [POST]
 *   auth/login       → AuthController::showLogin()     [GET]
 *   auth/login       → AuthController::login()         [POST]
 *   auth/logout      → AuthController::logout()        [GET]
 *
 * Supported routes (Step 6 — Profile):
 *   profile          → ProfileController::showProfile() [GET]
 *   profile/edit     → ProfileController::showEdit()    [GET]
 *   profile/update   → ProfileController::update()      [POST]
 */

// ─── Bootstrap ────────────────────────────────────────────────────────────────

// Define an absolute base path so every file can locate project resources
// regardless of from where it was included.
define('BASE_PATH', dirname(__DIR__));

// Start the session globally (controllers check session_status before calling again)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Route Parsing ────────────────────────────────────────────────────────────

// Get the ?url= segment produced by the .htaccess rewrite.
// Default to an empty string (home) if not present.
$url = trim($_GET['url'] ?? '', '/');

// ─── Routing ──────────────────────────────────────────────────────────────────

// ── Home ──────────────────────────────────────────────────────────────────────
if ($url === '' || $url === 'home') {

    $pageTitle = 'Mini Social Network — Home';
    require_once BASE_PATH . '/app/views/layouts/header.php';

    // Show a different card depending on whether the user is logged in
    if (isset($_SESSION['user_id'])): ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-house-door-fill text-primary me-2"></i>
                        Home
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">
                            Welcome back,
                            <strong><?= htmlspecialchars($_SESSION['user_full_name'], ENT_QUOTES, 'UTF-8') ?></strong>!
                        </h5>
                        <p class="card-text text-muted">
                            You are logged in as
                            <strong>@<?= htmlspecialchars($_SESSION['user_username'], ENT_QUOTES, 'UTF-8') ?></strong>.
                            More features are coming soon.
                        </p>
                        <a href="/SocialNetwork/public/?url=auth/logout" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Mini Social Network
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Connect with others</h4>
                        <p class="text-muted mb-4">
                            Create an account or log in to get started.
                        </p>
                        <a href="/SocialNetwork/public/?url=auth/register" class="btn btn-primary me-2">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                        <a href="/SocialNetwork/public/?url=auth/login" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php endif;

    require_once BASE_PATH . '/app/views/layouts/footer.php';
    exit;
}

// ── Auth Routes ───────────────────────────────────────────────────────────────
if ($url === 'auth/register' || $url === 'auth/login' || $url === 'auth/logout') {

    require_once BASE_PATH . '/app/controllers/AuthController.php';
    $authController = new AuthController();

    if ($url === 'auth/register') {
        // GET → show form | POST → process form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->register();
        } else {
            $authController->showRegister();
        }
        exit;
    }

    if ($url === 'auth/login') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->login();
        } else {
            $authController->showLogin();
        }
        exit;
    }

    if ($url === 'auth/logout') {
        $authController->logout();
        exit;
    }
}

// ── Profile Routes ────────────────────────────────────────────────────────────
if ($url === 'profile' || $url === 'profile/edit' || $url === 'profile/update') {

    require_once BASE_PATH . '/app/controllers/ProfileController.php';
    $profileController = new ProfileController();

    // GET  /profile          → display the profile page
    if ($url === 'profile') {
        $profileController->showProfile();
        exit;
    }

    // GET  /profile/edit     → display the edit form
    if ($url === 'profile/edit') {
        $profileController->showEdit();
        exit;
    }

    // POST /profile/update   → process the edit form
    if ($url === 'profile/update') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileController->update();
        } else {
            // Someone navigated to this URL directly — send them to the edit form
            header('Location: /SocialNetwork/public/?url=profile/edit');
        }
        exit;
    }
}

// ── 404 — Unknown Route ───────────────────────────────────────────────────────
http_response_code(404);
$pageTitle = '404 — Page Not Found';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
        <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem;"></i>
        <h1 class="mt-3">404</h1>
        <p class="text-muted">The page you are looking for does not exist.</p>
        <a href="/SocialNetwork/public/" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> Go Home
        </a>
    </div>
</div>
<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
