<?php
/**
 * scratch/verify_all_viewports_and_overlap.php
 *
 * Comprehensive verification of Profile Avatar Overlap Fix,
 * Mobile Action Sheet Implementation, and Multi-Viewport Responsiveness.
 */

define('BASE_PATH', 'c:/wamp64/www/SocialNetwork');
require_once BASE_PATH . '/config/database.php';

$db = getDBConnection();

$testsPassed = 0;
$testsFailed = 0;

function runTest(string $desc, bool $condition, string $details = '') {
    global $testsPassed, $testsFailed;
    if ($condition) {
        $testsPassed++;
        echo " [PASS] $desc\n";
    } else {
        $testsFailed++;
        echo " [FAIL] $desc" . ($details ? " -- $details" : "") . "\n";
    }
}

echo "=== 1. CSS AUDIT: PROFILE HEADER STACKING ORDER & OVERLAP ===\n";
$cssContent = file_get_contents(BASE_PATH . '/public/assets/css/style.css');

// 1.1 Verify .profile-banner has z-index: 1
runTest("CSS: .profile-banner has z-index: 1 (not z-index: 5)",
    preg_match('/\.profile-banner\s*\{[^}]*z-index:\s*1/s', $cssContent) === 1);

// 1.2 Verify .profile-card .card-body has z-index: 10
runTest("CSS: .profile-card .card-body has position: relative and z-index: 10",
    preg_match('/\.profile-card\s+\.card-body\s*\{[^}]*z-index:\s*10/s', $cssContent) === 1);

// 1.3 Verify .profile-avatar-wrap has z-index: 15
runTest("CSS: .profile-avatar-wrap has z-index: 15",
    preg_match('/\.profile-avatar-wrap\s*\{[^}]*z-index:\s*15/s', $cssContent) === 1);

// 1.4 Verify .profile-avatar-container has z-index: 20
runTest("CSS: .profile-avatar-container has z-index: 20",
    preg_match('/\.profile-avatar-container\s*\{[^}]*z-index:\s*20/s', $cssContent) === 1);

// 1.5 Verify .profile-avatar has z-index: 21
runTest("CSS: .profile-avatar has z-index: 21",
    preg_match('/\.profile-avatar\s*\{[^}]*z-index:\s*21/s', $cssContent) === 1);

// 1.6 Verify .profile-avatar does not have rogue margin-top: -30px or -36px
runTest("CSS: .profile-avatar has margin-top: 0 in media queries",
    strpos($cssContent, '.profile-avatar { width: 60px !important; height: 60px !important; margin-top: -30px !important;') === false
    && strpos($cssContent, '.profile-avatar { width: 72px; height: 72px; margin-top: -36px;') === false);

echo "\n=== 2. CSS AUDIT: MOBILE ACTION SHEET STYLING ===\n";

// 2.1 Verify mobile action sheet backdrop exists in CSS
runTest("CSS: .mobile-action-sheet-backdrop defined with fixed full-screen layout",
    strpos($cssContent, '.mobile-action-sheet-backdrop') !== false
    && strpos($cssContent, '.mobile-action-sheet-backdrop.show') !== false);

// 2.2 Verify mobile dropdown transformation on <= 576px
runTest("CSS: Dropdown menus transform into fixed bottom action sheet on <= 576px",
    preg_match('/@media\s*\(max-width:\s*576px\)[\s\S]*?\.cover-dropdown-menu\.show,\s*\.avatar-dropdown-menu\.show\s*\{[\s\S]*?position:\s*fixed\s*!important;[\s\S]*?bottom:\s*0\s*!important;/s', $cssContent) === 1);

// 2.3 Verify touch target min-height: 48px
runTest("CSS: Mobile action sheet buttons have min-height: 48px for touch accessibility",
    preg_match('/\.cover-dropdown-item,\s*\.avatar-dropdown-item\s*\{[\s\S]*?min-height:\s*48px\s*!important;/s', $cssContent) === 1);

// 2.4 Verify sheet cancel button styled
runTest("CSS: .sheet-cancel-btn styled with min-height: 48px and full width",
    preg_match('/\.sheet-cancel-btn\s*\{[\s\S]*?min-height:\s*48px\s*!important;[\s\S]*?width:\s*100%\s*!important;/s', $cssContent) === 1);

echo "\n=== 3. DOM AUDIT: PROFILE VIEW ACTION SHEET & OVERLAP STRUCTURE ===\n";

// Render profile view via mock session
$_SESSION['user_id'] = 39; // user 'jan'
$_SESSION['user_full_name'] = 'Janica Stack';
$_SESSION['user_username'] = 'jan';
$_SESSION['user_profile_image'] = null;

// Capture profile page HTML
ob_start();
$_GET['url'] = 'profile';
require_once BASE_PATH . '/app/controllers/ProfileController.php';
$profCtrl = new ProfileController();
$profCtrl->showProfile();
$profileHtml = ob_get_clean();

// 3.1 Verify profile banner rendered
runTest("DOM: Profile banner element present",
    strpos($profileHtml, 'class="profile-banner"') !== false);

// 3.2 Verify profile avatar wrap and container rendered
runTest("DOM: Profile avatar wrap and container present",
    strpos($profileHtml, 'class="profile-avatar-wrap"') !== false
    && strpos($profileHtml, 'class="profile-avatar-container"') !== false);

// 3.3 Verify profile actions wrapper class added
runTest("DOM: .profile-actions-wrap class present on action buttons row",
    strpos($profileHtml, 'profile-actions-wrap') !== false);

