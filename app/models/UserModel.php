<?php

/**
 * app/models/UserModel.php
 *
 * Handles ALL database operations related to the `users` table.
 *
 * Responsibilities:
 *   - Find a user by username
 *   - Check if a username already exists
 *   - Insert (register) a new user
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
}
