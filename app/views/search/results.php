<?php
/**
 * app/views/search/results.php
 *
 * Combined Search, Filtering, and Reporting View (Step 11 & Step 12)
 *
 * Variables passed from SearchController::index():
 *   - $keyword: string
 *   - $type: string ('all', 'users', 'posts')
 *   - $authorId: int|null
 *   - $dateFrom: string
 *   - $dateTo: string
 *   - $sort: string ('newest', 'oldest', 'most_liked', 'most_commented')
 *   - $authors: array of distinct authors
 *   - $users: array of matched users
 *   - $posts: array of matched posts with comment_count and like_count
 *   - $commentsByPost: array of comments indexed by post_id
 *   - $hasLikedByPost: array of like booleans indexed by post_id
 *   - $summaryStats: array with total_posts, total_authors, total_comments, total_likes
 *   - $emptyQuery: bool
 *   - $hasSearched: bool
 *   - $currentUserId: int|null
 */

require_once BASE_PATH . '/app/controllers/PostController.php';

$escapedKeyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
$userCount = count($users);
$postCount = count($posts);
$totalResults = $userCount + $postCount;

// Find author name if an author filter is active
$selectedAuthorName = '';
if ($authorId !== null) {
    foreach ($authors as $a) {
        if ((int) $a['id'] === $authorId) {
            $selectedAuthorName = $a['full_name'] . ' (@' . $a['username'] . ')';
            break;
        }
    }
}
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

        <!-- ── Search, Filtering & Reporting Form Card ───────────────────────── -->
        <div class="card glass fade-up mb-4">
            <div class="card-body" style="padding: 24px !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h1 class="h4 mb-0" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
                            Search & Reports
                        </h1>
                        <p class="small text-muted mb-0 mt-1">
                            Find users, filter community posts, and inspect interaction statistics.
                        </p>
                    </div>
                    <?php if ($hasSearched): ?>
                        <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm">
                            Reset Filters
                        </a>
                    <?php endif; ?>
                </div>

                <form method="GET" action="/SocialNetwork/public/" class="d-flex flex-column gap-3">
                    <input type="hidden" name="url" value="search">

                    <!-- Keyword Search Input -->
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input
                            type="search"
                            name="q"
                            class="form-control"
                            placeholder="Search post content or user names/usernames..."
                            value="<?= $escapedKeyword ?>"
                            aria-label="Search keyword"
                            maxlength="100"
                        >
                    </div>

                    <!-- Multi-Filter Grid -->
                    <div class="search-filters-grid">

                        <!-- Filter: Type -->
                        <div>
                            <label for="filterType" class="small text-muted mb-1 d-block">Search In</label>
                            <select name="type" id="filterType" class="form-select form-select-sm">
                                <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All (Users & Posts)</option>
                                <option value="posts" <?= $type === 'posts' ? 'selected' : '' ?>>Posts Only</option>
                                <option value="users" <?= $type === 'users' ? 'selected' : '' ?>>Users Only</option>
                            </select>
                        </div>

                        <!-- Filter: Author -->
                        <div>
                            <label for="filterAuthor" class="small text-muted mb-1 d-block">Author</label>
                            <select name="author_id" id="filterAuthor" class="form-select form-select-sm">
                                <option value="">All Authors</option>
                                <?php foreach ($authors as $a): ?>
                                    <option value="<?= (int) $a['id'] ?>" <?= $authorId === (int) $a['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter: Date From -->
                        <div>
                            <label for="filterDateFrom" class="small text-muted mb-1 d-block">From Date</label>
                            <input
                                type="date"
                                id="filterDateFrom"
                                name="date_from"
                                class="form-control form-control-sm"
                                value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <!-- Filter: Date To -->
                        <div>
                            <label for="filterDateTo" class="small text-muted mb-1 d-block">To Date</label>
                            <input
                                type="date"
                                id="filterDateTo"
                                name="date_to"
                                class="form-control form-control-sm"
                                value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <!-- Filter: Sort -->
                        <div>
                            <label for="filterSort" class="small text-muted mb-1 d-block">Sort By</label>
                            <select name="sort" id="filterSort" class="form-select form-select-sm">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                <option value="most_liked" <?= $sort === 'most_liked' ? 'selected' : '' ?>>Most Liked</option>
                                <option value="most_commented" <?= $sort === 'most_commented' ? 'selected' : '' ?>>Most Commented</option>
                            </select>
                        </div>

                    </div>

                    <!-- Actions Row -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top" style="border-color: var(--border) !important;">
                        <a href="/SocialNetwork/public/?url=search&type=posts&filter_applied=1" class="btn btn-ghost btn-sm text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                            Generate Full Community Report
                        </a>

                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Search & Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Empty / Initial State Handling ─────────────────────────────────── -->
        <?php if ($hasSearched && $emptyQuery): ?>
            <div class="alert alert-warning fade-up visible text-center" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Please enter a search keyword or select a filter criteria.
            </div>
        <?php elseif (!$hasSearched): ?>
            <div class="card glass text-center py-5 text-muted fade-up mb-4">
                <div class="stat-icon-wrap posts-icon mx-auto mb-3" style="width: 52px; height: 52px; border-radius: 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h2 class="h5 mb-2" style="font-weight: 600; color: var(--fg);">Explore Community & Generate Reports</h2>
                <p class="small text-muted mb-3" style="max-width: 440px; margin-inline: auto;">
                    Search for users and posts, apply author and date range filters, or generate comprehensive engagement reports with accurate like and comment statistics.
                </p>
                <div>
                    <a href="/SocialNetwork/public/?url=search&type=posts&filter_applied=1" class="btn btn-primary btn-sm px-4">
                        View Full Community Report
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- ── Active Filter Badges ───────────────────────────────────────── -->
            <div class="d-flex align-items-center flex-wrap gap-2 mb-3 fade-up">
                <span class="small text-muted fw-semibold me-1">Active Criteria:</span>
                <?php if ($keyword !== ''): ?>
                    <span class="filter-badge">
                        Keyword: <strong>&ldquo;<?= $escapedKeyword ?>&rdquo;</strong>
                    </span>
                <?php endif; ?>
                <?php if ($type !== 'all'): ?>
                    <span class="filter-badge">
                        Type: <strong><?= ucfirst($type) ?></strong>
                    </span>
                <?php endif; ?>
                <?php if ($selectedAuthorName !== ''): ?>
                    <span class="filter-badge">
                        Author: <strong><?= htmlspecialchars($selectedAuthorName, ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                <?php endif; ?>
                <?php if ($dateFrom !== '' || $dateTo !== ''): ?>
                    <span class="filter-badge">
                        Date: <strong><?= htmlspecialchars($dateFrom ?: 'Any') ?> &rarr; <?= htmlspecialchars($dateTo ?: 'Now') ?></strong>
                    </span>
                <?php endif; ?>
                <span class="filter-badge">
                    Sort: <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $sort)), ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
            </div>

            <!-- ── Summary Statistics Cards (Step 12) ─────────────────────────── -->
            <?php if ($type === 'all' || $type === 'posts'): ?>
                <section class="mb-4 fade-up" aria-label="Summary Statistics Report">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h2 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                            Summary Statistics
                        </h2>
                        <span class="small text-muted" style="font-size: 0.75rem;">
                            Step 12 Report
                        </span>
                    </div>

                    <div class="stats-grid">
                        <!-- Total Matching Posts -->
                        <div class="stat-card">
                            <div class="stat-icon-wrap posts-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?= (int) $summaryStats['total_posts'] ?></div>
                                <div class="stat-label">Total Posts</div>
                            </div>
                        </div>

                        <!-- Distinct Authors -->
                        <div class="stat-card">
                            <div class="stat-icon-wrap authors-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?= (int) $summaryStats['total_authors'] ?></div>
                                <div class="stat-label">Unique Authors</div>
                            </div>
                        </div>

                        <!-- Total Comments -->
                        <div class="stat-card">
                            <div class="stat-icon-wrap comments-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?= (int) $summaryStats['total_comments'] ?></div>
                                <div class="stat-label">Total Comments</div>
                            </div>
                        </div>

                        <!-- Total Likes -->
                        <div class="stat-card">
                            <div class="stat-icon-wrap likes-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?= (int) $summaryStats['total_likes'] ?></div>
                                <div class="stat-label">Total Likes</div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- ── Zero Results Alert ─────────────────────────────────────────── -->
            <?php if (($type === 'all' && $totalResults === 0) || ($type === 'users' && $userCount === 0) || ($type === 'posts' && $postCount === 0)): ?>
                <div class="card glass text-center py-5 text-muted fade-up mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2" style="opacity: 0.6;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    <p class="mt-2 mb-0">No matching results found for the selected criteria.</p>
                    <p class="small text-muted mt-1">Try broadening your search term or clearing filters.</p>
                    <div class="mt-3">
                        <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm">
                            Clear Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── 1. Matched Users Section (Step 11) ─────────────────────────── -->
            <?php if (($type === 'all' || $type === 'users') && !empty($users)): ?>
                <div class="search-section mb-4 fade-up">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                            Matching Users (<?= $userCount ?>)
                        </h3>
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

            <!-- ── 2. Detailed Posts Reporting Section (Step 12) ─────────────── -->
            <?php if (($type === 'all' || $type === 'posts') && !empty($posts)): ?>
                <div class="search-section mb-4 fade-up">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h6 text-uppercase text-muted fw-bold mb-0" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                            Detailed Posts Report (<?= $postCount ?>)
                        </h3>
                        <span class="small text-muted">
                            Order: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $sort)), ENT_QUOTES, 'UTF-8') ?>
                        </span>
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

                            <article class="card post-card glass fade-up mb-3" id="post-<?= $postId ?>">
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

                                    <!-- Exact Metrics & Interactive Bar (Step 12) -->
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

                                <!-- Collapsible Comments Inspection (Step 8 & 12) -->
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
