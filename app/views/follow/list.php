<?php
/**
 * app/views/follow/list.php
 *
 * Dedicated followers or following listing page for a user profile.
 *
 * Variables passed from FollowController::followers() or FollowController::following():
 *   - $pageMode: 'followers' | 'following'
 *   - $profileUser: array (user whose network is being inspected)
 *   - $users: array of user records
 *   - $followingIds: int[]
 *   - $currentUserId: int
 */

$isFollowersMode = ($pageMode === 'followers');
$title = $isFollowersMode 
    ? ($profileUser['id'] === $currentUserId ? 'My Followers' : htmlspecialchars($profileUser['full_name']) . "'s Followers")
    : ($profileUser['id'] === $currentUserId ? 'People I Follow' : 'People ' . htmlspecialchars($profileUser['full_name']) . ' Follows');

$emptyText = $isFollowersMode
    ? ($profileUser['id'] === $currentUserId ? "You don't have any followers yet." : htmlspecialchars($profileUser['full_name']) . " doesn't have any followers yet.")
    : ($profileUser['id'] === $currentUserId ? "You aren't following anyone yet." : htmlspecialchars($profileUser['full_name']) . " isn't following anyone yet.");
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 720px;">

        <!-- ── Main Card ─────────────────────────────────────────────────────── -->
        <div class="card glass follow-list-card fade-up">
            <div class="card-body p-3 p-md-4">

                <!-- Header with Back Button -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$profileUser['id'] ?>" class="btn btn-ghost btn-sm px-2 py-1" aria-label="Back to profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                        <div>
                            <h1 class="h5 mb-0 fw-bold" style="color: var(--fg);"><?= $title ?></h1>
                            <span class="small text-muted">
                                <?= count($users) ?> <?= count($users) === 1 ? 'person' : 'people' ?>
                            </span>
                        </div>
                    </div>

                    <a href="/SocialNetwork/public/?url=friends" class="btn btn-ghost btn-sm px-3" style="font-size: 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Friends Hub
                    </a>
                </div>

                <!-- Users List -->
                <?php if (empty($users)): ?>
                    <div class="p-5 text-center my-4 text-muted">
                        <div class="mb-3 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);"><?= $emptyText ?></h2>
                        <a href="/SocialNetwork/public/?url=friends" class="btn btn-primary btn-sm px-4 mt-3">
                            Discover People
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($users as $u): ?>
                            <?php
                                $uId = (int) $u['id'];
                                $isFollowed = in_array($uId, $followingIds, true);
                                $isSelf = ($uId === $currentUserId);

                                if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $u['profile_image'])) {
                                    $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                } else {
                                    $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                }
                            ?>
                            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between gap-3 transition-all" style="background: var(--surface-card); border: 1px solid var(--border);">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <a href="/SocialNetwork/public/?url=profile&id=<?= $uId ?>" class="flex-shrink-0 text-decoration-none">
                                        <img src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="44" height="44" style="object-fit: cover; border: 1px solid var(--border);">
                                    </a>
                                    <div class="min-w-0">
                                        <a href="/SocialNetwork/public/?url=profile&id=<?= $uId ?>" class="text-decoration-none d-block">
                                            <div class="fw-semibold text-truncate" style="color: var(--fg); font-size: 0.92rem;">
                                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="small text-muted text-truncate" style="font-size: 0.78rem;">
                                                @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </a>
                                        <?php if (!empty($u['bio'])): ?>
                                            <div class="small text-muted text-truncate mt-1" style="font-size: 0.78rem; max-width: 320px;">
                                                <?= htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <?php if (!$isSelf): ?>
                                        <button
                                            type="button"
                                            class="btn <?= $isFollowed ? 'btn-ghost' : 'btn-primary' ?> btn-sm follow-toggle-btn px-3"
                                            data-user-id="<?= $uId ?>"
                                            data-following="<?= $isFollowed ? '1' : '0' ?>"
                                            style="border-radius: var(--radius-pill); font-size: 0.8rem;"
                                        >
                                            <span class="follow-btn-label"><?= $isFollowed ? 'Following' : 'Follow' ?></span>
                                        </button>
                                        <a href="/SocialNetwork/public/?url=messages&user_id=<?= $uId ?>" class="btn btn-ghost btn-sm px-2" title="Send message" style="border-radius: var(--radius-pill);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge rounded-pill" style="background: var(--surface-input); color: var(--fg-muted); border: 1px solid var(--border);">You</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    function handleFollowToggle(btn) {
        var userId = btn.getAttribute('data-user-id');
        if (!userId) return;

        btn.disabled = true;

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
