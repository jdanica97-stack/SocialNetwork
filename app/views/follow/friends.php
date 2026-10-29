<?php
/**
 * app/views/follow/friends.php
 *
 * Friends and People Discovery Page
 *
 * Variables passed from FollowController::friends():
 *   - $tab: string ('discover', 'friends', 'following', 'followers')
 *   - $mutualFriends: array
 *   - $suggestions: array
 *   - $followingList: array
 *   - $followersList: array
 *   - $friendsCount: int
 *   - $followingCount: int
 *   - $followersCount: int
 *   - $suggestCount: int
 *   - $followingIds: int[]
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$currentUserId = (int) $_SESSION['user_id'];

// Helper to determine list by active tab
switch ($tab) {
    case 'friends':
        $activeList = $mutualFriends;
        $emptyTitle = 'No mutual friends yet';
        $emptyDesc  = 'When you follow someone and they follow you back, they will appear here as your friend.';
        break;
    case 'following':
        $activeList = $followingList;
        $emptyTitle = 'You are not following anyone yet';
        $emptyDesc  = 'Explore suggestions or search for creators and peers to stay updated with their posts.';
        break;
    case 'followers':
        $activeList = $followersList;
        $emptyTitle = 'No followers yet';
        $emptyDesc  = 'Post interesting content and connect with the community to grow your audience!';
        break;
    case 'discover':
    default:
        $activeList = $suggestions;
        $emptyTitle = 'No suggestions found';
        $emptyDesc  = 'You are already connected with everyone available or no new profiles exist right now.';
        break;
}
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 860px;">

        <!-- ── Flash Messages ────────────────────────────────────────────────── -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success fade-up visible mb-3" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger fade-up visible mb-3" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Main Card ─────────────────────────────────────────────────────── -->
        <div class="card glass friends-card fade-up">
            <div class="card-body p-3 p-md-4">

                <!-- Header Banner -->
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--surface-input); display: flex; align-items: center; justify-content: center; color: var(--fg); flex-shrink: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <h1 class="h5 mb-1 fw-bold" style="color: var(--fg);">Friends & Network</h1>
                            <p class="small text-muted mb-0">Discover members, follow creators, and manage your social circle.</p>
                        </div>
                    </div>

                    <a href="/SocialNetwork/public/?url=search" class="btn btn-ghost btn-sm px-3 align-self-start align-self-sm-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Find People
                    </a>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-pills gap-2 mb-4 p-1 rounded-3" style="background: var(--surface-input); border: 1px solid var(--border);">
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center py-2 px-3 fw-medium <?= $tab === 'discover' ? 'active' : '' ?>" href="/SocialNetwork/public/?url=friends&tab=discover" style="<?= $tab === 'discover' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            Discover
                            <?php if ($suggestCount > 0): ?>
                                <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.7rem;"><?= $suggestCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center py-2 px-3 fw-medium <?= $tab === 'friends' ? 'active' : '' ?>" href="/SocialNetwork/public/?url=friends&tab=friends" style="<?= $tab === 'friends' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                            Friends
                            <?php if ($friendsCount > 0): ?>
                                <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.7rem;"><?= $friendsCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center py-2 px-3 fw-medium <?= $tab === 'following' ? 'active' : '' ?>" href="/SocialNetwork/public/?url=friends&tab=following" style="<?= $tab === 'following' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>">
                            Following
                            <span class="badge rounded-pill ms-1" style="background: var(--surface); color: var(--fg); font-size: 0.7rem; border: 1px solid var(--border);"><?= $followingCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center py-2 px-3 fw-medium <?= $tab === 'followers' ? 'active' : '' ?>" href="/SocialNetwork/public/?url=friends&tab=followers" style="<?= $tab === 'followers' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>">
                            Followers
                            <span class="badge rounded-pill ms-1" style="background: var(--surface); color: var(--fg); font-size: 0.7rem; border: 1px solid var(--border);"><?= $followersCount ?></span>
                        </a>
                    </li>
                </ul>

                <!-- People List -->
                <?php if (empty($activeList)): ?>
                    <div class="p-5 text-center my-4 text-muted">
                        <div class="mb-3 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);"><?= htmlspecialchars($emptyTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="small text-muted mb-4" style="max-width: 360px; margin-inline: auto;">
                            <?= htmlspecialchars($emptyDesc, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php if ($tab !== 'discover'): ?>
                            <a href="/SocialNetwork/public/?url=friends&tab=discover" class="btn btn-primary btn-sm px-4">
                                Discover Members
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($activeList as $u): ?>
                            <?php
                                $uId = (int) $u['id'];
                                $isFollowed = in_array($uId, $followingIds, true);
                                $isSelf = ($uId === $currentUserId);

                                // Avatar URL
                                if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $u['profile_image'])) {
                                    $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                } else {
                                    $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=96&background=111113&color=f5f5f7&rounded=true&bold=true';
                                }
                            ?>
                            <div class="col-12 col-sm-6">
                                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between transition-all" style="background: var(--surface-card); border: 1px solid var(--border);">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <!-- Avatar -->
                                        <a href="/SocialNetwork/public/?url=profile&id=<?= $uId ?>" class="flex-shrink-0 text-decoration-none">
                                            <img
                                                src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                class="rounded-circle"
                                                width="48"
                                                height="48"
                                                style="object-fit: cover; border: 1px solid var(--border);"
                                            >
                                        </a>

                                        <!-- Name & Bio -->
                                        <div class="min-w-0 flex-grow-1">
                                            <a href="/SocialNetwork/public/?url=profile&id=<?= $uId ?>" class="text-decoration-none d-block">
                                                <div class="fw-semibold text-truncate" style="color: var(--fg); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <div class="small text-muted text-truncate" style="font-size: 0.8rem;">
                                                    @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </a>

                                            <?php if (!empty($u['bio'])): ?>
                                                <p class="small text-muted mb-0 mt-2 text-truncate-2" style="font-size: 0.8rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                    <?= htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (isset($u['followers_count'])): ?>
                                                <div class="small mt-2" style="color: var(--fg-faint); font-size: 0.74rem;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                    <?= (int)$u['followers_count'] ?> <?= (int)$u['followers_count'] === 1 ? 'follower' : 'followers' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Actions Row -->
                                    <div class="d-flex align-items-center gap-2 pt-2 border-top" style="border-color: var(--border) !important;">
                                        <?php if (!$isSelf): ?>
                                            <button
                                                type="button"
                                                class="btn <?= $isFollowed ? 'btn-ghost' : 'btn-primary' ?> btn-sm flex-grow-1 follow-toggle-btn"
                                                data-user-id="<?= $uId ?>"
                                                data-following="<?= $isFollowed ? '1' : '0' ?>"
                                                style="border-radius: var(--radius-pill); font-size: 0.82rem;"
                                            >
                                                <span class="follow-btn-label"><?= $isFollowed ? 'Following' : 'Follow' ?></span>
                                            </button>

                                            <a
                                                href="/SocialNetwork/public/?url=messages&user_id=<?= $uId ?>"
                                                class="btn btn-ghost btn-sm px-2"
                                                title="Send direct message"
                                                aria-label="Message"
                                                style="border-radius: var(--radius-pill);"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            </a>
                                        <?php else: ?>
                                            <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost btn-sm w-100" style="border-radius: var(--radius-pill); font-size: 0.82rem;">
                                                View Your Profile
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- ── Follow Toggle JavaScript (Shared Helper) ────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    function handleFollowToggle(btn) {
        var userId = btn.getAttribute('data-user-id');
        if (!userId) return;

        btn.disabled = true;
        var isFollowing = btn.getAttribute('data-following') === '1';

        var formData = new FormData();
        formData.append('user_id', userId);
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        fetch('/SocialNetwork/public/?url=follow/toggle', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btn.disabled = false;
            if (data.success) {
                var newStatus = data.is_following;
                btn.setAttribute('data-following', newStatus ? '1' : '0');

                var label = btn.querySelector('.follow-btn-label');
                if (label) {
                    label.textContent = newStatus ? 'Following' : 'Follow';
                } else {
                    btn.textContent = newStatus ? 'Following' : 'Follow';
                }

                if (newStatus) {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-ghost');
                } else {
                    btn.classList.remove('btn-ghost');
                    btn.classList.add('btn-primary');
                }
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(function () {
            btn.disabled = false;
            alert('An error occurred. Please try again.');
        });
    }

    document.querySelectorAll('.follow-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            handleFollowToggle(this);
        });
    });
});
</script>
