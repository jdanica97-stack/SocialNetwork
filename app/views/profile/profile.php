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

// Cover photo
$coverPhotoSrc = null;
if (!empty($user['cover_photo']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $user['cover_photo'])) {
    $coverPhotoSrc = $profileImageWebPath . rawurlencode($user['cover_photo']);
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
            <div class="profile-banner" id="coverPhotoBanner">
                <div class="profile-banner-media" id="coverPhotoMedia">
                    <?php if ($coverPhotoSrc): ?>
                        <img src="<?= htmlspecialchars($coverPhotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Cover photo" id="coverPhotoImg">
                    <?php endif; ?>
                </div>

                <?php if (!empty($isOwnProfile)): ?>
                    <div class="profile-cover-actions">
                        <div class="cover-dropdown-wrapper" id="coverDropdownWrapper">
                            <button
                                type="button"
                                class="cover-edit-btn"
                                id="editCoverDropdownBtn"
                                aria-expanded="false"
                                aria-haspopup="true"
                                aria-controls="coverDropdownMenu"
                                title="Edit cover photo"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                <span class="cover-btn-label">Edit Cover Photo</span>
                            </button>
                            <div class="cover-dropdown-menu" id="coverDropdownMenu" role="menu">
                                <div class="sheet-header">
                                    <div class="sheet-handle"></div>
                                    <span class="sheet-title">Cover Photo</span>
                                </div>
                                <button type="button" class="cover-dropdown-item" id="btnUploadCover" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span id="coverUploadActionText"><?= $coverPhotoSrc ? 'Change Cover Photo' : 'Upload Cover Photo' ?></span>
                                </button>
                                <button
                                    type="button"
                                    class="cover-dropdown-item item-danger"
                                    id="btnRemoveCover"
                                    role="menuitem"
                                    style="display: <?= $coverPhotoSrc ? 'flex' : 'none' ?>;"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    <span>Remove Cover Photo</span>
                                </button>
                                <button type="button" class="sheet-cancel-btn" id="cancelCoverSheetBtn">Cancel</button>
                            </div>
                        </div>
                        <input type="file" id="coverPhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none" aria-label="Upload cover photo">
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-body pt-0" style="padding: 0 28px 28px !important;">

                <!-- Avatar & Edit Button Row -->
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar-container">
                        <img
                            src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Profile picture of <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="profile-avatar"
                            id="profileAvatarImg"
                        >
                        <?php if (!empty($isOwnProfile)): ?>
                            <div class="avatar-action-dropdown" id="avatarDropdownWrapper">
                                <button
                                    type="button"
                                    class="avatar-edit-badge"
                                    id="editAvatarDropdownBtn"
                                    title="Manage profile picture"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    aria-controls="avatarDropdownMenu"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                </button>
                                <div class="avatar-dropdown-menu" id="avatarDropdownMenu" role="menu">
                                    <div class="sheet-header">
                                        <div class="sheet-handle"></div>
                                        <span class="sheet-title">Profile Picture</span>
                                    </div>
                                    <button type="button" class="avatar-dropdown-item" id="btnChangeAvatar" role="menuitem">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        <span id="avatarChangeActionText"><?= !empty($user['profile_image']) ? 'Change Profile Picture' : 'Upload Profile Picture' ?></span>
                                    </button>
                                    <button
                                        type="button"
                                        class="avatar-dropdown-item item-danger"
                                        id="btnRemoveAvatar"
                                        role="menuitem"
                                        style="display: <?= !empty($user['profile_image']) ? 'flex' : 'none' ?>;"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        <span>Remove Profile Picture</span>
                                    </button>
                                    <button type="button" class="sheet-cancel-btn" id="cancelAvatarSheetBtn">Cancel</button>
                                </div>
                            </div>
                            <input type="file" id="avatarPhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none" aria-label="Upload profile picture">
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($isOwnProfile)): ?>
                        <div class="profile-actions-wrap d-flex gap-2 align-items-center flex-wrap">
                            <a href="/SocialNetwork/public/?url=profile/edit" class="btn btn-ghost btn-sm" id="editProfileBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                            <a href="/SocialNetwork/public/?url=profile/account" class="btn btn-ghost btn-sm" id="manageAccountBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Manage Account
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="profile-actions-wrap d-flex gap-2 align-items-center flex-wrap">
                            <!-- Follow / Unfollow Button -->
                            <button
                                type="button"
                                class="btn <?= !empty($isFollowing) ? 'btn-ghost' : 'btn-primary' ?> btn-sm follow-toggle-btn px-3"
                                id="profileFollowBtn"
                                data-user-id="<?= (int)$user['id'] ?>"
                                data-following="<?= !empty($isFollowing) ? '1' : '0' ?>"
                                style="border-radius: var(--radius-pill);"
                            >
                                <span class="follow-btn-label"><?= !empty($isFollowing) ? 'Following' : 'Follow' ?></span>
                            </button>

                            <!-- Send Message Button -->
                            <a
                                href="/SocialNetwork/public/?url=messages&user_id=<?= (int)$user['id'] ?>"
                                class="btn btn-ghost btn-sm px-3"
                                id="profileSendMessageBtn"
                                title="Send Message to <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                style="border-radius: var(--radius-pill);"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="me-1"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Send Message
                            </a>
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

                <!-- Follower & Following Stats Links -->
                <div class="profile-stats d-flex align-items-center gap-4 mt-3 pt-3 border-top" style="border-color: var(--border) !important;">
                    <a href="/SocialNetwork/public/?url=followers&user_id=<?= (int)$user['id'] ?>" class="text-decoration-none d-flex align-items-baseline gap-1" style="color: var(--fg);" title="View followers">
                        <strong id="profileFollowerCount" style="font-size: 1.05rem;"><?= (int)($followerCount ?? 0) ?></strong>
                        <span style="color: var(--fg-muted); font-size: 0.85rem;">Followers</span>
                    </a>
                    <a href="/SocialNetwork/public/?url=following&user_id=<?= (int)$user['id'] ?>" class="text-decoration-none d-flex align-items-baseline gap-1" style="color: var(--fg);" title="View following">
                        <strong id="profileFollowingCount" style="font-size: 1.05rem;"><?= (int)($followingCount ?? 0) ?></strong>
                        <span style="color: var(--fg-muted); font-size: 0.85rem;">Following</span>
                    </a>
                </div>

            </div><!-- /.card-body -->

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    var profileFollowBtn = document.getElementById('profileFollowBtn');
    if (profileFollowBtn) {
        profileFollowBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var btn = this;
            var userId = btn.getAttribute('data-user-id');
            if (!userId) return;

            btn.disabled = true;
            var formData = new FormData();
            formData.append('user_id', userId);
            if (csrfToken) formData.append('csrf_token', csrfToken);

            fetch('/SocialNetwork/public/?url=follow/toggle', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.disabled = false;
                if (data.success) {
                    var newStatus = data.is_following;
                    btn.setAttribute('data-following', newStatus ? '1' : '0');

                    var label = btn.querySelector('.follow-btn-label');
                    if (label) {
                        label.textContent = newStatus ? 'Following' : 'Follow';
                    } else {
                        btn.textContent = newStatus ? 'Following' : 'Follow';
                    }

                    if (newStatus) {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-ghost');
                    } else {
                        btn.classList.remove('btn-ghost');
                        btn.classList.add('btn-primary');
                    }

                    var counterEl = document.getElementById('profileFollowerCount');
                    if (counterEl && typeof data.follower_count !== 'undefined') {
                        counterEl.textContent = data.follower_count;
                    }
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(function () {
                btn.disabled = false;
                alert('An error occurred. Please try again.');
            });
        });
    }
});
</script>


