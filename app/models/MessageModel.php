<?php

/**
 * app/models/MessageModel.php
 *
 * Handles database operations for direct messages between users.
 *
 * All operations use PDO prepared statements to guarantee security against SQL injection.
 */

class MessageModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Send (insert) a new direct message.
     *
     * @param int $senderId
     * @param int $recipientId
     * @param string $message
     * @return int|false Returns inserted message ID or false on failure.
     */
    public function sendMessage(int $senderId, int $recipientId, string $message): int|false
    {
        try {
            $sql = 'INSERT INTO messages (sender_id, recipient_id, message, is_read, created_at)
                    VALUES (:sender_id, :recipient_id, :message, 0, NOW())';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':sender_id', $senderId, PDO::PARAM_INT);
            $stmt->bindValue(':recipient_id', $recipientId, PDO::PARAM_INT);
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('MessageModel::sendMessage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get list of conversations for a user.
     * Each conversation represents the other user, the latest message, and unread count.
     *
     * @param int $userId
     * @return array
     */
    public function getConversations(int $userId): array
    {
        try {
            // Find all distinct partner IDs where user is either sender or recipient
            $sql = 'SELECT 
                        u.id AS user_id,
                        u.username,
                        u.full_name,
                        u.profile_image,
                        m.id AS last_message_id,
                        m.message AS last_message,
                        m.sender_id AS last_sender_id,
                        m.created_at AS last_message_at,
                        (
                            SELECT COUNT(*)
                            FROM messages unread_m
                            WHERE unread_m.sender_id = u.id
                              AND unread_m.recipient_id = :user_id_sub
                              AND unread_m.is_read = 0
                        ) AS unread_count
                    FROM users u
                    INNER JOIN messages m ON m.id = (
                        SELECT id FROM messages m2
                        WHERE (m2.sender_id = :user_id1 AND m2.recipient_id = u.id)
                           OR (m2.sender_id = u.id AND m2.recipient_id = :user_id2)
                        ORDER BY m2.created_at DESC, m2.id DESC
                        LIMIT 1
                    )
                    WHERE u.id != :user_id3
                    ORDER BY m.created_at DESC';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id_sub', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id3', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getConversations error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get message history between two users in chronological order.
     *
     * @param int $userId
     * @param int $otherUserId
     * @return array
     */
    public function getThread(int $userId, int $otherUserId): array
    {
        try {
            $sql = 'SELECT 
                        m.id,
                        m.sender_id,
                        m.recipient_id,
                        m.message,
                        m.is_read,
                        m.created_at,
                        sender.username AS sender_username,
                        sender.full_name AS sender_name,
                        sender.profile_image AS sender_image
                    FROM messages m
                    JOIN users sender ON m.sender_id = sender.id
                    WHERE (m.sender_id = :u1 AND m.recipient_id = :o1)
                       OR (m.sender_id = :o2 AND m.recipient_id = :u2)
                    ORDER BY m.created_at ASC, m.id ASC';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':u1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':o1', $otherUserId, PDO::PARAM_INT);
            $stmt->bindValue(':o2', $otherUserId, PDO::PARAM_INT);
            $stmt->bindValue(':u2', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getThread error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark all incoming unread messages from a specific partner as read.
     *
     * @param int $userId The recipient user
     * @param int $otherUserId The sender user
     * @return bool
     */
    public function markThreadAsRead(int $userId, int $otherUserId): bool
    {
        try {
            $sql = 'UPDATE messages 
                    SET is_read = 1 
                    WHERE recipient_id = :user_id 
                      AND sender_id = :other_user_id 
                      AND is_read = 0';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':other_user_id', $otherUserId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('MessageModel::markThreadAsRead error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Total number of unread incoming messages across all conversations.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM messages WHERE recipient_id = :user_id AND is_read = 0';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('MessageModel::countUnread error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retrieve all other registered users to enable starting new chats.
     *
     * @param int $userId
     * @return array
     */
    public function getAvailableUsers(int $userId): array
    {
        try {
            $sql = 'SELECT id, username, full_name, profile_image, bio
                    FROM users
                    WHERE id != :user_id
                    ORDER BY full_name ASC';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getAvailableUsers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a single message by ID.
     *
     * @param int $messageId
     * @return array|null
     */
    public function getMessageById(int $messageId): ?array
    {
        try {
            $sql = 'SELECT id, sender_id, recipient_id, message, is_read, created_at
                    FROM messages
                    WHERE id = :id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (PDOException $e) {
            error_log('MessageModel::getMessageById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve new incoming messages in a thread after a given message ID.
     *
     * @param int $userId
     * @param int $otherUserId
     * @param int $lastId
     * @return array
     */
    public function getNewMessages(int $userId, int $otherUserId, int $lastId): array
    {
        try {
            $sql = 'SELECT 
                        m.id,
                        m.sender_id,
                        m.recipient_id,
                        m.message,
                        m.is_read,
                        m.created_at
                    FROM messages m
                    WHERE ((m.sender_id = :u1 AND m.recipient_id = :o1)
                        OR (m.sender_id = :o2 AND m.recipient_id = :u2))
                      AND m.id > :last_id
                    ORDER BY m.created_at ASC, m.id ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':u1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':o1', $otherUserId, PDO::PARAM_INT);
            $stmt->bindValue(':o2', $otherUserId, PDO::PARAM_INT);
            $stmt->bindValue(':u2', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':last_id', $lastId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('MessageModel::getNewMessages error: ' . $e->getMessage());
            return [];
        }
    }
}
