<?php
/**
 * app/views/posts/feed.php
 *
 * Main Home / Newsfeed View
 *
 * Responsibilities:
 *   - When visitor is logged out ($currentUserId === null):
 *     Renders clean guest welcome and "About the platform" section.
 *     Zero posts/comments/likes are rendered.
 *   - When user is logged in ($currentUserId !== null):
 *     Renders greeting, Create Post card, Newsfeed stream, and interactive
 *     post cards with three-dot options menu for post owners.
 *
 * Receives from PostController::index():
 *   - $posts: array of all posts ordered newest first (empty when logged out)
 *   - $commentsByPost: array of comments indexed by post_id
 *   - $likeCountByPost: array of like counts indexed by post_id
 *   - $hasLikedByPost: array of boolean like states indexed by post_id
 *   - $currentUserId: int|null
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 680px;">

        <!-- ── Flash Messages ────────────────────────────────────────────────── -->
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

        <?php if ($currentUserId === null): ?>

            <!-- ── 1. GUEST / LOGGED-OUT HOME PAGE ─────────────────────────────── -->
            <div class="guest-landing fade-up">

                <!-- Welcome Hero -->
                <div class="hero-home mb-4">
                    <span class="badge mb-3" style="background: var(--surface-input); border: 1px solid var(--border); color: var(--fg-muted); padding: 6px 14px; font-weight: 500; font-size: 0.8rem; border-radius: var(--radius-pill);">
                        Mini Social Network
                    </span>
                    <h1 style="letter-spacing: -0.04em; margin-bottom: 16px;">Connect.<br>Share. Interact.</h1>
                    <p style="max-width: 500px; margin-inline: auto; margin-bottom: 28px; color: var(--fg-muted); font-size: 1rem; line-height: 1.65;">
                        A simple platform where users can create profiles, share posts, comment, and interact with other users.
                    </p>
                    <div class="btn-group-hero">
                        <a href="/SocialNetwork/public/?url=auth/register" class="btn btn-primary btn-lg">
                            Register
                        </a>
                        <a href="/SocialNetwork/public/?url=auth/login" class="btn btn-ghost btn-lg">
                            Login
                        </a>
                    </div>
                </div>

                <!-- "What is this website?" / About the platform Card -->
                <div class="card glass mb-4">
                    <div class="card-body" style="padding: 32px 28px !important;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <h2 class="h5 mb-0" style="font-weight: 600; color: var(--fg);">About the Platform</h2>
                        </div>
                        <p class="text-muted mb-4" style="font-size: 0.92rem; line-height: 1.65;">
                            Mini Social Network is a clean, distraction-free space built to share thoughts, stay connected, and engage with the community without noise or cluttered feeds.
                        </p>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
                                    <div class="mb-2 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </div>
                                    <h3 class="h6 mb-1" style="font-weight: 600; font-size: 0.9rem; color: var(--fg);">Share Posts</h3>
                                    <p class="small text-muted mb-0" style="line-height: 1.5;">
                                        Publish updates, thoughts, and attach photos to share with everyone.
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
                                    <div class="mb-2 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    </div>
                                    <h3 class="h6 mb-1" style="font-weight: 600; font-size: 0.9rem; color: var(--fg);">Likes & Comments</h3>
                                    <p class="small text-muted mb-0" style="line-height: 1.5;">
                                        Interact with posts instantly with live likes and join discussions with comments.
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
                                    <div class="mb-2 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <h3 class="h6 mb-1" style="font-weight: 600; font-size: 0.9rem; color: var(--fg);">User Profiles</h3>
                                    <p class="small text-muted mb-0" style="line-height: 1.5;">
                                        Customize your profile, add an avatar, bio, and explore the community.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center pt-4 mt-2">
                            <p class="small text-muted mb-2">Ready to join our community?</p>
                            <a href="/SocialNetwork/public/?url=auth/register" class="btn btn-primary btn-sm px-4">
                                Create an Account
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        <?php else: ?>

            <!-- ── 2. LOGGED-IN HOME PAGE & NEWSFEED ───────────────────────────── -->

            <!-- Greeting -->
            <div class="mb-4 fade-up">
                <h1 class="home-greeting">
                    Good day, <?= htmlspecialchars($_SESSION['user_full_name'] ?? 'Friend', ENT_QUOTES, 'UTF-8') ?>.
                </h1>
                <p class="home-sub mb-3">
                    What's on your mind today?
                </p>
            </div>

            <!-- Create Post Card (Posts CRUD) -->
            <div class="card glass fade-up mb-4">
                <div class="card-body">
                    <form method="POST" action="/SocialNetwork/public/?url=posts/create" enctype="multipart/form-data">
                        <div class="mb-3">
                            <textarea
                                name="content"
                                class="form-control"
                                rows="3"
                                placeholder="Share something with the community..."
                                maxlength="5000"
                                required
                            ></textarea>
                        </div>

                        <!-- Optional Image Attachment -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <label for="postImageInput" class="btn btn-ghost btn-sm text-muted d-inline-flex align-items-center gap-1" style="cursor: pointer; border: 1px dashed var(--border);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <span>Add Photo</span>
                                </label>
                                <input
                                    type="file"
                                    id="postImageInput"
                                    name="image"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                    style="display: none;"
                                    onchange="const n = this.files[0]?.name; document.getElementById('imageFileName').textContent = n ? n : '';"
                                >
                                <span id="imageFileName" class="small text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Newsfeed Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 fade-up">
                <h2 class="h5 mb-0" style="font-weight: 700; color: var(--fg); letter-spacing: -0.01em;">
                    Newsfeed
                </h2>
                <span class="small text-muted">
                    <?= count($posts) ?> <?= count($posts) === 1 ? 'post' : 'posts' ?>
                </span>
            </div>

            <!-- Posts Stream (Newsfeed) -->
            <div class="posts-stream">
                <?php if (empty($posts)): ?>
                    <div class="card glass text-center py-5 text-muted fade-up">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p class="mt-2 mb-0">No posts yet. Be the first to share something!</p>
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

                        // Human-readable relative time
                        $relativeTime = PostController::timeAgo($post['created_at']);
                        $formattedDate = date('M j, Y \a\t g:i A', strtotime($post['created_at']));
                        ?>

                        <article class="card post-card glass fade-up mb-4" id="post-<?= $postId ?>">
                            <div class="card-body">

                                <!-- Post Header: Author info, Timestamp, and Owner Options (Three-dot Menu) -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img
                                            src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);"
                                            loading="lazy"
                                        >
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.92rem; color: var(--fg);">
                                                <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--fg-faint);">
                                                @<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> &bull;
                                                <time datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($relativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Post Owner Controls (Three-dot Menu: Only visible to the post owner) -->
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
                                <div class="post-content-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--fg); margin-bottom: 16px; word-break: break-word; overflow-wrap: anywhere;">
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

                                <?php
                                $likeCount = $likeCountByPost[$postId] ?? 0;
                                $hasLiked  = $hasLikedByPost[$postId] ?? false;
                                ?>

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
                                                <!-- Filled red heart -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                            <?php else: ?>
                                                <!-- Outlined heart -->
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

                            <!-- ── Comments Section (Step 8) ────────────────────────── -->
                            <section class="comments-section" aria-label="Comments on post <?= $postId ?>">

                                <!-- Existing Comments List (READ) -->
                                <?php if (!empty($postComments)): ?>
                                    <div class="comments-list mb-3">
                                        <?php foreach ($postComments as $comment): ?>
                                            <?php
                                            $commentId = (int) $comment['id'];
                                            $isCommentOwner = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $comment['user_id']);

                                            // Commenter Avatar fallback
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

                                <!-- Create Comment Form (CREATE) -->
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

                            </section><!-- /.comments-section -->

                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>
