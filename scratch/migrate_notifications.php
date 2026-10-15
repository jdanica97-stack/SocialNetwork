<?php
/**
 * Migration: Create notifications table
 * Run: C:\wamp64\bin\php\php8.3.14\php.exe scratch\migrate_notifications.php
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=social_app', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Running notifications migration...\n";

// Check if table already exists
$exists = $pdo->query("SHOW TABLES LIKE 'notifications'")->fetchColumn();
if ($exists) {
    echo "[INFO] notifications table already exists — skipping creation.\n";
} else {
    $sql = "
    CREATE TABLE `notifications` (
      `id`           INT           NOT NULL AUTO_INCREMENT,
      `recipient_id` INT           NOT NULL COMMENT 'User who receives this notification',
      `actor_id`     INT           NOT NULL COMMENT 'User who performed the action',
      `type`         VARCHAR(40)   NOT NULL COMMENT 'message | comment | like_post | like_comment',
      `target_type`  VARCHAR(20)   NOT NULL COMMENT 'message | post | comment',
      `target_id`    INT           NOT NULL COMMENT 'ID of the target entity',
      `preview`      VARCHAR(160)  DEFAULT NULL COMMENT 'Short text preview (message snippet, comment excerpt, etc.)',
      `is_read`      TINYINT(1)    NOT NULL DEFAULT 0,
      `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_notif_recipient`   (`recipient_id`),
      KEY `idx_notif_unread`      (`recipient_id`, `is_read`),
      KEY `idx_notif_created`     (`created_at` DESC),
      CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_notif_actor`     FOREIGN KEY (`actor_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "[OK] notifications table created.\n";
}

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
echo "[OK] notifications table is accessible (rows: $count).\n";

echo "\nMigration complete.\n";