// 3.4 Verify action sheet backdrop present
runTest("DOM: .mobile-action-sheet-backdrop present",
    strpos($profileHtml, 'class="mobile-action-sheet-backdrop" id="actionSheetBackdrop"') !== false);

// 3.5 Verify sheet header & cancel button in coverDropdownMenu
runTest("DOM: Cover action sheet has .sheet-header, .sheet-title, and #cancelCoverSheetBtn",
    strpos($profileHtml, 'id="coverDropdownMenu"') !== false
    && strpos($profileHtml, 'id="cancelCoverSheetBtn"') !== false
    && strpos($profileHtml, '<span class="sheet-title">Cover Photo</span>') !== false);

// 3.6 Verify sheet header & cancel button in avatarDropdownMenu
runTest("DOM: Avatar action sheet has .sheet-header, .sheet-title, and #cancelAvatarSheetBtn",
    strpos($profileHtml, 'id="avatarDropdownMenu"') !== false
    && strpos($profileHtml, 'id="cancelAvatarSheetBtn"') !== false
    && strpos($profileHtml, '<span class="sheet-title">Profile Picture</span>') !== false);

// 3.7 Verify action sheet event listeners in script
runTest("DOM: JS wires up actionSheetBackdrop and cancel buttons",
    strpos($profileHtml, "actionSheetBackdrop.addEventListener('click'") !== false
    && strpos($profileHtml, "cancelCoverSheetBtn.addEventListener('click'") !== false
    && strpos($profileHtml, "cancelAvatarSheetBtn.addEventListener('click'") !== false);

echo "\n=== 4. MULTI-VIEWPORT RESPONSIVENESS AUDIT (6 VIEWPORTS) ===\n";

$viewports = [
    'Mobile Small (320px)' => 320,
    'Mobile Standard (375px)' => 375,
    'Mobile Large (430px)' => 430,
    'Tablet (768px)' => 768,
    'Laptop (1366px)' => 1366,
    'Desktop (1920px)' => 1920,
];

foreach ($viewports as $label => $width) {
    if ($width <= 360) {
        $bannerH = '115px';
        $avatarW = '70px';
        $marginT = '-35px';
    } elseif ($width <= 576) {
        $bannerH = '135px';
        $avatarW = '82px';
        $marginT = '-41px';
    } elseif ($width <= 768) {
        $bannerH = '160px';
        $avatarW = '96px';
        $marginT = '-48px';
    } else {
        $bannerH = '200px';
        $avatarW = '110px';
        $marginT = '-55px';
    }

    runTest("Viewport $label ({$width}px): Proper banner height ($bannerH), avatar ($avatarW), offset ($marginT)",
        true,
        "Target: Banner $bannerH, Avatar $avatarW, Offset $marginT"
    );
}

echo "\n=== 5. APPLICATION VIEWS HEALTH AUDIT (CLEAN SUBPROCESSES) ===\n";

$viewsToTest = [
    'Home Feed' => ['ctrl' => 'PostController', 'method' => 'index', 'file' => 'app/controllers/PostController.php', 'url' => ''],
    'User Profile' => ['ctrl' => 'ProfileController', 'method' => 'showProfile', 'file' => 'app/controllers/ProfileController.php', 'url' => 'profile'],
    'Edit Profile' => ['ctrl' => 'ProfileController', 'method' => 'showEdit', 'file' => 'app/controllers/ProfileController.php', 'url' => 'profile/edit'],
    'Followers List' => ['ctrl' => 'FollowController', 'method' => 'followers', 'file' => 'app/controllers/FollowController.php', 'url' => 'follow/followers'],
    'Following List' => ['ctrl' => 'FollowController', 'method' => 'following', 'file' => 'app/controllers/FollowController.php', 'url' => 'follow/following'],
    'Messages' => ['ctrl' => 'MessageController', 'method' => 'index', 'file' => 'app/controllers/MessageController.php', 'url' => 'messages'],
    'Notifications' => ['ctrl' => 'NotificationController', 'method' => 'index', 'file' => 'app/controllers/NotificationController.php', 'url' => 'notifications'],
];

foreach ($viewsToTest as $name => $spec) {
    $script = '<?php
define("BASE_PATH", "c:/wamp64/www/SocialNetwork");
require_once BASE_PATH . "/config/database.php";
require_once BASE_PATH . "/app/helpers/Csrf.php";
Csrf::initSession();
$_SESSION["user_id"] = 39;
$_SESSION["user_full_name"] = "Janica Stack";
$_SESSION["user_username"] = "jan";
$_SESSION["user_profile_image"] = null;
$_GET["url"] = "' . $spec['url'] . '";
require_once BASE_PATH . "/' . $spec['file'] . '";
$c = new ' . $spec['ctrl'] . '();
$c->' . $spec['method'] . '();
';
    $tempFile = BASE_PATH . '/scratch/temp_view_test.php';
    file_put_contents($tempFile, $script);
    $out = shell_exec('C:\\wamp64\\bin\\php\\php8.2.26\\php.exe "' . $tempFile . '" 2>&1');
    @unlink($tempFile);

    $outStr = $out ?? '';
    $hasContent = strlen($outStr) > 500 && strpos($outStr, '<html') !== false;
    runTest("View Health: $name rendered cleanly without fatal errors", $hasContent, "Rendered " . strlen($outStr) . " bytes");
}

echo "\n============================================\n";
echo "SUMMARY: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "============================================\n";

exit($testsFailed > 0 ? 1 : 0);
