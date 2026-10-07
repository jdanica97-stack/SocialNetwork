<?php
/**
 * public/index.php
 *
 * Front Controller — single entry point for the entire application.
 * Every HTTP request is routed here via public/.htaccess.
 *
 * Routing pattern:  /SocialNetwork/public/?url=<controller>/<action>
 *
 * Supported routes:
 *   (empty) / home          → PostController::index()          [GET]
 *   auth/login              → AuthController::showLogin()      [GET]
 *   auth/login              → AuthController::login()          [POST]
 *   auth/register           → AuthController::showRegister()   [GET]
 *   auth/register           → AuthController::register()       [POST]
 *   auth/logout             → AuthController::logout()         [GET]
 *   profile                 → ProfileController::showProfile() [GET]
 *   profile/edit            → ProfileController::showEdit()    [GET]
 *   profile/update          → ProfileController::update()      [POST]
 *   posts/create            → PostController::create()         [POST]
 *   posts/edit              → PostController::edit()           [GET]
 *   posts/update            → PostController::update()         [POST]
 *   posts/delete            → PostController::delete()         [POST]
 *   comments/create         → CommentController::create()      [POST]
 *   comments/edit           → CommentController::edit()        [GET]
 *   comments/update         → CommentController::update()      [POST]
 *   comments/delete         → CommentController::delete()      [POST]
 *   likes/toggle            → LikeController::toggle()         [POST]
 *   likes/like              → LikeController::like()           [POST]
 *   likes/unlike            → LikeController::unlike()         [POST]
 */

// ─── Bootstrap ────────────────────────────────────────────────────────────────

// Absolute path to the project root (one level above /public)
define('BASE_PATH', dirname(__DIR__));

// Start session once, globally — controllers check session_status() before calling again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the PDO connection factory — all controllers/models use getDBConnection()
require_once BASE_PATH . '/config/database.php';

// ─── Route Parsing ────────────────────────────────────────────────────────────

// The ?url= value is produced by the .htaccess RewriteRule
// Strip leading/trailing slashes so "auth/login" and "/auth/login/" both work
$url = trim($_GET['url'] ?? '', '/');

// ─── Dispatch ─────────────────────────────────────────────────────────────────

// ── Home / Newsfeed ───────────────────────────────────────────────────────────
if ($url === '' || $url === 'home') {
    require_once BASE_PATH . '/app/controllers/PostController.php';
    $postCtrl = new PostController();
    $postCtrl->index();
    exit;
}

// ── Search & Filter (Step 11) ─────────────────────────────────────────────────
if ($url === 'search' || str_starts_with($url, 'search/')) {
    require_once BASE_PATH . '/app/controllers/SearchController.php';
    $searchCtrl = new SearchController();
    $searchCtrl->index();
    exit;
}


// ── Auth Routes ───────────────────────────────────────────────────────────────
if ($url === 'auth/login' || $url === 'auth/register' || $url === 'auth/logout') {
    require_once BASE_PATH . '/app/controllers/AuthController.php';
    $auth = new AuthController();

    if ($url === 'auth/login') {
        ($_SERVER['REQUEST_METHOD'] === 'POST')
            ? $auth->login()
            : $auth->showLogin();
        exit;
    }

    if ($url === 'auth/register') {
        ($_SERVER['REQUEST_METHOD'] === 'POST')
            ? $auth->register()
            : $auth->showRegister();
        exit;
    }

    if ($url === 'auth/logout') {
        $auth->logout();
        exit;
    }
}

// ── Profile Routes ────────────────────────────────────────────────────────────
if ($url === 'profile' || $url === 'profile/edit' || $url === 'profile/update') {
    require_once BASE_PATH . '/app/controllers/ProfileController.php';
    $profile = new ProfileController();

    if ($url === 'profile') {
        $profile->showProfile();
        exit;
    }

    if ($url === 'profile/edit') {
        $profile->showEdit();
        exit;
    }

    if ($url === 'profile/update') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile->update();
        } else {
            header('Location: /SocialNetwork/public/?url=profile/edit');
        }
        exit;
    }
}

// ── Post Routes (Posts CRUD) ──────────────────────────────────────────────────
if ($url === 'posts/create' || $url === 'posts/edit' || $url === 'posts/update' || $url === 'posts/delete' || str_starts_with($url, 'posts/')) {
    require_once BASE_PATH . '/app/controllers/PostController.php';
    $postCtrl = new PostController();

    $segments = explode('/', $url);
    $action = $segments[1] ?? '';
    if (isset($segments[2]) && !isset($_GET['id'])) {
        $_GET['id'] = $segments[2];
    }

    if ($action === 'create') {
        $postCtrl->create();
        exit;
    }

    if ($action === 'edit') {
        $postCtrl->edit();
        exit;
    }

    if ($action === 'update') {
        $postCtrl->update();
        exit;
    }

    if ($action === 'delete') {
        $postCtrl->delete();
        exit;
    }
}

// ── Comments Routes (Step 8) ──────────────────────────────────────────────────
if ($url === 'comments/create' || $url === 'comments/edit' || $url === 'comments/update' || $url === 'comments/delete'
    || str_starts_with($url, 'comments/')) {

    require_once BASE_PATH . '/app/controllers/CommentController.php';
    $commentCtrl = new CommentController();

    $segments = explode('/', $url);
    $action = $segments[1] ?? '';
    if (isset($segments[2]) && !isset($_GET['id'])) {
        $_GET['id'] = $segments[2];
    }

    if ($action === 'create') {
        $commentCtrl->create();
        exit;
    }

    if ($action === 'edit') {
        $commentCtrl->edit();
        exit;
    }

    if ($action === 'update') {
        $commentCtrl->update();
        exit;
    }

    if ($action === 'delete') {
        $commentCtrl->delete();
        exit;
    }
}

// ── Likes Routes (Step 9) ─────────────────────────────────────────────────────
if ($url === 'likes/toggle' || $url === 'likes/like' || $url === 'likes/unlike' || str_starts_with($url, 'likes/')) {

    require_once BASE_PATH . '/app/controllers/LikeController.php';
    $likeCtrl = new LikeController();

    $segments = explode('/', $url);
    $action = $segments[1] ?? 'toggle';

    if ($action === 'like') {
        $likeCtrl->like();
        exit;
    }

    if ($action === 'unlike') {
        $likeCtrl->unlike();
        exit;
    }

    $likeCtrl->toggle();
    exit;
}

// ── 404 — Unknown Route ───────────────────────────────────────────────────────
http_response_code(404);
$pageTitle = '404 — Page Not Found';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
        <i class="bi bi-exclamation-circle text-danger" style="font-size: 3.5rem;"></i>
        <h1 class="mt-3">404</h1>
        <p class="text-muted">The page you are looking for does not exist.</p>
        <a href="/SocialNetwork/public/" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> Go Home
        </a>
    </div>
</div>
<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
