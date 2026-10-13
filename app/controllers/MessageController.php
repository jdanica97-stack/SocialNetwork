<?php

/**
 * app/controllers/MessageController.php
 *
 * Handles all direct messaging actions:
 *   - index() — List all conversations with preview & unread indicators (GET)
 *   - chat()  — View conversation with a specific user (GET)
 *   - send()  — Send message via standard POST or AJAX/fetch JSON (POST)
 *   - poll()  — Fetch latest messages for real-time chat polling (GET -> JSON)
 *
 * Security:
 *   - Authentication required: users must be logged in.
 *   - User ID is strictly obtained from $_SESSION['user_id'].
 *   - CSRF check enforced on send action.
 *   - Output escaping via htmlspecialchars() in views.
 *   - Input validated: receiver existence, non-empty text, max length 2000 chars.
 *   - Prevents messaging yourself.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/MessageModel.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class MessageController
{
    private MessageModel $messageModel;
    private UserModel $userModel;

    public function __construct()
    {
        Csrf::initSession();

        $db = getDBConnection();
        $this->messageModel = new MessageModel($db);
        $this->userModel    = new UserModel($db);
    }

    /**
     * Enforce authentication for message operations.
     */
    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Please log in to use messages.']);
                exit;
            }
            $_SESSION['flash_error'] = 'You must be logged in to view messages.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }

        return (int) $_SESSION['user_id'];
    }

    /**
     * Check if request is AJAX/fetch JSON.
     */
    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));
    }

    /**
     * Send standard JSON response.
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Display conversations inbox.
     * Route: GET /SocialNetwork/public/?url=messages
     */
    public function index(): void
    {
        $currentUserId = $this->requireAuth();

        $conversations = $this->messageModel->getConversations($currentUserId);
        $allUsers      = $this->userModel->getAllUsers(100);

        // Filter out current user from new conversation user list
        $contactList = array_filter($allUsers, fn($u) => (int)$u['id'] !== $currentUserId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $pageTitle = 'Messages — Orbit';
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/messages/inbox.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * View conversation chat with a specific user.
     * Route: GET /SocialNetwork/public/?url=messages/chat&user_id={id}
     */
    public function chat(): void
    {
        $currentUserId = $this->requireAuth();

        $otherUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT)
            ?: (isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null);

        if (!$otherUserId || $otherUserId <= 0) {
            $_SESSION['flash_error'] = 'Invalid conversation participant.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        if ($otherUserId === $currentUserId) {
            $_SESSION['flash_error'] = 'You cannot start a conversation with yourself.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        $otherUser = $this->userModel->findById($otherUserId);
        if (!$otherUser) {
            http_response_code(404);
            $_SESSION['flash_error'] = 'The user you are trying to message does not exist.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        // Mark incoming messages as read
        $this->messageModel->markAsRead($currentUserId, $otherUserId);

        // Fetch conversation messages
        $messages = $this->messageModel->getConversationMessages($currentUserId, $otherUserId);
        $conversations = $this->messageModel->getConversations($currentUserId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $pageTitle = 'Chat with ' . htmlspecialchars($otherUser['full_name']) . ' — Orbit';
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/messages/chat.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Send message.
     * Route: POST /SocialNetwork/public/?url=messages/send
     */
    public function send(): void
    {
        $currentUserId = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->isAjax()
                ? $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405)
                : header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        if (!Csrf::check()) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Security token expired. Please refresh the page.'], 403);
            }
            $_SESSION['flash_error'] = 'Security verification failed. Please try again.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        $receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT)
            ?: (isset($_POST['receiver_id']) && is_numeric($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : null);

        $text = trim($_POST['message'] ?? '');

        if (!$receiverId || $receiverId <= 0) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid recipient.'], 400);
            }
            $_SESSION['flash_error'] = 'Invalid recipient.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        if ($receiverId === $currentUserId) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'You cannot send messages to yourself.'], 400);
            }
            $_SESSION['flash_error'] = 'You cannot send messages to yourself.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        // Verify receiver exists
        $receiver = $this->userModel->findById($receiverId);
        if (!$receiver) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Recipient user not found.'], 404);
            }
            $_SESSION['flash_error'] = 'Recipient user not found.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        // Validation
        if ($text === '') {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Message cannot be empty.'], 400);
            }
            $_SESSION['flash_error'] = 'Message cannot be empty.';
            header('Location: /SocialNetwork/public/?url=messages/chat&user_id=' . $receiverId);
            exit;
        }

        if (mb_strlen($text) > 2000) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Message exceeds 2,000 characters limit.'], 400);
            }
            $_SESSION['flash_error'] = 'Message exceeds 2,000 characters limit.';
            header('Location: /SocialNetwork/public/?url=messages/chat&user_id=' . $receiverId);
            exit;
        }

        $newMsgId = $this->messageModel->sendMessage($currentUserId, $receiverId, $text);

        if ($newMsgId) {
            if ($this->isAjax()) {
                $this->jsonResponse([
                    'success'    => true,
                    'message_id' => $newMsgId,
                    'message'    => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                    'created_at' => date('g:i A'),
                    'sender_id'  => $currentUserId,
                ]);
            }
            header('Location: /SocialNetwork/public/?url=messages/chat&user_id=' . $receiverId);
            exit;
        }

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => false, 'message' => 'Could not send message due to a server error.'], 500);
        }
        $_SESSION['flash_error'] = 'Failed to send message. Please try again.';
        header('Location: /SocialNetwork/public/?url=messages/chat&user_id=' . $receiverId);
        exit;
    }

    /**
     * Poll latest messages for real-time chat updates.
     * Route: GET /SocialNetwork/public/?url=messages/poll&user_id={other_user_id}
     */
    public function poll(): void
    {
        $currentUserId = $this->requireAuth();

        $otherUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT)
            ?: (isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int) $_GET['user_id'] : null);

        if (!$otherUserId || $otherUserId <= 0) {
            $this->jsonResponse(['success' => false, 'messages' => []], 400);
        }

        // Mark incoming messages as read
        $this->messageModel->markAsRead($currentUserId, $otherUserId);

        $messages = $this->messageModel->getConversationMessages($currentUserId, $otherUserId);

        // Sanitize output for JSON response
        $formatted = array_map(function ($m) use ($currentUserId) {
            return [
                'id'          => (int) $m['id'],
                'sender_id'   => (int) $m['sender_id'],
                'is_mine'     => ((int) $m['sender_id'] === $currentUserId),
                'message'     => htmlspecialchars($m['message'], ENT_QUOTES, 'UTF-8'),
                'created_at'  => date('g:i A', strtotime($m['created_at'])),
                'is_read'     => (bool) $m['is_read'],
            ];
        }, $messages);

        $this->jsonResponse([
            'success'  => true,
            'messages' => $formatted,
        ]);
    }
}
