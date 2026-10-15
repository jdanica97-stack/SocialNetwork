<?php

/**
 * app/models/NotificationModel.php
 *
 * Handles all database operations for the `notifications` table.
 *
 * Notification types:
 *   - 'message'      — User received a direct message
 *   - 'comment'      — Someone commented on your post
 *   - 'like_post'    — Someone liked your post
 *   - 'like_comment' — Someone liked your comment (future extension)
 *
 * Security:
 *   - Pure database layer: no HTML, session, or controller logic.
 *   - All queries use PDO prepared statements.
 *   - Wrapped in try-catch with error_log diagnostics.
 */

class NotificationModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new notification.
     * Never notifies a user about their own actions.
     *
     * @param int         $recipientId  Who receives the notification
     * @param int         $actorId      Who performed the action
     * @param string      $type         'message' | 'comment' | 'like_post' | 'like_comment'
     * @param string      $targetType   'message' | 'post' | 'comment'
     * @param int         $targetId     ID of the relevant entity
     * @param string|null $preview      Optional short text preview (max 160 chars)
     * @return int|false  New notification ID or false on failure
     */
    public function create(int $recipientId, int $actorId, string $type, string $targetType, int $targetId, ?string $preview = null): int|false
    {
        // Never notify users about their own actions
        if ($recipientId === $actorId) {
            return false;
        }

        try {
            $previewTrimmed = $preview !== null ? mb_substr(trim($preview), 0, 160) : null;

            $sql = 'INSERT INTO notifications (recipient_id, actor_id, type, target_type, target_id, preview)
                    VALUES (:recipient_id, :actor_id, :type, :target_type, :target_id, :preview)';
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':recipient_id' => $recipientId,
                ':actor_id'     => $actorId,
                ':type'         => $type,
                ':target_type'  => $targetType,
                ':target_id'    => $targetId,
                ':preview'      => $previewTrimmed,
            ]);

            return $ok ? (int) $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('NotificationModel::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get paginated notifications for a user, newest first.
     *
     * @param int $userId      Recipient user ID
     * @param int $limit       Max rows to return
     * @param int $offset      Pagination offset
     * @return array
     */
    public function getForUser(int $userId, int $limit = 30, int $offset = 0): array
    {
        try {
            $sql = '
                SELECT
                    n.id,
                    n.type,
                    n.target_type,
                    n.target_id,
                    n.preview,
                    n.is_read,
                    n.created_at,
                    u.id          AS actor_id,
                    u.username    AS actor_username,
                    u.full_name   AS actor_full_name,
                    u.profile_image AS actor_profile_image
                FROM notifications n
                INNER JOIN users u ON u.id = n.actor_id
                WHERE n.recipient_id = :recipient_id
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT :limit OFFSET :offset
            ';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':recipient_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('NotificationModel::getForUser error: ' . $e->getMessage());
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
            $sql  = 'SELECT COUNT(*) FROM notifications WHERE recipient_id = :uid AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('NotificationModel::countUnread error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark a single notification as read (only if owned by the user).
     *
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markRead(int $notificationId, int $userId): bool
    {
        try {
            $sql  = 'UPDATE notifications SET is_read = 1 WHERE id = :id AND recipient_id = :uid';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $notificationId, ':uid' => $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('NotificationModel::markRead error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all of a user's notifications as read.
     *
     * @param int $userId
     * @return bool
     */
    public function markAllRead(int $userId): bool
    {
        try {
            $sql  = 'UPDATE notifications SET is_read = 1 WHERE recipient_id = :uid AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            return true;
        } catch (PDOException $e) {
            error_log('NotificationModel::markAllRead error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Prevent duplicate notifications for the same actor→recipient→type→target
     * within a short dedup window (60 seconds). Used to avoid spam on fast retries.
     *
     * @param int    $recipientId
     * @param int    $actorId
     * @param string $type
     * @param int    $targetId
     * @return bool  true if a duplicate exists (skip creation)
     */
    public function isDuplicate(int $recipientId, int $actorId, string $type, int $targetId): bool
    {
        try {
            $sql = '
                SELECT COUNT(*) FROM notifications
                WHERE recipient_id = :rid
                  AND actor_id     = :aid
                  AND type         = :type
                  AND target_id    = :tid
                  AND created_at   >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
            ';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':rid'  => $recipientId,
                ':aid'  => $actorId,
                ':type' => $type,
                ':tid'  => $targetId,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('NotificationModel::isDuplicate error: ' . $e->getMessage());
            return false;
        }
    }
}
