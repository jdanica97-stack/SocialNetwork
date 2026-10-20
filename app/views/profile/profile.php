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

                    <?php if (!empty($isOwnProfile)): ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <a href="/SocialNetwork/public/?url=profile/edit" class="btn btn-ghost btn-sm" id="editProfileBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                            <button type="button" class="btn btn-ghost btn-sm" id="accountSettingsToggleBtn" aria-expanded="false" aria-controls="accountSettingsSection">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Account Settings
                            </button>
                        </div>
                    <?php endif; ?>
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

            <!-- ── Expandable Account Settings Drawer (Hidden by default, only on own profile) ── -->
            <?php if (!empty($isOwnProfile)): ?>
            <div id="accountSettingsSection" class="account-settings-drawer" style="display: none;">
                <div class="p-3 p-md-4">
                    <div class="row justify-content-center">
                        <div class="col-12" style="max-width: 680px;">

                            <!-- ── Card 1: Account Information ───────────────────────── -->
                            <div class="card glass mb-4" style="background: var(--surface-card); border: 1px solid var(--border);">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div style="width:32px;height:32px;border-radius:8px;background:var(--surface-input);display:flex;align-items:center;justify-content:center;color:var(--fg);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </div>
                                        <div>
                                            <h2 class="h5 mb-0" style="font-weight:600;color:var(--fg);">Account Information</h2>
                                            <span class="small text-muted">Update your full name and public @username</span>
                                        </div>
                                    </div>

                                    <form method="POST" action="/SocialNetwork/public/?url=profile/account" id="profileAccountInfoForm" novalidate>
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
                                            >
                                            <div class="form-text small" style="color:var(--fg-faint);">1–100 characters.</div>
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
                                                >
                                            </div>
                                            <div class="form-text small" style="color:var(--fg-faint);">3–50 characters. Letters, numbers, and underscores only.</div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary btn-sm px-4" id="saveAccountInfoBtn">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- ── Card 2: Change Password ───────────────────────────── -->
                            <div class="card glass mb-2" style="background: var(--surface-card); border: 1px solid var(--border);">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div style="width:32px;height:32px;border-radius:8px;background:var(--surface-input);display:flex;align-items:center;justify-content:center;color:var(--fg);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </div>
                                        <div>
                                            <h2 class="h5 mb-0" style="font-weight:600;color:var(--fg);">Change Password</h2>
                                            <span class="small text-muted">Ensure your account is protected with a secure password</span>
                                        </div>
                                    </div>

                                    <form method="POST" action="/SocialNetwork/public/?url=profile/password" id="profileChangePasswordForm">
                                        <?= Csrf::field() ?>

                                        <!-- Current Password -->
                                        <div class="mb-3">
                                            <label for="profileCurrentPw" class="form-label small fw-medium" style="color:var(--fg-muted);">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" id="profileCurrentPw" name="current_password" class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
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
                                                <input type="password" id="profileNewPw" name="new_password" class="form-control" placeholder="At least 8 characters" minlength="8" required autocomplete="new-password">
                                                <button class="btn btn-ghost toggle-password-btn" type="button" data-target="profileNewPw" aria-label="Toggle password visibility">
                                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                            <div class="form-text small" style="color:var(--fg-faint);">Must be at least 8 characters long.</div>
                                        </div>

                                        <!-- Confirm New Password -->
                                        <div class="mb-4">
                                            <label for="profileConfirmPw" class="form-label small fw-medium" style="color:var(--fg-muted);">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" id="profileConfirmPw" name="confirm_password" class="form-control" placeholder="Re-type new password" minlength="8" required autocomplete="new-password">
                                                <button class="btn btn-ghost toggle-password-btn" type="button" data-target="profileConfirmPw" aria-label="Toggle password visibility">
                                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary btn-sm px-4" id="savePasswordBtn">Update Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Posts Section Header -->
            <div class="profile-section-label d-flex align-items-center justify-content-between">
                <span>Posts</span>
                <span class="small text-muted fw-normal">
                    <?= count($posts) ?> <?= count($posts) === 1 ? 'post' : 'posts' ?>
                </span>
            </div>

            <!-- Profile Posts Stream -->
            <div class="profile-posts-stream p-3 p-md-4">
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 10px; opacity: 0.5;" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <p class="mb-0 small">No posts published yet.</p>
                        <?php if ($isOwnProfile): ?>
                            <a href="/SocialNetwork/public/" class="btn btn-primary btn-sm mt-3">
                                Create your first post
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $postId       = (int) $post['id'];
                        $postAuthorId = (int) $post['user_id'];
                        $postComments = $commentsByPost[$postId] ?? [];
                        $commentCount = $commentCountByPost[$postId] ?? count($postComments);
                        $isPostOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === $postAuthorId);

                        // Author avatar with fallback
                        if (!empty($post['profile_image'])) {
                            $postAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($post['profile_image']);
                        } else {
                            $postAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($post['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                        }

                        $relativeTime  = PostController::timeAgo($post['created_at']);
                        $formattedDate = date('M j, Y \a\t g:i A', strtotime($post['created_at']));
                        $likeCount     = $likeCountByPost[$postId] ?? 0;
                        $hasLiked      = $hasLikedByPost[$postId] ?? false;
                        $authorProfileUrl = '/SocialNetwork/public/?url=profile&id=' . $postAuthorId;
                        ?>

                        <article class="card post-card glass fade-up mb-3" id="post-<?= $postId ?>" style="background: var(--surface-card); border: 1px solid var(--border);">
                            <div class="card-body">

                                <!-- Post Header -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Clickable avatar -->
                                        <a href="<?= htmlspecialchars($authorProfileUrl, ENT_QUOTES, 'UTF-8') ?>" title="View profile">
                                            <img
                                                src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); display: block;"
                                                loading="lazy"
                                            >
                                        </a>
                                        <div>
                                            <!-- Clickable name -->
                                            <a href="<?= htmlspecialchars($authorProfileUrl, ENT_QUOTES, 'UTF-8') ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--fg); text-decoration: none;" class="post-author-link">
                                                <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <div style="font-size: 0.74rem; color: var(--fg-faint);">
                                                @<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> &bull;
                                                <time datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($relativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Post Owner Controls -->
                                    <?php if ($isPostOwner): ?>
                                        <div class="post-menu-wrap position-relative">
                                            <button type="button" class="post-menu-btn" aria-label="Post options" aria-expanded="false" title="More options">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/>
                                                </svg>
                                            </button>
                                            <div class="post-menu-dropdown glass" role="menu">
                                                <a href="/SocialNetwork/public/?url=posts/edit&id=<?= $postId ?>" class="post-menu-item" role="menuitem">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    <span>Edit Post</span>
                                                </a>
                                                <form method="POST" action="/SocialNetwork/public/?url=posts/delete" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                    <?= Csrf::field() ?>
                                                    <input type="hidden" name="id" value="<?= $postId ?>">
                                                    <button type="submit" class="post-menu-item delete-item w-100 border-0 bg-transparent text-start" role="menuitem">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                        <span>Delete Post</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Post Content -->
                                <div class="post-content-body" style="font-size: 0.94rem; line-height: 1.6; color: var(--fg); margin-bottom: 14px; word-break: break-word; overflow-wrap: anywhere;">
                                    <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>

                                <!-- Post Images Gallery -->
                                <?php require BASE_PATH . '/app/views/posts/_gallery.php'; ?>

                                <!-- Post Interactions -->
                                <div class="post-interactions">
                                    <button
                                        type="button"
                                        class="like-btn like-button <?= $hasLiked ? 'liked' : '' ?>"
                                        data-post-id="<?= $postId ?>"
                                        data-liked="<?= $hasLiked ? '1' : '0' ?>"
                                        title="<?= $hasLiked ? 'Unlike this post' : 'Like this post' ?>"
                                    >
                                        <span class="like-icon" aria-hidden="true">
                                            <?php if ($hasLiked): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                            <?php endif; ?>
                                        </span>
                                        <span class="like-text"><?= $hasLiked ? 'Unlike' : 'Like' ?></span>
                                        <span class="like-count"><?= $likeCount ?></span>
                                    </button>

                                    <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.82rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <span class="post-comment-count-text" id="post-comment-count-<?= $postId ?>"><?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?></span>
                                    </span>
                                </div>

                            </div><!-- /.card-body -->

                            <!-- Comments Section -->
                            <section class="comments-section" aria-label="Comments on post <?= $postId ?>">

                                <?php if ($commentCount > 0): ?>
                                    <!-- "View all" toggle shown when there are more than 2 -->
                                    <?php if ($commentCount > 2): ?>
                                        <div class="comments-expand-row">
                                            <button
                                                type="button"
                                                class="comments-toggle-btn"
                                                data-post-id="<?= $postId ?>"
                                                data-total="<?= $commentCount ?>"
                                                aria-expanded="false"
                                            >
                                                View all <?= $commentCount ?> comments
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="comments-list" id="comments-list-<?= $postId ?>">
                                        <?php foreach ($postComments as $comment): ?>
                                            <?php
                                            $commentId       = (int) $comment['id'];
                                            $commentAuthorId = (int) ($comment['author_id'] ?? $comment['user_id']);
                                            $isCommentOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $comment['user_id']);

                                            if (!empty($comment['profile_image'])) {
                                                $commenterAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($comment['profile_image']);
                                            } else {
                                                $commenterAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($comment['full_name']) . '&size=64&background=111113&color=f5f5f7&rounded=true&bold=true';
                                            }

                                            $commentRelativeTime = PostController::timeAgo($comment['created_at']);
                                            $commentFullDate     = date('M j, Y \a\t g:i A', strtotime($comment['created_at']));
                                            $commenterProfileUrl = '/SocialNetwork/public/?url=profile&id=' . $commentAuthorId;
                                            ?>

                                            <div class="comment-item" id="comment-<?= $commentId ?>">
                                                <div class="d-flex align-items-start gap-2">
                                                    <a href="<?= htmlspecialchars($commenterProfileUrl, ENT_QUOTES, 'UTF-8') ?>" title="View profile">
                                                        <img
                                                            src="<?= htmlspecialchars($commenterAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                            alt="<?= htmlspecialchars($comment['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                            class="comment-avatar"
                                                            loading="lazy"
                                                        >
                                                    </a>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="d-flex align-items-start justify-content-between gap-1">
                                                            <div class="min-w-0">
                                                                <a href="<?= htmlspecialchars($commenterProfileUrl, ENT_QUOTES, 'UTF-8') ?>" class="comment-author-link">
                                                                    <?= htmlspecialchars($comment['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                                </a>
                                                                <span class="comment-username">@<?= htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                                                <span class="comment-time" title="<?= htmlspecialchars($commentFullDate, ENT_QUOTES, 'UTF-8') ?>">
                                                                    &bull; <?= htmlspecialchars($commentRelativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                                <!-- Comment Content -->
                                                                <div class="comment-content" style="word-break: break-word; overflow-wrap: anywhere;">
                                                                    <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                                </div>
                                                            </div>

                                                            <!-- Comment Kebab Menu -->
                                                            <?php if ($isCommentOwner): ?>
                                                                <div class="comment-menu-wrap">
                                                                    <button type="button" class="comment-menu-btn" aria-label="Comment options" aria-expanded="false" title="More options">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                                                        </svg>
                                                                    </button>
                                                                    <div class="comment-menu-dropdown" role="menu">
                                                                        <a href="/SocialNetwork/public/?url=comments/edit&id=<?= $commentId ?>" class="comment-menu-item" role="menuitem">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                                            Edit
                                                                        </a>
                                                                        <form method="POST" action="/SocialNetwork/public/?url=comments/delete" class="m-0 p-0" onsubmit="return confirm('Delete this comment?');">
                                                                            <?= Csrf::field() ?>
                                                                            <input type="hidden" name="id" value="<?= $commentId ?>">
                                                                            <button type="submit" class="comment-menu-item comment-menu-delete w-100 border-0 bg-transparent text-start" role="menuitem">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                                                Delete
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Create Comment Form -->
                                <div class="comment-form-wrap">
                                    <form method="POST" action="/SocialNetwork/public/?url=comments/create">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                                        <div class="d-flex gap-2 align-items-center">
                                            <textarea
                                                name="content"
                                                class="form-control comment-input"
                                                rows="1"
                                                placeholder="Write a comment..."
                                                maxlength="1000"
                                                required
                                            ></textarea>
                                            <button type="submit" class="btn btn-ghost btn-sm comment-submit-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                                <span class="visually-hidden">Post comment</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </section>

                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div><!-- /.profile-card -->

    </div>
</div>

<?php if (!empty($isOwnProfile)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── 1. Account Settings Section Expand / Collapse Toggle ─────────────
    var toggleBtn       = document.getElementById('accountSettingsToggleBtn');
    var settingsSection = document.getElementById('accountSettingsSection');

    if (toggleBtn && settingsSection) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var isHidden = (settingsSection.style.display === 'none' || !settingsSection.classList.contains('visible'));
            if (isHidden) {
                settingsSection.style.display = 'block';
                // Force reflow for smooth transition
                void settingsSection.offsetHeight;
                settingsSection.classList.add('visible');
                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.classList.add('active');
            } else {
                settingsSection.classList.remove('visible');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.classList.remove('active');
                setTimeout(function () {
                    if (!settingsSection.classList.contains('visible')) {
                        settingsSection.style.display = 'none';
                    }
                }, 250);
            }
        });
    }

    // ── 2. Password Visibility Toggles ────────────────────────────────────
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
});
</script>
<?php endif; ?>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
