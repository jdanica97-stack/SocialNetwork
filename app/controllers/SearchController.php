<?php

/**
 * app/controllers/SearchController.php
 *
 * Handles Search and User Discovery, Search and Filtering (Step 11):
 *   - index() — Process keyword searches across users and posts with category, author, and sorting filters.
 *
 * Filtering modes:
 *   - 'all'   — Searches both users and posts
 *   - 'users' — Searches only users (by username or full_name)
 *   - 'posts' — Searches only posts (by content keywords)
 *
 * Post Filters:
 *   - author_id: filter posts by author
 *   - sort: 'newest', 'oldest', 'most_liked', 'most_commented'
 *
 * Security contract:
 *   - Validates and sanitizes input (trim, length limit, type whitelist).
 *   - Empty keywords & criteria skip unnecessary queries.
 *   - Passes raw variables to the View which escapes output via htmlspecialchars().
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
     * Route: GET /SocialNetwork/public/?url=search&q=...&type=...&author_id=...&sort=...
     */
    public function index(): void
    {
        $this->requireAuth();

        $keyword = trim($_GET['q'] ?? '');
        if (mb_strlen($keyword) > 200) {
            $keyword = mb_substr($keyword, 0, 200);
        }

        $type = strtolower(trim($_GET['type'] ?? 'all'));
        if (!in_array($type, ['all', 'users', 'posts'], true)) {
            $type = 'all';
        }

        $authorId = (isset($_GET['author_id']) && is_numeric($_GET['author_id']) && (int) $_GET['author_id'] > 0)
            ? (int) $_GET['author_id']
            : null;

        $sort = strtolower(trim($_GET['sort'] ?? 'newest'));
        if (!in_array($sort, ['newest', 'oldest', 'most_liked', 'most_commented'], true)) {
            $sort = 'newest';
        }

        // Search has occurred if the query parameter 'q' is present in the URL, or if an author/sort filter was selected
        $hasSearched = isset($_GET['q']) || ($authorId !== null) || (isset($_GET['sort']) && $_GET['sort'] !== 'newest');
        $emptyQuery  = ($hasSearched && $keyword === '' && $authorId === null);

        $users          = [];
        $posts          = [];
        $authors        = [];
        $commentsByPost = [];
        $hasLikedByPost = [];

        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        try {
            // Retrieve distinct authors for author filter
            $authors = $this->postModel->getDistinctAuthors();

            if ($hasSearched && !$emptyQuery) {
                // 1. Search Users (by username or full_name)
                if (($type === 'all' || $type === 'users') && $keyword !== '') {
                    $users = $this->userModel->searchUsers($keyword);
                }

                // 2. Search Posts (by content keyword, author, and sort)
                if ($type === 'all' || $type === 'posts') {
                    $postFilters = [
                        'keyword'   => $keyword,
                        'author_id' => $authorId,
                        'sort'      => $sort,
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
            $flashError = 'Search service is temporarily unavailable. Please try again.';
            $authors = $authors ?? [];
        }

        $pageTitle = $hasSearched && $keyword !== ''
            ? 'Search: ' . $keyword . ' — Orbit'
            : 'Search — Orbit';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/search/results.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }
}
