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
     * Display the search results page.
     * Route: GET /SocialNetwork/public/?url=search&q=...&type=...
     */
    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $type    = strtolower(trim($_GET['type'] ?? 'all'));

        // Normalize filter type
        if (!in_array($type, ['all', 'users', 'posts'], true)) {
            $type = 'all';
        }

        $users           = [];
        $posts           = [];
        $commentsByPost  = [];
        $likeCountByPost = [];
        $hasLikedByPost  = [];
        $emptyQuery      = ($keyword === '');
        $hasSearched     = isset($_GET['q']);

        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        // Only query the database if a search keyword was provided
        if (!$emptyQuery) {
            // Search Users
            if ($type === 'all' || $type === 'users') {
                $users = $this->userModel->searchUsers($keyword);
            }

            // Search Posts
            if ($type === 'all' || $type === 'posts') {
                $posts = $this->postModel->searchPosts($keyword);

                // Fetch comments and like metadata for matching posts
                foreach ($posts as $p) {
                    $pId = (int) $p['id'];
                    $commentsByPost[$pId]  = $this->commentModel->getByPostId($pId);
                    $likeCountByPost[$pId] = $this->likeModel->countLikes($pId);
                    $hasLikedByPost[$pId]  = $currentUserId ? $this->likeModel->hasLiked($pId, $currentUserId) : false;
                }
            }
        }

        $pageTitle = $emptyQuery
            ? 'Search — Mini Social Network'
            : 'Search: ' . $keyword . ' — Mini Social Network';

        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/search/results.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }
}
