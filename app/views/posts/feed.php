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
                    <span class="badge mb-3 hero-badge">
                        Orbit Social Network
                    </span>
                    <h1 style="letter-spacing: -0.04em; margin-bottom: 16px;">Connect.<br>Share. Interact.</h1>
                    <p style="max-width: 500px; margin-inline: auto; margin-bottom: 28px; color: var(--fg-muted); font-size: 1rem; line-height: 1.65;">
                        A simple platform where users can create profiles, share posts, comment, and interact with other users.
                    </p>
                    <div class="btn-group-hero">
                        <a href="/SocialNetwork/public/?url=auth/login" class="btn btn-primary btn-lg px-4">
                            Login
                        </a>
                        <a href="/SocialNetwork/public/?url=auth/register" class="btn btn-ghost btn-lg px-4">
                            Register
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
                            Orbit is a clean, distraction-free space built to share thoughts, stay connected, and engage with the community without noise or cluttered feeds.
                        </p>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded feature-item" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
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
                                <div class="p-3 rounded feature-item" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
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
                                <div class="p-3 rounded feature-item" style="background: var(--surface-input); border: 1px solid var(--border); height: 100%;">
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
                    </div>
                </div>

            </div>

        <?php else: ?>

            <!-- ── 2. LOGGED-IN HOME PAGE & NEWSFEED ───────────────────────────── -->

            <!-- Unified Post Composer Card (Greeting + Input + Photo + Post in ONE Card) -->
            <div class="card glass fade-up mb-4 composer-card">
                <div class="card-body p-4">
                    <form method="POST" action="/SocialNetwork/public/?url=posts/create" enctype="multipart/form-data" id="createPostForm">
                        <?= Csrf::field() ?>

                        <!-- 1. Header Row: Avatar + Greeting + Subtitle -->
                        <div class="composer-header d-flex align-items-center gap-3 mb-3">
                            <?php if (!empty($currentUserAvatar)): ?>
                                <a href="/SocialNetwork/public/?url=profile" title="View your profile" class="flex-shrink-0">
                                    <img
                                        src="<?= htmlspecialchars($currentUserAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($_SESSION['user_full_name'] ?? 'Your profile', ENT_QUOTES, 'UTF-8') ?>"
                                        class="composer-user-avatar"
                                    >
                                </a>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <h1 class="composer-greeting mb-0">
                                    Good day, <?= htmlspecialchars($_SESSION['user_full_name'] ?? 'Friend', ENT_QUOTES, 'UTF-8') ?>.
                                </h1>
                                <p class="composer-subtitle mb-0">
                                    What's on your mind today?
                                </p>
                            </div>
                        </div>

                        <!-- 2. Post Input Row: Full-width compact textarea -->
                        <div class="composer-input-row mb-3">
                            <textarea
                                id="composerTextarea"
                                name="content"
                                class="form-control composer-textarea w-100"
                                placeholder="Share something with the community..."
                                maxlength="5000"
                                required
                                rows="2"
                            ></textarea>
                        </div>

                        <!-- 3. Image Preview Container (Inside Card, below text, above footer) -->
                        <div id="composerImagePreviewWrap" class="composer-preview-wrap mb-3 d-none">
                            <div id="composerPreviewGrid" class="composer-preview-grid d-flex flex-wrap gap-2"></div>
                        </div>

                        <!-- 4. Footer: Add Photo button (left) and Post button (right) -->
                        <div class="composer-footer d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top" style="border-color: var(--border) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <label for="postImageInput" class="btn btn-ghost btn-sm composer-photo-btn d-inline-flex align-items-center gap-1" style="cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <span>Add Photos</span>
                                </label>
                                <input
                                    type="file"
                                    id="postImageInput"
                                    name="images[]"
                                    multiple
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                    class="d-none"
                                >
                                <span id="imageFileName" class="small text-muted" style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-4" id="composerSubmitBtn">
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
                        $postAuthorId = (int) $post['user_id'];
                        $postComments = $commentsByPost[$postId] ?? [];
                        $commentCount = $commentCountByPost[$postId] ?? count($postComments);
                        $isPostOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === $postAuthorId);

                        // Author avatar with fallback
                        if (!empty($post['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $post['profile_image'])) {
                            $postAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($post['profile_image']);
                        } else {
                            $postAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($post['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                        }

                        // Human-readable relative time
                        $relativeTime = PostController::timeAgo($post['created_at']);
                        $formattedDate = date('M j, Y \a\t g:i A', strtotime($post['created_at']));
                        $profileUrl = '/SocialNetwork/public/?url=profile&id=' . $postAuthorId;
                        ?>

                        <article class="card post-card glass fade-up mb-4" id="post-<?= $postId ?>">
                            <div class="card-body">

                                <!-- Post Header: Author info, Timestamp, and Owner Options (Three-dot Menu) -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Clickable avatar → author profile -->
                                        <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" title="View <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>'s profile">
                                            <img
                                                src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); display: block;"
                                                loading="lazy"
                                            >
                                        </a>
                                        <div>
                                            <!-- Clickable name → author profile -->
                                            <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" style="font-weight: 600; font-size: 0.92rem; color: var(--fg); text-decoration: none;" class="post-author-link">
                                                <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
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
                                <div class="post-content-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--fg); margin-bottom: 16px; word-break: break-word; overflow-wrap: anywhere;">
                                    <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>

                                <!-- Post Images Gallery -->
                                <?php require BASE_PATH . '/app/views/posts/_gallery.php'; ?>

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

                            <!-- ── Comments Section ────────────────────────── -->
                            <section class="comments-section" aria-label="Comments on post <?= $postId ?>">

                                <!-- Existing Comments List (preview: first 2) -->
                                <?php if ($commentCount > 0): ?>
                                    <!-- "View all" toggle — only shown when there are more than 2 comments -->
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
                                            $commentId      = (int) $comment['id'];
                                            $commentAuthorId= (int) ($comment['author_id'] ?? $comment['user_id']);
                                            $isCommentOwner = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $comment['user_id']);

                                            // Commenter Avatar fallback
                                            if (!empty($comment['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $comment['profile_image'])) {
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
                                                    <!-- Clickable avatar → commenter profile -->
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

                                                            <!-- Comment Kebab Menu (ONLY for comment owner) -->
                                                            <?php if ($isCommentOwner): ?>
                                                                <div class="comment-menu-wrap">
                                                                    <button
                                                                        type="button"
                                                                        class="comment-menu-btn"
                                                                        aria-label="Comment options"
                                                                        aria-expanded="false"
                                                                        title="More options"
                                                                    >
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

                                <!-- Create Comment Form (CREATE) -->
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

                            </section><!-- /.comments-section -->

                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

