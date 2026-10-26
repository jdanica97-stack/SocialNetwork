<?php

/**
 * app/controllers/NotificationController.php
 *
 * Handles user notifications:
 *   - index()   — Display user's notifications list (GET)
 *   - read()    — Mark a single notification as read (POST)
 *   - readAll() — Mark all notifications as read (POST)
 *   - delete()  — Delete a single notification (POST)
 *
 * Security:
 *   - Strictly enforces session authentication.
 *   - CSRF protection on actions.
 *   - Strict authorization: users can ONLY view/modify their own notifications.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class NotificationController
{
    private NotificationModel $notificationModel;

    public function __construct()
    {
        Csrf::initSession();

        $db = getDBConnection();
        $this->notificationModel = new NotificationModel($db);
    }

    /**
     * Enforce active user session.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to view notifications.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    /**
     * Display all notifications for current user.
     */
    public function index(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $notifications = $this->notificationModel->getUserNotifications($currentUserId, 50);
        $unreadCount   = $this->notificationModel->countUnread($currentUserId);

        $pageTitle = 'Notifications — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/notifications/index.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Mark a single notification as read.
     */
    public function read(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check()) {
            header('Location: /SocialNetwork/public/?url=notifications');
            exit;
        }

        $notificationId = (int) ($_POST['id'] ?? 0);
        if ($notificationId > 0) {
            $this->notificationModel->markAsRead($notificationId, $currentUserId);
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /SocialNetwork/public/?url=notifications');
        exit;
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check()) {
            header('Location: /SocialNetwork/public/?url=notifications');
            exit;
        }

        $this->notificationModel->markAllAsRead($currentUserId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /SocialNetwork/public/?url=notifications');
        exit;
    }

    /**
     * Delete a notification.
     */
    public function delete(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check()) {
            header('Location: /SocialNetwork/public/?url=notifications');
            exit;
        }

        $notificationId = (int) ($_POST['id'] ?? 0);
        if ($notificationId > 0) {
            $this->notificationModel->deleteNotification($notificationId, $currentUserId);
        }

        header('Location: /SocialNetwork/public/?url=notifications');
        exit;
    }
}