<?php if (!empty($isOwnProfile)): ?>
<!-- ── Cover Photo Cropper Modal ─────────────────────────────────────── -->
<div id="coverCropModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.78);align-items:center;justify-content:center;backdrop-filter:blur(6px);" role="dialog" aria-modal="true" aria-label="Crop cover photo">
    <div class="cropper-modal-dialog" style="background:var(--surface-card);border-radius:18px;padding:22px;max-width:720px;width:95%;max-height:92vh;overflow:auto;box-shadow:0 18px 48px rgba(0,0,0,0.45);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <h3 style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Crop Cover Photo</h3>
            </div>
            <button type="button" id="coverCropClose" style="background:none;border:none;cursor:pointer;color:var(--fg-muted);font-size:1.4rem;line-height:1;" aria-label="Close">&times;</button>
        </div>
        <div class="crop-image-container" style="max-height:380px;overflow:hidden;border-radius:10px;background:#0d0d10;">
            <img id="coverCropImage" src="" alt="Cover photo to crop" style="max-width:100%;display:block;">
        </div>

        <!-- Zoom & Positioning Toolbar -->
        <div class="cropper-toolbar">
            <button type="button" class="cropper-tool-btn" id="coverZoomIn" title="Zoom In">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom In
            </button>
            <button type="button" class="cropper-tool-btn" id="coverZoomOut" title="Zoom Out">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom Out
            </button>
            <button type="button" class="cropper-tool-btn" id="coverReset" title="Reset Crop">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reset
            </button>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button type="button" id="coverCropCancel" class="btn btn-ghost btn-sm px-3">Cancel</button>
            <button type="button" id="coverCropApply" class="btn btn-primary btn-sm px-4">Apply &amp; Upload</button>
        </div>
        <p id="coverCropStatus" style="font-size:0.8rem;color:var(--fg-muted);margin-top:8px;text-align:center;min-height:1em;"></p>
    </div>
