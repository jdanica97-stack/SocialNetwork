<?php

/**
 * app/controllers/MessageController.php
 *
 * Handles direct messaging functionality:
 *   - index() — Display conversations and message thread (GET)
 *   - send()  — Send a new direct message and create recipient notification (POST)
 *
 * Security:
 *   - Strictly enforces session authentication.
 *   - CSRF protection on message submission.
 *   - Users can only view messages where they are the sender or recipient.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/MessageModel.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class MessageController
{
    private MessageModel $messageModel;
    private NotificationModel $notificationModel;
    private UserModel $userModel;

    public function __construct()
    {
        Csrf::initSession();

        $db = getDBConnection();
        $this->messageModel      = new MessageModel($db);
        $this->notificationModel = new NotificationModel($db);
        $this->userModel         = new UserModel($db);
    }

    /**
     * Enforce active user session.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to access messages.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    /**
     * Display conversations and active chat thread.
     */
    public function index(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Load all active conversation threads
        $conversations = $this->messageModel->getConversations($currentUserId);

        // Determine selected conversation partner
        $selectedUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

        // Prevent chatting with self
        if ($selectedUserId === $currentUserId) {
            $selectedUserId = null;
        }

        $activePartner = null;
        if ($selectedUserId !== null) {
            $activePartner = $this->userModel->findById($selectedUserId);
            // If user doesn't exist, clear selection
            if (!$activePartner) {
                $selectedUserId = null;
            }
        } elseif (!empty($conversations)) {
            // Default to the most recent conversation partner
            $selectedUserId = (int) $conversations[0]['user_id'];
            $activePartner  = $this->userModel->findById($selectedUserId);
        }

        // If an active conversation is open, mark incoming messages as read and fetch history
        $messages = [];
        if ($selectedUserId !== null && $activePartner !== null) {
            $this->messageModel->markThreadAsRead($currentUserId, $selectedUserId);
            $messages = $this->messageModel->getThread($currentUserId, $selectedUserId);
        }

        // Load available users for starting a new conversation
        $availableUsers = $this->messageModel->getAvailableUsers($currentUserId);

        $pageTitle = 'Messages — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/messages/index.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Process sending a new direct message.
     * Supports both standard POST requests and modern AJAX/JSON requests.
     */
    public function send(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(405);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        if (!Csrf::check()) {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh the page.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Invalid or expired security token. Please try again.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        $recipientId = (int) ($_POST['recipient_id'] ?? 0);
        $messageText = trim($_POST['message'] ?? '');

        // Validation
        if ($recipientId <= 0 || $recipientId === $currentUserId) {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Invalid message recipient.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Invalid message recipient.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        $recipient = $this->userModel->findById($recipientId);
        if (!$recipient) {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(404);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'The selected recipient does not exist.']);
                exit;
            }
            $_SESSION['flash_error'] = 'The selected recipient does not exist.';
            header('Location: /SocialNetwork/public/?url=messages');
            exit;
        }

        if ($messageText === '') {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Message cannot be empty.';
            header('Location: /SocialNetwork/public/?url=messages&user_id=' . $recipientId);
            exit;
        }

        if (mb_strlen($messageText) > 5000) {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Message cannot exceed 5,000 characters.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Message cannot exceed 5,000 characters.';
            header('Location: /SocialNetwork/public/?url=messages&user_id=' . $recipientId);
            exit;
        }

        // Store message
        $messageId = $this->messageModel->sendMessage($currentUserId, $recipientId, $messageText);

        if ($messageId) {
            // Generate notification for recipient
            $senderName = !empty($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : 'Someone';
            $this->notificationModel->createNotification(
                $recipientId,
                $currentUserId,
                'message',
                $currentUserId, // target_id points to sender so clicking notification opens thread with sender
                $senderName . ' sent you a message.'
            );

            if ($isAjax) {
                $msgData = $this->messageModel->getMessageById($messageId);
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode([
                    'success' => true,
                    'message' => [
                        'id'             => (int) $messageId,
                        'sender_id'      => $currentUserId,
                        'recipient_id'   => $recipientId,
                        'message'        => $messageText,
                        'created_at'     => $msgData['created_at'] ?? date('Y-m-d H:i:s'),
                        'formatted_time' => date('g:i A · M j', strtotime($msgData['created_at'] ?? 'now')),
                        'is_read'        => 0,
                    ]
                ]);
                exit;
            }
        } else {
            if ($isAjax) {
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['success' => false, 'message' => 'Failed to send message due to a server error. Please try again.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Failed to send message due to a server error. Please try again.';
        }

        header('Location: /SocialNetwork/public/?url=messages&user_id=' . $recipientId);
        exit;
    }

    /**
     * Poll/fetch new incoming messages for the active conversation thread.
     */
    public function fetch(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $partnerId = isset($_GET['partner_id']) ? (int) $_GET['partner_id'] : 0;
        $lastId    = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if ($partnerId <= 0 || $partnerId === $currentUserId) {
            echo json_encode(['success' => false, 'messages' => []]);
            exit;
        }

        $newMessages = $this->messageModel->getNewMessages($currentUserId, $partnerId, $lastId);
        if (!empty($newMessages)) {
            $this->messageModel->markThreadAsRead($currentUserId, $partnerId);
            foreach ($newMessages as &$msg) {
                $msg['formatted_time'] = date('g:i A · M j', strtotime($msg['created_at']));
            }
            unset($msg);
        }

        echo json_encode(['success' => true, 'messages' => $newMessages]);
        exit;
    }
}
