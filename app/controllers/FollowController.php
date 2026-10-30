<?php

/**
 * app/controllers/FollowController.php
 *
 * Handles following, unfollowing, and the unified Followers & Following page:
 *   - toggle()    — Toggle follow/unfollow status (POST / AJAX)
 *   - index()     — Unified Followers and Following page with tab switching (GET)
 *   - followers() — Alias/entrypoint for followers tab (GET)
 *   - following() — Alias/entrypoint for following tab (GET)
 *
 * Security:
 *   - Authentication required for all endpoints (requireAuth).
 *   - CSRF protection for mutating actions.
 *   - Acting user is strictly derived from session ($_SESSION['user_id']).
 *   - Cannot follow self.
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/FollowModel.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';

class FollowController
{
    private FollowModel $followModel;
    private UserModel $userModel;
    private NotificationModel $notificationModel;

    public function __construct()
    {
        Csrf::initSession();

        $db = getDBConnection();
        $this->followModel       = new FollowModel($db);
        $this->userModel         = new UserModel($db);
        $this->notificationModel = new NotificationModel($db);
    }

    /**
     * Enforce active user session.
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
                exit;
            }

            $_SESSION['flash_error'] = 'You must be logged in to access that page.';
            header('Location: /SocialNetwork/public/?url=auth/login');
            exit;
        }
    }

    /**
     * Toggle follow/unfollow for a user.
     * Expects POST with user_id and valid CSRF token.
     */
    public function toggle(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                http_response_code(405);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
                exit;
            }
            header('Location: /SocialNetwork/public/?url=followers');
            exit;
        }

        if (!Csrf::check()) {
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh the page.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
            header('Location: /SocialNetwork/public/?url=followers');
            exit;
        }

        $targetUserId = (int) ($_POST['user_id'] ?? 0);

        if ($targetUserId <= 0 || $targetUserId === $currentUserId) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Invalid user selected.']);
                exit;
            }
            $_SESSION['flash_error'] = 'You cannot follow this user.';
            header('Location: /SocialNetwork/public/?url=followers');
            exit;
        }

        $targetUser = $this->userModel->findById($targetUserId);
        if (!$targetUser) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'The selected user does not exist.']);
                exit;
            }
            $_SESSION['flash_error'] = 'User not found.';
            header('Location: /SocialNetwork/public/?url=followers');
            exit;
        }

        $isFollowing = $this->followModel->isFollowing($currentUserId, $targetUserId);

        if ($isFollowing) {
            $this->followModel->unfollow($currentUserId, $targetUserId);
            $newStatus = false;
            $msg = 'Unfollowed @' . $targetUser['username'];
        } else {
            $this->followModel->follow($currentUserId, $targetUserId);
            $newStatus = true;
            $msg = 'Now following @' . $targetUser['username'];

            // Send notification to target user
            $actorName = !empty($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : 'Someone';
            $this->notificationModel->createNotification(
                $targetUserId,
                $currentUserId,
                'follow',
                $currentUserId,
                $actorName . ' started following you.'
            );
        }

        $followerCount  = $this->followModel->getFollowerCount($targetUserId);
        $followingCount = $this->followModel->getFollowingCount($currentUserId);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success'         => true,
                'is_following'    => $newStatus,
                'follower_count'  => $followerCount,
                'following_count' => $followingCount,
                'message'         => $msg
            ]);
            exit;
        }

        $_SESSION['flash_success'] = $msg;
        $referer = $_SERVER['HTTP_REFERER'] ?? ('/SocialNetwork/public/?url=profile&id=' . $targetUserId);
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Display the unified Followers and Following page.
     * Accessible via ?url=followers, ?url=following, or ?url=follow
     */
    public function index(string $defaultTab = 'following'): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $targetUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : $currentUserId;
        if ($targetUserId <= 0) {
            $targetUserId = $currentUserId;
        }

        $profileUser = $this->userModel->findById($targetUserId);
        if (!$profileUser) {
            $_SESSION['flash_error'] = 'User profile not found.';
            header('Location: /SocialNetwork/public/');
            exit;
        }

        $isOwnNetwork = ($targetUserId === $currentUserId);

        // Determine active tab
        $tab = strtolower(trim($_GET['tab'] ?? ''));
        if ($tab !== 'followers' && $tab !== 'following') {
            $tab = $defaultTab;
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Load followers and following lists
        $followers = $this->followModel->getFollowers($targetUserId, 100);
        $following = $this->followModel->getFollowing($targetUserId, 100);

        $followerCount  = count($followers);
        $followingCount = count($following);

        // Current user's following list (to mark Follow / Following buttons properly)
        $myFollowingIds = $this->followModel->getFollowingIds($currentUserId);

        $pageTitle = $isOwnNetwork
            ? ($tab === 'followers' ? 'My Followers' : 'People I Follow') . ' — Mini Social Network'
            : htmlspecialchars($profileUser['full_name']) . ($tab === 'followers' ? "'s Followers" : ' — Following') . ' — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/follow/index.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Entrypoint for followers page.
     */
    public function followers(): void
    {
        $this->index('followers');
    }

    /**
     * Entrypoint for following page.
     */
    public function following(): void
    {
        $this->index('following');
    }

    /**
     * Dedicated Friends page — displays mutual connections.
     */
    public function friends(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $searchQuery = trim($_GET['q'] ?? '');

        // Fetch mutual friends (both users follow each other)
        $friends = $this->followModel->getFriends($currentUserId, 100, $searchQuery);
        $friendsTotalCount = $this->followModel->getFriendsCount($currentUserId);

        // Current user's following list (for button state)
        $myFollowingIds = $this->followModel->getFollowingIds($currentUserId);

        $pageTitle = 'Friends — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/follow/friends.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }
}
