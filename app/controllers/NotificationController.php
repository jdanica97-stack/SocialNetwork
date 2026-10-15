<?php

/**
 * app/controllers/NotificationController.php
 *
 * Handles notification-related HTTP actions:
 *   - index()      — Full notification page (GET)
 *   - count()      — JSON unread count poll endpoint (GET → JSON)
 *   - fetch()      — JSON list of recent notifications (GET → JSON)
 *   - markRead()   — Mark one notification read (POST → JSON)
 *   - markAllRead()— Mark all notifications read (POST → JSON)
 *
 * Security:
 *   - Authentication required for all endpoints.
 *   - Users can only read/mark their own notifications.
 *   - CSRF required for state-changing (mark) operations.
 *   - No notification data exposed to unauthenticated callers.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class NotificationController
{
    private NotificationModel $notifModel;

    public function __construct()
    {
        Csrf::initSession();
        $db = getDBConnection();
        $this->notifModel = new NotificationModel($db);
    }

    // ─── Auth Guard ──────────────────────────────────────────────────────────

    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Please log in.'], 401);
            }
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    // ─── Routes ──────────────────────────────────────────────────────────────

    /**
     * Full notifications page.
     * Route: GET ?url=notifications
     */
    public function index(): void
    {
        $userId = $this->requireAuth();
        $notifications = $this->notifModel->getForUser($userId, 50);

        $pageTitle = 'Notifications — Orbit';
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/notifications/index.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * JSON: unread count only (lightweight — called by header polling).
     * Route: GET ?url=notifications/count
     */
    public function count(): void
    {
        $userId = $this->requireAuth();
        $unread = $this->notifModel->countUnread($userId);
        $this->jsonResponse(['success' => true, 'unread' => $unread]);
    }

    /**
     * JSON: list of recent notifications (for dropdown panel).
     * Route: GET ?url=notifications/fetch
     */
    public function fetch(): void
    {
        $userId = $this->requireAuth();
        $notifs = $this->notifModel->getForUser($userId, 20);

        $formatted = array_map(function ($n) {
            return [
                'id'                  => (int)    $n['id'],
                'type'                => $n['type'],
                'target_type'         => $n['target_type'],
                'target_id'           => (int)    $n['target_id'],
                'preview'             => $n['preview'] ? htmlspecialchars($n['preview'], ENT_QUOTES, 'UTF-8') : null,
                'is_read'             => (bool)   $n['is_read'],
                'created_at'          => $n['created_at'],
                'time_label'          => $this->timeLabel($n['created_at']),
                'actor_id'            => (int)    $n['actor_id'],
                'actor_username'      => htmlspecialchars($n['actor_username'],  ENT_QUOTES, 'UTF-8'),
                'actor_full_name'     => htmlspecialchars($n['actor_full_name'], ENT_QUOTES, 'UTF-8'),
                'actor_profile_image' => $n['actor_profile_image'] ?? null,
                'link'                => $this->buildLink($n),
                'description'         => $this->buildDescription($n),
            ];
        }, $notifs);

        $this->jsonResponse([
            'success'       => true,
            'notifications' => $formatted,
            'unread'        => $this->notifModel->countUnread($userId),
        ]);
    }

    /**
     * Mark one notification as read.
     * Route: POST ?url=notifications/mark-read
     */
    public function markRead(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        if (!Csrf::check()) {
            $this->jsonResponse(['success' => false, 'message' => 'Security token expired.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid notification ID.'], 400);
        }

        $ok = $this->notifModel->markRead($id, $userId);
        $this->jsonResponse([
            'success' => $ok,
            'unread'  => $this->notifModel->countUnread($userId),
        ]);
    }

    /**
     * Mark all notifications as read.
     * Route: POST ?url=notifications/mark-all-read
     */
    public function markAllRead(): void
    {
        $userId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        if (!Csrf::check()) {
            $this->jsonResponse(['success' => false, 'message' => 'Security token expired.'], 403);
        }

        $ok = $this->notifModel->markAllRead($userId);
        $this->jsonResponse([
            'success' => $ok,
            'unread'  => 0,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Build a human-readable notification description.
     */
    private function buildDescription(array $n): string
    {
        $actor = htmlspecialchars($n['actor_full_name'], ENT_QUOTES, 'UTF-8');
        return match ($n['type']) {
            'message'      => "$actor sent you a message.",
            'comment'      => "$actor commented on your post.",
            'like_post'    => "$actor liked your post.",
            'like_comment' => "$actor liked your comment.",
            default        => "$actor interacted with your content.",
        };
    }

    /**
     * Build the link URL the notification should navigate to.
     */
    private function buildLink(array $n): string
    {
        $base = '/SocialNetwork/public/?url=';
        return match ($n['type']) {
            'message'      => $base . 'messages/chat&user_id=' . (int)$n['actor_id'],
            'comment'      => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
            'like_post'    => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
            'like_comment' => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
            default        => '/SocialNetwork/public/',
        };
    }

    /**
     * Convert a MySQL timestamp to a short relative label.
     */
    private function timeLabel(string $createdAt): string
    {
        $diff = time() - strtotime($createdAt);
        if ($diff < 60)    return 'just now';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j', strtotime($createdAt));
    }
}
