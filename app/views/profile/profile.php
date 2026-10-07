<?php

/**
 * app/views/profile/profile.php
 *
 * Displays the logged-in user's profile page — redesigned with monochrome glassmorphism.
 *
 * Variables provided by ProfileController::showProfile():
 *   $user         array        — user row from the database (NO password field)
 *   $flashSuccess string|null  — one-time success message
 *   $flashError   string|null  — one-time error message
 *   $pageTitle    string       — page <title>
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

// Helper: image URL or initials fallback
$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';

if (!empty($user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    $initials  = rawurlencode($user['full_name']);
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials
               . '&size=180&background=111113&color=f5f5f7&rounded=true&bold=true';
}
?>

<div class="row justify-content-center">
    <div class="col-12">

        <!-- ── Flash Messages ────────────────────────────────────────────── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success fade-up visible" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger fade-up visible" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Glass Profile Card ────────────────────────────────────────── -->
        <div class="card profile-card glass fade-up">

            <!-- Subtle monochrome banner -->
            <div class="profile-banner"></div>

            <div class="card-body pt-0" style="padding: 0 28px 28px !important;">

                <!-- Avatar & Edit Button Row -->
                <div class="profile-avatar-wrap">
                    <img
                        src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Profile picture of <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                        class="profile-avatar"
                    >

                    <a href="/SocialNetwork/public/?url=profile/edit" class="btn btn-ghost btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Profile
                    </a>
                </div>

                <!-- Name & Username -->
                <h2 class="profile-name">
                    <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <p class="profile-username">
                    @<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Bio -->
                <div class="profile-bio">
                    <?php if (!empty($user['bio'])): ?>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php else: ?>
                        <p class="mb-0 text-muted fst-italic">No bio yet. <a href="/SocialNetwork/public/?url=profile/edit">Add one?</a></p>
                    <?php endif; ?>
                </div>

                <!-- Account Information -->
                <div class="profile-meta">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Member since <?= htmlspecialchars(date('F j, Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                </div>

            </div><!-- /.card-body -->

            <!-- Posts Section Header -->
            <div class="profile-section-label">
                Posts
            </div>

            <!-- Posts Placeholder -->
            <div class="p-4 text-center text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 10px; opacity: 0.5;" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <p class="mb-0 small">Posts will appear here in Step 7.</p>
            </div>

        </div><!-- /.profile-card -->

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
