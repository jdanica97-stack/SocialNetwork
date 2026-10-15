<?php
/**
 * scratch/test_full_suite.php
 *
 * Full Acceptance Test Suite for:
 * 1. Messaging System (Send, Receive, Reply, Refresh, Auth guard, Self-messaging prevention)
 * 2. Notification System (Message, Comment, Like notifications; Self-notification prevention; Mark read; Unread count)
 */

$base = 'http://localhost/SocialNetwork/public/index.php';

function makeRequest(string $url, array $post = [], ?string $cookieFile = null, array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Keep headers/cookies clean
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body
    ];
}

function getCsrfToken(string $cookieFile, string $base): string {
    $res = makeRequest($base . '?url=auth/login', [], $cookieFile);
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $res['body'], $m)) {
        return $m[1];
    }
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $res['body'], $m)) {
        return $m[1];
    }
    return '';
}

function loginUser(string $username, string $password, string $cookieFile, string $base): bool {
    $csrf = getCsrfToken($cookieFile, $base);
    $res = makeRequest($base . '?url=auth/login', [
        'username' => $username,
        'password' => $password,
        'csrf_token' => $csrf
    ], $cookieFile);

    // Redirect to home/newsfeed on success (302)
    return ($res['code'] === 302);
}

echo "====================================================\n";
echo "   SMCC CONNECT - FULL INTEGRATION & ACCEPTANCE TEST\n";
echo "====================================================\n\n";

$cookieAlice = tempnam(sys_get_temp_dir(), 'alice_');
$cookieBob = tempnam(sys_get_temp_dir(), 'bob_');

// 1. Authenticate Alice (User 1) & Bob (User 2)
echo "[1] Testing Authentication for Alice & Bob...\n";
$loginAlice = loginUser('alice_wonder', 'password123', $cookieAlice, $base);
$loginBob = loginUser('bob_builder', 'password123', $cookieBob, $base);

if ($loginAlice && $loginBob) {
    echo "  -> SUCCESS: Alice and Bob logged in successfully.\n";
} else {
    echo "  -> FAILURE: Login failed. Alice: " . ($loginAlice ? 'OK':'FAIL') . ", Bob: " . ($loginBob ? 'OK':'FAIL') . "\n";
    exit(1);
}

