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
 *   profile                 → ProfileController::showProfile()   [GET]
 *   profile/edit            → ProfileController::showEdit()      [GET]
 *   profile/update          → ProfileController::update()        [POST]
 *   profile/username        → ProfileController::updateUsername() [POST]
 *   profile/password        → ProfileController::updatePassword() [POST]
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

// Global unhandled exception handler to prevent leaking sensitive credentials/stack traces
set_exception_handler(function (\Throwable $e) {
    error_log('[UNCAUGHT EXCEPTION] ' . $e->getMessage() . "\n" . $e->getTraceAsString());

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

    http_response_code(500);

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'An unexpected server error occurred. Please try again later.'
        ]);
        exit;
    }

    $pageTitle = '500 — Server Error';
    if (file_exists(BASE_PATH . '/app/views/layouts/header.php')) {
        require_once BASE_PATH . '/app/views/layouts/header.php';
    }
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6 text-center py-5">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3.5rem;"></i>
            <h1 class="mt-3">500</h1>
            <p class="text-muted">An unexpected server error occurred. Our team has been notified.</p>
            <a href="/SocialNetwork/public/" class="btn btn-primary">
                <i class="bi bi-house me-1"></i> Go Home
            </a>
        </div>
    </div>
    <?php
    if (file_exists(BASE_PATH . '/app/views/layouts/footer.php')) {
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }
    exit;
});

// Load CSRF protection helper and initialize session with secure cookie parameters
require_once BASE_PATH . '/app/helpers/Csrf.php';
Csrf::initSession();

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
if ($url === 'profile' || $url === 'profile/edit' || $url === 'profile/update'
    || $url === 'profile/account' || $url === 'profile/username' || $url === 'profile/password') {
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

    if ($url === 'profile/account') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile->updateAccount();
        } else {
            header('Location: /SocialNetwork/public/?url=profile');
        }
        exit;
    }

    if ($url === 'profile/username') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile->updateUsername();
        } else {
            header('Location: /SocialNetwork/public/?url=profile');
        }
        exit;
    }

    if ($url === 'profile/password') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile->updatePassword();
        } else {
            header('Location: /SocialNetwork/public/?url=profile');
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

    if ($action === 'fetch') {
        $commentCtrl->fetch();
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

// ── Messages Routes ───────────────────────────────────────────────────────────
if ($url === 'messages' || str_starts_with($url, 'messages/')) {
    require_once BASE_PATH . '/app/controllers/MessageController.php';
    $msgCtrl = new MessageController();

    if ($url === 'messages/send') {
        $msgCtrl->send();
        exit;
    }

    if ($url === 'messages/fetch') {
        $msgCtrl->fetch();
        exit;
    }

    $msgCtrl->index();
    exit;
}

// ── Notifications Routes ──────────────────────────────────────────────────────
if ($url === 'notifications' || str_starts_with($url, 'notifications/')) {
    require_once BASE_PATH . '/app/controllers/NotificationController.php';
    $notifCtrl = new NotificationController();

    if ($url === 'notifications/read') {
        $notifCtrl->read();
        exit;
    }

    if ($url === 'notifications/read-all') {
        $notifCtrl->readAll();
        exit;
    }

    if ($url === 'notifications/delete') {
        $notifCtrl->delete();
        exit;
    }

    $notifCtrl->index();
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
