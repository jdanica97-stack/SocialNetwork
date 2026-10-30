<?php

/**
 * app/models/FollowModel.php
 *
 * Handles database operations for the user follow / friends graph.
 *
 * Table schema:
 *   follows (id, follower_id, following_id, created_at)
 *
 * All database operations use PDO prepared statements to guarantee SQL injection safety.
 */

class FollowModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Follow a user.
     *
     * @param int $followerId The user who initiates following
     * @param int $followingId The user being followed
     * @return bool
     */
    public function follow(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            return false;
        }

        try {
            $sql = 'INSERT IGNORE INTO follows (follower_id, following_id, created_at)
                    VALUES (:follower_id, :following_id, NOW())';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':follower_id', $followerId, PDO::PARAM_INT);
            $stmt->bindValue(':following_id', $followingId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('FollowModel::follow error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unfollow a user.
     *
     * @param int $followerId The user unfollowing
     * @param int $followingId The user being unfollowed
     * @return bool
     */
    public function unfollow(int $followerId, int $followingId): bool
    {
        try {
            $sql = 'DELETE FROM follows
                    WHERE follower_id = :follower_id AND following_id = :following_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':follower_id', $followerId, PDO::PARAM_INT);
            $stmt->bindValue(':following_id', $followingId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('FollowModel::unfollow error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user A is following user B.
     *
     * @param int $followerId
     * @param int $followingId
     * @return bool
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        try {
            $sql = 'SELECT 1 FROM follows
                    WHERE follower_id = :follower_id AND following_id = :following_id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':follower_id', $followerId, PDO::PARAM_INT);
            $stmt->bindValue(':following_id', $followingId, PDO::PARAM_INT);
            $stmt->execute();
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('FollowModel::isFollowing error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count how many followers a user has.
     *
     * @param int $userId
     * @return int
     */
    public function getFollowerCount(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM follows WHERE following_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('FollowModel::getFollowerCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count how many users a user is following.
     *
     * @param int $userId
     * @return int
     */
    public function getFollowingCount(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM follows WHERE follower_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('FollowModel::getFollowingCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get list of users following $userId.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFollowers(int $userId, int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = 'SELECT 
                        u.id, 
                        u.username, 
                        u.full_name, 
                        u.bio, 
                        u.profile_image, 
                        f.created_at AS followed_at,
                        (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS followers_count
                    FROM follows f
                    INNER JOIN users u ON f.follower_id = u.id
                    WHERE f.following_id = :user_id
                    ORDER BY f.created_at DESC, f.id DESC
                    LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('FollowModel::getFollowers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of users $userId is following.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFollowing(int $userId, int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = 'SELECT 
                        u.id, 
                        u.username, 
                        u.full_name, 
                        u.bio, 
                        u.profile_image, 
                        f.created_at AS followed_at,
                        (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS followers_count
                    FROM follows f
                    INNER JOIN users u ON f.following_id = u.id
                    WHERE f.follower_id = :user_id
                    ORDER BY f.created_at DESC, f.id DESC
                    LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('FollowModel::getFollowing error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get mutual friends count (users where both follow each other).
     *
     * @param int $userId
     * @return int
     */
    public function getFriendsCount(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*)
                    FROM follows f1
                    INNER JOIN follows f2 ON f2.follower_id = f1.following_id AND f2.following_id = f1.follower_id
                    WHERE f1.follower_id = :userId';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('FollowModel::getFriendsCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get mutual friends (users where both follow each other), with optional search.
     *
     * @param int $userId
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getFriends(int $userId, int $limit = 50, string $search = ''): array
    {
        try {
            $sql = 'SELECT 
                        u.id, 
                        u.username, 
                        u.full_name, 
                        u.bio, 
                        u.profile_image, 
                        f1.created_at AS friendship_date,
                        (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS followers_count
                    FROM users u
                    INNER JOIN follows f1 ON f1.following_id = u.id AND f1.follower_id = :u1
                    INNER JOIN follows f2 ON f2.follower_id = u.id AND f2.following_id = :u2';

            if ($search !== '') {
                $sql .= ' WHERE (u.full_name LIKE :search OR u.username LIKE :search)';
            }

            $sql .= ' ORDER BY f1.created_at DESC LIMIT :limit';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':u1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':u2', $userId, PDO::PARAM_INT);
            if ($search !== '') {
                $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('FollowModel::getFriends error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user recommendations / suggestions for $userId (users they do not follow yet).
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getSuggestions(int $userId, int $limit = 12): array
    {
        try {
            $sql = 'SELECT 
                        u.id, 
                        u.username, 
                        u.full_name, 
                        u.bio, 
                        u.profile_image,
                        (SELECT COUNT(*) FROM follows WHERE following_id = u.id) AS followers_count
                    FROM users u
                    WHERE u.id != :user_id
                      AND u.id NOT IN (
                          SELECT following_id FROM follows WHERE follower_id = :user_id_sub
                      )
                    ORDER BY followers_count DESC, u.id DESC
                    LIMIT :limit';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id_sub', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('FollowModel::getSuggestions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Return an array of user IDs that $userId is following.
     *
     * @param int $userId
     * @return int[]
     */
    public function getFollowingIds(int $userId): array
    {
        try {
            $sql = 'SELECT following_id FROM follows WHERE follower_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('FollowModel::getFollowingIds error: ' . $e->getMessage());
            return [];
        }
    }
}
