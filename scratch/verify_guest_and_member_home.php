<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';
require_once BASE_PATH . '/app/controllers/PostController.php';

// Test 1: Logged-out visit to home
$_SESSION = [];
$pc = new PostController();
ob_start();
$pc->index();
$guestOut = ob_get_clean();

echo 'Guest home length: ' . strlen($guestOut) . PHP_EOL;
echo 'Guest sees guest-landing: ' . (strpos($guestOut, 'guest-landing') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Guest sees About the Platform: ' . (strpos($guestOut, 'About the Platform') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Guest sees Register button: ' . (strpos($guestOut, 'Register') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Guest sees Login button: ' . (strpos($guestOut, 'Login') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Guest sees sidePanel: ' . (strpos($guestOut, 'id="sidePanel"') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Guest logo target: ' . (preg_match('/<a[^>]*href="\/SocialNetwork\/public\/"[^>]*class="logo-panel-btn"[^>]*>/', $guestOut) ? 'YES (/SocialNetwork/public/)' : 'NO') . PHP_EOL;

// Test 2: Logged-in visit to home
$_SESSION = ['user_id' => 1, 'user_username' => 'alice_wonder', 'user_full_name' => 'Alice Wonderland'];
ob_start();
$pc->index();
$loggedInOut = ob_get_clean();

echo 'Logged-in sees Newsfeed: ' . (strpos($loggedInOut, 'Newsfeed') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Logged-in sees Create Post: ' . (strpos($loggedInOut, 'Share something with the community') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Logged-in sees sidePanel: ' . (strpos($loggedInOut, 'id="sidePanel"') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Logged-in logo target: ' . (preg_match('/<button[^>]*id="logoSidePanelBtn"[^>]*>/', $loggedInOut) ? 'YES (sidebar drawer button)' : 'NO') . PHP_EOL;
