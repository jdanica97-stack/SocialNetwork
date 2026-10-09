<?php

/**
 * app/controllers/SearchController.php
 *
 * Handles Search and Filtering (Step 11):
 *   - index() — Process keyword searches across users and posts with filtering
 *
 * Filtering modes:
 *   - 'all'   — Searches both users and posts
 *   - 'users' — Searches only users (by username or full_name)
 *   - 'posts' — Searches only posts (by content)
 *
 * Security contract:
 *   - Validates and sanitizes input (trim).
 *   - Empty keywords skip database queries to prevent unnecessary DB load.
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
     * Display the search results and community statistics report page.
     * Route: GET /SocialNetwork/public/?url=search&q=...&type=...&author_id=...&date_from=...&date_to=...&sort=...
     */
    public function index(): void
    {
        $keyword  = trim($_GET['q'] ?? '');
        $type     = strtolower(trim($_GET['type'] ?? 'all'));
        $authorId = (isset($_GET['author_id']) && $_GET['author_id'] !== '') ? (int) $_GET['author_id'] : null;
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to'] ?? '');
        $sort     = strtolower(trim($_GET['sort'] ?? 'newest'));

        // Normalize filter type
        if (!in_array($type, ['all', 'users', 'posts'], true)) {
            $type = 'all';
        }

        // Validate date formats (YYYY-MM-DD)
        if ($dateFrom !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }
        if ($dateTo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        // Normalize sort order
        if (!in_array($sort, ['newest', 'oldest', 'most_liked', 'most_commented'], true)) {
            $sort = 'newest';
        }

        $filterApplied = isset($_GET['filter_applied']);
        $hasSearched   = isset($_GET['q']) || $filterApplied || ($authorId !== null) || ($dateFrom !== '') || ($dateTo !== '') || (isset($_GET['sort']) && $_GET['sort'] !== 'newest');

        // Check whether the search/filter request is empty
        if ($type === 'users') {
            $emptyQuery = ($keyword === '');
        } else {
            $emptyQuery = ($keyword === '' && $authorId === null && $dateFrom === '' && $dateTo === '' && !$filterApplied);
        }

        $users           = [];
        $posts           = [];
        $commentsByPost  = [];
        $hasLikedByPost  = [];
        $summaryStats    = [
            'total_posts'    => 0,
            'total_authors'  => 0,
            'total_comments' => 0,
            'total_likes'    => 0,
        ];

        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        // Retrieve distinct authors for author filter dropdown
        $authors = $this->postModel->getDistinctAuthors();

        // Only query the database if search criteria was supplied
        if (!$emptyQuery) {
            // 1. Search Users (Step 11)
            if (($type === 'all' || $type === 'users') && $keyword !== '') {
                $users = $this->userModel->searchUsers($keyword);
            }

            // 2. Search & Filter Posts with Summary Reporting (Step 11 & Step 12)
            if ($type === 'all' || $type === 'posts') {
                $filters = [
                    'keyword'   => $keyword,
                    'author_id' => $authorId,
                    'date_from' => $dateFrom,
                    'date_to'   => $dateTo,
                    'sort'      => $sort,
                ];

                // Compute summary statistics without Cartesian product
                $summaryStats = $this->postModel->getSearchSummaryStats($filters);

                // Fetch matching posts with exact aggregated comment_count and like_count
                $posts = $this->postModel->searchPostsAdvanced($filters);

                // Fetch comment details and like state for current user
                foreach ($posts as $p) {
                    $pId = (int) $p['id'];
                    $commentsByPost[$pId] = $this->commentModel->getByPostId($pId);
                    $hasLikedByPost[$pId] = $currentUserId ? $this->likeModel->hasLiked($pId, $currentUserId) : false;
                }
            }
        }

        $pageTitle = $emptyQuery
            ? 'Search & Community Reports — Mini Social Network'
            : ($keyword !== '' ? 'Search: ' . $keyword . ' — Mini Social Network' : 'Community Report — Mini Social Network');

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/search/results.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }
}
