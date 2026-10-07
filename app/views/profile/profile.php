<?php

/**
 * app/views/profile/profile.php
 *
 * Displays the logged-in user's profile page.
 *
 * Variables provided by ProfileController::showProfile():
 *   $user         array        — user row from the database (NO password field)
 *   $flashSuccess string|null  — one-time success message
 *   $flashError   string|null  — one-time error message
 *   $pageTitle    string       — page <title>
 *
 * Rules:
 *   - No SQL queries here.
 *   - No business logic here.
 *   - ALL user-supplied values are escaped with htmlspecialchars() before output.
 *   - The password field is never available here (excluded in UserModel::findById).
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

// ── Helper: build the image URL or fall back to a placeholder ─────────────────
// The web path to uploaded profile images
$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';

// Use the stored filename, or show a generic avatar icon via UI Avatars
if (!empty($user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    // UI Avatars is a free service that generates initials-based avatar images.
    // We embed the user's full name as a URL parameter.
    $initials  = rawurlencode($user['full_name']);
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials
               . '&size=160&background=0d6efd&color=fff&rounded=true&bold=true';
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">

        <!-- ── Flash Messages ────────────────────────────────────────────── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Profile Card ──────────────────────────────────────────────── -->
        <div class="card shadow-sm">

            <!-- Blue banner -->
            <div class="bg-primary rounded-top" style="height: 90px;"></div>

            <div class="card-body pt-0">

                <!-- ── Avatar ──────────────────────────────────────────── -->
                <div class="d-flex justify-content-between align-items-end mb-3"
                     style="margin-top: -50px;">

                    <img
                        src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Profile picture of <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                        class="rounded-circle border border-4 border-white shadow-sm bg-white"
                        style="width: 100px; height: 100px; object-fit: cover;"
                    >

                    <!-- Edit Profile button (top right) -->
                    <a href="/SocialNetwork/public/?url=profile/edit"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                </div>

                <!-- ── Name & Username ─────────────────────────────────── -->
                <h4 class="mb-0 fw-bold">
                    <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>
                </h4>
                <p class="text-muted mb-3">
                    <i class="bi bi-at"></i><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- ── Bio ────────────────────────────────────────────── -->
                <div class="mb-3">
                    <?php if (!empty($user['bio'])): ?>
                        <p class="mb-0">
                            <?= nl2br(htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted fst-italic mb-0">
                            No bio yet.
                            <a href="/SocialNetwork/public/?url=profile/edit">Add one?</a>
                        </p>
                    <?php endif; ?>
                </div>

                <hr>

                <!-- ── Account Information ────────────────────────────── -->
                <div class="row text-muted small">
                    <div class="col-auto">
                        <i class="bi bi-calendar3 me-1"></i>
                        Member since
                        <?= htmlspecialchars(
                                date('F j, Y', strtotime($user['created_at'])),
                                ENT_QUOTES, 'UTF-8'
                            ) ?>
                    </div>
                </div>

            </div><!-- /.card-body -->
        </div><!-- /.card -->

        <!-- ── Posts Placeholder (Step 7) ───────────────────────────────── -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-grid-3x3-gap me-2"></i>Posts
            </div>
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-file-post" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">Posts will appear here in Step 7.</p>
            </div>
        </div>

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
