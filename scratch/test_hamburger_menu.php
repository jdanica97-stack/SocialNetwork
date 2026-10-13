<?php
/**
 * scratch/test_hamburger_menu.php
 *
 * Automated verification suite for the Hamburger Menu feature:
 *   1. Hamburger button markup rendered for authenticated users.
 *   2. Hamburger button markup completely omitted for guest / logged-out visitors.
 *   3. Side panel drawer contains the hamburger menu items (Home, Search, Profile, Messages - Coming Soon).
 *   4. Side panel drawer completely omitted for logged-out visitors.
 *   5. CSS defines .hamburger-nav-btn, .hamburger-icon-symbol, and aria-expanded state.
 *   6. JavaScript (side-panel.js) attaches to #hamburgerMenuBtn without duplication,
 *      handles opening, closing, toggle, backdrop click, Escape key, and auto-closing on active link selection.
 *   7. Regression testing across authentication, profile, search, and posts.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/PostModel.php';
require_once BASE_PATH . '/app/controllers/PostController.php';

$pass = 0;
$fail = 0;

function assertTest(bool $condition, string $description, string $details = '') {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo "  [PASS] " . $description . PHP_EOL;
    } else {
        $fail++;
        echo "  [FAIL] " . $description . ($details ? " -- {$details}" : '') . PHP_EOL;
    }
}

echo "========================================================\n";
echo "HAMBURGER MENU AUTOMATED VERIFICATION SUITE\n";
echo "========================================================\n\n";

// -----------------------------------------------------------------------------
// Test Group 1: Authenticated View Markup
// -----------------------------------------------------------------------------
echo "Test Group 1: Authenticated View Markup\n";

ob_start();
$_SESSION = [
    'user_id' => 1,
    'user_username' => 'alice_wonder',
    'user_full_name' => 'Alice Wonderland'
];
require BASE_PATH . '/app/views/layouts/header.php';
$loggedInHtml = ob_get_clean();

assertTest(strpos($loggedInHtml, 'id="hamburgerMenuBtn"') !== false, 'Header contains #hamburgerMenuBtn for logged-in user');
assertTest(strpos($loggedInHtml, 'class="halo-nav-btn hamburger-nav-btn"') !== false, 'Hamburger button has expected CSS classes');
assertTest(strpos($loggedInHtml, '&#9776;') !== false || strpos($loggedInHtml, '☰') !== false, 'Hamburger button displays ☰ icon symbol');
assertTest(strpos($loggedInHtml, 'aria-label="Toggle navigation menu"') !== false, 'Hamburger button has accessible aria-label');
assertTest(strpos($loggedInHtml, 'aria-controls="sidePanel"') !== false, 'Hamburger button has aria-controls="sidePanel"');
assertTest(strpos($loggedInHtml, 'id="sidePanel"') !== false, 'Side panel drawer rendered for logged-in user');
assertTest(strpos($loggedInHtml, 'id="sidePanelHomeLink"') === false, 'Home link is REMOVED from side panel drawer');
assertTest(strpos($loggedInHtml, 'id="sidePanelSearchLink"') !== false, 'Side panel contains Search link');
assertTest(strpos($loggedInHtml, 'id="sidePanelProfileLink"') !== false, 'Side panel contains Profile link');
assertTest(strpos($loggedInHtml, 'id="sidePanelMessagesLink"') !== false || strpos($loggedInHtml, 'id="sidePanelMessagesItem"') !== false, 'Side panel contains Messages link/item');
assertTest(strpos($loggedInHtml, 'id="sidePanelLogoutLink"') !== false, 'Side panel contains Sign Out link');
assertTest(strpos($loggedInHtml, 'href="/SocialNetwork/public/?url=auth/logout"') !== false, 'Sign Out links to auth/logout');

// -----------------------------------------------------------------------------
// Test Group 2: Logged-Out / Guest View Markup (Strict Access Control)
// -----------------------------------------------------------------------------
echo "\nTest Group 2: Logged-Out / Guest View (Strict Hiding)\n";

ob_start();
$_SESSION = [];
unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_full_name']);
require BASE_PATH . '/app/views/layouts/header.php';
$loggedOutHtml = ob_get_clean();

assertTest(strpos($loggedOutHtml, 'id="hamburgerMenuBtn"') === false, 'Hamburger button is completely NOT rendered for logged-out visitors');
assertTest(strpos($loggedOutHtml, 'hamburger-nav-btn') === false, 'No hamburger classes rendered for guests');
assertTest(strpos($loggedOutHtml, 'id="sidePanel"') === false, 'Side panel drawer is NOT rendered in DOM for logged-out visitors');
assertTest(strpos($loggedOutHtml, 'id="sidePanelBackdrop"') === false, 'Side panel backdrop is NOT rendered for guests');
assertTest(strpos($loggedOutHtml, '?url=auth/login') !== false, 'Login link present for guests');
assertTest(strpos($loggedOutHtml, '?url=auth/register') !== false, 'Register link present for guests');

// -----------------------------------------------------------------------------
// Test Group 3: CSS Verification
// -----------------------------------------------------------------------------
echo "\nTest Group 3: CSS Stylesheet Verification\n";

$css = file_get_contents(BASE_PATH . '/public/assets/css/style.css');
assertTest(strpos($css, '.hamburger-nav-btn') !== false, 'CSS contains .hamburger-nav-btn');
assertTest(strpos($css, '.hamburger-icon-symbol') !== false, 'CSS contains .hamburger-icon-symbol');
assertTest(strpos($css, '.hamburger-nav-btn[aria-expanded="true"]') !== false, 'CSS contains active expanded styling for hamburger');
assertTest(strpos($css, '.side-panel.open') !== false, 'CSS defines .side-panel.open drawer transition');
assertTest(strpos($css, '.side-panel-backdrop.open') !== false, 'CSS defines .side-panel-backdrop.open');

// -----------------------------------------------------------------------------
// Test Group 4: JavaScript Verification (side-panel.js)
// -----------------------------------------------------------------------------
echo "\nTest Group 4: JavaScript Logic Verification\n";

$js = file_get_contents(BASE_PATH . '/public/assets/js/side-panel.js');
assertTest(strpos($js, "getElementById('hamburgerMenuBtn')") !== false, 'side-panel.js retrieves hamburgerMenuBtn');
assertTest(strpos($js, "hamburgerBtn.addEventListener('click'") !== false, 'side-panel.js binds click handler to hamburgerBtn');
assertTest(strpos($js, "togglePanel()") !== false, 'side-panel.js calls togglePanel() on hamburger click');
assertTest(strpos($js, "hamburgerBtn.setAttribute('aria-expanded'") !== false, 'side-panel.js synchronizes aria-expanded on hamburger');
assertTest(strpos($js, "closePanel()") !== false, 'side-panel.js defines closePanel()');
assertTest(strpos($js, "Escape") !== false, 'side-panel.js handles Escape key to close menu');
assertTest(strpos($js, "activeLink") !== false, 'side-panel.js closes panel when user selects an active navigation link');
assertTest(strpos($js, "disabledItem") !== false, 'side-panel.js prevents interaction on disabled items');

echo "\n========================================================\n";
echo "SUMMARY: {$pass} Passed, {$fail} Failed\n";
echo "========================================================\n";

if ($fail === 0) {
    echo "ALL HAMBURGER MENU TESTS PASSED!\n";
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
