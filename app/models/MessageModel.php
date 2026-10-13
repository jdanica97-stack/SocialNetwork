<?php

/**
 * app/models/MessageModel.php
 *
 * Handles ALL database operations related to the `messages` table.
 *
 * Responsibilities:
 *   - sendMessage()           — Insert a new direct message between users
 *   - getConversations()       — Retrieve user's active conversations list with latest message & unread count
 *   - getConversationMessages() — Retrieve messages exchanged between two specific users
 *   - markAsRead()             — Mark incoming messages in a conversation as read
 *   - getUnreadCount()         — Get total unread messages count for a user
 *   - deleteMessage()          — Delete a message (owner only)
 *
 * Database Relationships:
 *   - messages.sender_id   → users.id (ON DELETE CASCADE)
 *   - messages.receiver_id → users.id (ON DELETE CASCADE)
 *
 * Security:
 *   - Pure database access: no HTML, session, or validation logic here.
 *   - All queries use PDO prepared statements to protect against SQL injection.
 *   - Wrapped in try-catch with error_log diagnostics.
 */

class MessageModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Send a new message.
     *
     * @param int    $senderId
     * @param int    $receiverId
     * @param string $message
     * @return int|false Last inserted message ID or false on failure
     */
    public function sendMessage(int $senderId, int $receiverId, string $message): int|false
    {
        try {
            $sql = 'INSERT INTO messages (sender_id, receiver_id, message) 
                    VALUES (:sender_id, :receiver_id, :message)';
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':sender_id'   => $senderId,
                ':receiver_id' => $receiverId,
                ':message'     => $message,
            ]);

            return $success ? (int) $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('MessageModel::sendMessage database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all conversations for a user.
     * Returns the other participant's details, the most recent message, and unread counts.
     *
     * @param int $userId
     * @return array
     */
    public function getConversations(int $userId): array
    {
        try {
            // Find the other participant in each conversation
            $sql = '
                SELECT 
                    u.id AS other_user_id,
                    u.username,
                    u.full_name,
                    u.profile_image,
                    latest.id AS latest_message_id,
                    latest.message AS latest_message,
                    latest.created_at AS latest_message_time,
                    latest.sender_id AS latest_sender_id,
                    COALESCE(unread.unread_count, 0) AS unread_count
                FROM (
                    SELECT 
                        CASE WHEN sender_id = :u1 THEN receiver_id ELSE sender_id END AS participant_id,
                        MAX(id) AS max_id
                    FROM messages
                    WHERE sender_id = :u2 OR receiver_id = :u3
                    GROUP BY participant_id
                ) conv
                INNER JOIN users u ON u.id = conv.participant_id
                INNER JOIN messages latest ON latest.id = conv.max_id
                LEFT JOIN (
                    SELECT sender_id, COUNT(*) AS unread_count
                    FROM messages
                    WHERE receiver_id = :u4 AND is_read = 0
                    GROUP BY sender_id
                ) unread ON unread.sender_id = conv.participant_id
                ORDER BY latest.created_at DESC, latest.id DESC
            ';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':u1' => $userId,
                ':u2' => $userId,
                ':u3' => $userId,
                ':u4' => $userId,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getConversations database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get messages between two users ordered chronologically.
     *
     * @param int $user1Id
     * @param int $user2Id
     * @param int $limit
     * @return array
     */
    public function getConversationMessages(int $user1Id, int $user2Id, int $limit = 100): array
    {
        try {
            $sql = '
                SELECT m.id, m.sender_id, m.receiver_id, m.message, m.is_read, m.created_at,
                       u.username AS sender_username, u.full_name AS sender_full_name, u.profile_image AS sender_profile_image
                FROM messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE (m.sender_id = :s1 AND m.receiver_id = :r1)
                   OR (m.sender_id = :s2 AND m.receiver_id = :r2)
                ORDER BY m.created_at ASC, m.id ASC
                LIMIT :limit
            ';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':s1', $user1Id, PDO::PARAM_INT);
            $stmt->bindValue(':r1', $user2Id, PDO::PARAM_INT);
            $stmt->bindValue(':s2', $user2Id, PDO::PARAM_INT);
            $stmt->bindValue(':r2', $user1Id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getConversationMessages database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark all unread messages from a specific sender to the receiver as read.
     *
     * @param int $receiverId Logged-in user
     * @param int $senderId   The conversation partner
     * @return bool
     */
    public function markAsRead(int $receiverId, int $senderId): bool
    {
        try {
            $sql = 'UPDATE messages 
                    SET is_read = 1 
                    WHERE receiver_id = :receiver_id 
                      AND sender_id = :sender_id 
                      AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':receiver_id' => $receiverId,
                ':sender_id'   => $senderId,
            ]);
        } catch (PDOException $e) {
            error_log('MessageModel::markAsRead database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total unread messages count for a user across all conversations.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('MessageModel::getUnreadCount database error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a single message if owned by the user.
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    public function deleteMessage(int $messageId, int $userId): bool
    {
        try {
            $sql = 'DELETE FROM messages WHERE id = :id AND sender_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'      => $messageId,
                ':user_id' => $userId,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('MessageModel::deleteMessage database error: ' . $e->getMessage());
            return false;
        }
    }
}
