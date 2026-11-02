<?php
/**
 * app/views/search/results.php
 *
 * Search Page View — Unified Single Card Design
 *
 * Variables passed from SearchController::index():
 *   - $keyword: string
 *   - $type: string ('all', 'posts', 'people')
 *   - $postType: string ('all', 'text', 'photo')
 *   - $dateRange: string ('any', 'today', 'week', 'month')
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
$isFiltered = ($type !== 'all') || ($postType !== 'all') || ($dateRange !== 'any') || ($keyword !== '');
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

        <!-- ── Main Unified Search Card ── -->
        <div class="card glass search-unified-card fade-up mb-4">
            <div class="card-body">

                <!-- Header: Title & Category Pills -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h1 class="h4 mb-1" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
                            Search
                        </h1>
                        <p class="small text-muted mb-0">
                            Find people, posts, and community discussions.
                        </p>
                    </div>

                    <!-- Content Type Filter Tabs: All, Posts, People -->
                    <div class="search-category-tabs" role="tablist" aria-label="Search content types">
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'all' ? 'active' : '' ?>"
                            onclick="setCategory('all')"
                            role="tab"
                            aria-selected="<?= $type === 'all' ? 'true' : 'false' ?>"
                            id="tab-all"
                        >
                            <span>All</span>
                            <?php if ($hasSearched && $keyword !== ''): ?>
                                <span class="category-tab-badge"><?= $totalResults ?></span>
                            <?php endif; ?>
                        </button>
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'posts' ? 'active' : '' ?>"
                            onclick="setCategory('posts')"
                            role="tab"
                            aria-selected="<?= $type === 'posts' ? 'true' : 'false' ?>"
                            id="tab-posts"
                        >
                            <span>Posts</span>
                            <?php if ($hasSearched): ?>
                                <span class="category-tab-badge"><?= $postCount ?></span>
                            <?php endif; ?>
                        </button>
                        <button
                            type="button"
                            class="category-tab-btn <?= $type === 'people' ? 'active' : '' ?>"
                            onclick="setCategory('people')"
                            role="tab"
                            aria-selected="<?= $type === 'people' ? 'true' : 'false' ?>"
                            id="tab-people"
                        >
                            <span>People</span>
                            <?php if ($hasSearched && $keyword !== ''): ?>
                                <span class="category-tab-badge"><?= $userCount ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>

                <!-- Search Form -->
                <form method="GET" action="/SocialNetwork/public/" id="searchForm" class="d-flex flex-column gap-3">
                    <input type="hidden" name="url" value="search">
                    <input type="hidden" name="type" id="searchCategoryInput" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Search Input Bar -->
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input
                            type="search"
                            name="q"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search people or posts..."
                            value="<?= $escapedKeyword ?>"
                            aria-label="Search people or posts"
                            maxlength="200"
                            autofocus
                        >
                    </div>

                    <!-- Clean Filter Bar: Post Type & Date Posted on left, Actions on right -->
                    <div class="search-subfilters-bar">
                        <div class="search-filters-group">
                            <?php if ($type !== 'people'): ?>
                                <!-- 1. Post Type Filter (All, Text Posts, Photo Posts) -->
                                <select
                                    name="post_type"
                                    id="filterPostType"
                                    class="search-filter-select"
                                    aria-label="Filter by post type"
                                    onchange="document.getElementById('searchForm').submit()"
                                >
                                    <option value="all" <?= $postType === 'all' ? 'selected' : '' ?>>All Posts</option>
                                    <option value="text" <?= $postType === 'text' ? 'selected' : '' ?>>Text Posts</option>
                                    <option value="photo" <?= $postType === 'photo' ? 'selected' : '' ?>>Photo Posts</option>
                                </select>

                                <!-- 2. Date Posted Filter (Anytime, Today, This Week, This Month) -->
                                <select
                                    name="date"
                                    id="filterDate"
                                    class="search-filter-select"
                                    aria-label="Filter by date posted"
                                    onchange="document.getElementById('searchForm').submit()"
                                >
                                    <option value="any" <?= ($dateRange === 'any' || $dateRange === 'anytime') ? 'selected' : '' ?>>Anytime</option>
                                    <option value="today" <?= $dateRange === 'today' ? 'selected' : '' ?>>Today</option>
                                    <option value="week" <?= $dateRange === 'week' ? 'selected' : '' ?>>This Week</option>
                                    <option value="month" <?= $dateRange === 'month' ? 'selected' : '' ?>>This Month</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <?php if ($hasSearched || $isFiltered): ?>
                                <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm px-3" id="resetFiltersBtn" title="Reset all search filters">
                                    Reset Filters
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-sm px-4" id="submitSearchBtn">
                                Search
                            </button>
                        </div>
                    </div>
                </form>

                <!-- ── Results Section Embedded Directly Inside This Card ────── -->
                <div class="search-card-divider"></div>

                <div class="search-results-container">
                    <?php if ($emptyQuery): ?>
                        <!-- Empty Keyword Warning -->
                        <div class="text-center py-4 px-3 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-warning"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p class="mb-1" style="color: var(--fg); font-weight: 500;">Please enter a search term or select a filter.</p>
                            <p class="small text-muted mb-0">Type a name, username, or post keyword in the search box above.</p>
                        </div>

                    <?php elseif (!$hasSearched): ?>
                        <!-- Engaging Initial Guidance State with Quick Filters -->
                        <div class="text-center py-4 px-3 text-muted">
                            <div class="stat-icon-wrap mx-auto mb-3" style="width: 48px; height: 48px; border-radius: 14px; background: var(--surface-input); display: inline-flex; align-items: center; justify-content: center; color: var(--fg-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <h2 class="h6 mb-1" style="font-weight: 600; color: var(--fg);">Explore Orbit Community</h2>
                            <p class="small text-muted mb-3" style="max-width: 420px; margin-inline: auto;">
                                Search for people across the network, or explore community posts filtered by text, photos, or date.
                            </p>

                            <!-- Quick Discover Shortcuts -->
                            <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pt-1">
                                <a href="/SocialNetwork/public/?url=search&q=a&type=people" class="search-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    Discover People
                                </a>
                                <a href="/SocialNetwork/public/?url=search&type=posts&post_type=photo" class="search-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Photo Posts
                                </a>
                                <a href="/SocialNetwork/public/?url=search&type=posts&post_type=text" class="search-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                                    Text Posts
                                </a>
                                <a href="/SocialNetwork/public/?url=search&type=posts&date=today" class="search-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Today's Posts
                                </a>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- 0 Results State -->
                        <?php if (($type === 'all' && $totalResults === 0) || ($type === 'people' && $userCount === 0) || ($type === 'posts' && $postCount === 0)): ?>
                            <div class="text-center py-5 px-3 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                <p class="mt-2 mb-1" style="color: var(--fg); font-weight: 500;">
                                    <?php if ($keyword !== ''): ?>
                                        No results found for &ldquo;<?= $escapedKeyword ?>&rdquo;
                                    <?php else: ?>
                                        No matching records found for the selected filters.
                                    <?php endif; ?>
                                </p>
                                <p class="small text-muted mb-3">Try adjusting your filters or searching with different keywords.</p>
                                <div>
                                    <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm">
                                        Reset Filters
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ── 1. People / Users Results ───────────────────────── -->
                        <?php if (($type === 'all' || $type === 'people') && !empty($users)): ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h2 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                                        People (<?= $userCount ?>)
                                    </h2>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($users as $u): ?>
                                        <?php
                                        if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . basename($u['profile_image']))) {
                                            $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode(basename($u['profile_image']));
                                        } else {
                                            $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                        }
                                        $userProfileUrl = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$u['id'])
                                            ? '/SocialNetwork/public/?url=profile'
                                            : ('/SocialNetwork/public/?url=profile&id=' . (int)$u['id']);
                                        ?>
                                        <div class="search-item-card p-3">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-3">
                                                    <a href="<?= htmlspecialchars($userProfileUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none flex-shrink-0" title="View <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>'s profile">
                                                        <img
                                                            src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                            alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                            style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); display: block;"
                                                            loading="lazy"
                                                        >
                                                    </a>
                                                    <div>
                                                        <div style="font-weight: 600; font-size: 0.95rem;">
                                                            <a href="<?= htmlspecialchars($userProfileUrl, ENT_QUOTES, 'UTF-8') ?>" style="color: var(--fg); text-decoration: none;" class="post-author-link">
                                                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                            </a>
                                                        </div>
                                                        <div style="font-size: 0.8rem; color: var(--fg-faint);">
                                                            <a href="<?= htmlspecialchars($userProfileUrl, ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: none;">
                                                                @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                                            </a>
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

                                        // Author avatar & name — PRESERVED
                                        if (!empty($post['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $post['profile_image'])) {
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
                                                        <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$post['user_id'] ?>" class="text-decoration-none">
                                                            <img
                                                                src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                                alt="<?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                                style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);"
                                                                loading="lazy"
                                                            >
                                                        </a>
                                                        <div>
                                                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--fg);">
                                                                <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$post['user_id'] ?>" style="color: inherit; text-decoration: none;">
                                                                    <?= htmlspecialchars($post['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                                </a>
                                                            </div>
                                                            <div style="font-size: 0.75rem; color: var(--fg-faint);">
                                                                <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$post['user_id'] ?>" style="color: inherit; text-decoration: none;">
                                                                    @<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?>
                                                                </a> &bull;
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
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/></svg>
                                                            </button>
                                                            <div class="post-menu-dropdown glass" role="menu">
                                                                <a href="/SocialNetwork/public/?url=posts/edit&id=<?= $postId ?>" class="post-menu-item" role="menuitem">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                                    <span>Edit Post</span>
                                                                </a>
                                                                <form
                                                                    action="/SocialNetwork/public/?url=posts/delete"
                                                                    method="POST"
                                                                    class="m-0 p-0"
                                                                    onsubmit="return confirm('Are you sure you want to delete this post?');"
                                                                >
                                                                    <?= \Csrf::field() ?>
                                                                    <input type="hidden" name="id" value="<?= $postId ?>">
                                                                    <button type="submit" class="post-menu-item delete-item w-100 border-0 bg-transparent text-start" role="menuitem">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                        <span>Delete Post</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Post Content -->
                                                <div class="post-body mb-3" id="post-body-<?= $postId ?>">
                                                    <p class="post-text mb-0" id="post-text-<?= $postId ?>">
                                                        <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                    </p>
                                                </div>

                                                <!-- Post Images Gallery -->
                                                <?php require BASE_PATH . '/app/views/posts/_gallery.php'; ?>

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
                                                        <?php
                                                        $commentsToRender = $commentCount > 2 ? array_slice($postComments, 0, 2) : $postComments;
                                                        foreach ($commentsToRender as $comment):
                                                            $commentId       = (int) $comment['id'];
                                                            $commentAuthorId = (int) ($comment['author_id'] ?? $comment['user_id']);
                                                            $isCommentOwner  = isset($_SESSION['user_id']) && ((int) $_SESSION['user_id'] === (int) $comment['user_id']);

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
                                                                                <div class="comment-content" style="word-break: break-word; overflow-wrap: anywhere;">
                                                                                    <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                                                </div>
                                                                            </div>

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
                                                                                            <?= \Csrf::field() ?>
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
                                                        <?= \Csrf::field() ?>
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

                                            </div>
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
 * Switch category via tabs and submit form
 */
function setCategory(cat) {
    var hiddenInput = document.getElementById('searchCategoryInput');
    if (hiddenInput) {
        hiddenInput.value = cat;
    }

    // Update active tab buttons visual state
    document.querySelectorAll('.category-tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });

    var activeBtn = document.getElementById('tab-' + cat);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.setAttribute('aria-selected', 'true');
    }

    // Submit form immediately
    document.getElementById('searchForm').submit();
}
</script>
