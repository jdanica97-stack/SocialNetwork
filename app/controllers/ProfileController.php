<?php

/**
 * app/controllers/ProfileController.php
 *
 * Handles all profile-related actions for the currently logged-in user:
 *   - showProfile()  — Load and display the user's profile page (GET)
 *   - showEdit()     — Display the profile edit form pre-filled with current data (GET)
 *   - update()       — Process the edit form: validate, save, redirect (POST)
 *
 * Security contract:
 *   - Every method begins by verifying the session (requireAuth()).
 *   - The user ID always comes from $_SESSION['user_id'] — never from
 *     a URL parameter or a form field — so a user cannot tamper with
 *     their own ID to edit someone else's profile.
 *   - Output escaping is done in the Views, not here.
 *   - SQL is done in UserModel, not here.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';
require_once BASE_PATH . '/app/controllers/PostController.php';

class ProfileController
{
    private UserModel $userModel;
    private PostModel $postModel;
    private CommentModel $commentModel;
    private LikeModel $likeModel;

    // ── Upload settings ───────────────────────────────────────────────────────
    // Absolute server path where uploaded profile images are saved
    private string $uploadDir;

    // Web-accessible path prefix used in <img src="...">
    private string $uploadWebPath = '/SocialNetwork/public/assets/images/profiles/';

    // Allowed MIME types for profile pictures
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    // Maximum upload size: 2 MB in bytes
    private int $maxFileSize = 2097152;

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

        // Absolute path to the profiles upload directory
        $this->uploadDir = BASE_PATH . '/public/assets/images/profiles/';
    }

    // ─── Authentication Guard ─────────────────────────────────────────────────

    /**
     * Redirect to login if no session is active.
     * Called at the top of every public method.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            // Store a message so the login page can tell the user why they were redirected
            $_SESSION['flash_error'] = 'You must be logged in to view that page.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    // ─── View Profile ─────────────────────────────────────────────────────────

    /**
     * Load the logged-in user's data and render profile.php.
     *
     * Flow:
     *   session → user_id → UserModel::findById() → profile.php
     */
    public function showProfile(): void
    {
        $this->requireAuth();

        $currentUserId = (int) $_SESSION['user_id'];

        // If an optional 'id' parameter is provided (e.g., viewing another user's profile),
        // validate and use it; otherwise default to the authenticated user's own profile.
        $targetUserId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : $currentUserId);

        $user = $this->userModel->findById($targetUserId);

        if ($user === false) {
            $_SESSION['flash_error'] = 'User profile could not be found.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // Pull and clear any flash messages set by update()
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Fetch posts created by this user (newest first)
        $posts = $this->postModel->getByUserId($targetUserId);

        // Load comments, likes count, and current user's liked status for each post
        $commentsByPost  = [];
        $likeCountByPost = [];
        $hasLikedByPost  = [];

        foreach ($posts as $p) {
            $pId = (int) $p['id'];
            $commentsByPost[$pId]  = $this->commentModel->getByPostId($pId);
            $likeCountByPost[$pId] = $this->likeModel->countLikes($pId);
            $hasLikedByPost[$pId]  = $this->likeModel->hasLiked($pId, $currentUserId);
        }

        $isOwnProfile = ($targetUserId === $currentUserId);
        $pageTitle    = $isOwnProfile ? 'My Profile — Mini Social Network' : htmlspecialchars($user['full_name']) . ' — Profile';

        require_once BASE_PATH . '/app/views/profile/profile.php';
    }

    // ─── Edit Profile (GET) ───────────────────────────────────────────────────

    /**
     * Load the current user's data and render the edit form pre-filled.
     *
     * Flow:
     *   session → user_id → UserModel::findById() → edit.php
     */
    public function showEdit(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $user   = $this->userModel->findById($userId);

        if ($user === false) {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $errors = [];

        $pageTitle = 'Edit Profile — Mini Social Network';
        require_once BASE_PATH . '/app/views/profile/edit.php';
    }

    // ─── Update Profile (POST) ────────────────────────────────────────────────

    /**
     * Validate the submitted edit form and persist changes.
     *
     * Steps:
     *   1. Collect and trim POST fields
     *   2. Validate full name (required, max 100 chars)
     *   3. Validate bio (optional, max 500 chars)
     *   4. Handle profile image upload (optional)
     *   5. Call UserModel::updateProfile()
     *   6. If a new image was uploaded, call UserModel::updateProfileImage()
     *   7. Sync the session's display name
     *   8. Redirect to profile with a success message
     */
    public function update(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];

        // We need the current user data so the form can be re-displayed on error
        $user = $this->userModel->findById($userId);
        if ($user === false) {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        // ── Collect input ─────────────────────────────────────────────────────
        $fullName = trim($_POST['full_name'] ?? '');
        $bio      = trim($_POST['bio']       ?? '');

        $errors = [];

        // ── Validate full name ────────────────────────────────────────────────
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        } elseif (mb_strlen($fullName) > 100) {
            $errors[] = 'Full name must not exceed 100 characters.';
        }

        // ── Validate bio (optional) ───────────────────────────────────────────
        if (mb_strlen($bio) > 500) {
            $errors[] = 'Bio must not exceed 500 characters.';
        }

        // ── Handle profile image upload (optional) ────────────────────────────
        $newImageFilename = null; // will stay null if no file was chosen

        if (!empty($_FILES['profile_image']['name'])) {
            $uploadResult = $this->handleImageUpload($_FILES['profile_image']);

            if ($uploadResult['success']) {
                $newImageFilename = $uploadResult['filename'];
            } else {
                // Merge upload errors into the main errors array
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }

        // ── Re-display form if validation failed ──────────────────────────────
        if (!empty($errors)) {
            $pageTitle = 'Edit Profile — Mini Social Network';
            require_once BASE_PATH . '/app/views/profile/edit.php';
            return;
        }

        // ── Persist text fields ───────────────────────────────────────────────
        $this->userModel->updateProfile($userId, $fullName, $bio);

        // ── Persist new image (only if one was successfully uploaded) ─────────
        if ($newImageFilename !== null) {
            // Delete the old image file to avoid orphaned files accumulating
            if (!empty($user['profile_image'])) {
                $oldFile = $this->uploadDir . $user['profile_image'];
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $this->userModel->updateProfileImage($userId, $newImageFilename);
        }

        // ── Keep the session's display name in sync ────────────────────────────
        $_SESSION['user_full_name'] = $fullName;

        // ── Flash success and redirect ─────────────────────────────────────────
        $_SESSION['flash_success'] = 'Your profile has been updated successfully.';
        header('Location: /SocialNetwork/public/?url=profile');
        exit;
    }

    // ─── Private: Image Upload Handler ───────────────────────────────────────

    /**
     * Validate and move an uploaded profile image.
     *
     * Returns an associative array:
     *   ['success' => true,  'filename' => 'abc123.jpg']
     *   ['success' => false, 'errors'   => ['...', '...']]
     *
     * Security checks performed:
     *   - PHP upload error code
     *   - File size limit (2 MB)
     *   - MIME type via finfo (content-based, not extension-based)
     *   - Generates a random filename — original name is discarded
     *
     * @param  array $file  One element of $_FILES (e.g. $_FILES['profile_image'])
     * @return array
     */
    private function handleImageUpload(array $file): array
    {
        $errors = [];

        // 1. PHP-level upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed. Please try again. (Error code: ' . $file['error'] . ')';
            return ['success' => false, 'errors' => $errors];
        }

        // 2. File size check (before reading content)
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = 'Profile image must not exceed 2 MB.';
            return ['success' => false, 'errors' => $errors];
        }

        // 3. MIME-type check using finfo (reads file content, not just the extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            $errors[] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
            return ['success' => false, 'errors' => $errors];
        }

        // 4. Derive a safe file extension from the MIME type
        $extensionMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = $extensionMap[$mimeType];

        // 5. Generate a random, unpredictable filename — discard the user's original name
        $filename    = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $this->uploadDir . $filename;

        // 6. Ensure the upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // 7. Move the file from the PHP temp directory to the upload folder
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'Could not save the uploaded image. Please try again.';
            return ['success' => false, 'errors' => $errors];
        }

        return ['success' => true, 'filename' => $filename];
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    /**
     * Return the web-accessible URL for a stored profile image filename,
     * or null if no image is set (so the view can show a placeholder).
     *
     * This is a public helper so views can call it via the controller
     * if needed, but we keep it here to centralise the path logic.
     *
     * @param  string|null $filename
     * @return string|null
     */
    public function imageUrl(?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }
        return $this->uploadWebPath . rawurlencode($filename);
    }
}
