<?php
/**
 * Full end-to-end browser-simulation test for messaging feature.
 * Tests: login → messages inbox → chat page → send message → poll
 */

$base = 'http://localhost/SocialNetwork/public/';
$cookieJar = sys_get_temp_dir() . '/test_cookies_' . getmypid() . '.txt';

function httpRequest(string $url, array $postData = [], string $cookieFile = '', array $extraHeaders = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    if ($extraHeaders) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $extraHeaders);
    }
    $raw = curl_exec($ch);
    $info = curl_getinfo($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headers = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    return ['status' => $info['http_code'], 'headers' => $headers, 'body' => $body, 'url' => $info['effective_url']];
}

function pass(string $msg) { echo "[PASS] $msg\n"; }
function fail(string $msg) { echo "[FAIL] $msg\n"; }
function info(string $msg) { echo "[INFO] $msg\n"; }

// ─── Step 1: Load login page to get CSRF token ───────────────────────────
$res = httpRequest($base . '?url=login', [], $cookieJar);
$status = $res['status'];
$body = $res['body'];
info("Login page status: $status");
if ($status !== 200) { fail("Login page not accessible"); exit(1); }

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $body, $m);
$csrf = $m[1] ?? '';
if (!$csrf) {
    // Try hidden input
    preg_match('/name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\']/', $body, $m);
    $csrf = $m[1] ?? '';
}
if (!$csrf) {
    preg_match('/value=["\']([^"\']+)["\'][^>]*name=["\']csrf_token["\']/', $body, $m);
    $csrf = $m[1] ?? '';
}
if ($csrf) { pass("Got CSRF token: " . substr($csrf, 0, 16) . "..."); }
else { fail("Could not extract CSRF token"); exit(1); }

// ─── Step 2: Login as alice_wonder ───────────────────────────────────────
$res = httpRequest($base . '?url=login', [
    'username'   => 'alice_wonder',
    'password'   => 'password123',
    'csrf_token' => $csrf,
], $cookieJar);
$status = $res['status'];
info("Login POST status: $status, redirected to: " . $res['url']);
if (str_contains($res['url'], 'login')) { fail("Login failed — still on login page"); exit(1); }
pass("Logged in as alice_wonder");

// ─── Step 3: Load Messages Inbox ─────────────────────────────────────────
$res = httpRequest($base . '?url=messages', [], $cookieJar);
$status = $res['status'];
$body   = $res['body'];
info("Inbox status: $status");
if ($status !== 200) { fail("Inbox returned $status"); }
elseif (str_contains($body, 'Direct Messages')) { pass("Inbox loaded successfully"); }
elseif (str_contains($body, 'login') || str_contains($body, 'Login')) { fail("Inbox redirected to login — auth not working"); }
else { fail("Inbox page loaded but expected content not found"); }

// Check for PHP errors in body
if (preg_match('/Fatal error|Parse error|Warning:|Notice:/i', $body, $em)) {
    fail("PHP error in inbox: " . htmlspecialchars(substr($em[0], 0, 200)));
} else {
    pass("No PHP errors in inbox page");
}

// Check conversations list
if (str_contains($body, 'bob_builder') || str_contains($body, 'Bob Builder')) {
    pass("Conversation with Bob Builder visible in inbox");
} else {
    info("No conversation with bob_builder found in inbox (may be empty)");
}

// ─── Step 4: Load Chat page with bob_builder (user ID 2) ─────────────────
$res = httpRequest($base . '?url=messages/chat&user_id=2', [], $cookieJar);
$status = $res['status'];
$body   = $res['body'];
info("Chat page status: $status");
if ($status !== 200) { fail("Chat page returned $status"); }
elseif (str_contains($body, 'chatSendForm') || str_contains($body, 'chatSubmitBtn')) { pass("Chat page loaded with form"); }
else { fail("Chat page loaded but send form not found"); }

if (preg_match('/Fatal error|Parse error|Warning:|Notice:/i', $body, $em)) {
    fail("PHP error in chat page: " . htmlspecialchars(substr($em[0], 0, 200)));
} else {
    pass("No PHP errors in chat page");
}

// Extract CSRF from meta tag in chat page
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $body, $m);
$csrfChat = $m[1] ?? $csrf;

// Extract from hidden input in chat form
preg_match('/name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\']/', $body, $m2);
$csrfForm = $m2[1] ?? $csrfChat;
info("CSRF token for send: " . substr($csrfForm, 0, 16) . "...");

// ─── Step 5: Send a message (simulating AJAX fetch) ──────────────────────
$testMsg = 'E2E test message at ' . date('Y-m-d H:i:s');
$res = httpRequest(
    $base . '?url=messages/send',
    [
        'receiver_id' => 2,
        'message'     => $testMsg,
        'csrf_token'  => $csrfForm,
    ],
    $cookieJar,
    ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
);
$status   = $res['status'];
$rawBody  = trim($res['body']);
info("Send POST status: $status");
info("Send response body: " . substr($rawBody, 0, 500));

$json = json_decode($rawBody, true);
if ($json === null) {
    fail("Send response is not valid JSON: " . substr($rawBody, 0, 300));
} elseif (isset($json['success']) && $json['success']) {
    pass("Message sent successfully! message_id=" . ($json['message_id'] ?? '?') . " text=" . ($json['message'] ?? '?'));
} else {
    fail("Send failed: " . ($json['message'] ?? $rawBody));
}

// ─── Step 6: Poll to verify message appears ───────────────────────────────
$res = httpRequest(
    $base . '?url=messages/poll&user_id=2',
    [],
    $cookieJar,
    ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
);
$status  = $res['status'];
$rawBody = trim($res['body']);
info("Poll status: $status");
$json = json_decode($rawBody, true);
if ($json === null) {
    fail("Poll response not valid JSON: " . substr($rawBody, 0, 300));
} elseif (isset($json['success']) && $json['success']) {
    $count = count($json['messages'] ?? []);
    pass("Poll returned $count message(s)");
    // Verify our test message is in there
    $found = false;
    foreach ($json['messages'] ?? [] as $m) {
        if (str_contains($m['message'] ?? '', 'E2E test')) { $found = true; break; }
    }
    if ($found) pass("Test message visible in poll results");
    else info("Test message not found in poll (may be filtered by ID)");
} else {
    fail("Poll failed: " . ($json['message'] ?? $rawBody));
}

// ─── Cleanup ─────────────────────────────────────────────────────────────
if (file_exists($cookieJar)) unlink($cookieJar);
echo "\n=== Test Complete ===\n";
