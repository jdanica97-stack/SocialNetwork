<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=social_app', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$attr = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);
echo 'EMULATE_PREPARES: ' . var_export($attr, true) . PHP_EOL;

// Test the exact getConversations query
$userId = 1;
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

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':u1' => $userId, ':u2' => $userId, ':u3' => $userId, ':u4' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'getConversations OK — rows: ' . count($rows) . PHP_EOL;
    print_r($rows);
} catch (PDOException $e) {
    echo 'getConversations FAILED: ' . $e->getMessage() . PHP_EOL;
}

// Test getConversationMessages
$sql2 = '
    SELECT m.id, m.sender_id, m.receiver_id, m.message, m.is_read, m.created_at,
           u.username AS sender_username, u.full_name AS sender_full_name
    FROM messages m
    INNER JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = :s1 AND m.receiver_id = :r1)
       OR (m.sender_id = :s2 AND m.receiver_id = :r2)
    ORDER BY m.created_at ASC, m.id ASC
    LIMIT :limit
';
try {
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindValue(':s1', 1, PDO::PARAM_INT);
    $stmt2->bindValue(':r1', 2, PDO::PARAM_INT);
    $stmt2->bindValue(':s2', 2, PDO::PARAM_INT);
    $stmt2->bindValue(':r2', 1, PDO::PARAM_INT);
    $stmt2->bindValue(':limit', 100, PDO::PARAM_INT);
    $stmt2->execute();
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo 'getConversationMessages OK — rows: ' . count($rows2) . PHP_EOL;
    print_r($rows2);
} catch (PDOException $e) {
    echo 'getConversationMessages FAILED: ' . $e->getMessage() . PHP_EOL;
}
