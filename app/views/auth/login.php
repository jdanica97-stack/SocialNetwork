<?php

/**
 * app/views/auth/login.php
 *
 * Login form view.
 *
 * Variables available (set by AuthController):
 *   $errors        array        — validation / auth error messages (may be empty)
 *   $old           array        — previously submitted values to re-fill the form
 *   $flashSuccess  string|null  — one-time success message (e.g. "Registered!")
 *
 * Rules:
 *   - No SQL queries here.
 *   - No business logic here.
 *   - All output is escaped with htmlspecialchars() to prevent XSS.
 */

// Set the page title used by the header layout
$pageTitle = 'Login — Mini Social Network';

require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <!-- ── Page Heading ──────────────────────────────────────────────── -->
        <div class="text-center mb-4">
            <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2 fw-bold">Welcome Back</h2>
            <p class="text-muted">Log in to your Mini Social Network account.</p>
        </div>

        <!-- ── Flash Success Message (e.g. after registration or logout) ─── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Error Messages ────────────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Login failed:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Login Form ─────────────────────────────────────────────────── -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/SocialNetwork/public/?url=auth/login" novalidate>

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">
                            Username <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="Your username"
                                value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Your password"
                                required
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- ── Link to Register ──────────────────────────────────────────── -->
        <p class="text-center mt-3 text-muted">
            Don't have an account?
            <a href="/SocialNetwork/public/?url=auth/register" class="fw-semibold">Register here</a>
        </p>

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
