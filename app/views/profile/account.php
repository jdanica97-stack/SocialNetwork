<?php
/**
 * app/views/profile/account.php
 *
 * Dedicated Manage Account Page.
 * Displays Account Information and Change Password interfaces
 * with zero profile posts underneath.
 *
 * Variables provided by ProfileController::showAccount():
 *   $user          array   — Current user account row
 *   $flashSuccess  ?string — Success flash notification
 *   $flashError    ?string — Error flash notification
 *   $pageTitle     string  — Page <title>
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';
if (!empty($user['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    $initials  = rawurlencode($user['full_name'] ?? 'User');
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials . '&size=120&background=111113&color=f5f5f7&rounded=true&bold=true';
}
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 960px;">

        <!-- ── Navigation Bar / Back Link ─────────────────────────────────── -->
        <div class="d-flex align-items-center justify-content-between mb-3 fade-up">
            <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost btn-sm" id="backToProfileBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Profile
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="/SocialNetwork/public/?url=profile/edit" class="btn btn-ghost btn-sm" id="editProfileNavBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </a>
                <a href="/SocialNetwork/public/?url=profile" class="btn btn-outline-secondary btn-sm" id="viewProfileBtn">
                    View Profile
                </a>
            </div>
        </div>

        <!-- ── Flash Notifications ────────────────────────────────────────── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show fade-up mb-4" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2" style="vertical-align: -2px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show fade-up mb-4" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2" style="vertical-align: -2px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Dedicated Manage Account Glass Card ────────────────────────── -->
        <div class="card glass manage-account-card fade-up mb-4" style="background: var(--surface-card); border: 1px solid var(--border);">
            <div class="card-body p-3 p-md-4">

                <!-- Header Banner -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <img
                            src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="rounded-circle"
                            style="width: 44px; height: 44px; object-fit: cover; border: 2px solid var(--border);"
                        >
                        <div>
                            <h1 class="h5 mb-0" style="font-weight: 700; color: var(--fg); letter-spacing: -0.01em;">
                                Manage Account
                            </h1>
                            <span class="small text-muted">Update your credentials, name, and security settings</span>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2" style="font-size: 0.8rem; border-radius: var(--radius-pill);">
                            @<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>

                <!-- ── Two Column Layout: Account Information & Change Password ─ -->
                <div class="row g-4 align-items-stretch">

                    <!-- Left Column: Account Information -->
                    <div class="col-12 col-lg-6 d-flex flex-column">
                        <div class="manage-account-section p-3 p-md-4 rounded-3 d-flex flex-column h-100" style="background: var(--surface); border: 1px solid var(--border);">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--surface-input);display:flex;align-items:center;justify-content:center;color:var(--fg);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <h2 class="h6 mb-0" style="font-weight:600;color:var(--fg);">Account Information</h2>
                                    <span class="small text-muted" style="font-size:0.75rem;">Full name & public @username</span>
                                </div>
                            </div>

                            <form method="POST" action="/SocialNetwork/public/?url=profile/account" id="profileAccountInfoForm" novalidate class="d-flex flex-column flex-grow-1">
                                <?= Csrf::field() ?>

                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label for="accountFullName" class="form-label small fw-medium" style="color:var(--fg-muted);">Full Name</label>
                                    <input
                                        type="text"
                                        id="accountFullName"
                                        name="full_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        maxlength="100"
                                        required
                                        autocomplete="name"
                                        placeholder="Enter full name"
                                    >
                                    <div class="form-text small" style="color:var(--fg-faint);">1–100 characters. Visible on your posts and profile.</div>
                                </div>

                                <!-- Username -->
                                <div class="mb-4">
                                    <label for="accountUsername" class="form-label small fw-medium" style="color:var(--fg-muted);">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="background:var(--surface-input);border-color:var(--border);color:var(--fg-muted);">@</span>
                                        <input
                                            type="text"
                                            id="accountUsername"
                                            name="username"
                                            class="form-control"
                                            value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            maxlength="50"
                                            pattern="[a-zA-Z0-9_]{3,50}"
                                            required
                                            autocomplete="username"
                                            placeholder="Enter username"
                                        >
                                    </div>
                                    <div class="form-text small" style="color:var(--fg-faint);">3–50 characters. Letters, numbers, and underscores only.</div>
                                </div>

                                <div class="mt-auto pt-2 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-4" id="saveAccountInfoBtn">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Change Password -->
                    <div class="col-12 col-lg-6 d-flex flex-column">
                        <div class="manage-account-section p-3 p-md-4 rounded-3 d-flex flex-column h-100" style="background: var(--surface); border: 1px solid var(--border);">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--surface-input);display:flex;align-items:center;justify-content:center;color:var(--fg);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </div>
                                <div>
                                    <h2 class="h6 mb-0" style="font-weight:600;color:var(--fg);">Change Password</h2>
                                    <span class="small text-muted" style="font-size:0.75rem;">Protect your account with a secure password</span>
                                </div>
                            </div>

                            <form method="POST" action="/SocialNetwork/public/?url=profile/password" id="profileChangePasswordForm" class="d-flex flex-column flex-grow-1" novalidate>
                                <?= Csrf::field() ?>

                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label for="profileCurrentPw" class="form-label small fw-medium" style="color:var(--fg-muted);">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" id="profileCurrentPw" name="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                                        <button class="btn btn-ghost toggle-password-btn" type="button" data-target="profileCurrentPw" aria-label="Toggle password visibility">
                                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="profileNewPw" class="form-label small fw-medium" style="color:var(--fg-muted);">New Password</label>
                                    <div class="input-group">
                                        <input type="password" id="profileNewPw" name="new_password" class="form-control" placeholder="Enter new password" required autocomplete="new-password" aria-describedby="profilePasswordRequirements">
                                        <button class="btn btn-ghost toggle-password-btn" type="button" data-target="profileNewPw" aria-label="Toggle password visibility">
                                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>

                                    <!-- Password Requirements Checklist -->
                                    <div id="profilePasswordRequirements" class="password-checklist mt-2" aria-live="polite">
                                        <div class="checklist-title">Password Requirements</div>
                                        <ul class="checklist-items">
                                            <li id="profile-req-length" class="req-item unmet" aria-checked="false" role="checkbox">
                                                <span class="req-icon" aria-hidden="true"></span>
                                                <span class="req-label">At least 8 characters long</span>
                                            </li>
                                            <li id="profile-req-number" class="req-item unmet" aria-checked="false" role="checkbox">
                                                <span class="req-icon" aria-hidden="true"></span>
                                                <span class="req-label">Contains a number</span>
                                            </li>
                                            <li id="profile-req-special" class="req-item unmet" aria-checked="false" role="checkbox">
                                                <span class="req-icon" aria-hidden="true"></span>
                                                <span class="req-label">Contains a special character</span>
                                            </li>
                                            <li id="profile-req-uppercase" class="req-item unmet" aria-checked="false" role="checkbox">
                                                <span class="req-icon" aria-hidden="true"></span>
                                                <span class="req-label">Contains an uppercase letter</span>
                                            </li>
                                            <li id="profile-req-lowercase" class="req-item unmet" aria-checked="false" role="checkbox">
                                                <span class="req-icon" aria-hidden="true"></span>
                                                <span class="req-label">Contains a lowercase letter</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="mb-4">
                                    <label for="profileConfirmPw" class="form-label small fw-medium" style="color:var(--fg-muted);">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" id="profileConfirmPw" name="confirm_password" class="form-control" placeholder="Confirm new password" required autocomplete="new-password" aria-describedby="profileConfirmFeedback">
                                        <button class="btn btn-ghost toggle-password-btn" type="button" data-target="profileConfirmPw" aria-label="Toggle password visibility">
                                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                    <div id="profileConfirmFeedback" class="password-match-feedback" aria-live="polite"></div>
                                </div>

                                <div class="mt-auto pt-2 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-4" id="savePasswordBtn" disabled>Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div><!-- /.row -->

            </div>
        </div>

    </div>
</div>

<script src="/SocialNetwork/public/assets/js/password-validator.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle
    document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;
            var eyeOpen   = this.querySelector('.eye-open');
            var eyeClosed = this.querySelector('.eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen)   eyeOpen.classList.add('d-none');
                if (eyeClosed) eyeClosed.classList.remove('d-none');
            } else {
                input.type = 'password';
                if (eyeOpen)   eyeOpen.classList.remove('d-none');
                if (eyeClosed) eyeClosed.classList.add('d-none');
            }
        });
    });

    // Password complexity validation
    var curPwInput     = document.getElementById('profileCurrentPw');
    var newPwInput     = document.getElementById('profileNewPw');
    var confirmPwInput = document.getElementById('profileConfirmPw');
    var submitBtn      = document.getElementById('savePasswordBtn');
    var feedbackEl     = document.getElementById('profileConfirmFeedback');

    var reqLength    = document.getElementById('profile-req-length');
    var reqNumber    = document.getElementById('profile-req-number');
    var reqSpecial   = document.getElementById('profile-req-special');
    var reqUppercase = document.getElementById('profile-req-uppercase');
    var reqLowercase = document.getElementById('profile-req-lowercase');

    function updateRequirement(el, isValid) {
        if (!el) return;
        if (isValid) {
            el.classList.remove('unmet');
            el.classList.add('met');
            el.setAttribute('aria-checked', 'true');
        } else {
            el.classList.remove('met');
            el.classList.add('unmet');
            el.setAttribute('aria-checked', 'false');
        }
    }

    function checkPasswordComplexity() {
        var pw = newPwInput ? newPwInput.value : '';
        var hasLength    = pw.length >= 8;
        var hasNumber    = /[0-9]/.test(pw);
        var hasSpecial   = /[^a-zA-Z0-9]/.test(pw);
        var hasUppercase = /[A-Z]/.test(pw);
        var hasLowercase = /[a-z]/.test(pw);

        updateRequirement(reqLength, hasLength);
        updateRequirement(reqNumber, hasNumber);
        updateRequirement(reqSpecial, hasSpecial);
        updateRequirement(reqUppercase, hasUppercase);
        updateRequirement(reqLowercase, hasLowercase);

        return (hasLength && hasNumber && hasSpecial && hasUppercase && hasLowercase);
    }

    function checkPasswordMatch() {
        var newPw     = newPwInput ? newPwInput.value : '';
        var confirmPw = confirmPwInput ? confirmPwInput.value : '';

        if (!feedbackEl) return false;

        if (confirmPw === '') {
            feedbackEl.textContent = '';
            feedbackEl.className = 'password-match-feedback';
            return false;
        }

        if (newPw === confirmPw) {
            feedbackEl.textContent = 'Passwords match';
            feedbackEl.className = 'password-match-feedback match';
            return true;
        } else {
            feedbackEl.textContent = 'Passwords do not match';
            feedbackEl.className = 'password-match-feedback mismatch';
            return false;
        }
    }

    function validateAll() {
        var isComplex = checkPasswordComplexity();
        var isMatch   = checkPasswordMatch();
        var hasCur    = curPwInput ? curPwInput.value.trim().length > 0 : false;
        if (submitBtn) {
            submitBtn.disabled = !(isComplex && isMatch && hasCur);
        }
    }

    if (newPwInput)     newPwInput.addEventListener('input', validateAll);
    if (confirmPwInput) confirmPwInput.addEventListener('input', validateAll);
    if (curPwInput)     curPwInput.addEventListener('input', validateAll);
});
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
