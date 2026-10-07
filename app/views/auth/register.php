<?php

/**
 * app/views/auth/register.php
 *
 * Registration form view.
 *
 * Variables available (set by AuthController):
 *   $errors  array   — validation error messages (may be empty)
 *   $old     array   — previously submitted values to re-fill the form
 *
 * Rules:
 *   - No SQL queries here.
 *   - No business logic here.
 *   - All output is escaped with htmlspecialchars() to prevent XSS.
 */

// Set the page title used by the header layout
$pageTitle = 'Register — Mini Social Network';

require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <!-- ── Page Heading ──────────────────────────────────────────────── -->
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill text-primary" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2 fw-bold">Create an Account</h2>
            <p class="text-muted">Join the Mini Social Network today.</p>
        </div>

        <!-- ── Error Messages ────────────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Registration Form ─────────────────────────────────────────── -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/SocialNetwork/public/?url=auth/register" novalidate>

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            placeholder="e.g. John Doe"
                            value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                            autocomplete="name"
                        >
                    </div>

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
                                placeholder="e.g. john_doe"
                                value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required
                                autocomplete="username"
                            >
                        </div>
                        <div class="form-text">3–50 characters. Letters, numbers, and underscores only.</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
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
                                placeholder="Minimum 8 characters"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">
                            Confirm Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Re-enter your password"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-check me-1"></i> Register
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- ── Link to Login ─────────────────────────────────────────────── -->
        <p class="text-center mt-3 text-muted">
            Already have an account?
            <a href="/SocialNetwork/public/?url=auth/login" class="fw-semibold">Log in here</a>
        </p>

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
