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
        try {
            $sql  = 'SELECT id, username, password, full_name, bio, profile_image, created_at
                     FROM users
                     WHERE username = :username
                     LIMIT 1';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserModel::findByUsername database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $sql  = 'SELECT COUNT(*) FROM users WHERE username = :username';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('UserModel::usernameExists database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $sql = 'INSERT INTO users (username, password, full_name)
                    VALUES (:username, :password, :full_name)';

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':username'  => $username,
                ':password'  => $passwordHash,
                ':full_name' => $fullName,
            ]);
        } catch (PDOException $e) {
            error_log('UserModel::createUser database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            // Deliberately exclude `password` — it must never appear in profile views.
            $sql = 'SELECT id, username, full_name, bio, profile_image, created_at
                    FROM users
                    WHERE id = :id
                    LIMIT 1';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserModel::findById database error: ' . $e->getMessage());
            return false;
        }
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
        try {
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
        } catch (PDOException $e) {
            error_log('UserModel::updateProfile database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $sql = 'UPDATE users
                    SET profile_image = :profile_image
                    WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':profile_image' => $filename,
                ':id'            => $id,
            ]);
        } catch (PDOException $e) {
            error_log('UserModel::updateProfileImage database error: ' . $e->getMessage());
            return false;
        }
    }

    // ─── Step 11: Search Operations ───────────────────────────────────────────

    /**
     * Search users by username or full name.
     * Uses PDO prepared statements with SQL LIKE for partial matching.
     * Excludes password from results.
     *
     * @param string $keyword Search keyword
     * @param int|null $limit Optional max results
     * @return array Matching user rows
     */
    public function searchUsers(string $keyword, ?int $limit = 50): array
    {
        try {
            $keyword = trim($keyword);
            if ($keyword === '') {
                return [];
            }

            $sql = 'SELECT id, username, full_name, bio, profile_image, created_at
                    FROM users
                    WHERE username LIKE :kw_username
                       OR full_name LIKE :kw_name
                    ORDER BY full_name ASC, username ASC';

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':kw_username', '%' . $keyword . '%', PDO::PARAM_STR);
                $stmt->bindValue(':kw_name', '%' . $keyword . '%', PDO::PARAM_STR);
                $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':kw_username' => '%' . $keyword . '%',
                    ':kw_name'     => '%' . $keyword . '%',
                ]);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('UserModel::searchUsers database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all users up to a limit (useful for contact picker / user directory).
     * Excludes password.
     *
     * @param int $limit
     * @return array
     */
    public function getAllUsers(int $limit = 100): array
    {
        try {
            $sql = 'SELECT id, username, full_name, bio, profile_image, created_at
                    FROM users
                    ORDER BY full_name ASC, username ASC
                    LIMIT :limit';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('UserModel::getAllUsers database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the stored password hash for a user by their primary ID.
     * Used exclusively for authentication / verifying current password in Settings.
     *
     * @param int $id User ID
     * @return string|false
     */
    public function getUserPasswordHash(int $id): string|false
    {
        try {
            $sql  = 'SELECT password FROM users WHERE id = :id LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $col = $stmt->fetchColumn();
            return $col !== false ? (string) $col : false;
        } catch (PDOException $e) {
            error_log('UserModel::getUserPasswordHash database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a username is already taken by any user OTHER than the specified user ID.
     *
     * @param string $username
     * @param int $userId
     * @return bool
     */
    public function usernameExistsExcludingUser(string $username, int $userId): bool
    {
        try {
            $sql  = 'SELECT COUNT(*) FROM users WHERE username = :username AND id != :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':user_id'  => $userId,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('UserModel::usernameExistsExcludingUser database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the username for a specific user ID.
     *
     * @param int $id
     * @param string $newUsername
     * @return bool
     */
    public function updateUsername(int $id, string $newUsername): bool
    {
        try {
            $sql  = 'UPDATE users SET username = :username WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':username' => $newUsername,
                ':id'       => $id,
            ]);
        } catch (PDOException $e) {
            error_log('UserModel::updateUsername database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the password hash for a specific user ID.
     *
     * @param int $id
     * @param string $newPasswordHash
     * @return bool
     */
    public function updatePassword(int $id, string $newPasswordHash): bool
    {
        try {
            $sql  = 'UPDATE users SET password = :password WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':password' => $newPasswordHash,
                ':id'       => $id,
            ]);
        } catch (PDOException $e) {
            error_log('UserModel::updatePassword database error: ' . $e->getMessage());
            return false;
        }
    }
}

