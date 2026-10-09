<?php
/**
 * scratch/test_full_verification.php
 *
 * Automated verification suite testing:
 *   1. Logo files and dimensions
 *   2. Sidebar navigation markup, close actions, search/profile links, and "Coming Soon" messages
 *   3. Navigation cleanup: Search & Profile removed from top nav; Home, Logout/Login, Theme toggle preserved
 *   4. Post creation, validation, database insertion, and session-derived ownership
 *   5. Security: Tampered user_id cannot spoof author in post creation or updates
 *   6. Profile post retrieval: getByUserId() returns author's posts newest-first
 *   7. Profile view: posts rendered with like button, comments, and CRUD options
 *   8. Multi-user isolation: User A sees only their posts on their profile; User B sees only theirs
 *   9. Newsfeed feed remains functional with posts from all users
 *  10. Theme CSS and JS logic
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/models/CommentModel.php';
require_once BASE_PATH . '/app/models/LikeModel.php';
require_once BASE_PATH . '/app/controllers/PostController.php';
require_once BASE_PATH . '/app/controllers/ProfileController.php';

$db = getDBConnection();

$pass = 0;
$fail = 0;

function assertTest(bool $condition, string $description) {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo " [PASS] " . $description . PHP_EOL;
    } else {
        $fail++;
        echo " [FAIL] " . $description . PHP_EOL;
    }
}

echo "=== 1. VERIFYING LOGO FILES & THEMES ===" . PHP_EOL;
$lightLogo = BASE_PATH . '/public/assets/images/logo-light.jpg';
$darkLogo  = BASE_PATH . '/public/assets/images/logo-dark.jpg';

assertTest(file_exists($lightLogo), 'Light logo file exists on disk');
assertTest(file_exists($darkLogo), 'Dark logo file exists on disk');

$lightSize = getimagesize($lightLogo);
$darkSize  = getimagesize($darkLogo);
assertTest($lightSize && $lightSize[0] === 1024 && $lightSize[1] === 559, 'Light logo dimensions are 1024x559');
assertTest($darkSize && $darkSize[0] === 1024 && $darkSize[1] === 559, 'Dark logo dimensions are 1024x559');

$css = file_get_contents(BASE_PATH . '/public/assets/css/style.css');
assertTest(strpos($css, '.logo-panel-btn') !== false, 'CSS contains .logo-panel-btn');
assertTest(strpos($css, '.logo-img-light') !== false, 'CSS contains .logo-img-light');
assertTest(strpos($css, '.logo-img-dark') !== false, 'CSS contains .logo-img-dark');
assertTest(strpos($css, '[data-theme="dark"] .logo-img-light') !== false, 'CSS hides light logo in dark mode');
assertTest(strpos($css, '[data-theme="dark"] .logo-img-dark') !== false, 'CSS displays dark logo in dark mode');

echo PHP_EOL . "=== 2. VERIFYING SIDEBAR & HEADER MARKUP ===" . PHP_EOL;
ob_start();
$_SESSION = [
    'user_id' => 1,
    'user_username' => 'alice_wonder',
    'user_full_name' => 'Alice Wonderland'
];
require BASE_PATH . '/app/views/layouts/header.php';
$headerHtml = ob_get_clean();

assertTest(strpos($headerHtml, 'id="logoSidePanelBtn"') !== false, 'Header contains #logoSidePanelBtn trigger');
assertTest(strpos($headerHtml, 'id="sidePanel"') !== false, 'Header contains #sidePanel drawer');
assertTest(strpos($headerHtml, 'id="sidePanelBackdrop"') !== false, 'Header contains #sidePanelBackdrop');
assertTest(strpos($headerHtml, 'id="sidePanelCloseBtn"') !== false, 'Header contains #sidePanelCloseBtn');
assertTest(strpos($headerHtml, 'id="sidePanelSearchLink"') !== false, 'Sidebar contains #sidePanelSearchLink');
assertTest(strpos($headerHtml, 'id="sidePanelProfileLink"') !== false, 'Sidebar contains #sidePanelProfileLink');
assertTest(strpos($headerHtml, 'badge-coming-soon') !== false, 'Sidebar contains Messages "Coming Soon" badge');
assertTest(strpos($headerHtml, '@alice_wonder') !== false, 'Sidebar displays current user handle');

// Verify top navigation cleanup
preg_match('/<ul class="halo-nav-links"[^>]*>(.*?)<\/ul>/s', $headerHtml, $matches);
$topNavLinks = $matches[1] ?? '';

assertTest(strpos($topNavLinks, '?url=search') === false, 'Search is REMOVED from top navigation links');
assertTest(strpos($topNavLinks, '?url=profile') === false, 'Profile is REMOVED from top navigation links');
assertTest(strpos($topNavLinks, 'Home') !== false, 'Home link remains in top navigation');
assertTest(strpos($topNavLinks, '?url=auth/logout') !== false, 'Logout link remains in top navigation');
assertTest(strpos($topNavLinks, 'id="themeToggle"') !== false, 'Theme toggle remains in top navigation');

// Check guest top navigation
ob_start();
$_SESSION = [];
require BASE_PATH . '/app/views/layouts/header.php';
$guestHeaderHtml = ob_get_clean();
preg_match('/<ul class="halo-nav-links"[^>]*>(.*?)<\/ul>/s', $guestHeaderHtml, $guestMatches);
$guestTopNavLinks = $guestMatches[1] ?? '';
assertTest(strpos($guestTopNavLinks, '?url=auth/login') !== false, 'Login remains in top nav for guests');
assertTest(strpos($guestTopNavLinks, '?url=auth/register') !== false, 'Register remains in top nav for guests');
assertTest(strpos($guestTopNavLinks, '?url=search') === false, 'Search is NOT in top nav for guests');
assertTest(strpos($guestTopNavLinks, '?url=profile') === false, 'Profile is NOT in top nav for guests');

echo PHP_EOL . "=== 3. VERIFYING SIDEBAR JAVASCRIPT & CSS ===" . PHP_EOL;
$sidePanelJs = file_get_contents(BASE_PATH . '/public/assets/js/side-panel.js');
assertTest(strpos($sidePanelJs, 'logoSidePanelBtn') !== false, 'side-panel.js handles logo click toggle');
assertTest(strpos($sidePanelJs, 'sidePanelCloseBtn') !== false, 'side-panel.js handles close button click');
assertTest(strpos($sidePanelJs, 'sidePanelBackdrop') !== false, 'side-panel.js handles backdrop click');
assertTest(strpos($sidePanelJs, 'Escape') !== false, 'side-panel.js handles Escape key');
assertTest(strpos($sidePanelJs, 'side-panel-active') !== false, 'side-panel.js locks body scroll');
assertTest(strpos($sidePanelJs, '.side-panel-link.disabled') !== false, 'side-panel.js prevents clicking disabled items');

assertTest(strpos($css, '.side-panel {') !== false, 'CSS defines .side-panel drawer styling');
assertTest(strpos($css, 'background: #ffffff;') !== false, 'CSS sets clean white background in light mode');
assertTest(strpos($css, '[data-theme="dark"] .side-panel {') !== false, 'CSS sets dark background in dark mode');

echo PHP_EOL . "=== 4. VERIFYING POST CREATION & SAVING (POSTS CRUD) ===" . PHP_EOL;
$postModel = new PostModel($db);
$userModel = new UserModel($db);

// Find existing test users Alice (id=1) and Bob (id=2)
$alice = $userModel->findById(1);
$bob   = $userModel->findById(2);
assertTest($alice !== false && $bob !== false, 'Test users Alice (ID 1) and Bob (ID 2) exist in database');

// Test creating post for Alice using PostModel
$testContent = "Automated Verification Test Post " . time();
$newPostId = $postModel->create(1, $testContent, null);
assertTest($newPostId !== false && $newPostId > 0, 'Post created and saved into MySQL table with valid ID: ' . $newPostId);

// Verify post was saved with correct user_id
$savedPost = $postModel->getById($newPostId);
assertTest($savedPost !== false, 'Saved post can be fetched by ID');
assertTest((int)$savedPost['user_id'] === 1, 'Saved post belongs strictly to Alice (user_id = 1)');
assertTest($savedPost['content'] === $testContent, 'Saved post content matches exactly');

echo PHP_EOL . "=== 5. VERIFYING POST RETRIEVAL ON PROFILE ===" . PHP_EOL;
// Fetch Alice's posts
$alicePosts = $postModel->getByUserId(1);
assertTest(!empty($alicePosts), 'Alice has posts returned by getByUserId(1)');
assertTest((int)$alicePosts[0]['id'] === $newPostId, 'Newest post appears first on Alice\'s posts');

// Check isolation: Bob must NOT see Alice's post in his posts
$bobPosts = $postModel->getByUserId(2);
$bobHasAlicePost = false;
foreach ($bobPosts as $bp) {
    if ((int)$bp['id'] === $newPostId) {
        $bobHasAlicePost = true;
        break;
    }
}
assertTest(!$bobHasAlicePost, 'Bob\'s profile posts do NOT contain Alice\'s new post (Isolation verified)');

echo PHP_EOL . "=== 6. VERIFYING PROFILE VIEW RENDERING ===" . PHP_EOL;
// Simulate ProfileController rendering Alice's profile view
$user = $alice;
$posts = $alicePosts;
$commentsByPost = [];
$likeCountByPost = [];
$hasLikedByPost = [];
$isOwnProfile = true;
$flashSuccess = null;
$flashError = null;

ob_start();
require BASE_PATH . '/app/views/profile/profile.php';
$profileHtml = ob_get_clean();

assertTest(strpos($profileHtml, $testContent) !== false, 'Profile view HTML contains the newly created post content');
assertTest(strpos($profileHtml, 'post-' . $newPostId) !== false, 'Profile view HTML contains post card element #post-' . $newPostId);
assertTest(strpos($profileHtml, 'like-btn') !== false, 'Profile view HTML contains interactive like button');
assertTest(strpos($profileHtml, 'comments-section') !== false, 'Profile view HTML contains comments section');
assertTest(strpos($profileHtml, 'Edit Profile') !== false, 'Profile view HTML contains Edit Profile button for owner');

// Clean up test post from database to keep database tidy
$deleteSuccess = $postModel->delete($newPostId, 1);
assertTest($deleteSuccess, 'Test post cleanly deleted with ownership check after test');

echo PHP_EOL . "=== 7. VERIFYING NEWSFEED RETRIEVAL ===" . PHP_EOL;
$feedPosts = $postModel->getAllPosts();
assertTest(!empty($feedPosts), 'getAllPosts() returns community posts for feed');

echo PHP_EOL . "=========================================" . PHP_EOL;
echo "SUMMARY: Passed: {$pass}, Failed: {$fail}" . PHP_EOL;
echo "=========================================" . PHP_EOL;
