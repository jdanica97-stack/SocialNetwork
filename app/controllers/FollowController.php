<?php

/**
 * app/controllers/FollowController.php
 *
 * Handles following, unfollowing, and the Friends / Social Discovery pages:
 *   - toggle()    — Toggle follow/unfollow status (POST / AJAX)
 *   - friends()   — Main Friends hub: Discover, Mutual Friends, Following, Followers (GET)
 *   - followers() — List of followers for a user (GET)
 *   - following() — List of users a user is following (GET)
 *
 * Security:
 *   - Authentication required for all endpoints.
 *   - CSRF protection for mutating actions.
 *   - Prevents following oneself.
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
            header('Location: /SocialNetwork/public/?url=friends');
            exit;
        }

        if (!Csrf::check()) {
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
            header('Location: /SocialNetwork/public/?url=friends');
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
            header('Location: /SocialNetwork/public/?url=friends');
            exit;
        }

        $targetUser = $this->userModel->findById($targetUserId);
        if (!$targetUser) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'User not found.']);
                exit;
            }
            $_SESSION['flash_error'] = 'User not found.';
            header('Location: /SocialNetwork/public/?url=friends');
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
     * Display the Friends & People discovery hub.
     */
    public function friends(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $tab = trim($_GET['tab'] ?? 'discover');
        $validTabs = ['discover', 'friends', 'following', 'followers'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'discover';
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Data based on active tab or overall summary
        $mutualFriends = $this->followModel->getFriends($currentUserId);
        $suggestions   = $this->followModel->getSuggestions($currentUserId, 20);
        $followingList = $this->followModel->getFollowing($currentUserId, 50);
        $followersList = $this->followModel->getFollowers($currentUserId, 50);

        $friendsCount   = count($mutualFriends);
        $followingCount = $this->followModel->getFollowingCount($currentUserId);
        $followersCount = $this->followModel->getFollowerCount($currentUserId);
        $suggestCount   = count($suggestions);

        $followingIds = $this->followModel->getFollowingIds($currentUserId);

        $pageTitle = 'Friends & People — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/follow/friends.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Display followers list for a user.
     */
    public function followers(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $targetUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : $currentUserId;
        $profileUser  = $this->userModel->findById($targetUserId);

        if (!$profileUser) {
            $_SESSION['flash_error'] = 'User profile not found.';
            header('Location: /SocialNetwork/public/?url=friends');
            exit;
        }

        $users = $this->followModel->getFollowers($targetUserId, 50);
        $followingIds = $this->followModel->getFollowingIds($currentUserId);
        $pageMode = 'followers';

        $pageTitle = ($targetUserId === $currentUserId ? 'My Followers' : htmlspecialchars($profileUser['full_name']) . "'s Followers") . ' — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/follow/list.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Display following list for a user.
     */
    public function following(): void
    {
        $this->requireAuth();
        $currentUserId = (int) $_SESSION['user_id'];

        $targetUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : $currentUserId;
        $profileUser  = $this->userModel->findById($targetUserId);

        if (!$profileUser) {
            $_SESSION['flash_error'] = 'User profile not found.';
            header('Location: /SocialNetwork/public/?url=friends');
            exit;
        }

        $users = $this->followModel->getFollowing($targetUserId, 50);
        $followingIds = $this->followModel->getFollowingIds($currentUserId);
        $pageMode = 'following';

        $pageTitle = ($targetUserId === $currentUserId ? 'People I Follow' : 'People ' . htmlspecialchars($profileUser['full_name']) . ' Follows') . ' — Mini Social Network';

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/follow/list.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }
}
