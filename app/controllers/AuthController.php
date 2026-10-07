<?php

/**
 * app/controllers/AuthController.php
 *
 * Handles all authentication-related actions:
 *   - showRegister()  — Display the registration form (GET)
 *   - register()      — Process the registration form (POST)
 *   - showLogin()     — Display the login form (GET)
 *   - login()         — Process the login form (POST)
 *   - logout()        — Destroy the session and redirect (GET)
 *
 * This controller:
 *   - Validates user input
 *   - Calls UserModel for database operations
 *   - Hashes passwords with password_hash()
 *   - Verifies passwords with password_verify()
 *   - Manages PHP sessions
 *   - Redirects after success
 *   - Passes error/success messages to views
 *
 * Rules:
 *   - No direct SQL queries here — delegate to UserModel.
 *   - No HTML output here — render views.
 *   - Business logic and validation live here, not in views.
 */

// Load the database connection helper and the User model
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';

class AuthController
{
    // Shared UserModel instance used by all methods
    private UserModel $userModel;

    /**
     * Constructor — start session and instantiate UserModel.
     */
    public function __construct()
    {
        // Start the session if one isn't already running
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Create one PDO connection for this request and inject it into the model
        $this->userModel = new UserModel(getDBConnection());
    }

    // ─── Registration ─────────────────────────────────────────────────────────

    /**
     * Show the registration form (GET request).
     *
     * Passes an empty errors array and old input to the view
     * so the template always has those variables available.
     */
    public function showRegister(): void
    {
        $errors = [];
        $old    = []; // will hold previously entered values on re-display

        require_once BASE_PATH . '/app/views/auth/register.php';
    }

    /**
     * Process the registration form (POST request).
     *
     * Validation order:
     *   1. Check required fields
     *   2. Check username length / characters
     *   3. Check password length
     *   4. Check password confirmation match
     *   5. Check username uniqueness (DB query)
     *
     * On success → redirect to login with a flash success message.
     * On failure → re-display the form with errors and old input.
     */
    public function register(): void
    {
        // ── Collect & sanitize raw POST input ────────────────────────────────
        // trim() removes accidental leading/trailing whitespace
        // htmlspecialchars() is used in views for output escaping, not here
        $fullName        = trim($_POST['full_name']        ?? '');
        $username        = trim($_POST['username']         ?? '');
        $password        = $_POST['password']              ?? '';
        $confirmPassword = $_POST['confirm_password']      ?? '';

        // Keep old values so the form can be re-populated on error
        $old = [
            'full_name' => $fullName,
            'username'  => $username,
        ];

        $errors = [];

        // ── Validation ───────────────────────────────────────────────────────

        // 1. Required field checks
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }
        if ($username === '') {
            $errors[] = 'Username is required.';
        }
        if ($password === '') {
            $errors[] = 'Password is required.';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Please confirm your password.';
        }

        // 2. Username format: 3–50 characters, letters/digits/underscores only
        if ($username !== '' && !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3–50 characters and may only contain letters, numbers, and underscores.';
        }

        // 3. Minimum password length (8 characters)
        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        // 4. Password confirmation match
        if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // 5. Username uniqueness — only hit the DB if everything else is valid
        if (empty($errors) && $this->userModel->usernameExists($username)) {
            $errors[] = 'That username is already taken. Please choose another.';
        }

        // ── Abort if there are validation errors ─────────────────────────────
        if (!empty($errors)) {
            require_once BASE_PATH . '/app/views/auth/register.php';
            return;
        }

        // ── Hash the password ─────────────────────────────────────────────────
        // PASSWORD_BCRYPT uses the bcrypt algorithm.
        // The result is a 60-character string that includes the salt.
        // NEVER store the raw $password.
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // ── Insert the new user via the model ─────────────────────────────────
        $created = $this->userModel->createUser($username, $passwordHash, $fullName);

        if (!$created) {
            // Unexpected DB failure — show a generic error
            $errors[] = 'Registration failed due to a server error. Please try again.';
            require_once BASE_PATH . '/app/views/auth/register.php';
            return;
        }

        // ── Success: store a flash message and redirect to login ──────────────
        $_SESSION['flash_success'] = 'Account created successfully! You can now log in.';
        $this->redirect('/SocialNetwork/public/?url=auth/login');
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    /**
     * Show the login form (GET request).
     */
    public function showLogin(): void
    {
        // Pull and clear any flash messages set by register() or logout()
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        $errors = [];
        $old    = [];

        require_once BASE_PATH . '/app/views/auth/login.php';
    }

    /**
     * Process the login form (POST request).
     *
     * Flow:
     *   1. Collect input
     *   2. Validate required fields
     *   3. Look up user by username
     *   4. Verify password with password_verify()
     *   5. Create session variables
     *   6. Redirect to home / dashboard
     */
    public function login(): void
    {
        // ── Collect input ─────────────────────────────────────────────────────
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';

        $old    = ['username' => $username];
        $errors = [];

        // ── Validation ────────────────────────────────────────────────────────

        if ($username === '') {
            $errors[] = 'Username is required.';
        }
        if ($password === '') {
            $errors[] = 'Password is required.';
        }

        if (!empty($errors)) {
            $flashSuccess = null;
            require_once BASE_PATH . '/app/views/auth/login.php';
            return;
        }

        // ── Look up the user ──────────────────────────────────────────────────
        $user = $this->userModel->findByUsername($username);

        // Use a generic "invalid credentials" message to avoid revealing
        // whether the username or the password was wrong (security best practice).
        if ($user === false) {
            $errors[] = 'Invalid username or password.';
            $flashSuccess = null;
            require_once BASE_PATH . '/app/views/auth/login.php';
            return;
        }

        // ── Verify the password ───────────────────────────────────────────────
        // password_verify() compares the raw password against the stored hash.
        // It is timing-safe and handles the salt automatically.
        if (!password_verify($password, $user['password'])) {
            $errors[] = 'Invalid username or password.';
            $flashSuccess = null;
            require_once BASE_PATH . '/app/views/auth/login.php';
            return;
        }

        // ── Regenerate session ID to prevent session fixation attacks ─────────
        session_regenerate_id(true);

        // ── Store user info in the session ────────────────────────────────────
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_username']  = $user['username'];
        $_SESSION['user_full_name'] = $user['full_name'];

        // ── Redirect to the home / dashboard page ─────────────────────────────
        $this->redirect('/SocialNetwork/public/');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    /**
     * Destroy the session and redirect to the login page.
     *
     * Steps:
     *   1. Unset all session variables
     *   2. Destroy the session
     *   3. Redirect to login with a flash message
     */
    public function logout(): void
    {
        // 1. Clear all session data
        $_SESSION = [];

        // 2. Delete the session cookie from the browser
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // 3. Destroy the session on the server
        session_destroy();

        // 4. Start a fresh session just to hold the flash message
        session_start();
        $_SESSION['flash_success'] = 'You have been logged out successfully.';

        $this->redirect('/SocialNetwork/public/?url=auth/login');
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    /**
     * Send a Location header and stop execution.
     *
     * @param string $url  The URL to redirect to.
     */
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