</div>

<!-- ── Avatar Cropper Modal (Direct on Profile) ────────────────────────── -->
<div id="avatarCropModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.78);align-items:center;justify-content:center;backdrop-filter:blur(6px);" role="dialog" aria-modal="true" aria-label="Crop profile picture">
    <div class="cropper-modal-dialog" style="background:var(--surface-card);border-radius:18px;padding:22px;max-width:540px;width:95%;max-height:92vh;overflow:auto;box-shadow:0 18px 48px rgba(0,0,0,0.45);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg);"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/></svg>
                <h3 style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Crop Profile Picture</h3>
            </div>
            <button type="button" id="avatarCropClose" style="background:none;border:none;cursor:pointer;color:var(--fg-muted);font-size:1.4rem;line-height:1;" aria-label="Close">&times;</button>
        </div>

        <div class="crop-image-container cropper-circular" style="max-height:360px;overflow:hidden;border-radius:10px;background:#0d0d10;">
            <img id="avatarCropImage" src="" alt="Profile picture to crop" style="max-width:100%;display:block;">
        </div>

        <!-- Zoom & Positioning Toolbar -->
        <div class="cropper-toolbar">
            <button type="button" class="cropper-tool-btn" id="avatarZoomIn" title="Zoom In">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom In
            </button>
            <button type="button" class="cropper-tool-btn" id="avatarZoomOut" title="Zoom Out">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom Out
            </button>
            <button type="button" class="cropper-tool-btn" id="avatarReset" title="Reset Crop">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reset
            </button>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button type="button" id="avatarCropCancel" class="btn btn-ghost btn-sm px-3">Cancel</button>
            <button type="button" id="avatarCropApply" class="btn btn-primary btn-sm px-4">Apply &amp; Upload</button>
        </div>
        <p id="avatarCropStatus" style="font-size:0.8rem;color:var(--fg-muted);margin-top:8px;text-align:center;min-height:1em;"></p>
    </div>
</div>

<!-- ── Confirmation Modal: Remove Cover Photo ────────────────────────── -->
<div id="confirmRemoveCoverModal" class="custom-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="removeCoverModalTitle">
    <div class="confirm-modal-dialog">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <h4 id="removeCoverModalTitle" style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Remove Cover Photo?</h4>
        </div>
        <p style="font-size:0.85rem;color:var(--fg-muted);margin-bottom:20px;line-height:1.5;">
            Are you sure you want to remove your cover photo? Your profile will revert to the default background theme.
        </p>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-ghost btn-sm px-3" id="cancelRemoveCoverBtn">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm px-4" id="confirmRemoveCoverBtn">Remove</button>
        </div>
    </div>
</div>

<!-- ── Confirmation Modal: Remove Profile Picture ───────────────────── -->
<div id="confirmRemoveAvatarModal" class="custom-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="removeAvatarModalTitle">
    <div class="confirm-modal-dialog">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <h4 id="removeAvatarModalTitle" style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Remove Profile Picture?</h4>
        </div>
        <p style="font-size:0.85rem;color:var(--fg-muted);margin-bottom:20px;line-height:1.5;">
            Are you sure you want to remove your profile picture? Your profile will revert to your default initials avatar.
        </p>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-ghost btn-sm px-3" id="cancelRemoveAvatarBtn">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm px-4" id="confirmRemoveAvatarBtn">Remove</button>
        </div>
    </div>
