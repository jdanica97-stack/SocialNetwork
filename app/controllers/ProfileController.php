<?php

/**
 * app/controllers/ProfileController.php
 *
 * Handles all profile-related actions for the currently logged-in user:
 *   - showProfile()    — Load and display the user's profile page (GET)
 *   - showEdit()       — Display the profile edit form pre-filled with current data (GET)
 *   - update()         — Process the edit form: validate, save, redirect (POST)
 *   - updateUsername() — Process username/name change form (POST)
 *   - updatePassword() — Process password change form (POST)
 *
 * Security contract:
 *   - Every method begins by verifying the session (requireAuth()).
 *   - The user ID always comes from $_SESSION['user_id'] — never from
 *     a URL parameter or a form field — so a user cannot tamper with
 *     their own ID to edit someone else's profile.
 *   - CSRF protection on all mutating actions.
 *   - Output escaping is done in the Views, not here.
 *   - SQL is done in UserModel, not here.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';
require_once BASE_PATH . '/app/controllers/PostController.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';
require_once BASE_PATH . '/app/helpers/PasswordValidator.php';

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
        Csrf::initSession();

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
        if (isset($_GET['id'])) {
            if (!is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
                $_SESSION['flash_error'] = 'Invalid user profile reference.';
                http_response_code(400);
                header('Location: /SocialNetwork/public/');
                exit;
            }
            $targetUserId = (int) $_GET['id'];
        } else {
            $targetUserId = $currentUserId;
        }

        $user = $this->userModel->findById($targetUserId);

        if ($user === false) {
            $_SESSION['flash_error'] = 'User profile could not be found.';
            http_response_code(404);
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
        $commentsByPost     = [];
        $commentCountByPost = [];
        $likeCountByPost    = [];
        $hasLikedByPost     = [];

        foreach ($posts as $p) {
            $pId = (int) $p['id'];
            $commentsByPost[$pId]     = $this->commentModel->getPreviewByPostId($pId, 2);
            $commentCountByPost[$pId] = $this->commentModel->countByPostId($pId);
            $likeCountByPost[$pId]    = $this->likeModel->countLikes($pId);
            $hasLikedByPost[$pId]     = $this->likeModel->hasLiked($pId, $currentUserId);
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        // We need the current user data so the form can be re-displayed on error
        $user = $this->userModel->findById($userId);
        if ($user === false) {
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $errors = [];

        // ── CSRF Check ────────────────────────────────────────────────────────
        if (!Csrf::check()) {
            $errors[] = 'Security verification failed (invalid CSRF token). Please try again.';
            $pageTitle = 'Edit Profile — Mini Social Network';
            require_once BASE_PATH . '/app/views/profile/edit.php';
            return;
        }

        // ── Collect input ─────────────────────────────────────────────────────
        $bio = trim($_POST['bio'] ?? '');

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

        // ── Persist bio ───────────────────────────────────────────────────────
        $textUpdated = $this->userModel->updateBio($userId, $bio);

        // ── Persist new image (only if one was successfully uploaded) ─────────
        $imgUpdated = true;
        if ($newImageFilename !== null) {
            // Delete the old image file to avoid orphaned files accumulating
            if (!empty($user['profile_image'])) {
                $oldFile = $this->uploadDir . $user['profile_image'];
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $imgUpdated = $this->userModel->updateProfileImage($userId, $newImageFilename);
        }

        if (!$textUpdated || !$imgUpdated) {
            http_response_code(500);
            $errors[] = 'An unexpected database error occurred while updating your profile. Please try again.';
            $pageTitle = 'Edit Profile — Mini Social Network';
            require_once BASE_PATH . '/app/views/profile/edit.php';
            return;
        }

        // ── Keep the session's profile image in sync ──────────────────────────
        if ($newImageFilename !== null && $imgUpdated) {
            $_SESSION['user_profile_image'] = $newImageFilename;
        }

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

    // ─── Update Account Information (Full Name + Username) (POST) ──────────────

    /**
     * Process updating both Full Name and Username in a single atomic operation.
     * Route: POST /SocialNetwork/public/?url=profile/account
     */
    public function updateAccount(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');

        // ── Validate Full Name ────────────────────────────────────────────────
        if ($fullName === '') {
            $_SESSION['flash_error'] = 'Full name is required.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }
        if (mb_strlen($fullName) > 100) {
            $_SESSION['flash_error'] = 'Full name must not exceed 100 characters.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        // ── Validate Username ─────────────────────────────────────────────────
        if ($username === '') {
            $_SESSION['flash_error'] = 'Username cannot be empty.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $_SESSION['flash_error'] = 'Username must be 3–50 characters and may only contain letters, numbers, and underscores.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        // ── Check if username is taken by another user ────────────────────────
        if ($this->userModel->usernameExistsExcludingUser($username, $userId)) {
            $_SESSION['flash_error'] = 'That username is already taken. Please choose another.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        // ── Persist both fields atomically ────────────────────────────────────
        $updated = $this->userModel->updateAccountInfo($userId, $fullName, $username);
        if ($updated) {
            $_SESSION['user_full_name'] = $fullName;
            $_SESSION['user_username']  = $username;
            $_SESSION['flash_success'] = 'Account information updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update account information due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=profile');
        exit;
    }

    // ─── Update Username (POST) ───────────────────────────────────────────────

    /**
     * Process changing the user's username.
     * Route: POST /SocialNetwork/public/?url=profile/username
     */
    public function updateUsername(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $newUsername = trim($_POST['username'] ?? '');

        if ($newUsername === '') {
            $_SESSION['flash_error'] = 'Username cannot be empty.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $newUsername)) {
            $_SESSION['flash_error'] = 'Username must be 3–50 characters and may only contain letters, numbers, and underscores.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            $_SESSION['flash_error'] = 'User account not found.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if ($newUsername === $currentUser['username']) {
            $_SESSION['flash_success'] = 'Username is already set to @' . $newUsername . '.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if ($this->userModel->usernameExistsExcludingUser($newUsername, $userId)) {
            $_SESSION['flash_error'] = 'That username is already taken. Please choose another.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $updated = $this->userModel->updateUsername($userId, $newUsername);
        if ($updated) {
            $_SESSION['user_username'] = $newUsername;
            $_SESSION['flash_success'] = 'Username successfully updated to @' . $newUsername . '.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update username due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=profile');
        exit;
    }

    // ─── Update Password (POST) ───────────────────────────────────────────────

    /**
     * Process changing the user's password.
     * Route: POST /SocialNetwork/public/?url=profile/password
     */
    public function updatePassword(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password']     ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['flash_error'] = 'Please fill in all password fields.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $storedHash = $this->userModel->getUserPasswordHash($userId);
        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            $_SESSION['flash_error'] = 'Your current password is incorrect.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $pwErrors = PasswordValidator::validate($newPassword, $confirmPassword, 'New password');
        if (!empty($pwErrors)) {
            $_SESSION['flash_error'] = implode(' ', $pwErrors);
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        if (password_verify($newPassword, $storedHash)) {
            $_SESSION['flash_error'] = 'Your new password cannot be the same as your current password.';
            header('Location: /SocialNetwork/public/?url=profile');
            exit;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $updated = $this->userModel->updatePassword($userId, $newHash);

        if ($updated) {
            $_SESSION['flash_success'] = 'Password changed successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update password due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=profile');
        exit;
    }
}
