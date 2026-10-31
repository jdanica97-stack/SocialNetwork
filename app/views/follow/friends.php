<?php
/**
 * app/views/follow/friends.php
 *
 * Friends Page — Displays mutual connections (users who follow each other)
 *
 * Variables passed from FollowController::friends():
 *   - $friends: array (list of mutual friend records)
 *   - $friendsTotalCount: int (total number of mutual friends)
 *   - $searchQuery: string (search keyword if searched)
 *   - $myFollowingIds: int[]
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$currentUserId = (int) $_SESSION['user_id'];
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 760px;">

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

        <!-- ── Main Friends Card ──────────────────────────────────────────────── -->
        <div class="card glass friends-page-card fade-up">
            <div class="card-body p-3 p-md-4">

                <!-- Page Header: Title + Description + Friend Count -->
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h1 class="h4 mb-0 fw-bold" style="color: var(--fg);">Friends</h1>
                            <span class="badge rounded-pill" style="background: var(--surface-input); color: var(--fg); border: 1px solid var(--border); font-size: 0.75rem; font-weight: 600; padding: 4px 10px;">
                                <?= (int)$friendsTotalCount ?> <?= (int)$friendsTotalCount === 1 ? 'friend' : 'friends' ?>
                            </span>
                        </div>
                        <p class="small text-muted mb-0 mt-1">Discover people and manage your connections.</p>
                    </div>

                    <!-- Link to Following / Followers Network -->
                    <div class="d-flex align-items-center gap-2">
                        <a href="/SocialNetwork/public/?url=following" class="btn btn-ghost btn-sm px-3" style="border-radius: var(--radius-pill); font-size: 0.82rem;">
                            Following & Followers
                        </a>
                    </div>
                </div>

                <!-- Friend Search Bar -->
                <form action="/SocialNetwork/public/" method="GET" class="mb-4" id="friendsSearchForm">
                    <input type="hidden" name="url" value="friends">
                    <div class="d-flex align-items-center gap-2">
                        <div class="position-relative flex-grow-1">
                            <input
                                type="search"
                                name="q"
                                id="friendsSearchInput"
                                class="form-control"
                                placeholder="Search friends by name or @username..."
                                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="off"
                                style="border-radius: var(--radius-pill); padding-left: 38px; font-size: 0.86rem; background: var(--surface-input); border-color: var(--border); color: var(--fg);"
                                aria-label="Search friends"
                            >
                            <svg class="position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.5; color: var(--fg-muted);" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>

                        <?php if ($searchQuery !== ''): ?>
                            <a href="/SocialNetwork/public/?url=friends" class="btn btn-ghost btn-sm px-3 flex-shrink-0" style="border-radius: var(--radius-pill);">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Friend Entries List -->
                <?php if (empty($friends)): ?>
                    <div class="p-5 text-center my-3 text-muted">
                        <div class="mb-3 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <?php if ($searchQuery !== ''): ?>
                            <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);">No friends match "<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"</h2>
                            <p class="small text-muted mb-3" style="max-width: 320px; margin-inline: auto;">
                                Try checking for spelling errors or searching with a different name.
                            </p>
                            <a href="/SocialNetwork/public/?url=friends" class="btn btn-primary btn-sm px-4" style="border-radius: var(--radius-pill);">
                                Clear Search
                            </a>
                        <?php else: ?>
                            <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);">No friends yet</h2>
                            <p class="small text-muted mb-4" style="max-width: 340px; margin-inline: auto;">
                                Friends are community members who follow you and whom you follow back. Discover people to start connecting!
                            </p>
                            <a href="/SocialNetwork/public/?url=search" class="btn btn-primary btn-sm px-4" style="border-radius: var(--radius-pill);">
                                Discover People
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="friends-list d-flex flex-column gap-2" id="friendsCardsList">
                        <?php foreach ($friends as $f): ?>
                            <?php
                                $fId = (int) $f['id'];
                                $isFollowed = in_array($fId, $myFollowingIds, true);

                                // Avatar URL
                                if (!empty($f['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $f['profile_image'])) {
                                    $fAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($f['profile_image']);
                                } else {
                                    $fAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($f['full_name']) . '&size=96&background=111113&color=f5f5f7&rounded=true&bold=true';
                                }
                                $profileUrl = '/SocialNetwork/public/?url=profile&id=' . $fId;
                                $messageUrl = '/SocialNetwork/public/?url=messages&user_id=' . $fId;
                            ?>
                            <div class="friend-entry-card p-3 rounded-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 transition-all" data-username="<?= htmlspecialchars(strtolower($f['username']), ENT_QUOTES, 'UTF-8') ?>" data-fullname="<?= htmlspecialchars(strtolower($f['full_name']), ENT_QUOTES, 'UTF-8') ?>" style="background: var(--surface-card); border: 1px solid var(--border);">
                                <!-- Left: Avatar + Details -->
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex-shrink-0" title="View <?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?>'s profile">
                                        <img
                                            src="<?= htmlspecialchars($fAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="rounded-circle"
                                            width="48"
                                            height="48"
                                            style="object-fit: cover; border: 1px solid var(--border);"
                                            loading="lazy"
                                        >
                                    </a>

                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-inline-block text-truncate friend-name" style="color: var(--fg); font-weight: 600; font-size: 0.95rem;">
                                                <?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <!-- Mutual Friend Indicator Badge -->
                                            <span class="badge rounded-pill" style="background: rgba(34, 197, 94, 0.12); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.25); font-size: 0.68rem; padding: 2px 8px; font-weight: 500;">
                                                Friends
                                            </span>
                                        </div>

                                        <span class="friend-username small text-muted text-truncate d-block" style="font-size: 0.78rem;">
                                            @<?= htmlspecialchars($f['username'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                        <?php if (!empty($f['bio'])): ?>
                                            <p class="small text-muted mb-0 mt-1 text-truncate" style="font-size: 0.78rem; max-width: 360px;">
                                                <?= htmlspecialchars($f['bio'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Right: Actions (Send Message & Following/Unfollow) -->
                                <div class="d-flex align-items-center gap-2 flex-shrink-0 align-self-end align-self-sm-center friend-entry-actions">
                                    <!-- Send Message Button -->
                                    <a
                                        href="<?= htmlspecialchars($messageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        class="btn btn-ghost btn-sm px-3 d-inline-flex align-items-center gap-1"
                                        title="Send message to <?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                        aria-label="Send Message to <?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                        style="border-radius: var(--radius-pill); font-size: 0.82rem;"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <span>Message</span>
                                    </a>

                                    <!-- Follow / Unfollow Toggle Button -->
                                    <button
                                        type="button"
                                        class="btn <?= $isFollowed ? 'btn-ghost' : 'btn-primary' ?> btn-sm follow-toggle-btn px-3"
                                        data-user-id="<?= $fId ?>"
                                        data-following="<?= $isFollowed ? '1' : '0' ?>"
                                        style="border-radius: var(--radius-pill); font-size: 0.82rem;"
                                    >
                                        <span class="follow-btn-label"><?= $isFollowed ? 'Following' : 'Follow' ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div id="noClientFilterNotice" class="p-4 text-center text-muted small d-none">
                    No friends match your filter.
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ── Real-Time Filter & AJAX Toggle Script ──────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    // 1. Real-Time Client-Side Filter on typing
    var searchInput = document.getElementById('friendsSearchInput');
    var cardsList   = document.getElementById('friendsCardsList');
    var noMatchEl   = document.getElementById('noClientFilterNotice');

    if (searchInput && cardsList) {
        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var items = cardsList.querySelectorAll('.friend-entry-card');
            var matchCount = 0;

            items.forEach(function (item) {
                var username = item.getAttribute('data-username') || '';
                var fullname = item.getAttribute('data-fullname') || '';
                if (q === '' || username.indexOf(q) !== -1 || fullname.indexOf(q) !== -1) {
                    item.style.display = '';
                    matchCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noMatchEl) {
                if (items.length > 0 && matchCount === 0 && q !== '') {
                    noMatchEl.classList.remove('d-none');
                } else {
                    noMatchEl.classList.add('d-none');
                }
            }
        });
    }

    // 2. Follow / Unfollow AJAX Action
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
            alert('A network error occurred. Please try again.');
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
