<?php

/**
 * app/controllers/PostController.php
 *
 * Handles all actions related to posts and the newsfeed:
 *   - index()  — Display the main newsfeed with posts from all users (GET)
 *   - create() — Process creating a new post with optional image (POST)
 *   - edit()   — Show edit post form for own post (GET)
 *   - update() — Process updating post content (POST)
 *   - delete() — Process deleting own post (POST)
 *
 * Security contract:
 *   - Enforces session authentication for create, edit, update, delete.
 *   - Strict ownership check: users can ONLY edit or delete their own posts.
 *   - User ID is strictly obtained from $_SESSION['user_id'].
 *   - Validates post content and optional image uploads safely.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';

class PostController
{
    private PostModel $postModel;
    private CommentModel $commentModel;
    private LikeModel $likeModel;

    // Allowed image MIME types for post attachments
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    // Max post image file size: 5 MB
    private int $maxImageSize = 5242880;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $db = getDBConnection();
        $this->postModel    = new PostModel($db);
        $this->commentModel = new CommentModel($db);
        $this->likeModel    = new LikeModel($db);
    }

    // ─── Authentication Guard ─────────────────────────────────────────────────

    /**
     * Enforce active user session before protected operations.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to manage posts.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    // ─── Newsfeed (GET) ───────────────────────────────────────────────────────

    /**
     * Display the main newsfeed.
     * Retrieves posts from ALL users ordered from newest to oldest.
     * Loads associated comments, like counts, and user like states.
     */
    public function index(): void
    {
        $pageTitle = 'Mini Social Network — Home';

        // Flash messages
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // 1. Get all posts from all users (newest first)
        $posts = $this->postModel->getAllPosts();

        // 2. Load comments, likes count, and user like states for each post
        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        $commentsByPost  = [];
        $likeCountByPost = [];
        $hasLikedByPost  = [];

        foreach ($posts as $p) {
            $pId = (int) $p['id'];
            $commentsByPost[$pId]  = $this->commentModel->getByPostId($pId);
            $likeCountByPost[$pId] = $this->likeModel->countLikes($pId);
            $hasLikedByPost[$pId]  = $currentUserId ? $this->likeModel->hasLiked($pId, $currentUserId) : false;
        }

        // 3. Render newsfeed view
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/posts/feed.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    // ─── Create Post (POST) ───────────────────────────────────────────────────

    /**
     * Create a new post.
     * Validates text content and optional image upload.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $userId  = (int) $_SESSION['user_id'];
        $content = trim($_POST['content'] ?? '');
        $imageName = null;

        if ($content === '') {
            $_SESSION['flash_error'] = 'Post content cannot be empty.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if (mb_strlen($content) > 5000) {
            $_SESSION['flash_error'] = 'Post content exceeds the 5,000 character limit.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Handle optional image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash_error'] = 'An error occurred while uploading the image.';
                header('Location: /SocialNetwork/public/');
                exit;
            }

            if ($file['size'] > $this->maxImageSize) {
                $_SESSION['flash_error'] = 'Image size must be 5 MB or smaller.';
                header('Location: /SocialNetwork/public/');
                exit;
            }

            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
                $_SESSION['flash_error'] = 'Invalid image format. Allowed formats: JPG, PNG, GIF, WebP.';
                header('Location: /SocialNetwork/public/');
                exit;
            }

            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
            ];
            $ext = $mimeToExt[$mimeType] ?? 'jpg';

            $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
            $uploadDir  = BASE_PATH . '/public/assets/images/posts/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imageName = 'posts/' . $uniqueName;
            } else {
                $_SESSION['flash_error'] = 'Failed to save the uploaded image.';
                header('Location: /SocialNetwork/public/');
                exit;
            }
        }

        $newPostId = $this->postModel->create($userId, $content, $imageName);

        if ($newPostId) {
            $_SESSION['flash_success'] = 'Post created successfully!';
            header('Location: /SocialNetwork/public/#post-' . $newPostId);
        } else {
            $_SESSION['flash_error'] = 'Failed to create post. Please try again.';
            header('Location: /SocialNetwork/public/');
        }
        exit;
    }

    // ─── Edit Post (GET) ──────────────────────────────────────────────────────

    /**
     * Show form to edit an existing post.
     * Enforces strict ownership check.
     */
    public function edit(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null);

        if (!$postId) {
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $post = $this->postModel->getById($postId);

        if (!$post || (int) $post['user_id'] !== $userId) {
            $_SESSION['flash_error'] = 'Post not found or you are not authorized to edit it.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $pageTitle = 'Edit Post — Mini Social Network';
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/posts/edit.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
        exit;
    }

    // ─── Update Post (POST) ───────────────────────────────────────────────────

    /**
     * Process updating an existing post.
     * Enforces strict ownership check.
     */
    public function update(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $userId  = (int) $_SESSION['user_id'];
        $postId  = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);
        $content = trim($_POST['content'] ?? '');

        if (!$postId || $content === '') {
            $_SESSION['flash_error'] = 'Content cannot be empty.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if (mb_strlen($content) > 5000) {
            $_SESSION['flash_error'] = 'Post content exceeds 5,000 characters.';
            header('Location: /SocialNetwork/public/?url=posts/edit&id=' . $postId);
            exit;
        }

        $updated = $this->postModel->update($postId, $userId, $content);

        if ($updated) {
            $_SESSION['flash_success'] = 'Post updated successfully.';
        } else {
            // Note: rowCount() is 0 if content didn't change
            $post = $this->postModel->getById($postId);
            if ($post && (int) $post['user_id'] === $userId) {
                $_SESSION['flash_success'] = 'Post saved (no changes detected).';
            } else {
                $_SESSION['flash_error'] = 'Unauthorized or post could not be updated.';
            }
        }

        header('Location: /SocialNetwork/public/#post-' . $postId);
        exit;
    }

    // ─── Delete Post (POST) ───────────────────────────────────────────────────

    /**
     * Delete an existing post.
     * Enforces strict ownership check.
     */
    public function delete(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);

        if (!$postId) {
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Fetch post to check image and ownership
        $post = $this->postModel->getById($postId);

        if (!$post || (int) $post['user_id'] !== $userId) {
            $_SESSION['flash_error'] = 'You are not authorized to delete this post.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Delete associated image file from disk if present
        if (!empty($post['image'])) {
            $imagePath = BASE_PATH . '/public/assets/images/' . $post['image'];
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleted = $this->postModel->delete($postId, $userId);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Post deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not delete post.';
        }

        header('Location: /SocialNetwork/public/');
        exit;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Format a timestamp into a human-readable relative time (e.g., "10 minutes ago"),
     * falling back to formatted date/time for older posts.
     *
     * @param string $datetime
     * @return string
     */
    public static function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        if (!$time) {
            return $datetime;
        }

        $diff = time() - $time;

        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $mins = max(1, (int) floor($diff / 60));
            return $mins === 1 ? '1 minute ago' : $mins . ' minutes ago';
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours === 1 ? '1 hour ago' : $hours . ' hours ago';
        }
        if ($diff < 604800) {
            $days = (int) floor($diff / 86400);
            return $days === 1 ? 'Yesterday' : $days . ' days ago';
        }

        return date('M j, Y \a\t g:i A', $time);
    }
}
