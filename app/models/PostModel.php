<?php

/**
 * app/models/PostModel.php
 *
 * Handles ALL database operations related to the `posts` table.
 *
 * Responsibilities (Step 10 — Newsfeed Improvements):
 *   - getAllPosts() — Retrieve all posts from all users, ordered newest first (READ)
 *   - getById()     — Retrieve a single post with author details (READ)
 *   - getByUserId() — Retrieve all posts by a specific user (READ)
 *   - create()      — Insert a new post (CREATE)
 *   - update()      — Update post content with ownership verification (UPDATE)
 *   - delete()      — Delete a post with ownership verification (DELETE)
 *   - countAll()    — Total count of posts in system
 *
 * Database Relationships:
 *   - users.id → posts.user_id (one user has many posts)
 *   - posts.id → comments.post_id (one post has many comments)
 *   - posts.id → likes.post_id (one post has many likes)
 *
 * Rules:
 *   - Pure database access: no HTML output, no validation logic, no session logic.
 *   - Every query uses PDO prepared statements to protect against SQL injection.
 *   - Table: posts (id, user_id, content, image, created_at)
 */

class PostModel
{
    private PDO $db;

    /**
     * Constructor — receives the active PDO connection.
     *
     * @param PDO $db Active PDO connection from getDBConnection()
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve all posts from all users for the newsfeed.
     * Ordered by created_at DESC (newest first), with p.id DESC as secondary sort.
     *
     * Joined with the `users` table to fetch the author's username, full_name, and profile_image.
     *
     * @param int|null $limit Optional maximum number of posts to retrieve (for pagination/infinite scroll)
     * @param int $offset Optional offset
     * @return array Array of associative arrays representing posts
     */
    public function getAllPosts(?int $limit = null, int $offset = 0): array
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC, p.id DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve a single post by its ID with author details.
     *
     * @param int $id Post ID
     * @return array|false Associative array of post data or false if not found
     */
    public function getById(int $id): array|false
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all posts created by a specific user.
     *
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC, p.id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new post.
     *
     * @param int $userId The author's user ID
     * @param string $content Text content of the post
     * @param string|null $image Optional image filename
     * @return int|false The newly created post ID, or false on failure
     */
    public function create(int $userId, string $content, ?string $image = null): int|false
    {
        $sql = 'INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)';
        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image'   => $image,
        ]);

        if ($success) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing post's content.
     * Includes strict ownership verification in the WHERE clause.
     *
     * @param int $id Post ID
     * @param int $userId Owner's user ID
     * @param string $content Updated content
     * @return bool True if updated, false if not found or unauthorized
     */
    public function update(int $id, int $userId, string $content): bool
    {
        $sql = 'UPDATE posts SET content = :content WHERE id = :id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':content' => $content,
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an existing post.
     * Includes strict ownership verification in the WHERE clause.
     * Cascading deletes on foreign keys in MySQL handle removing comments and likes.
     *
     * @param int $id Post ID
     * @param int $userId Owner's user ID
     * @return bool True if deleted, false if not found or unauthorized
     */
    public function delete(int $id, int $userId): bool
    {
        $sql = 'DELETE FROM posts WHERE id = :id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Count the total number of posts in the application.
     *
     * @return int
     */
    public function countAll(): int
    {
        $sql = 'SELECT COUNT(*) FROM posts';
        $stmt = $this->db->query($sql);

        return (int) $stmt->fetchColumn();
    }

    // ─── Step 11: Search Operations ───────────────────────────────────────────

    /**
     * Search posts by content keyword.
     * Joined with users table to include author details.
     * Ordered by created_at DESC (newest matching posts first).
     *
     * @param string $keyword Search keyword
     * @param int|null $limit Optional max results
     * @return array Array of matching posts
     */
    public function searchPosts(string $keyword, ?int $limit = 50): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.content LIKE :keyword
            ORDER BY p.created_at DESC, p.id DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':keyword' => '%' . $keyword . '%']);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

