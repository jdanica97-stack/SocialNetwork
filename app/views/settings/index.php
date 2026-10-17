<?php
/**
 * app/views/settings/index.php
 *
 * Account Settings View — Manage username and password credentials.
 * Follows Orbit's monochrome glassmorphism design language.
 */
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 680px;">

        <!-- ── Page Header ────────────────────────────────────────────────────── -->
        <div class="mb-4 fade-up">
            <h1 class="h3 mb-1" style="font-weight: 700; letter-spacing: -0.02em; color: var(--fg);">
                Account Settings
            </h1>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                Manage your profile username and security credentials.
            </p>
        </div>

        <!-- ── Flash Messages ────────────────────────────────────────────────── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success fade-up visible mb-4" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger fade-up visible mb-4" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Card 1: Change Username ──────────────────────────────────────── -->
        <div class="card glass fade-up mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="side-panel-icon-wrap" style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-input); display: flex; align-items: center; justify-content: center; color: var(--fg);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h2 class="h5 mb-0" style="font-weight: 600; color: var(--fg);">Change Username</h2>
                        <span class="small text-muted">Your unique @handle across the community</span>
                    </div>
                </div>

                <div class="p-3 mb-3 rounded" style="background: var(--surface-input); border: 1px solid var(--border);">
                    <div class="small text-muted mb-1">Current username</div>
                    <div class="fw-semibold" style="color: var(--fg); font-size: 1rem;">
                        @<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <form method="POST" action="/SocialNetwork/public/?url=settings/username">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label for="newUsernameInput" class="form-label small fw-medium" style="color: var(--fg-muted);">
                            New Username
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--surface-input); border-color: var(--border); color: var(--fg-muted);">@</span>
                            <input
                                type="text"
                                id="newUsernameInput"
                                name="username"
                                class="form-control"
                                value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="50"
                                pattern="[a-zA-Z0-9_]{3,50}"
                                required
                                autocomplete="username"
                            >
                        </div>
                        <div class="form-text small" style="color: var(--fg-faint);">
                            3–50 characters. Letters, numbers, and underscores only.
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Save Username
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Card 2: Change Password ──────────────────────────────────────── -->
        <div class="card glass fade-up mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="side-panel-icon-wrap" style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-input); display: flex; align-items: center; justify-content: center; color: var(--fg);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h2 class="h5 mb-0" style="font-weight: 600; color: var(--fg);">Change Password</h2>
                        <span class="small text-muted">Ensure your account is protected with a secure password</span>
                    </div>
                </div>

                <form method="POST" action="/SocialNetwork/public/?url=settings/password" id="changePasswordForm">
                    <?= Csrf::field() ?>

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="currentPasswordInput" class="form-label small fw-medium" style="color: var(--fg-muted);">
                            Current Password
                        </label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="currentPasswordInput"
                                name="current_password"
                                class="form-control"
                                placeholder="Enter your current password"
                                required
                                autocomplete="current-password"
                            >
                            <button class="btn btn-ghost toggle-password-btn" type="button" data-target="currentPasswordInput" aria-label="Toggle password visibility">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="newPasswordInput" class="form-label small fw-medium" style="color: var(--fg-muted);">
                            New Password
                        </label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="newPasswordInput"
                                name="new_password"
                                class="form-control"
                                placeholder="At least 8 characters"
                                minlength="8"
                                required
                                autocomplete="new-password"
                            >
                            <button class="btn btn-ghost toggle-password-btn" type="button" data-target="newPasswordInput" aria-label="Toggle password visibility">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <div class="form-text small" style="color: var(--fg-faint);">
                            Must be at least 8 characters long.
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label for="confirmPasswordInput" class="form-label small fw-medium" style="color: var(--fg-muted);">
                            Confirm New Password
                        </label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="confirmPasswordInput"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Re-type new password"
                                minlength="8"
                                required
                                autocomplete="new-password"
                            >
                            <button class="btn btn-ghost toggle-password-btn" type="button" data-target="confirmPasswordInput" aria-label="Toggle password visibility">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password show/hide toggle handler
    document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;

            var eyeOpen = this.querySelector('.eye-open');
            var eyeClosed = this.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.classList.add('d-none');
                if (eyeClosed) eyeClosed.classList.remove('d-none');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('d-none');
                if (eyeClosed) eyeClosed.classList.add('d-none');
            }
        });
    });
});
</script>
