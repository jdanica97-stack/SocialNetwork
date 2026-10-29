<?php
/**
 * app/views/search/results.php
 *
 * Search Page View — Unified Single Card Design
 *
 * Variables passed from SearchController::index():
 *   - $keyword: string
 *   - $type: string ('all', 'users', 'posts')
 *   - $users: array of matched users
 *   - $posts: array of matched posts with comment_count and like_count
 *   - $commentsByPost: array of comments indexed by post_id
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
    <div class="col-12" style="max-width: 720px;">

        <!-- Back to feed -->
        <div class="mb-3">
            <a href="/SocialNetwork/public/" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Newsfeed
            </a>
        </div>

        <!-- ── Main Unified Search Card (Search Form + Results All in One Card) ── -->
        <div class="card glass search-unified-card fade-up mb-4">
            <div class="card-body">

                <!-- Header: Title & Description -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h1 class="h4 mb-1" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
                            Search
                        </h1>
                        <p class="small text-muted mb-0">
                            Find people and community posts.
                        </p>
                    </div>

                    <!-- Category Pills / Tabs (Quick Filter) -->
                    <div class="search-category-tabs" role="tablist" aria-label="Search categories">
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'all' ? 'active' : '' ?>"
                            onclick="setCategory('all')"
                            role="tab"
                            aria-selected="<?= $type === 'all' ? 'true' : 'false' ?>"
                        >
                            <span>All</span>
                            <?php if ($hasSearched && $keyword !== ''): ?>
                                <span class="category-tab-badge"><?= $totalResults ?></span>
                            <?php endif; ?>
                        </button>
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'users' ? 'active' : '' ?>"
                            onclick="setCategory('users')"
                            role="tab"
                            aria-selected="<?= $type === 'users' ? 'true' : 'false' ?>"
                        >
                            <span>Users</span>
                            <?php if ($hasSearched && $keyword !== ''): ?>
                                <span class="category-tab-badge"><?= $userCount ?></span>
                            <?php endif; ?>
                        </button>
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'posts' ? 'active' : '' ?>"
                            onclick="setCategory('posts')"
                            role="tab"
                            aria-selected="<?= $type === 'posts' ? 'true' : 'false' ?>"
                        >
                            <span>Posts</span>
                            <?php if ($hasSearched && $keyword !== ''): ?>
                                <span class="category-tab-badge"><?= $postCount ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>

                <!-- Search Form -->
                <form method="GET" action="/SocialNetwork/public/" id="searchForm" class="d-flex flex-column gap-3">
                    <input type="hidden" name="url" value="search">
                    <input type="hidden" name="type" id="searchCategoryInput" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input
                                    type="search"
                                    name="q"
                                    id="searchInput"
                                    class="form-control"
                                    placeholder="Search users or posts..."
                                    value="<?= $escapedKeyword ?>"
                                    aria-label="Search users or posts"
                                    maxlength="200"
                                    autofocus
                                >
                            </div>
                        </div>

                        <!-- Dropdown (Synchronized with Pills, sleek custom styling) -->
                        <div class="col-12 col-md-4">
                            <select
                                id="categorySelectDropdown"
                                class="form-select search-custom-select w-100"
                                aria-label="Search category"
                                onchange="onCategoryDropdownChange(this.value)"
                            >
                                <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All (Users & Posts)</option>
                                <option value="users" <?= $type === 'users' ? 'selected' : '' ?>>Users Only</option>
                                <option value="posts" <?= $type === 'posts' ? 'selected' : '' ?>>Posts Only</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search and Reset Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-1">
                        <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm px-3">
                            Clear
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Search
                        </button>
                    </div>
                </form>

                <!-- ── Results Section Embedded Directly Inside This Card ────── -->
                <div class="search-card-divider"></div>

                <div class="search-results-container">
                    <?php if ($emptyQuery): ?>
                        <!-- Empty Keyword Warning -->
                        <div class="text-center py-4 px-3 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-warning"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p class="mb-1" style="color: var(--fg); font-weight: 500;">Please enter a search term.</p>
                            <p class="small text-muted mb-0">Type a name, username, or post keyword in the search box above.</p>
                        </div>

                    <?php elseif (!$hasSearched): ?>
                        <!-- Initial Guidance State -->
                        <div class="text-center py-5 px-3 text-muted">
                            <div class="stat-icon-wrap mx-auto mb-3" style="width: 48px; height: 48px; border-radius: 14px; background: var(--surface-input); display: inline-flex; align-items: center; justify-content: center; color: var(--fg-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <h2 class="h6 mb-1" style="font-weight: 600; color: var(--fg);">Find People and Posts</h2>
                            <p class="small text-muted mb-0" style="max-width: 360px; margin-inline: auto;">
                                Discover other users by their name or username, or search through post contents across the community.
                            </p>
                        </div>

                    <?php else: ?>

                        <!-- 0 Results State -->
                        <?php if (($type === 'all' && $totalResults === 0) || ($type === 'users' && $userCount === 0) || ($type === 'posts' && $postCount === 0)): ?>
                            <div class="text-center py-5 px-3 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                <p class="mt-2 mb-1" style="color: var(--fg); font-weight: 500;">No results found for &ldquo;<?= $escapedKeyword ?>&rdquo;</p>
                                <p class="small text-muted mb-3">Try checking for typos or searching with different keywords.</p>
                                <div>
                                    <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm">
                                        Clear Search
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ── 1. Users Results ───────────────────────────────── -->
                        <?php if (($type === 'all' || $type === 'users') && !empty($users)): ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h2 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                                        Users (<?= $userCount ?>)
                                    </h2>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($users as $u): ?>
                                        <?php
                                        if (!empty($u['profile_image'])) {
                                            $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                        } else {
                                            $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                        }
                                        ?>
                                        <div class="search-item-card p-3">
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

                                                <?php if (isset($_SESSION['user_id'])): ?>
                                                    <?php if ((int) $_SESSION['user_id'] === (int) $u['id']): ?>
                                                        <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost btn-sm">
                                                            My Profile
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="/SocialNetwork/public/?url=profile&id=<?= (int) $u['id'] ?>" class="btn btn-ghost btn-sm">
                                                            View Profile
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ── 2. Posts Results ───────────────────────────────── -->
                        <?php if (($type === 'all' || $type === 'posts') && !empty($posts)): ?>
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h2 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                                        Posts (<?= $postCount ?>)
                                    </h2>
                                </div>

                                <div class="posts-stream">
                                    <?php foreach ($posts as $post): ?>
                                        <?php
                                        $postId       = (int) $post['id'];
                                        $postComments = $commentsByPost[$postId] ?? [];
                                        $commentCount = (int) $post['comment_count'];
                                        $likeCount    = (int) $post['like_count'];
                                        $isPostOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $post['user_id']);

                                        // Author avatar
                                        if (!empty($post['profile_image'])) {
                                            $postAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($post['profile_image']);
                                        } else {
                                            $postAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($post['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                        }

                                        $relativeTime  = PostController::timeAgo($post['created_at']);
                                        $formattedDate = date('M j, Y \a\t g:i A', strtotime($post['created_at']));
                                        $hasLiked      = $hasLikedByPost[$postId] ?? false;
                                        ?>

                                        <article class="card post-card glass mb-3" id="post-<?= $postId ?>" style="background: var(--surface-input) !important; border: 1px solid var(--border) !important;">
                                            <div class="card-body">

                                                <!-- Author info & Ownership Menu -->
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

                                                    <!-- Post Owner Controls (Three-dot Menu) -->
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

                                                <!-- Content -->
                                                <div class="post-content-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--fg); margin-bottom: 14px;">
                                                    <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                </div>

                                                <!-- Post Images Gallery -->
                                                <?php require BASE_PATH . '/app/views/posts/_gallery.php'; ?>

                                                <!-- Exact Metrics & Interactive Bar -->
                                                <div class="post-interactions d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div class="d-flex align-items-center gap-3">
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

                                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                                        Post #<?= $postId ?>
                                                    </div>
                                                </div>

                                            </div>

                                            <!-- Collapsible Comments Inspection -->
                                            <?php if (!empty($postComments)): ?>
                                                <details class="comments-section" style="cursor: pointer;">
                                                    <summary class="report-comments-toggle py-1 mb-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                        View <?= count($postComments) ?> <?= count($postComments) === 1 ? 'comment' : 'comments' ?>
                                                    </summary>

                                                    <div class="comments-list pt-2">
                                                        <?php foreach ($postComments as $comment): ?>
                                                            <?php
                                                            $commentId = (int) $comment['id'];
                                                            if (!empty($comment['profile_image'])) {
                                                                $cAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($comment['profile_image']);
                                                            } else {
                                                                $cAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($comment['full_name']) . '&size=64&background=111113&color=f5f5f7&rounded=true&bold=true';
                                                            }
                                                            $cTime = PostController::timeAgo($comment['created_at']);
                                                            ?>
                                                            <div class="comment-item mb-2" id="comment-<?= $commentId ?>">
                                                                <div class="d-flex align-items-start gap-2">
                                                                    <img
                                                                        src="<?= htmlspecialchars($cAvatar, ENT_QUOTES, 'UTF-8') ?>"
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
                                                                                <span class="comment-time ms-1">
                                                                                    &bull; <?= htmlspecialchars($cTime, ENT_QUOTES, 'UTF-8') ?>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="comment-content">
                                                                            <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </details>
                                            <?php endif; ?>

                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
/**
 * Switch category via tabs and sync dropdown & hidden input.
 * Submits form immediately if a search keyword is already entered.
 */
