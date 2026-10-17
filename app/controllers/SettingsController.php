<?php

/**
 * app/controllers/SettingsController.php
 *
 * Handles Account Settings:
 *   - showSettings()   — Display settings view (GET)
 *   - updateUsername() — Process username change (POST)
 *   - updatePassword() — Process password change (POST)
 *
 * Security contract:
 *   - Session authentication strictly enforced.
 *   - CSRF protection on all mutating actions.
 *   - Password verification via password_verify() and hashing with PASSWORD_BCRYPT.
 *   - Parameterized PDO queries through UserModel.
 *   - Safe error/success messaging via flash sessions.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class SettingsController
{
    private UserModel $userModel;

    public function __construct()
    {
        Csrf::initSession();
        $db = getDBConnection();
        $this->userModel = new UserModel($db);
    }

    /**
     * Enforce active user session before accessing settings.
     */
    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to access account settings.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }

        return (int) $_SESSION['user_id'];
    }

    /**
     * Display the Account Settings page.
     * Route: GET /SocialNetwork/public/?url=settings
     */
    public function showSettings(): void
    {
        $userId = $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['flash_error'] = 'User account not found.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $pageTitle = 'Account Settings — Orbit';
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/settings/index.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Process changing the user's username.
     * Route: POST /SocialNetwork/public/?url=settings/username
     */
    public function updateUsername(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        $newUsername = trim($_POST['username'] ?? '');

        // 1. Required field check
        if ($newUsername === '') {
            $_SESSION['flash_error'] = 'Username cannot be empty.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 2. Format validation: 3-50 chars, alphanumeric and underscores only
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $newUsername)) {
            $_SESSION['flash_error'] = 'Username must be 3–50 characters and may only contain letters, numbers, and underscores.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            $_SESSION['flash_error'] = 'User account not found.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 3. Check if unchanged
        if ($newUsername === $currentUser['username']) {
            $_SESSION['flash_success'] = 'Username is already set to @' . $newUsername . '.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 4. Check uniqueness against other users
        if ($this->userModel->usernameExistsExcludingUser($newUsername, $userId)) {
            $_SESSION['flash_error'] = 'That username is already taken. Please choose another.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 5. Update in database
        $updated = $this->userModel->updateUsername($userId, $newUsername);
        if ($updated) {
            // Update session variable so navbar and sidebar update immediately
            $_SESSION['user_username'] = $newUsername;
            $_SESSION['flash_success'] = 'Username successfully updated to @' . $newUsername . '.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update username due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=settings');
        exit;
    }

    /**
     * Process changing the user's password.
     * Route: POST /SocialNetwork/public/?url=settings/password
     */
    public function updatePassword(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        if (!Csrf::check()) {
            $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token). Please try again.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password']     ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Required field checks
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['flash_error'] = 'Please fill in all password fields.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 2. Fetch stored hash and verify current password
        $storedHash = $this->userModel->getUserPasswordHash($userId);
        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            $_SESSION['flash_error'] = 'Your current password is incorrect.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 3. Minimum length requirement for new password
        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'New password must be at least 8 characters long.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 4. Password confirmation match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'The new password and confirmation do not match.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 5. Prevent setting identical password
        if (password_verify($newPassword, $storedHash)) {
            $_SESSION['flash_error'] = 'Your new password cannot be the same as your current password.';
            header('Location: /SocialNetwork/public/?url=settings');
            exit;
        }

        // 6. Securely hash and update
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $updated = $this->userModel->updatePassword($userId, $newHash);

        if ($updated) {
            $_SESSION['flash_success'] = 'Password changed successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update password due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=settings');
        exit;
    }
}
