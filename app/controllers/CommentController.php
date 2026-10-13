<?php

/**
 * app/controllers/CommentController.php
 *
 * Handles all comment-related actions:
 *   - create() — Process comment creation (POST)
 *   - edit()   — Show comment edit view (GET)
 *   - update() — Process comment update (POST)
 *   - delete() — Process comment deletion (POST)
 *
 * Security contract:
 *   - Authentication required: users must be logged in ($_SESSION['user_id']).
 *   - Input validation: content must be non-empty and within character limits.
 *   - Strict server-side authorization: users may ONLY update or delete their own comments.
 *   - CSRF/tamper-safe: user_id is never taken from $_POST/$_GET, only from $_SESSION.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class CommentController
{
    private CommentModel $commentModel;
    private PostModel $postModel;

    public function __construct()
    {
        Csrf::initSession();

        $db = getDBConnection();
        $this->commentModel = new CommentModel($db);
        $this->postModel    = new PostModel($db);
    }

    // ─── Authentication Guard ─────────────────────────────────────────────────

    /**
     * Enforce active user session before any comment operation.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            $_SESSION['flash_error'] = 'You must be logged in to perform that action.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    // ─── Create Comment (POST) ────────────────────────────────────────────────

    /**
     * Store a new comment under a specific post.
     * Route: POST /SocialNetwork/public/?url=comments/create
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $postId  = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

        if (!Csrf::check()) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/' . ($postId && $postId > 0 ? '#post-' . $postId : ''));
            exit;
        }

        if (!$postId || $postId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Verify that target post exists before attempting to comment (foreign key integrity)
        $post = $this->postModel->getById($postId);
        if (!$post) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'The post you are commenting on does not exist or has been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $content = trim($_POST['content'] ?? '');
        $userId  = (int) $_SESSION['user_id'];

        // Validation
        if ($content === '') {
            $_SESSION['flash_error'] = 'Comment content cannot be empty.';
            header('Location: /SocialNetwork/public/#post-' . $postId);
            exit;
        }

        if (mb_strlen($content) > 1000) {
            $_SESSION['flash_error'] = 'Comment must be 1000 characters or fewer.';
            header('Location: /SocialNetwork/public/#post-' . $postId);
            exit;
        }

        $result = $this->commentModel->createComment($postId, $userId, $content);

        if ($result !== false) {
            $_SESSION['flash_success'] = 'Comment posted successfully.';
        } else {
            http_response_code(500);
            $_SESSION['flash_error'] = 'Could not save your comment due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/#post-' . $postId);
        exit;
    }

    // ─── Edit Comment Form (GET) ──────────────────────────────────────────────

    /**
     * Display the form to edit an existing comment.
     * Route: GET /SocialNetwork/public/?url=comments/edit&id={commentId}
     */
    public function edit(): void
    {
        $this->requireAuth();

        $commentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null);
        if (!$commentId || $commentId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid comment reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Comment not found or has been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Server-side ownership check
        $userId = (int) $_SESSION['user_id'];
        if ((int) $comment['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'You are not authorized to edit this comment.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $errors = [];
        $pageTitle = 'Edit Comment — Mini Social Network';
        require_once BASE_PATH . '/app/views/comments/edit.php';
    }

    // ─── Update Comment (POST) ────────────────────────────────────────────────

    /**
     * Process updated content for an existing comment.
     * Route: POST /SocialNetwork/public/?url=comments/update
     */
    public function update(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $commentId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);

        if (!Csrf::check()) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=comments/edit&id=' . ($commentId && $commentId > 0 ? $commentId : ''));
            exit;
        }

        if (!$commentId || $commentId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid comment reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Comment not found or has been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Server-side ownership check
        $userId = (int) $_SESSION['user_id'];
        if ((int) $comment['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Unauthorized: You can only edit your own comments.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $content = trim($_POST['content'] ?? '');

        // Validation
        $errors = [];
        if ($content === '') {
            $errors[] = 'Comment content cannot be empty.';
        } elseif (mb_strlen($content) > 1000) {
            $errors[] = 'Comment must be 1000 characters or fewer.';
        }

        if (!empty($errors)) {
            // Re-render edit view with validation errors preserving typed text
            $comment['content'] = $content;
            $pageTitle = 'Edit Comment — Mini Social Network';
            require_once BASE_PATH . '/app/views/comments/edit.php';
            return;
        }

        $updated = $this->commentModel->updateComment($commentId, $content);

        if ($updated) {
            $_SESSION['flash_success'] = 'Comment updated successfully.';
        } else {
            http_response_code(500);
            $_SESSION['flash_error'] = 'Could not update your comment due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/#post-' . $comment['post_id']);
        exit;
    }

    // ─── Delete Comment (POST) ────────────────────────────────────────────────

    /**
     * Delete an existing comment after verifying ownership.
     * Route: POST /SocialNetwork/public/?url=comments/delete
     */
    public function delete(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if (!Csrf::check()) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $commentId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);
        $userId    = (int) $_SESSION['user_id'];

        if (!$commentId || $commentId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid comment reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Comment not found or has already been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Server-side ownership check
        if ((int) $comment['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Unauthorized: You can only delete your own comments.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $postId = (int) $comment['post_id'];
        $deleted = $this->commentModel->deleteComment($commentId);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Comment deleted successfully.';
        } else {
            http_response_code(500);
            $_SESSION['flash_error'] = 'Could not delete the comment. Please try again.';
        }

        header('Location: /SocialNetwork/public/#post-' . $postId);
        exit;
    }
}