function setCategory(cat) {
    var hiddenInput = document.getElementById('searchCategoryInput');
    var selectElem  = document.getElementById('categorySelectDropdown');
    var searchInput = document.getElementById('searchInput');

    if (hiddenInput) hiddenInput.value = cat;
    if (selectElem)  selectElem.value = cat;

    // Update active tab buttons visual state
    document.querySelectorAll('.category-tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });
    event.currentTarget.classList.add('active');
    event.currentTarget.setAttribute('aria-selected', 'true');

    // If there is already a keyword typed, auto-filter
    if (searchInput && searchInput.value.trim().length > 0) {
        document.getElementById('searchForm').submit();
    }
}

/**
 * Handle change on the dropdown selector
 */
function onCategoryDropdownChange(val) {
    var hiddenInput = document.getElementById('searchCategoryInput');
    if (hiddenInput) hiddenInput.value = val;

    // Sync tabs
    document.querySelectorAll('.category-tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });
    var targetBtn = document.querySelector('.category-tab-btn[onclick*="\'' + val + '\'"]');
    if (targetBtn) {
        targetBtn.classList.add('active');
        targetBtn.setAttribute('aria-selected', 'true');
    }

    var searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim().length > 0) {
        document.getElementById('searchForm').submit();
    }
}
</script>
