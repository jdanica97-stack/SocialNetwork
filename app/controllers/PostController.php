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
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class PostController
{
    private PostModel $postModel;
    private CommentModel $commentModel;
    private LikeModel $likeModel;
    private UserModel $userModel;

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
        Csrf::initSession();

        $db = getDBConnection();
        $this->postModel    = new PostModel($db);
        $this->commentModel = new CommentModel($db);
        $this->likeModel    = new LikeModel($db);
        $this->userModel    = new UserModel($db);
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
     * Display the main Home page.
     * If user is logged in: retrieves posts, comments, and likes for the newsfeed.
     * If user is logged out: does NOT fetch posts from database (server-side protection);
     * displays the guest welcome and about platform section.
     */
    public function index(): void
    {
        // Flash messages
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        $posts              = [];
        $commentsByPost     = [];
        $commentCountByPost = [];
        $likeCountByPost    = [];
        $hasLikedByPost     = [];

        $currentUserAvatar = null;

        // ── Server-Side Authentication Check ──────────────────────────────────────
        // Only fetch posts from database if the user is actively logged in
        if ($currentUserId !== null) {
            $pageTitle = 'Mini Social Network — Home';

            // 0. Load logged-in user details for profile picture display
            $currentUser = $this->userModel->findById($currentUserId);
            $displayName = !empty($currentUser['full_name']) ? $currentUser['full_name'] : ($_SESSION['user_full_name'] ?? 'User');
            if ($currentUser && !empty($currentUser['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $currentUser['profile_image'])) {
                $currentUserAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($currentUser['profile_image']);
            } else {
                $currentUserAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($displayName) . '&size=120&background=111113&color=f5f5f7&rounded=true&bold=true';
            }

            // 1. Get all posts from all users (newest first)
            $posts = $this->postModel->getAllPosts();

            // 2. Load only first 2 comments (preview), total count, and likes for each post
            foreach ($posts as $p) {
                $pId = (int) $p['id'];
                $commentsByPost[$pId]     = $this->commentModel->getPreviewByPostId($pId, 2);
                $commentCountByPost[$pId] = $this->commentModel->countByPostId($pId);
                $likeCountByPost[$pId]    = $this->likeModel->countLikes($pId);
                $hasLikedByPost[$pId]     = $this->likeModel->hasLiked($pId, $currentUserId);
            }
        } else {
            $pageTitle = 'Welcome — Mini Social Network';
        }

        // 3. Render view (newsfeed for logged-in users, guest landing for visitors)
        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/posts/feed.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
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

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
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

        // Handle optional multi-image upload
        $imagePaths = [];
        try {
            $imagePaths = $this->processUploadedImages();
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $newPostId = $this->postModel->create($userId, $content, $imagePaths);

        if ($newPostId) {
            $_SESSION['flash_success'] = 'Post created successfully!';
            header('Location: /SocialNetwork/public/#post-' . $newPostId);
        } else {
            $this->cleanupFiles($imagePaths);
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

        if (!$postId || $postId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $post = $this->postModel->getById($postId);

        if (!$post) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Post not found or has been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if ((int) $post['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'You are not authorized to edit this post.';
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
            http_response_code(405);
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);

        if (!Csrf::check()) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/' . ($postId && $postId > 0 ? '?url=posts/edit&id=' . $postId : ''));
            exit;
        }

        if (!$postId || $postId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $post = $this->postModel->getById($postId);

        if (!$post) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Post not found or has been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if ((int) $post['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Unauthorized: You can only edit your own posts.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $_SESSION['flash_error'] = 'Content cannot be empty.';
            header('Location: /SocialNetwork/public/?url=posts/edit&id=' . $postId);
            exit;
        }

        if (mb_strlen($content) > 5000) {
            $_SESSION['flash_error'] = 'Post content exceeds 5,000 characters.';
            header('Location: /SocialNetwork/public/?url=posts/edit&id=' . $postId);
            exit;
        }

        $newImages = [];
        try {
            $newImages = $this->processUploadedImages();
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /SocialNetwork/public/?url=posts/edit&id=' . $postId);
            exit;
        }

        $keepImages = isset($_POST['keep_images']) && is_array($_POST['keep_images']) ? $_POST['keep_images'] : [];
        $currentImages = $this->postModel->getImagesByPostId($postId);
        if (empty($currentImages) && !empty($post['image'])) {
            $currentImages = [$post['image']];
        }
        $validatedKeep = array_values(array_intersect($keepImages, $currentImages));

        $updated = $this->postModel->updatePostWithImages($postId, $userId, $content, $validatedKeep, $newImages);

        if ($updated) {
            $_SESSION['flash_success'] = 'Post updated successfully.';
        } else {
            $this->cleanupFiles($newImages);
            http_response_code(500);
            $_SESSION['flash_error'] = 'Failed to update post due to a server error. Please try again.';
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

        $userId = (int) $_SESSION['user_id'];
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);

        if (!$postId || $postId <= 0) {
            http_response_code(400);
            $_SESSION['flash_error'] = 'Invalid post reference.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Fetch post to check image and ownership
        $post = $this->postModel->getById($postId);

        if (!$post) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'Post not found or has already been deleted.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        if ((int) $post['user_id'] !== $userId) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'You are not authorized to delete this post.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Delete associated image file from disk if present
        if (!empty($post['image'])) {
            $imagePath = BASE_PATH . '/public/assets/images/' . $post['image'];
            if (is_file($imagePath)) {
                @unlink($imagePath);
            }
        }

        $deleted = $this->postModel->delete($postId, $userId);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Post deleted successfully.';
        } else {
            http_response_code(500);
            $_SESSION['flash_error'] = 'Could not delete post due to a server error.';
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

    /**
     * Process uploaded image(s) safely from $_FILES['images'] or $_FILES['image'].
     *
     * @return array Array of relative image paths (e.g. ['posts/xyz.jpg'])
     * @throws RuntimeException If an invalid file or size error occurs
     */
    private function processUploadedImages(): array
    {
        $uploadedPaths = [];
        $filesToProcess = [];

        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $filesToProcess[] = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
            }
        } elseif (isset($_FILES['image']) && !is_array($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $filesToProcess[] = $_FILES['image'];
        }

        if (empty($filesToProcess)) {
            return [];
        }

        $uploadDir = BASE_PATH . '/public/assets/images/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        foreach ($filesToProcess as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->cleanupFiles($uploadedPaths);
                throw new RuntimeException('An error occurred during image upload (error code: ' . (int)$file['error'] . ').');
            }

            if ($file['size'] > $this->maxImageSize) {
                $this->cleanupFiles($uploadedPaths);
                throw new RuntimeException('Image size exceeds the 5 MB limit.');
            }

            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
                $this->cleanupFiles($uploadedPaths);
                throw new RuntimeException('Invalid image format. Allowed formats: JPG, PNG, GIF, WebP.');
            }

            $ext = $mimeToExt[$mimeType] ?? 'jpg';
            $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
            $destination = $uploadDir . $uniqueName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $this->cleanupFiles($uploadedPaths);
                throw new RuntimeException('Failed to save the uploaded image.');
            }

            $uploadedPaths[] = 'posts/' . $uniqueName;
        }

        return $uploadedPaths;
    }

    /**
     * Clean up newly uploaded files if a subsequent batch file fails or post creation fails.
     *
     * @param array $paths Relative image paths
     */
    private function cleanupFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $fullPath = BASE_PATH . '/public/assets/images/' . $path;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