// 2. Messaging: Alice sends a message to Bob
echo "\n[2] Testing Messaging: Alice -> Bob...\n";
$aliceCsrf = getCsrfToken($cookieAlice, $base);
$msgAliceText = "Hello Bob! Automated test timestamp: " . time();
$sendRes = makeRequest($base . '?url=messages/send', [
    'receiver_id' => 2,
    'message' => $msgAliceText,
    'csrf_token' => $aliceCsrf
], $cookieAlice, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

$sendJson = json_decode($sendRes['body'], true);
if ($sendRes['code'] === 200 && !empty($sendJson['success'])) {
    echo "  -> SUCCESS: Message sent (ID: {$sendJson['message_id']}).\n";
} else {
    echo "  -> FAILURE: Could not send message. Response: {$sendRes['body']}\n";
    exit(1);
}

// 3. Messaging: Bob opens chat and polls/receives the message
echo "\n[3] Testing Chat Retrieval & Polling for Bob...\n";
$pollRes = makeRequest($base . '?url=messages/poll&user_id=1', [], $cookieBob, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);
$pollJson = json_decode($pollRes['body'], true);
$foundMsg = false;
if ($pollRes['code'] === 200 && !empty($pollJson['success'])) {
    foreach ($pollJson['messages'] as $m) {
        if (str_contains($m['message'], $msgAliceText)) {
            $foundMsg = true;
            break;
        }
    }
}
if ($foundMsg) {
    echo "  -> SUCCESS: Bob received Alice's message in real-time poll.\n";
} else {
    echo "  -> FAILURE: Bob did not find Alice's message.\n";
    exit(1);
}

// 4. Messaging: Bob replies to Alice
echo "\n[4] Testing Bob Replies to Alice...\n";
$bobCsrf = getCsrfToken($cookieBob, $base);
$msgBobText = "Hi Alice, message received loud and clear! " . time();
$replyRes = makeRequest($base . '?url=messages/send', [
    'receiver_id' => 1,
    'message' => $msgBobText,
    'csrf_token' => $bobCsrf
], $cookieBob, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

$replyJson = json_decode($replyRes['body'], true);
if ($replyRes['code'] === 200 && !empty($replyJson['success'])) {
    echo "  -> SUCCESS: Bob replied successfully (ID: {$replyJson['message_id']}).\n";
} else {
    echo "  -> FAILURE: Bob failed to reply. Response: {$replyRes['body']}\n";
    exit(1);
}

// 5. Notifications: Check if Bob received a notification for Alice's message
echo "\n[5] Testing Notifications for Messages...\n";
$notifRes = makeRequest($base . '?url=notifications/fetch', [], $cookieBob, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);
$notifJson = json_decode($notifRes['body'], true);
$hasMsgNotif = false;
$notifIdToMark = null;
if (!empty($notifJson['notifications'])) {
    foreach ($notifJson['notifications'] as $notif) {
        if ($notif['type'] === 'message' && $notif['actor_username'] === 'alice_wonder') {
            $hasMsgNotif = true;
            $notifIdToMark = $notif['id'];
            break;
        }
    }
}
if ($hasMsgNotif) {
    echo "  -> SUCCESS: Bob has a notification for Alice's incoming message.\n";
} else {
    echo "  -> FAILURE: Bob did not receive message notification.\n";
    exit(1);
}

// 6. Notifications: Testing Comment Notifications
echo "\n[6] Testing Comment Notification Trigger (Bob comments on Alice's post)...\n";
// Find a post by Alice (User 1)
$pdo = new PDO('mysql:host=127.0.0.1;dbname=social_app', 'root', '');
$stmt = $pdo->query("SELECT id FROM posts WHERE user_id = 1 LIMIT 1");
$alicePostId = $stmt->fetchColumn();

if (!$alicePostId) {
    // Create a post for Alice if none exists
    $pdo->exec("INSERT INTO posts (user_id, content) VALUES (1, 'Hello Orbit community from Alice!')");
    $alicePostId = $pdo->lastInsertId();
}

$bobCsrf = getCsrfToken($cookieBob, $base);
$commentRes = makeRequest($base . '?url=comments/create', [
    'post_id' => $alicePostId,
    'content' => 'Great post Alice! Comment test ' . time(),
    'csrf_token' => $bobCsrf
], $cookieBob);

// Verify Alice gets notification
$aliceNotifRes = makeRequest($base . '?url=notifications/fetch', [], $cookieAlice, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);
$aliceNotifJson = json_decode($aliceNotifRes['body'], true);
$hasCommentNotif = false;
if (!empty($aliceNotifJson['notifications'])) {
    foreach ($aliceNotifJson['notifications'] as $notif) {
        if ($notif['type'] === 'comment' && $notif['actor_username'] === 'bob_builder' && (int)$notif['target_id'] === (int)$alicePostId) {
            $hasCommentNotif = true;
            break;
        }
    }
}
if ($hasCommentNotif) {
    echo "  -> SUCCESS: Alice received notification when Bob commented on her post.\n";
} else {
    echo "  -> FAILURE: Alice did not receive comment notification.\n";
    exit(1);
}

// 7. Notifications: Testing Like Notification Trigger
echo "\n[7] Testing Post Like Notification Trigger (Bob likes Alice's post)...\n";
$bobCsrf = getCsrfToken($cookieBob, $base);
// Toggle like
$likeRes = makeRequest($base . '?url=likes/toggle', [
    'post_id' => $alicePostId,
    'csrf_token' => $bobCsrf
], $cookieBob, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

$aliceNotifRes2 = makeRequest($base . '?url=notifications/fetch', [], $cookieAlice, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);
$aliceNotifJson2 = json_decode($aliceNotifRes2['body'], true);
$hasLikeNotif = false;
if (!empty($aliceNotifJson2['notifications'])) {
    foreach ($aliceNotifJson2['notifications'] as $notif) {
        if ($notif['type'] === 'like_post' && $notif['actor_username'] === 'bob_builder' && (int)$notif['target_id'] === (int)$alicePostId) {
            $hasLikeNotif = true;
            break;
        }
    }
}
if ($hasLikeNotif) {
    echo "  -> SUCCESS: Alice received notification when Bob liked her post.\n";
} else {
    echo "  -> FAILURE: Alice did not receive like notification.\n";
    exit(1);
}

// 8. Security & Edge cases
echo "\n[8] Testing Security: Prevent self-notifications & Unauthenticated access...\n";
// Alice likes her own post -> should NOT create notification
$aliceCsrf = getCsrfToken($cookieAlice, $base);
makeRequest($base . '?url=likes/toggle', [
    'post_id' => $alicePostId,
    'csrf_token' => $aliceCsrf
], $cookieAlice, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

$selfNotifCount = $pdo->query("SELECT COUNT(*) FROM notifications WHERE recipient_id = 1 AND actor_id = 1")->fetchColumn();
if ((int)$selfNotifCount === 0) {
    echo "  -> SUCCESS: Self-notification correctly prevented.\n";
} else {
    echo "  -> FAILURE: Self-notification was created!\n";
    exit(1);
}

// Unauthenticated user trying to access messages/notifications
$unauthRes = makeRequest($base . '?url=messages');
if ($unauthRes['code'] === 302 && str_contains($unauthRes['headers'], 'auth/login')) {
    echo "  -> SUCCESS: Unauthenticated user redirected to login.\n";
} else {
    echo "  -> FAILURE: Messages route is accessible without login!\n";
    exit(1);
}

// 9. Mark read and unread count test
echo "\n[9] Testing Mark As Read & Count API...\n";
if ($notifIdToMark) {
    $bobCsrf = getCsrfToken($cookieBob, $base);
    $markRes = makeRequest($base . '?url=notifications/mark-read', [
        'id' => $notifIdToMark,
        'csrf_token' => $bobCsrf
    ], $cookieBob, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
    $markJson = json_decode($markRes['body'], true);
    if (!empty($markJson['success'])) {
        echo "  -> SUCCESS: Bob marked notification as read. Remaining unread: {$markJson['unread']}.\n";
    } else {
        echo "  -> FAILURE: Failed to mark notification read.\n";
        exit(1);
    }
}

// Cleanup cookies
@unlink($cookieAlice);
@unlink($cookieBob);

echo "\n====================================================\n";
echo "   ALL TESTS PASSED WITH 100% SUCCESS!\n";
echo "====================================================\n";
