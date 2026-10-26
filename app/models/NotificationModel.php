<?php

/**
 * app/models/NotificationModel.php
 *
 * Handles database operations for user system notifications.
 *
 * Supports event types:
 *   - 'message': Received a new private message
 *   - 'like': A user liked your post
 *   - 'comment': A user commented on your post
 *
 * All operations use PDO prepared statements to guarantee security against SQL injection.
 */

class NotificationModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a notification for a user.
     *
     * @param int $userId Target recipient of notification
     * @param int|null $actorId User performing the action
     * @param string $type Event type ('message', 'like', 'comment', etc.)
     * @param int|null $targetId Related target ID (partner user_id for message, post_id for like/comment)
     * @param string $message Descriptive text of notification
     * @return int|false
     */
    public function createNotification(int $userId, ?int $actorId, string $type, ?int $targetId, string $message): int|false
    {
        // Don't notify a user about their own actions
        if ($actorId !== null && $userId === $actorId) {
            return false;
        }

        try {
            $sql = 'INSERT INTO notifications (user_id, actor_id, type, target_id, message, is_read, created_at)
                    VALUES (:user_id, :actor_id, :type, :target_id, :message, 0, NOW())';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            if ($actorId !== null) {
                $stmt->bindValue(':actor_id', $actorId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':actor_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
            if ($targetId !== null) {
                $stmt->bindValue(':target_id', $targetId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':target_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('NotificationModel::createNotification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve all notifications for a user with actor profile details.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserNotifications(int $userId, int $limit = 50): array
    {
        try {
            $sql = 'SELECT 
                        n.id,
                        n.user_id,
                        n.actor_id,
                        n.type,
                        n.target_id,
                        n.message,
                        n.is_read,
                        n.created_at,
                        actor.username AS actor_username,
                        actor.full_name AS actor_name,
                        actor.profile_image AS actor_image
                    FROM notifications n
                    LEFT JOIN users actor ON n.actor_id = actor.id
                    WHERE n.user_id = :user_id
                    ORDER BY n.created_at DESC, n.id DESC
                    LIMIT :limit';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('NotificationModel::getUserNotifications error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count unread notifications for a user.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('NotificationModel::countUnread error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark a single notification as read (strictly owned by the user).
     *
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $sql = 'UPDATE notifications 
                    SET is_read = 1 
                    WHERE id = :id AND user_id = :user_id';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $notificationId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('NotificationModel::markAsRead error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all unread notifications for a user as read.
     *
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            $sql = 'UPDATE notifications 
                    SET is_read = 1 
                    WHERE user_id = :user_id AND is_read = 0';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('NotificationModel::markAllAsRead error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a single notification (strictly owned by the user).
     *
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function deleteNotification(int $notificationId, int $userId): bool
    {
        try {
            $sql = 'DELETE FROM notifications WHERE id = :id AND user_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $notificationId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('NotificationModel::deleteNotification error: ' . $e->getMessage());
            return false;
        }
    }
}
