<?php

/**
 * app/controllers/LikeController.php
 *
 * Handles all AJAX / fetch() like-related actions:
 *   - toggle() — Toggle like/unlike status for a post (POST) -> JSON response
 *   - like()   — Add a like to a post (POST) -> JSON response
 *   - unlike() — Remove a like from a post (POST) -> JSON response
 *
 * Security & Validation:
 *   - Authentication required: users must be logged in ($_SESSION['user_id']).
 *   - Validates post_id parameter.
 *   - Uses LikeModel prepared statements.
 *   - Protects against duplicate likes via application check and database UNIQUE constraint.
 *   - Returns clean JSON response (no page reloads, preserves scroll position).
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/LikeModel.php';

class LikeController
{
    private LikeModel $likeModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->likeModel = new LikeModel(getDBConnection());
    }

    /**
     * Enforce authentication for like operations and respond with JSON.
     *
     * @return int Returns logged-in user ID, or halts with 401 JSON response.
     */
    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'Please log in to like posts.'
            ]);
            exit;
        }

        return (int) $_SESSION['user_id'];
    }

    /**
     * Helper to retrieve validated post_id from POST request or JSON input.
     *
     * @return int|null
     */
    private function getPostId(): ?int
    {
        if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
            $id = (int) $_POST['post_id'];
            return $id > 0 ? $id : null;
        }

        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json) && isset($json['post_id']) && is_numeric($json['post_id'])) {
                $id = (int) $json['post_id'];
                return $id > 0 ? $id : null;
            }
        }

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = (int) $_GET['id'];
            return $id > 0 ? $id : null;
        }

        return null;
    }

    /**
     * Send standard JSON response.
     *
     * @param array $data
     * @param int   $statusCode
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Toggle like/unlike for a post via AJAX fetch().
     * Route: POST /SocialNetwork/public/?url=likes/toggle
     */
    public function toggle(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed.'
            ], 405);
        }

        $postId = $this->getPostId();
        if (!$postId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post reference.'
            ], 400);
        }

        if ($this->likeModel->hasLiked($postId, $userId)) {
            $this->likeModel->removeLike($postId, $userId);
            $liked = false;
        } else {
            $this->likeModel->addLike($postId, $userId);
            $liked = true;
        }

        $count = $this->likeModel->countLikes($postId);

        $this->jsonResponse([
            'success' => true,
            'liked'   => $liked,
            'count'   => $count
        ]);
    }

    /**
     * Add a like to a post via AJAX fetch().
     * Route: POST /SocialNetwork/public/?url=likes/like
     */
    public function like(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed.'
            ], 405);
        }

        $postId = $this->getPostId();
        if (!$postId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post reference.'
            ], 400);
        }

        if (!$this->likeModel->hasLiked($postId, $userId)) {
            $this->likeModel->addLike($postId, $userId);
        }

        $count = $this->likeModel->countLikes($postId);

        $this->jsonResponse([
            'success' => true,
            'liked'   => true,
            'count'   => $count
        ]);
    }

    /**
     * Remove a like from a post via AJAX fetch().
     * Route: POST /SocialNetwork/public/?url=likes/unlike
     */
    public function unlike(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed.'
            ], 405);
        }

        $postId = $this->getPostId();
        if (!$postId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post reference.'
            ], 400);
        }

        if ($this->likeModel->hasLiked($postId, $userId)) {
            $this->likeModel->removeLike($postId, $userId);
        }

        $count = $this->likeModel->countLikes($postId);

        $this->jsonResponse([
            'success' => true,
            'liked'   => false,
            'count'   => $count
        ]);
    }
}