</div>
<div class="mobile-action-sheet-backdrop" id="actionSheetBackdrop"></div>
<?php endif; ?>
<!-- Local Cropper.js Stylesheet & Script -->
<link rel="stylesheet" href="/SocialNetwork/public/assets/css/cropper.min.css">
<script src="/SocialNetwork/public/assets/js/cropper.min.js"></script>

<script>
(function () {
    'use strict';

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showToast(msg, type) {
        var toast = document.getElementById('profile-action-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'profile-action-toast';
            toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 22px;border-radius:999px;font-size:0.85rem;font-weight:500;z-index:99999;box-shadow:0 6px 20px rgba(0,0,0,0.3);transition:opacity 0.25s ease,transform 0.25s ease;pointer-events:none;';
            document.body.appendChild(toast);
        }
        toast.style.background = type === 'success' ? 'rgba(34,197,94,0.94)' : 'rgba(239,68,68,0.94)';
        toast.style.color = '#fff';
        toast.textContent = msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(8px)';
        }, 3200);
    }

    // ── Dropdown Menus & Mobile Action Sheet ───────────────────────────────────
    var coverDropdownBtn    = document.getElementById('editCoverDropdownBtn');
    var coverDropdownMenu   = document.getElementById('coverDropdownMenu');
    var avatarDropdownBtn   = document.getElementById('editAvatarDropdownBtn');
    var avatarDropdownMenu  = document.getElementById('avatarDropdownMenu');
    var actionSheetBackdrop = document.getElementById('actionSheetBackdrop');
    var cancelCoverSheetBtn = document.getElementById('cancelCoverSheetBtn');
    var cancelAvatarSheetBtn = document.getElementById('cancelAvatarSheetBtn');

    function closeAllDropdowns() {
        if (coverDropdownMenu) coverDropdownMenu.classList.remove('show');
        if (coverDropdownBtn) coverDropdownBtn.setAttribute('aria-expanded', 'false');
        if (avatarDropdownMenu) avatarDropdownMenu.classList.remove('show');
        if (avatarDropdownBtn) avatarDropdownBtn.setAttribute('aria-expanded', 'false');
        if (actionSheetBackdrop) actionSheetBackdrop.classList.remove('show');
    }

    if (coverDropdownBtn && coverDropdownMenu) {
        coverDropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = coverDropdownMenu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                coverDropdownMenu.classList.add('show');
                coverDropdownBtn.setAttribute('aria-expanded', 'true');
                if (actionSheetBackdrop) actionSheetBackdrop.classList.add('show');
            }
        });
    }

    if (avatarDropdownBtn && avatarDropdownMenu) {
        avatarDropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = avatarDropdownMenu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                avatarDropdownMenu.classList.add('show');
                avatarDropdownBtn.setAttribute('aria-expanded', 'true');
                if (actionSheetBackdrop) actionSheetBackdrop.classList.add('show');
            }
        });
    }

    if (actionSheetBackdrop) {
        actionSheetBackdrop.addEventListener('click', function () {
            closeAllDropdowns();
        });
    }

    if (cancelCoverSheetBtn) {
        cancelCoverSheetBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAllDropdowns();
        });
    }

    if (cancelAvatarSheetBtn) {
        cancelAvatarSheetBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAllDropdowns();
        });
    }

    document.addEventListener('click', function (e) {
        if (coverDropdownMenu && !coverDropdownMenu.contains(e.target) && e.target !== coverDropdownBtn) {
            coverDropdownMenu.classList.remove('show');
            if (coverDropdownBtn) coverDropdownBtn.setAttribute('aria-expanded', 'false');
        }
        if (avatarDropdownMenu && !avatarDropdownMenu.contains(e.target) && e.target !== avatarDropdownBtn) {
            avatarDropdownMenu.classList.remove('show');
            if (avatarDropdownBtn) avatarDropdownBtn.setAttribute('aria-expanded', 'false');
        }
        if (actionSheetBackdrop && !coverDropdownMenu.classList.contains('show') && !avatarDropdownMenu.classList.contains('show')) {
            actionSheetBackdrop.classList.remove('show');
        }
    });

    // ── 1. Cover Photo Management ─────────────────────────────────────────────
    var coverInput      = document.getElementById('coverPhotoInput');
    var btnUploadCover  = document.getElementById('btnUploadCover');
    var btnRemoveCover  = document.getElementById('btnRemoveCover');
    var coverActionText = document.getElementById('coverUploadActionText');
    var coverMediaWrap  = document.getElementById('coverPhotoMedia');
    var coverCropModal  = document.getElementById('coverCropModal');
    var coverCropImage  = document.getElementById('coverCropImage');
    var coverCropClose  = document.getElementById('coverCropClose');
    var coverCropCancel = document.getElementById('coverCropCancel');
    var coverCropApply  = document.getElementById('coverCropApply');
    var coverCropStatus = document.getElementById('coverCropStatus');
    var coverCropper    = null;

    if (btnUploadCover && coverInput) {
        btnUploadCover.addEventListener('click', function () {
            closeAllDropdowns();
            coverInput.click();
        });
    }

    if (coverInput) {
        coverInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG, PNG, GIF, and WebP images are allowed.');
                this.value = '';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Image must be 10 MB or smaller.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                if (coverCropImage) coverCropImage.src = e.target.result;
                if (coverCropModal) {
                    coverCropModal.style.display = 'flex';
                    if (coverCropStatus) coverCropStatus.textContent = '';
                }
                if (coverCropper) { coverCropper.destroy(); coverCropper = null; }

                coverCropper = new Cropper(coverCropImage, {
                    aspectRatio: 16 / 7,
                    viewMode: 1,
                    autoCropArea: 1,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: true,
                    cropBoxResizable: true,
                    dragMode: 'move'
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function closeCoverModal() {
        if (coverCropModal) coverCropModal.style.display = 'none';
        if (coverCropper) { coverCropper.destroy(); coverCropper = null; }
        if (coverCropImage) coverCropImage.src = '';
        if (coverInput) coverInput.value = '';
    }

    if (coverCropClose) coverCropClose.addEventListener('click', closeCoverModal);
    if (coverCropCancel) coverCropCancel.addEventListener('click', closeCoverModal);

    var coverZoomIn  = document.getElementById('coverZoomIn');
    var coverZoomOut = document.getElementById('coverZoomOut');
    var coverReset   = document.getElementById('coverReset');

    if (coverZoomIn)  coverZoomIn.addEventListener('click', function () { if (coverCropper) coverCropper.zoom(0.1); });
    if (coverZoomOut) coverZoomOut.addEventListener('click', function () { if (coverCropper) coverCropper.zoom(-0.1); });
    if (coverReset)   coverReset.addEventListener('click', function () { if (coverCropper) coverCropper.reset(); });

    if (coverCropApply) {
        coverCropApply.addEventListener('click', function () {
            if (!coverCropper) return;

            coverCropApply.disabled = true;
            coverCropApply.textContent = 'Uploading…';
            if (coverCropStatus) coverCropStatus.textContent = '';

            var canvas = coverCropper.getCroppedCanvas({ maxWidth: 1200, maxHeight: 525 });
            if (!canvas) {
                if (coverCropStatus) coverCropStatus.textContent = 'Failed to crop image.';
                coverCropApply.disabled = false;
                coverCropApply.textContent = 'Apply & Upload';
                return;
            }

            var base64Data = canvas.toDataURL('image/jpeg', 0.85);
            var formData = new FormData();
            formData.append('csrf_token', getCsrfToken());
            formData.append('cover_photo_data', base64Data);

            fetch('/SocialNetwork/public/?url=profile/cover', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (coverMediaWrap) {
                        coverMediaWrap.innerHTML = '<img src="' + data.cover_url + '" alt="Cover photo" id="coverPhotoImg">';
                    }
                    if (btnRemoveCover) btnRemoveCover.style.display = 'flex';
                    if (coverActionText) coverActionText.textContent = 'Change Cover Photo';
                    closeCoverModal();
                    showToast('Cover photo updated successfully!', 'success');
                } else {
                    if (coverCropStatus) coverCropStatus.textContent = data.message || 'Upload failed.';
                }
            })
            .catch(function () {
                if (coverCropStatus) coverCropStatus.textContent = 'Network error. Please try again.';
            })
            .finally(function () {
                coverCropApply.disabled = false;
                coverCropApply.textContent = 'Apply & Upload';
            });
        });
    }

    // Remove Cover Photo Confirmation
    var removeCoverModal   = document.getElementById('confirmRemoveCoverModal');
    var cancelRemoveCover  = document.getElementById('cancelRemoveCoverBtn');
    var confirmRemoveCover = document.getElementById('confirmRemoveCoverBtn');

    if (btnRemoveCover && removeCoverModal) {
        btnRemoveCover.addEventListener('click', function () {
            closeAllDropdowns();
            removeCoverModal.style.display = 'flex';
        });
    }

    if (cancelRemoveCover && removeCoverModal) {
        cancelRemoveCover.addEventListener('click', function () {
            removeCoverModal.style.display = 'none';
        });
    }

    if (confirmRemoveCover && removeCoverModal) {
        confirmRemoveCover.addEventListener('click', function () {
            confirmRemoveCover.disabled = true;
            confirmRemoveCover.textContent = 'Removing…';

            var formData = new FormData();
            formData.append('csrf_token', getCsrfToken());

            fetch('/SocialNetwork/public/?url=profile/remove-cover', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (coverMediaWrap) {
                        coverMediaWrap.innerHTML = '';
                    }
                    if (btnRemoveCover) btnRemoveCover.style.display = 'none';
                    if (coverActionText) coverActionText.textContent = 'Upload Cover Photo';
                    removeCoverModal.style.display = 'none';
                    showToast('Cover photo removed successfully.', 'success');
                } else {
                    alert(data.message || 'Failed to remove cover photo.');
                }
            })
            .catch(function () {
                alert('Network error. Please try again.');
            })
            .finally(function () {
                confirmRemoveCover.disabled = false;
                confirmRemoveCover.textContent = 'Remove';
            });
        });
    }

    // ── 2. Profile Picture Management ─────────────────────────────────────────
    var avatarInput       = document.getElementById('avatarPhotoInput');
    var btnChangeAvatar   = document.getElementById('btnChangeAvatar');
    var btnRemoveAvatar   = document.getElementById('btnRemoveAvatar');
    var avatarChangeText  = document.getElementById('avatarChangeActionText');
    var profileAvatarImg  = document.getElementById('profileAvatarImg');
    var avatarCropModal   = document.getElementById('avatarCropModal');
    var avatarCropImage   = document.getElementById('avatarCropImage');
    var avatarCropClose   = document.getElementById('avatarCropClose');
    var avatarCropCancel  = document.getElementById('avatarCropCancel');
    var avatarCropApply   = document.getElementById('avatarCropApply');
    var avatarCropStatus  = document.getElementById('avatarCropStatus');
    var avatarCropper     = null;

    if (btnChangeAvatar && avatarInput) {
        btnChangeAvatar.addEventListener('click', function () {
            closeAllDropdowns();
            avatarInput.click();
        });
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG, PNG, GIF, and WebP images are allowed.');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be 5 MB or smaller.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                if (avatarCropImage) avatarCropImage.src = e.target.result;
                if (avatarCropModal) {
                    avatarCropModal.style.display = 'flex';
                    if (avatarCropStatus) avatarCropStatus.textContent = '';
                }
                if (avatarCropper) { avatarCropper.destroy(); avatarCropper = null; }

                avatarCropper = new Cropper(avatarCropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.95,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: true,
                    cropBoxResizable: true,
                    dragMode: 'move'
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function closeAvatarModal() {
        if (avatarCropModal) avatarCropModal.style.display = 'none';
        if (avatarCropper) { avatarCropper.destroy(); avatarCropper = null; }
        if (avatarCropImage) avatarCropImage.src = '';
        if (avatarInput) avatarInput.value = '';
    }

    if (avatarCropClose) avatarCropClose.addEventListener('click', closeAvatarModal);
    if (avatarCropCancel) avatarCropCancel.addEventListener('click', closeAvatarModal);

    var avatarZoomIn  = document.getElementById('avatarZoomIn');
    var avatarZoomOut = document.getElementById('avatarZoomOut');
    var avatarReset   = document.getElementById('avatarReset');

    if (avatarZoomIn)  avatarZoomIn.addEventListener('click', function () { if (avatarCropper) avatarCropper.zoom(0.1); });
    if (avatarZoomOut) avatarZoomOut.addEventListener('click', function () { if (avatarCropper) avatarCropper.zoom(-0.1); });
    if (avatarReset)   avatarReset.addEventListener('click', function () { if (avatarCropper) avatarCropper.reset(); });

    if (avatarCropApply) {
        avatarCropApply.addEventListener('click', function () {
            if (!avatarCropper) return;

            avatarCropApply.disabled = true;
            avatarCropApply.textContent = 'Uploading…';
            if (avatarCropStatus) avatarCropStatus.textContent = '';

            var canvas = avatarCropper.getCroppedCanvas({ width: 400, height: 400 });
            if (!canvas) {
                if (avatarCropStatus) avatarCropStatus.textContent = 'Failed to crop image.';
                avatarCropApply.disabled = false;
                avatarCropApply.textContent = 'Apply & Upload';
                return;
            }

            var base64Data = canvas.toDataURL('image/jpeg', 0.88);
            var formData = new FormData();
            formData.append('csrf_token', getCsrfToken());
            formData.append('profile_image_data', base64Data);

            fetch('/SocialNetwork/public/?url=profile/update', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) {
                // If update redirected, reload or update DOM
                if (res.ok) {
                    if (profileAvatarImg) profileAvatarImg.src = base64Data;
                    var navAvatars = document.querySelectorAll('.nav-user-avatar');
                    navAvatars.forEach(function (el) { el.src = base64Data; });
                    if (btnRemoveAvatar) btnRemoveAvatar.style.display = 'flex';
                    if (avatarChangeText) avatarChangeText.textContent = 'Change Profile Picture';
                    closeAvatarModal();
                    showToast('Profile picture updated successfully!', 'success');
                } else {
                    if (avatarCropStatus) avatarCropStatus.textContent = 'Upload failed. Please try again.';
                }
            })
            .catch(function () {
                if (avatarCropStatus) avatarCropStatus.textContent = 'Network error. Please try again.';
            })
            .finally(function () {
                avatarCropApply.disabled = false;
                avatarCropApply.textContent = 'Apply & Upload';
            });
        });
    }

    // Remove Profile Picture Confirmation
    var removeAvatarModal   = document.getElementById('confirmRemoveAvatarModal');
    var cancelRemoveAvatar  = document.getElementById('cancelRemoveAvatarBtn');
    var confirmRemoveAvatar = document.getElementById('confirmRemoveAvatarBtn');

    if (btnRemoveAvatar && removeAvatarModal) {
        btnRemoveAvatar.addEventListener('click', function () {
            closeAllDropdowns();
            removeAvatarModal.style.display = 'flex';
        });
    }

    if (cancelRemoveAvatar && removeAvatarModal) {
        cancelRemoveAvatar.addEventListener('click', function () {
            removeAvatarModal.style.display = 'none';
        });
    }

    if (confirmRemoveAvatar && removeAvatarModal) {
        confirmRemoveAvatar.addEventListener('click', function () {
            confirmRemoveAvatar.disabled = true;
            confirmRemoveAvatar.textContent = 'Removing…';

            var formData = new FormData();
            formData.append('csrf_token', getCsrfToken());

            fetch('/SocialNetwork/public/?url=profile/remove-avatar', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (profileAvatarImg && data.avatar_url) {
                        profileAvatarImg.src = data.avatar_url;
                    }
                    var navAvatars = document.querySelectorAll('.nav-user-avatar');
                    navAvatars.forEach(function (el) { el.src = data.avatar_url; });
                    if (btnRemoveAvatar) btnRemoveAvatar.style.display = 'none';
                    if (avatarChangeText) avatarChangeText.textContent = 'Upload Profile Picture';
                    removeAvatarModal.style.display = 'none';
                    showToast('Profile picture removed successfully.', 'success');
                } else {
                    alert(data.message || 'Failed to remove profile picture.');
                }
            })
            .catch(function () {
                alert('Network error. Please try again.');
            })
            .finally(function () {
                confirmRemoveAvatar.disabled = false;
                confirmRemoveAvatar.textContent = 'Remove';
            });
        });
    }

    // Close Modals on Backdrop Click or Escape
    [coverCropModal, avatarCropModal, removeCoverModal, removeAvatarModal].forEach(function (m) {
        if (!m) return;
        m.addEventListener('click', function (e) {
            if (e.target === m) {
                m.style.display = 'none';
                if (m === coverCropModal && coverCropper) { coverCropper.destroy(); coverCropper = null; }
                if (m === avatarCropModal && avatarCropper) { avatarCropper.destroy(); avatarCropper = null; }
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeAllDropdowns();
            if (coverCropModal && coverCropModal.style.display === 'flex') closeCoverModal();
            if (avatarCropModal && avatarCropModal.style.display === 'flex') closeAvatarModal();
            if (removeCoverModal && removeCoverModal.style.display === 'flex') removeCoverModal.style.display = 'none';
            if (removeAvatarModal && removeAvatarModal.style.display === 'flex') removeAvatarModal.style.display = 'none';
        }
    });

}());
</script>
<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
