<?php
require_once 'c:/wamp64/www/SocialNetwork/scratch/test_full_suite.php';
// Let's test just the like toggle directly
$base = 'http://localhost/SocialNetwork/public/index.php';
$cookieBob = tempnam(sys_get_temp_dir(), 'bob_');
loginUser('bob_builder', 'password123', $cookieBob, $base);
$bobCsrf = getCsrfToken($cookieBob, $base);
$likeRes = makeRequest($base . '?url=likes/toggle', [
    'post_id' => 1,
    'csrf_token' => $bobCsrf
], $cookieBob, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
echo "HTTP CODE: " . $likeRes['code'] . "\n";
echo "BODY: " . $likeRes['body'] . "\n";
