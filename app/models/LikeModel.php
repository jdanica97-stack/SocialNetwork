<?php

/**
 * app/models/LikeModel.php
 *
 * Handles ALL database operations related to the `likes` table.
 *
 * Responsibilities (Step 9 — Likes):
 *   - addLike()     — Insert a like for a post by a user (CREATE)
 *   - removeLike()  — Delete a like for a post by a user (DELETE)
 *   - hasLiked()    — Check if a user has already liked a post (READ)
 *   - countLikes()  — Count total likes for a post (READ)
 *   - getLikesMap() — Batch retrieve like counts and user like states for posts
 *
 * Rules:
 *   - Pure database access: no HTML output, no validation logic, no session logic.
 *   - Every query uses PDO prepared statements to protect against SQL injection.
 *   - Table: likes (id, post_id, user_id) with UNIQUE(post_id, user_id)
 */

class LikeModel
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
     * Add a like for a user on a post.
     * Prevents duplicate likes at the application and DB level.
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function addLike(int $postId, int $userId): bool
    {
        // Application-level duplicate check
        if ($this->hasLiked($postId, $userId)) {
            return false;
        }

        $sql = 'INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)';
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([
                ':post_id' => $postId,
                ':user_id' => $userId,
            ]);
        } catch (PDOException $e) {
            // In case of race condition triggering UNIQUE(post_id, user_id)
            return false;
        }
    }

    /**
     * Remove a like for a user on a post.
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function removeLike(int $postId, int $userId): bool
    {
        $sql = 'DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Check if a specific user has already liked a post.
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function hasLiked(int $postId, int $userId): bool
    {
        $sql = 'SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
        ]);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Count total likes for a post.
     *
     * @param int $postId
     * @return int
     */
    public function countLikes(int $postId): int
    {
        $sql = 'SELECT COUNT(*) FROM likes WHERE post_id = :post_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        return (int) $stmt->fetchColumn();
    }
}
