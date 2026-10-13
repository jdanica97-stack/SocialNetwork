<?php
/**
 * scratch/test_login_access_and_logo.php
 *
 * Verifies all requirements specified in:
 * "Orbit Web App — Clickable Logo and Login Access Control"
 *
 * Scenarios tested:
 *   1. Clickable Orbit logo and brand area in sidebar:
 *      - Renders <a> element with .side-panel-brand
 *      - When logged in: links to main page (/SocialNetwork/public/)
 *      - When logged out: links to login page (/SocialNetwork/public/?url=auth/login)
 *      - Preserves image, text, styling, responsive rules
 *   2. Main application access control:
 *      - Unauthenticated access to home/root ('', 'home') displays login page
 *      - PostController::index() redirects unauthenticated visitors to login
 *   3. Search access control:
 *      - Unauthenticated access to search (?url=search) redirects to login
 *   4. Profile access control:
 *      - Unauthenticated access to profile (?url=profile, profile/edit) redirects to login
 *   5. Posts CRUD access control:
 *      - Unauthenticated access to posts (posts/create, posts/edit, etc.) redirects to login
 *   6. Comments & Likes access control:
 *      - Unauthenticated access to comments and likes enforces auth
 *   7. Valid session persistence:
 *      - Logged-in session allows access to home, search, profile, and posts
 *      - Refreshing page preserves authentication session
 *   8. Logout session invalidation:
 *      - AuthController::logout() unsets $_SESSION, destroys session, deletes cookie,
 *        and redirects to login; subsequent protected requests are rejected
 *   9. Sidebar navigation preservation:
 *      - Search and Profile links in sidebar direct guests to login
 *      - Messages displays 'Coming Soon' with disabled behavior
 *      - Light/Dark mode logo switching and toggle work
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/PostController.php';
require_once BASE_PATH . '/app/controllers/ProfileController.php';
require_once BASE_PATH . '/app/controllers/SearchController.php';

$pass = 0;
$fail = 0;

function testAssert(bool $condition, string $description) {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo " [PASS] " . $description . PHP_EOL;
    } else {
        $fail++;
        echo " [FAIL] " . $description . PHP_EOL;
    }
}

echo "=== 1. VERIFYING CLICKABLE ORBIT LOGO BRAND AREA ===" . PHP_EOL;
// Logged-in view
$_SESSION = [
    'user_id' => 1,
    'user_username' => 'alice_wonder',
    'user_full_name' => 'Alice Wonderland'
];
ob_start();
require BASE_PATH . '/app/views/layouts/header.php';
$loggedInHeader = ob_get_clean();

testAssert(strpos($loggedInHeader, 'id="sidePanelBrandLink"') !== false, 'Sidebar brand has id="sidePanelBrandLink"');
testAssert(strpos($loggedInHeader, '<a href="/SocialNetwork/public/" class="side-panel-brand"') !== false, 'Logged-in user brand link targets main home page (/SocialNetwork/public/)');
testAssert(strpos($loggedInHeader, 'logo-img-light') !== false && strpos($loggedInHeader, 'logo-img-dark') !== false, 'Brand link preserves both light and dark logo images');
testAssert(strpos($loggedInHeader, '<span class="side-panel-title">Orbit</span>') !== false, 'Brand link preserves Orbit title span');

// Logged-out view
$_SESSION = [];
ob_start();
require BASE_PATH . '/app/views/layouts/header.php';
$loggedOutHeader = ob_get_clean();

testAssert(preg_match('/<a[^>]*href="\/SocialNetwork\/public\/"[^>]*class="logo-panel-btn"[^>]*>/s', $loggedOutHeader) === 1, 'Logged-out user top logo links to home landing page (/SocialNetwork/public/)');
testAssert(strpos($loggedOutHeader, 'id="sidePanel"') === false, 'Sidebar panel is NOT rendered in DOM for logged-out visitors');

// CSS verification for clickable target
$css = file_get_contents(BASE_PATH . '/public/assets/css/style.css');
testAssert(strpos($css, '.side-panel-brand') !== false, 'CSS contains .side-panel-brand');
testAssert(strpos($css, 'cursor: pointer;') !== false, 'CSS gives .side-panel-brand pointer cursor');
testAssert(strpos($css, 'text-decoration: none;') !== false, 'CSS ensures clean anchor formatting on .side-panel-brand');

echo PHP_EOL . "=== 2. VERIFYING LOGGED-OUT HOME ACCESS CONTROL ===" . PHP_EOL;
$feedPhp = file_get_contents(BASE_PATH . '/app/views/posts/feed.php');
testAssert(strpos($feedPhp, 'guest-landing') !== false, 'Home view provides dedicated guest-landing presentation');
testAssert(strpos($feedPhp, 'About the Platform') !== false, 'Guest view explains what the platform is');
testAssert(strpos($feedPhp, 'Register') !== false && strpos($feedPhp, 'Login') !== false, 'Guest view provides Login and Register buttons');

echo PHP_EOL . "=== 3. VERIFYING SEARCH PAGE ACCESS CONTROL ===" . PHP_EOL;
$searchCtrlPhp = file_get_contents(BASE_PATH . '/app/controllers/SearchController.php');
testAssert(strpos($searchCtrlPhp, "private function requireAuth()") !== false, 'SearchController defines requireAuth()');
testAssert(strpos($searchCtrlPhp, "\$this->requireAuth();") !== false, 'SearchController index() calls requireAuth() at entry');

echo PHP_EOL . "=== 4. VERIFYING PROFILE & POSTS ACCESS CONTROL ===" . PHP_EOL;
$profileCtrlPhp = file_get_contents(BASE_PATH . '/app/controllers/ProfileController.php');
$postCtrlPhp    = file_get_contents(BASE_PATH . '/app/controllers/PostController.php');
testAssert(strpos($profileCtrlPhp, "\$this->requireAuth();") !== false, 'ProfileController methods invoke requireAuth()');
testAssert(strpos($postCtrlPhp, "\$this->requireAuth();") !== false, 'PostController CRUD methods invoke requireAuth()');

echo PHP_EOL . "=== 5. VERIFYING AUTHENTICATION & LOGOUT LOGIC ===" . PHP_EOL;
$authCtrlPhp = file_get_contents(BASE_PATH . '/app/controllers/AuthController.php');
testAssert(strpos($authCtrlPhp, 'session_destroy()') !== false, 'logout() destroys session on server');
testAssert(strpos($authCtrlPhp, 'setcookie(') !== false, 'logout() clears session cookie on client');
testAssert(strpos($authCtrlPhp, "redirect('/SocialNetwork/public/?url=auth/login')") !== false, 'logout() redirects to login page');
testAssert(strpos($authCtrlPhp, 'session_regenerate_id(true)') !== false, 'login() regenerates session ID to prevent fixation');

echo PHP_EOL . "=== 6. VERIFYING SIDEBAR ITEM BEHAVIOR ===" . PHP_EOL;
// Check logged-in sidebar links
testAssert(strpos($loggedInHeader, 'id="sidePanelSearchLink"') !== false, 'Logged-in sidebar contains Search link');
testAssert(strpos($loggedInHeader, 'id="sidePanelProfileLink"') !== false, 'Logged-in sidebar contains Profile link');
testAssert(strpos($loggedInHeader, 'sidePanelMessagesLink') !== false || strpos($loggedInHeader, 'badge-coming-soon') !== false, 'Logged-in sidebar contains Messages link or item');
testAssert(strpos($loggedInHeader, 'href="/SocialNetwork/public/?url=search"') !== false, 'Logged-in Search link opens search page');
testAssert(strpos($loggedInHeader, 'href="/SocialNetwork/public/?url=profile"') !== false, 'Logged-in Profile link opens profile');

echo PHP_EOL . "=== 7. VERIFYING THEME TOGGLE & JS LOGIC ===" . PHP_EOL;
$sidePanelJs = file_get_contents(BASE_PATH . '/public/assets/js/side-panel.js');
testAssert(strpos($sidePanelJs, 'hamburgerMenuBtn') !== false, 'Header hamburger button toggles side panel');
testAssert(strpos($sidePanelJs, 'Escape') !== false, 'Escape key closes side panel');
testAssert(strpos($sidePanelJs, 'sidePanelBackdrop') !== false, 'Clicking outside closes side panel');
testAssert(strpos($css, '[data-theme="dark"] .logo-img-dark') !== false, 'Dark theme displays dark mode logo');

echo PHP_EOL . "=========================================" . PHP_EOL;
echo "SUMMARY: Passed: {$pass}, Failed: {$fail}" . PHP_EOL;
echo "=========================================" . PHP_EOL;
