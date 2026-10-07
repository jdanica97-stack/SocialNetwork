<?php

/**
 * app/models/CommentModel.php
 *
 * Handles ALL database operations related to the `comments` table.
 *
 * Responsibilities (Step 8 — Comments CRUD):
 *   - createComment()     — Insert a new comment for a post (CREATE)
 *   - getByPostId()       — Fetch all comments for a post with author details (READ)
 *   - findById()          — Retrieve a single comment row by its primary key ID (READ)
 *   - updateComment()     — Update the content of an existing comment (UPDATE)
 *   - deleteComment()     — Delete a comment by its primary key ID (DELETE)
 *   - isOwner()           — Verify if a specific user owns a comment (AUTHORIZATION)
 *
 * Rules:
 *   - Pure database access: no HTML output, no validation logic, no session logic.
 *   - Every query uses PDO prepared statements to protect against SQL injection.
 *   - Table: comments (id, post_id, user_id, content, created_at)
 */

class CommentModel
{
    private PDO $db;

    /**
     * Constructor — receives the active PDO connection.
     *
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    /**
     * Insert a new comment record into `comments`.
     *
     * @param int    $postId   ID of the post being commented on.
     * @param int    $userId   ID of the comment author (from session).
     * @param string $content  Cleaned/trimmed comment content.
     * @return int|false       Last inserted comment ID on success, or false on failure.
     */
    public function createComment(int $postId, int $userId, string $content): int|false
    {
        $sql = 'INSERT INTO comments (post_id, user_id, content)
                VALUES (:post_id, :user_id, :content)';

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':content' => $content,
        ]);

        if ($success) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    /**
     * Fetch all comments for a specific post in ascending chronological order.
     * Joins with the `users` table to retrieve author name, username, and profile image.
     *
     * @param int $postId
     * @return array
     */
    public function getByPostId(int $postId): array
    {
        $sql = 'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at,
                       u.username, u.full_name, u.profile_image
                FROM comments c
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at ASC, c.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        return $stmt->fetchAll();
    }

    /**
     * Retrieve a single comment by primary-key ID.
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id): array|false
    {
        $sql = 'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at,
                       u.username, u.full_name, u.profile_image
                FROM comments c
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Check if a given user owns a comment.
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function isOwner(int $commentId, int $userId): bool
    {
        $sql = 'SELECT COUNT(*) FROM comments
                WHERE id = :id AND user_id = :user_id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'      => $commentId,
            ':user_id' => $userId,
        ]);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Update the content of an existing comment.
     *
     * @param int    $id       Comment ID.
     * @param string $content  New content string.
     * @return bool
     */
    public function updateComment(int $id, string $content): bool
    {
        $sql = 'UPDATE comments
                SET content = :content
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':content' => $content,
            ':id'      => $id,
        ]);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    /**
     * Delete a comment by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteComment(int $id): bool
    {
        $sql = 'DELETE FROM comments WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
