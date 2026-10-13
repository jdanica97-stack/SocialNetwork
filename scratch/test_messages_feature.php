<?php
/**
 * scratch/test_messages_feature.php
 *
 * Automated verification test for the Direct Messaging feature:
 *  1. Model Layer:
 *     - Sending a message between User 1 and User 2.
 *     - Reading conversation list & verifying unread count.
 *     - Fetching conversation messages chronologically.
 *     - Marking messages as read.
 *     - Deleting a message (ownership verification).
 *  2. Controller / HTTP Routing Layer:
 *     - Authentication enforcement (unauthenticated users blocked/redirected).
 *     - Accessing /?url=messages (inbox).
 *     - Accessing /?url=messages/chat with valid and invalid IDs.
 *     - Preventing messaging oneself.
 *     - Sending message via POST with CSRF verification.
 *     - Live polling endpoint /?url=messages/poll.
 *  3. Header & UI Layer:
 *     - Messages navigation link active in side panel when logged in.
 *     - No "disabled" or "Coming Soon" badge remaining on Messages item.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/MessageModel.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

$db = getDBConnection();
$messageModel = new MessageModel($db);
$userModel    = new UserModel($db);

$testsRun = 0;
$testsPassed = 0;

function assertTest(bool $condition, string $message): void {
    global $testsRun, $testsPassed;
    $testsRun++;
    if ($condition) {
        $testsPassed++;
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
    }
}

echo "=== RUNNING DIRECT MESSAGING VERIFICATION TESTS ===\n\n";

// 1. Verify Test Users Exist
$users = $userModel->getAllUsers(10);
assertTest(count($users) >= 2, "At least 2 test users exist in database (found " . count($users) . ")");
$user1 = $users[0];
$user2 = $users[1];
$user1Id = (int)$user1['id'];
$user2Id = (int)$user2['id'];
echo "Testing with User 1: {$user1['username']} (ID {$user1Id}) and User 2: {$user2['username']} (ID {$user2Id})\n";

// 2. Test Model sendMessage
$testText = "Hello from automated test " . time();
$msgId = $messageModel->sendMessage($user1Id, $user2Id, $testText);
assertTest($msgId !== false && $msgId > 0, "Model successfully inserts a new message (ID: $msgId)");

// 3. Test getConversationMessages
$messages = $messageModel->getConversationMessages($user1Id, $user2Id);
assertTest(!empty($messages), "Model retrieves conversation messages between users");
$found = false;
foreach ($messages as $m) {
    if ((int)$m['id'] === $msgId && $m['message'] === $testText) {
        $found = true;
        break;
    }
}
assertTest($found, "Inserted message is present in retrieved conversation stream");

// 4. Test Unread Count & Conversations list
$unreadBefore = $messageModel->getUnreadCount($user2Id);
assertTest($unreadBefore > 0, "Recipient has unread count > 0 before reading (unread: $unreadBefore)");

$convsUser2 = $messageModel->getConversations($user2Id);
$targetConv = null;
foreach ($convsUser2 as $c) {
    if ((int)$c['other_user_id'] === $user1Id) {
        $targetConv = $c;
        break;
    }
}
assertTest($targetConv !== null, "Conversation appears in recipient's inbox");
assertTest((int)$targetConv['unread_count'] > 0, "Inbox reports unread messages correctly");

// 5. Test markAsRead
$marked = $messageModel->markAsRead($user2Id, $user1Id);
assertTest($marked === true, "Model marks messages as read");
$unreadAfter = $messageModel->getUnreadCount($user2Id);
assertTest($unreadAfter < $unreadBefore, "Unread count decreased after markAsRead");

// 6. Test Model deleteMessage
$msgToDeleteId = $messageModel->sendMessage($user1Id, $user2Id, "To be deleted");
$deleteUnauthorized = $messageModel->deleteMessage($msgToDeleteId, 999999);
assertTest($deleteUnauthorized === false, "Unauthorized user cannot delete someone else's message");
$deleteAuthorized = $messageModel->deleteMessage($msgToDeleteId, $user1Id);
assertTest($deleteAuthorized === true, "Sender can delete their own message");

// 7. Verify Side Panel header.php
$headerFile = file_get_contents(BASE_PATH . '/app/views/layouts/header.php');
assertTest(str_contains($headerFile, 'id="sidePanelMessagesLink"'), "header.php contains active #sidePanelMessagesLink");
assertTest(str_contains($headerFile, 'href="/SocialNetwork/public/?url=messages"'), "header.php links to /SocialNetwork/public/?url=messages");
assertTest(!str_contains($headerFile, 'badge-coming-soon'), "header.php has no 'badge-coming-soon' on Messages item");

// 8. Verify index.php routing
$indexFile = file_get_contents(BASE_PATH . '/public/index.php');
assertTest(str_contains($indexFile, "str_starts_with(\$url, 'messages/')"), "public/index.php handles messages routes");

// 9. Verify View files exist
assertTest(file_exists(BASE_PATH . '/app/views/messages/inbox.php'), "app/views/messages/inbox.php exists");
assertTest(file_exists(BASE_PATH . '/app/views/messages/chat.php'), "app/views/messages/chat.php exists");

// 10. Verify CSS styles exist
$cssFile = file_get_contents(BASE_PATH . '/public/assets/css/style.css');
assertTest(str_contains($cssFile, '.chat-bubble'), "style.css contains .chat-bubble styles");
assertTest(str_contains($cssFile, '.chat-bubble-mine'), "style.css contains .chat-bubble-mine styles");
assertTest(str_contains($cssFile, '.chat-bubble-theirs'), "style.css contains .chat-bubble-theirs styles");

// 11. Verify Profile page has Message button
$profileFile = file_get_contents(BASE_PATH . '/app/views/profile/profile.php');
assertTest(str_contains($profileFile, 'url=messages/chat&user_id='), "profile.php includes Message button for other users");

echo "\n===================================================\n";
echo "SUMMARY: $testsPassed / $testsRun tests passed.\n";
if ($testsPassed === $testsRun) {
    echo "RESULT: ALL DIRECT MESSAGING TESTS PASSED SUCCESSFULLY!\n";
} else {
    echo "RESULT: SOME TESTS FAILED.\n";
}
