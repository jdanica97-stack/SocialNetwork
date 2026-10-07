<?php

/**
 * app/models/UserModel.php
 *
 * Handles ALL database operations related to the `users` table.
 *
 * Responsibilities (Step 5 — Authentication):
 *   - Find a user by username
 *   - Check if a username already exists
 *   - Insert (register) a new user
 *
 * Responsibilities (Step 6 — Profile):
 *   - Find a user by their primary-key ID
 *   - Update a user's full name and bio
 *   - Update a user's profile image path
 *
 * Rules:
 *   - No HTML output here — this is a Model, not a View.
 *   - No business logic, validation, or session handling here
 *     — those belong in the Controller.
 *   - Every query uses PDO prepared statements to prevent SQL injection.
 */

class UserModel
{
    // Holds the PDO database connection
    private PDO $db;

    /**
     * Constructor — receives the PDO connection from the controller.
     *
     * @param PDO $db  Active PDO connection from getDBConnection()
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─── Read Operations ──────────────────────────────────────────────────────

    /**
     * Find and return a single user row by username.
     *
     * Used during login to retrieve the stored password hash
     * and other user details.
     *
     * @param  string      $username  The username to look up.
     * @return array|false            Associative array of the user row,
     *                                or false if not found.
     */
    public function findByUsername(string $username): array|false
    {
        $sql  = 'SELECT id, username, password, full_name, bio, profile_image, created_at
                 FROM users
                 WHERE username = :username
                 LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        return $stmt->fetch(); // PDO::FETCH_ASSOC set globally in config/database.php
    }

    /**
     * Check whether a username is already taken.
     *
     * Used during registration to reject duplicate usernames
     * before attempting an INSERT.
     *
     * @param  string $username  The username to check.
     * @return bool              true if the username exists, false otherwise.
     */
    public function usernameExists(string $username): bool
    {
        $sql  = 'SELECT COUNT(*) FROM users WHERE username = :username';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        return (int) $stmt->fetchColumn() > 0;
    }

    // ─── Write Operations ─────────────────────────────────────────────────────

    /**
     * Insert a new user record into the `users` table.
     *
     * The password stored here must already be a bcrypt hash
     * produced by password_hash() — the Model never handles raw passwords.
     *
     * @param  string $username      The chosen username.
     * @param  string $passwordHash  The bcrypt hash of the user's password.
     * @param  string $fullName      The user's full name.
     * @return bool                  true on success, false on failure.
     */
    public function createUser(string $username, string $passwordHash, string $fullName): bool
    {
        $sql = 'INSERT INTO users (username, password, full_name)
                VALUES (:username, :password, :full_name)';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':username'  => $username,
            ':password'  => $passwordHash,
            ':full_name' => $fullName,
        ]);
    }

    // ─── Step 6: Profile Operations ───────────────────────────────────────────

    /**
     * Find and return a single user row by their primary-key ID.
     *
     * Used by ProfileController to load the logged-in user's data
     * without exposing the password to the View.
     *
     * @param  int         $id  The user's primary-key ID.
     * @return array|false      Associative array of the user row,
     *                          or false if not found.
     */
    public function findById(int $id): array|false
    {
        // Deliberately exclude `password` — it must never appear in profile views.
        $sql = 'SELECT id, username, full_name, bio, profile_image, created_at
                FROM users
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Update the user's editable profile fields (full name and bio).
     *
     * Called by ProfileController after successful validation.
     * The username and password are intentionally NOT updatable here.
     *
     * @param  int    $id        The user's primary-key ID (from session).
     * @param  string $fullName  Validated, trimmed full name.
     * @param  string $bio       Validated, trimmed bio (may be empty string).
     * @return bool              true on success, false on DB failure.
     */
    public function updateProfile(int $id, string $fullName, string $bio): bool
    {
        $sql = 'UPDATE users
                SET full_name = :full_name,
                    bio       = :bio
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':full_name' => $fullName,
            ':bio'       => $bio,
            ':id'        => $id,
        ]);
    }

    /**
     * Update only the profile_image column for a given user.
     *
     * Kept separate from updateProfile() so an image upload failure
     * does not roll back text-field changes.
     *
     * @param  int    $id        The user's primary-key ID (from session).
     * @param  string $filename  The stored filename (e.g. "abc123.jpg").
     * @return bool              true on success, false on DB failure.
     */
    public function updateProfileImage(int $id, string $filename): bool
    {
        $sql = 'UPDATE users
                SET profile_image = :profile_image
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':profile_image' => $filename,
            ':id'            => $id,
        ]);
    }
}
