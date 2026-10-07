<?php
/**
 * app/views/search/results.php
 *
 * Search Results View (Step 11 — Search and Filtering)
 *
 * Variables passed from SearchController::index():
 *   - $keyword: string
 *   - $type: string ('all', 'users', 'posts')
 *   - $users: array of matched users
 *   - $posts: array of matched posts
 *   - $commentsByPost: array of comments indexed by post_id
 *   - $likeCountByPost: array of like counts indexed by post_id
 *   - $hasLikedByPost: array of like booleans indexed by post_id
 *   - $emptyQuery: bool
 *   - $hasSearched: bool
 *   - $currentUserId: int|null
 */

require_once BASE_PATH . '/app/controllers/PostController.php';

$escapedKeyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
$userCount = count($users);
$postCount = count($posts);
$totalResults = $userCount + $postCount;
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 680px;">

        <!-- Back to feed -->
        <div class="mb-3">
            <a href="/SocialNetwork/public/" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Newsfeed
            </a>
        </div>

        <!-- ── Search & Filter Form Card ─────────────────────────────────────── -->
        <div class="card glass fade-up mb-4">
            <div class="card-body" style="padding: 24px !important;">
                <h1 class="h4 mb-3" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
                    Search Community
                </h1>

                <form method="GET" action="/SocialNetwork/public/" class="d-flex flex-column gap-3">
                    <input type="hidden" name="url" value="search">

                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input
                            type="search"
                            name="q"
                            class="form-control"
                            placeholder="Search users by name/username or posts by keyword..."
                            value="<?= $escapedKeyword ?>"
                            aria-label="Search keyword"
                            maxlength="100"
                        >
                    </div>

                    <!-- Filter & Submit -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label for="filterType" class="small text-muted mb-0">Show:</label>
                            <select name="type" id="filterType" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                                <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All (Users & Posts)</option>
                                <option value="users" <?= $type === 'users' ? 'selected' : '' ?>>Users only</option>
                                <option value="posts" <?= $type === 'posts' ? 'selected' : '' ?>>Posts only</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Empty / Initial State ─────────────────────────────────────────── -->
        <?php if ($hasSearched && $emptyQuery): ?>
            <div class="alert alert-warning fade-up visible text-center" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Please enter a search term.
            </div>
        <?php elseif (!$hasSearched): ?>
            <div class="card glass text-center py-5 text-muted fade-up">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p class="mt-2 mb-0">Find people and posts across the community. Type a keyword above to get started.</p>
            </div>
        <?php else: ?>

            <!-- ── Search Header ─────────────────────────────────────────────── -->
            <div class="mb-4 fade-up">
                <h2 class="h5 mb-1" style="font-weight: 600; color: var(--fg);">
                    Results for &ldquo;<?= $escapedKeyword ?>&rdquo;
                </h2>
                <p class="small text-muted mb-0">
                    <?php if ($type === 'all'): ?>
                        Found <?= $userCount ?> <?= $userCount === 1 ? 'user' : 'users' ?> and <?= $postCount ?> <?= $postCount === 1 ? 'post' : 'posts' ?>
                    <?php elseif ($type === 'users'): ?>
                        Found <?= $userCount ?> <?= $userCount === 1 ? 'user' : 'users' ?>
                    <?php else: ?>
                        Found <?= $postCount ?> <?= $postCount === 1 ? 'post' : 'posts' ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- No results condition -->
            <?php if (($type === 'all' && $totalResults === 0) || ($type === 'users' && $userCount === 0) || ($type === 'posts' && $postCount === 0)): ?>
                <div class="card glass text-center py-5 text-muted fade-up">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    <p class="mt-2 mb-0">No results found for &ldquo;<?= $escapedKeyword ?>&rdquo;.</p>
                    <p class="small text-muted mt-1">Try checking for typos or searching for a different keyword.</p>
                </div>
            <?php endif; ?>

            <!-- ── 1. Matched Users Section ───────────────────────────────────── -->
            <?php if (($type === 'all' || $type === 'users') && !empty($users)): ?>
                <div class="search-section mb-4 fade-up">
                    <h3 class="h6 text-uppercase text-muted fw-bold mb-3" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                        Users (<?= $userCount ?>)
                    </h3>

                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($users as $u): ?>
                            <?php
                            if (!empty($u['profile_image'])) {
                                $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                            } else {
                                $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                            }
                            ?>
                            <div class="card glass p-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <img
                                            src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);"
                                            loading="lazy"
                                        >
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--fg);">
                                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--fg-faint);">
                                                @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <?php if (!empty($u['bio'])): ?>
                                                <div class="mt-1 small text-muted" style="max-width: 480px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $u['id']): ?>
                                        <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost btn-sm">
                                            My Profile
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── 2. Matched Posts Section ───────────────────────────────────── -->
            <?php if (($type === 'all' || $type === 'posts') && !empty($posts)): ?>
                <div class="search-section mb-4 fade-up">
                    <h3 class="h6 text-uppercase text-muted fw-bold mb-3" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                        Posts (<?= $postCount ?>)
                    </h3>

                    <div class="posts-stream">
                        <?php foreach ($posts as $post): ?>
                            <?php
                            $postId       = (int) $post['id'];
                            $postComments = $commentsByPost[$postId] ?? [];
                            $commentCount = count($postComments);
                            $isPostOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $post['user_id']);

                            // Author avatar
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

                            <article class="card post-card glass fade-up mb-3" id="post-<?= $postId ?>">
                                <div class="card-body">

                                    <!-- Author info -->
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
                                                <div style="font-size: 0.75rem; color: var(--fg-faint);">
                                                    @<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> &bull;
                                                    <time datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($relativeTime, ENT_QUOTES, 'UTF-8') ?>
                                                    </time>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($isPostOwner): ?>
                                            <div class="d-flex gap-2 align-items-center">
                                                <a href="/SocialNetwork/public/?url=posts/edit&id=<?= $postId ?>" class="comment-action-link" title="Edit post">
                                                    Edit
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Content -->
                                    <div class="post-content-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--fg); margin-bottom: 14px;">
                                        <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>

                                    <?php if (!empty($post['image'])): ?>
                                        <div class="mb-3 post-image-container">
                                            <img
                                                src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?>"
                                                alt="Post attachment"
                                                class="img-fluid rounded"
                                                style="max-height: 400px; width: 100%; object-fit: cover; border: 1px solid var(--border);"
                                                loading="lazy"
                                            >
                                        </div>
                                    <?php endif; ?>

                                    <!-- Interactions -->
                                    <div class="post-interactions">
                                        <?php if ($currentUserId): ?>
                                            <button
                                                type="button"
                                                class="like-btn like-button <?= $hasLiked ? 'liked' : '' ?>"
                                                data-post-id="<?= $postId ?>"
                                                data-liked="<?= $hasLiked ? '1' : '0' ?>"
                                                title="<?= $hasLiked ? 'Unlike' : 'Like' ?>"
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
                                        <?php else: ?>
                                            <a href="/SocialNetwork/public/?url=auth/login" class="like-btn like-button">
                                                <span class="like-icon" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                                </span>
                                                <span class="like-text">Like</span>
                                                <span class="like-count"><?= $likeCount ?></span>
                                            </a>
                                        <?php endif; ?>

                                        <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.82rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            <?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?>
                                        </span>
                                    </div>

                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>
