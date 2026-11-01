<?php

/**
 * app/controllers/SearchController.php
 *
 * Handles Search and User Discovery, Search and Filtering:
 *   - index() — Process keyword searches across people and posts with Content Type, Post Type, and Date Posted filters.
 *
 * Content Type filters:
 *   - 'all'    — Searches both people and community posts
 *   - 'posts'  — Searches only posts
 *   - 'people' — Searches only users/people (supports 'users' as alias)
 *
 * Post Type filters:
 *   - 'all'   — All posts
 *   - 'text'  — Text posts only (no images)
 *   - 'photo' — Photo posts only (contains one or more images)
 *
 * Date Posted filters:
 *   - 'any' / 'anytime' — Any time (no date restriction)
 *   - 'today'           — Posts created today (calendar day)
 *   - 'week'            — Posts created this week (calendar week)
 *   - 'month'           — Posts created this month (calendar month)
 *
 * Security contract:
 *   - Validates and sanitizes input (trim, length limit, parameter whitelist).
 *   - Empty queries skip unnecessary work.
 *   - Views escape output via htmlspecialchars().
 *   - Database queries are strictly handled by UserModel and PostModel using prepared statements.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';

class SearchController
{
    private UserModel $userModel;
    private PostModel $postModel;
    private CommentModel $commentModel;
    private LikeModel $likeModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $db = getDBConnection();
        $this->userModel    = new UserModel($db);
        $this->postModel    = new PostModel($db);
        $this->commentModel = new CommentModel($db);
        $this->likeModel    = new LikeModel($db);
    }

    /**
     * Redirect to login if user is not authenticated.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to access search.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    /**
     * Display the search results page.
     * Route: GET /SocialNetwork/public/?url=search&q=...&type=...&post_type=...&date=...
     */
    public function index(): void
    {
        $this->requireAuth();

        // 1. Search keyword
        $keyword = trim($_GET['q'] ?? '');
        if (mb_strlen($keyword) > 200) {
            $keyword = mb_substr($keyword, 0, 200);
        }

        // 2. Content Type filter: all, posts, people
        $rawType = strtolower(trim($_GET['type'] ?? 'all'));
        if ($rawType === 'users') {
            $rawType = 'people';
        }
        $allowedTypes = ['all', 'posts', 'people'];
        $type = in_array($rawType, $allowedTypes, true) ? $rawType : 'all';

        // 3. Post Type filter: all, text, photo
        $rawPostType = strtolower(trim($_GET['post_type'] ?? 'all'));
        $allowedPostTypes = ['all', 'text', 'photo'];
        $postType = in_array($rawPostType, $allowedPostTypes, true) ? $rawPostType : 'all';

        // 4. Date Posted filter: any / anytime, today, week, month
        $rawDate = strtolower(trim($_GET['date'] ?? 'any'));
        if ($rawDate === 'anytime') {
            $rawDate = 'any';
        }
        $allowedDates = ['any', 'today', 'week', 'month'];
        $dateRange = in_array($rawDate, $allowedDates, true) ? $rawDate : 'any';

        // Check whether search or filter action was submitted
        $hasSearched = isset($_GET['q']) ||
            (isset($_GET['type']) && $_GET['type'] !== 'all') ||
            (isset($_GET['post_type']) && $_GET['post_type'] !== 'all') ||
            (isset($_GET['date']) && $rawDate !== 'any');

        $emptyQuery = ($hasSearched && $keyword === '' && $postType === 'all' && $dateRange === 'any' && $type === 'all');

        $users          = [];
        $posts          = [];
        $commentsByPost = [];
        $hasLikedByPost = [];

        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        try {
            if ($hasSearched && !$emptyQuery) {
                // 1. Search People (by username or full_name)
                if (($type === 'all' || $type === 'people') && $keyword !== '') {
                    $users = $this->userModel->searchUsers($keyword);
                }

                // 2. Search Posts (by content keyword, post_type, and date_posted)
                if ($type === 'all' || $type === 'posts') {
                    $postFilters = [
                        'keyword'     => $keyword,
                        'post_type'   => $postType,
                        'date_posted' => $dateRange,
                    ];

                    $posts = $this->postModel->searchPostsAdvanced($postFilters);

                    // Fetch comment details and like state for current user
                    foreach ($posts as $p) {
                        $pId = (int) $p['id'];
                        $commentsByPost[$pId] = $this->commentModel->getByPostId($pId);
                        $hasLikedByPost[$pId] = $currentUserId ? $this->likeModel->hasLiked($pId, $currentUserId) : false;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[SearchController::index] Exception: ' . $e->getMessage());
        }

        $pageTitle = $hasSearched && $keyword !== ''
            ? 'Search: ' . $keyword . ' — Orbit'
            : 'Search — Orbit';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/search/results.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }
}
