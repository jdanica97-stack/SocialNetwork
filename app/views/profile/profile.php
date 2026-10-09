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
                        <a href="/SocialNetwork/public/?url=profile/edit" class="btn btn-ghost btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit Profile
                        </a>
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
                        $postComments = $commentsByPost[$postId] ?? [];
                        $commentCount = count($postComments);
                        $isPostOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $post['user_id']);

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
                        ?>

                        <article class="card post-card glass fade-up mb-3" id="post-<?= $postId ?>" style="background: var(--surface-card); border: 1px solid var(--border);">
                            <div class="card-body">

                                <!-- Post Header: Author info, Timestamp, and Owner Options (Three-dot Menu) -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img
                                            src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);"
                                            loading="lazy"
                                        >
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--fg);">
                                                <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="font-size: 0.74rem; color: var(--fg-faint);">
                                                @<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> &bull;
                                                <time datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($relativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Post Owner Controls (Three-dot Menu: Only visible to post owner) -->
                                    <?php if ($isPostOwner): ?>
                                        <div class="post-menu-wrap position-relative">
                                            <button
                                                type="button"
                                                class="post-menu-btn"
                                                aria-label="Post options"
                                                aria-expanded="false"
                                                title="More options"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="5" r="1.75"/>
                                                    <circle cx="12" cy="12" r="1.75"/>
                                                    <circle cx="12" cy="19" r="1.75"/>
                                                </svg>
                                            </button>

                                            <div class="post-menu-dropdown glass" role="menu">
                                                <a href="/SocialNetwork/public/?url=posts/edit&id=<?= $postId ?>" class="post-menu-item" role="menuitem">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    <span>Edit Post</span>
                                                </a>
                                                <form method="POST" action="/SocialNetwork/public/?url=posts/delete" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this post?');">
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

                                <!-- Post Image (if present) -->
                                <?php if (!empty($post['image'])): ?>
                                    <div class="mb-3 post-image-container">
                                        <img
                                            src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="Post attachment"
                                            class="img-fluid rounded"
                                            style="max-height: 480px; width: 100%; object-fit: cover; border: 1px solid var(--border);"
                                            loading="lazy"
                                        >
                                    </div>
                                <?php endif; ?>

                                <!-- Post Interactions (AJAX Like button + Like count + Comment count) -->
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
                                        <?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?>
                                    </span>
                                </div>

                            </div><!-- /.card-body -->

                            <!-- Comments Section -->
                            <section class="comments-section" aria-label="Comments on post <?= $postId ?>">

                                <!-- Existing Comments List -->
                                <?php if (!empty($postComments)): ?>
                                    <div class="comments-list mb-3">
                                        <?php foreach ($postComments as $comment): ?>
                                            <?php
                                            $commentId = (int) $comment['id'];
                                            $isCommentOwner = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $comment['user_id']);

                                            if (!empty($comment['profile_image'])) {
                                                $commenterAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($comment['profile_image']);
                                            } else {
                                                $commenterAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($comment['full_name']) . '&size=64&background=111113&color=f5f5f7&rounded=true&bold=true';
                                            }

                                            $commentRelativeTime = PostController::timeAgo($comment['created_at']);
                                            $commentFullDate = date('M j, Y \a\t g:i A', strtotime($comment['created_at']));
                                            ?>

                                            <div class="comment-item" id="comment-<?= $commentId ?>">
                                                <div class="d-flex align-items-start gap-2">
                                                    <img
                                                        src="<?= htmlspecialchars($commenterAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                        alt="<?= htmlspecialchars($comment['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        class="comment-avatar"
                                                        loading="lazy"
                                                    >
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                                            <div>
                                                                <span class="comment-author">
                                                                    <?= htmlspecialchars($comment['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                                <span class="comment-username">
                                                                    @<?= htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                                <span class="comment-time ms-1" title="<?= htmlspecialchars($commentFullDate, ENT_QUOTES, 'UTF-8') ?>">
                                                                    &bull; <?= htmlspecialchars($commentRelativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                            </div>

                                                            <!-- Edit & Delete buttons (ONLY for comment owner) -->
                                                            <?php if ($isCommentOwner): ?>
                                                                <div class="comment-actions">
                                                                    <a href="/SocialNetwork/public/?url=comments/edit&id=<?= $commentId ?>" class="comment-action-link" title="Edit comment">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                                        Edit
                                                                    </a>
                                                                    <form method="POST" action="/SocialNetwork/public/?url=comments/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                                        <input type="hidden" name="id" value="<?= $commentId ?>">
                                                                        <button type="submit" class="comment-action-link delete-link border-0 bg-transparent p-0" title="Delete comment">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                                            Delete
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Comment Content -->
                                                        <div class="comment-content" style="word-break: break-word; overflow-wrap: anywhere;">
                                                            <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
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
                                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                                        <div class="d-flex gap-2 align-items-start">
                                            <textarea
                                                name="content"
                                                class="form-control comment-input"
                                                rows="2"
                                                placeholder="Write a comment..."
                                                maxlength="1000"
                                                required
                                            ></textarea>
                                            <button type="submit" class="btn btn-primary btn-sm px-3" style="margin-top: 2px;">
                                                Comment
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

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
