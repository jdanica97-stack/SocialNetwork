<?php
/**
 * app/views/follow/index.php
 *
 * Unified Followers and Following Page
 *
 * Variables passed from FollowController::index():
 *   - $profileUser: array (user whose followers/following are being viewed)
 *   - $isOwnNetwork: bool (true if viewing authenticated user's own network)
 *   - $tab: string ('followers' or 'following')
 *   - $followers: array (list of user records following $profileUser)
 *   - $following: array (list of user records that $profileUser follows)
 *   - $followerCount: int
 *   - $followingCount: int
 *   - $myFollowingIds: int[] (IDs of users the current authenticated user follows)
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$currentUserId = (int) $_SESSION['user_id'];
$activeTab = ($tab === 'followers') ? 'followers' : 'following';
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

        <!-- ── Main Card ─────────────────────────────────────────────────────── -->
        <div class="card glass follow-main-card fade-up">
            <div class="card-body p-3 p-md-4">

                <!-- Header / Profile Context -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$profileUser['id'] ?>" class="btn btn-ghost btn-sm px-2 py-1 flex-shrink-0" title="Back to profile" aria-label="Back to profile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                        <div class="min-w-0">
                            <h1 class="h5 mb-0 fw-bold text-truncate" style="color: var(--fg);">
                                <?= $isOwnNetwork ? 'Followers & Following' : htmlspecialchars($profileUser['full_name']) . "'s Network" ?>
                            </h1>
                            <div class="small text-muted text-truncate" style="font-size: 0.8rem;">
                                @<?= htmlspecialchars($profileUser['username']) ?> &bull; 
                                <span id="headerTotalCounts"><?= $followerCount ?> followers &bull; <?= $followingCount ?> following</span>
                            </div>
                        </div>
                    </div>

                    <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$profileUser['id'] ?>" class="btn btn-ghost btn-sm px-3 flex-shrink-0" style="font-size: 0.82rem; border-radius: var(--radius-pill);">
                        View Profile
                    </a>
                </div>

                <!-- Two Clearly Accessible Tabs: Followers & Following -->
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 mb-3">
                    <ul class="nav nav-pills gap-1 p-1 rounded-3 flex-grow-1" style="background: var(--surface-input); border: 1px solid var(--border);" role="tablist">
                        <!-- Followers Tab -->
                        <li class="nav-item flex-fill" role="presentation">
                            <button
                                type="button"
                                class="nav-link w-100 text-center py-2 px-3 fw-semibold tab-switch-btn <?= $activeTab === 'followers' ? 'active' : '' ?>"
                                id="tab-btn-followers"
                                data-target-tab="followers"
                                role="tab"
                                aria-selected="<?= $activeTab === 'followers' ? 'true' : 'false' ?>"
                                aria-controls="pane-followers"
                                style="<?= $activeTab === 'followers' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                Followers
                                <span class="badge rounded-pill ms-1 tab-badge" id="badge-followers" style="background: var(--surface); color: var(--fg); font-size: 0.72rem; border: 1px solid var(--border);"><?= $followerCount ?></span>
                            </button>
                        </li>

                        <!-- Following Tab -->
                        <li class="nav-item flex-fill" role="presentation">
                            <button
                                type="button"
                                class="nav-link w-100 text-center py-2 px-3 fw-semibold tab-switch-btn <?= $activeTab === 'following' ? 'active' : '' ?>"
                                id="tab-btn-following"
                                data-target-tab="following"
                                role="tab"
                                aria-selected="<?= $activeTab === 'following' ? 'true' : 'false' ?>"
                                aria-controls="pane-following"
                                style="<?= $activeTab === 'following' ? 'background: var(--surface-card); color: var(--fg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);' : 'color: var(--fg-muted);' ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                                Following
                                <span class="badge rounded-pill ms-1 tab-badge" id="badge-following" style="background: var(--surface); color: var(--fg); font-size: 0.72rem; border: 1px solid var(--border);"><?= $followingCount ?></span>
                            </button>
                        </li>
                    </ul>

                    <!-- Search / Filter Input -->
                    <div class="position-relative flex-grow-0" style="min-width: 200px;">
                        <input
                            type="search"
                            id="userListSearchInput"
                            class="form-control form-control-sm"
                            placeholder="Filter by name or @..."
                            autocomplete="off"
                            style="border-radius: var(--radius-pill); padding-left: 32px; font-size: 0.82rem; background: var(--surface-input); border-color: var(--border); color: var(--fg);"
                            aria-label="Filter users"
                        >
                        <svg class="position-absolute" style="left: 11px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.5; color: var(--fg-muted);" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                </div>

                <!-- ── Section 1: Followers Pane ──────────────────────────────── -->
                <div class="tab-pane-content <?= $activeTab === 'followers' ? '' : 'd-none' ?>" id="pane-followers" role="tabpanel" aria-labelledby="tab-btn-followers">
                    <?php if (empty($followers)): ?>
                        <div class="p-5 text-center my-3 text-muted">
                            <div class="mb-3 opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);">
                                <?= $isOwnNetwork ? 'No followers yet' : htmlspecialchars($profileUser['full_name']) . ' has no followers yet' ?>
                            </h2>
                            <p class="small text-muted mb-0" style="max-width: 320px; margin-inline: auto;">
                                <?= $isOwnNetwork ? 'Share posts and interact with others in the community to grow your network.' : 'Be the first to follow and stay updated with their posts!' ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="user-cards-list d-flex flex-column gap-2" id="list-followers">
                            <?php foreach ($followers as $u): ?>
                                <?php
                                    $uId = (int) $u['id'];
                                    $isFollowed = in_array($uId, $myFollowingIds, true);
                                    $isSelf = ($uId === $currentUserId);

                                    if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $u['profile_image'])) {
                                        $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                    } else {
                                        $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=88&background=111113&color=f5f5f7&rounded=true&bold=true';
                                    }
                                    $profileUrl = '/SocialNetwork/public/?url=profile&id=' . $uId;
                                ?>
                                <div class="user-card-item p-3 rounded-3 d-flex align-items-center justify-content-between gap-3 transition-all" data-username="<?= htmlspecialchars(strtolower($u['username']), ENT_QUOTES, 'UTF-8') ?>" data-fullname="<?= htmlspecialchars(strtolower($u['full_name']), ENT_QUOTES, 'UTF-8') ?>" style="background: var(--surface-card); border: 1px solid var(--border);">
                                    <!-- User Info (Avatar & Names) -->
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex-shrink-0" title="View <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>'s profile">
                                            <img src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="46" height="46" style="object-fit: cover; border: 1px solid var(--border);" loading="lazy">
                                        </a>
                                        <div class="min-w-0">
                                            <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block user-fullname" style="color: var(--fg); font-weight: 600; font-size: 0.93rem;">
                                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <span class="user-username small text-muted text-truncate d-block" style="font-size: 0.78rem;">
                                                @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if (!empty($u['bio'])): ?>
                                                <p class="small text-muted mb-0 mt-1 text-truncate" style="font-size: 0.78rem; max-width: 320px;">
                                                    <?= htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <?php if (!$isSelf): ?>
                                            <!-- Follow / Unfollow Button -->
                                            <button
                                                type="button"
                                                class="btn <?= $isFollowed ? 'btn-ghost' : 'btn-primary' ?> btn-sm follow-toggle-btn px-3"
                                                data-user-id="<?= $uId ?>"
                                                data-following="<?= $isFollowed ? '1' : '0' ?>"
                                                style="border-radius: var(--radius-pill); font-size: 0.8rem;"
                                            >
                                                <span class="follow-btn-label"><?= $isFollowed ? 'Following' : 'Follow' ?></span>
                                            </button>

                                            <!-- Send Message Button -->
                                            <a
                                                href="/SocialNetwork/public/?url=messages&user_id=<?= $uId ?>"
                                                class="btn btn-ghost btn-sm px-2"
                                                title="Send Message"
                                                aria-label="Send Message to <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                style="border-radius: var(--radius-pill);"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                                <span class="d-none d-md-inline ms-1" style="font-size: 0.78rem;">Message</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge rounded-pill" style="background: var(--surface-input); color: var(--fg-muted); border: 1px solid var(--border); padding: 5px 12px; font-size: 0.75rem;">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── Section 2: Following Pane ──────────────────────────────── -->
                <div class="tab-pane-content <?= $activeTab === 'following' ? '' : 'd-none' ?>" id="pane-following" role="tabpanel" aria-labelledby="tab-btn-following">
                    <?php if (empty($following)): ?>
                        <div class="p-5 text-center my-3 text-muted">
                            <div class="mb-3 opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                            </div>
                            <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);">
                                <?= $isOwnNetwork ? 'You are not following anyone yet' : htmlspecialchars($profileUser['full_name']) . ' is not following anyone yet' ?>
                            </h2>
                            <p class="small text-muted mb-3" style="max-width: 320px; margin-inline: auto;">
                                Follow other members to keep up with their stories, posts, and discussions.
                            </p>
                            <?php if ($isOwnNetwork): ?>
                                <a href="/SocialNetwork/public/?url=search" class="btn btn-primary btn-sm px-4" style="border-radius: var(--radius-pill);">
                                    Find People to Follow
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="user-cards-list d-flex flex-column gap-2" id="list-following">
                            <?php foreach ($following as $u): ?>
                                <?php
                                    $uId = (int) $u['id'];
                                    $isFollowed = in_array($uId, $myFollowingIds, true);
                                    $isSelf = ($uId === $currentUserId);

                                    if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $u['profile_image'])) {
                                        $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                    } else {
                                        $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=88&background=111113&color=f5f5f7&rounded=true&bold=true';
                                    }
                                    $profileUrl = '/SocialNetwork/public/?url=profile&id=' . $uId;
                                ?>
                                <div class="user-card-item p-3 rounded-3 d-flex align-items-center justify-content-between gap-3 transition-all" data-username="<?= htmlspecialchars(strtolower($u['username']), ENT_QUOTES, 'UTF-8') ?>" data-fullname="<?= htmlspecialchars(strtolower($u['full_name']), ENT_QUOTES, 'UTF-8') ?>" style="background: var(--surface-card); border: 1px solid var(--border);">
                                    <!-- User Info (Avatar & Names) -->
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex-shrink-0" title="View <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>'s profile">
                                            <img src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="46" height="46" style="object-fit: cover; border: 1px solid var(--border);" loading="lazy">
                                        </a>
                                        <div class="min-w-0">
                                            <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block user-fullname" style="color: var(--fg); font-weight: 600; font-size: 0.93rem;">
                                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <span class="user-username small text-muted text-truncate d-block" style="font-size: 0.78rem;">
                                                @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if (!empty($u['bio'])): ?>
                                                <p class="small text-muted mb-0 mt-1 text-truncate" style="font-size: 0.78rem; max-width: 320px;">
                                                    <?= htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <?php if (!$isSelf): ?>
                                            <!-- Follow / Unfollow Button -->
                                            <button
                                                type="button"
                                                class="btn <?= $isFollowed ? 'btn-ghost' : 'btn-primary' ?> btn-sm follow-toggle-btn px-3"
                                                data-user-id="<?= $uId ?>"
                                                data-following="<?= $isFollowed ? '1' : '0' ?>"
                                                style="border-radius: var(--radius-pill); font-size: 0.8rem;"
                                            >
                                                <span class="follow-btn-label"><?= $isFollowed ? 'Following' : 'Follow' ?></span>
                                            </button>

                                            <!-- Send Message Button -->
                                            <a
                                                href="/SocialNetwork/public/?url=messages&user_id=<?= $uId ?>"
                                                class="btn btn-ghost btn-sm px-2"
                                                title="Send Message"
                                                aria-label="Send Message to <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                style="border-radius: var(--radius-pill);"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                                <span class="d-none d-md-inline ms-1" style="font-size: 0.78rem;">Message</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge rounded-pill" style="background: var(--surface-input); color: var(--fg-muted); border: 1px solid var(--border); padding: 5px 12px; font-size: 0.75rem;">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="noSearchMatchNotice" class="p-4 text-center text-muted small d-none">
                    No users match your filter.
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ── Tab Switching, Real-Time Filter & Follow AJAX Toggle ─────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    // ── 1. Tab Switching (Followers / Following) ──
    var tabButtons = document.querySelectorAll('.tab-switch-btn');
    var paneFollowers = document.getElementById('pane-followers');
    var paneFollowing = document.getElementById('pane-following');
    var searchInput   = document.getElementById('userListSearchInput');

    function setActiveTab(targetTab) {
        tabButtons.forEach(function (btn) {
            var isCurrent = btn.getAttribute('data-target-tab') === targetTab;
            btn.classList.toggle('active', isCurrent);
            btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
            if (isCurrent) {
                btn.style.background = 'var(--surface-card)';
                btn.style.color = 'var(--fg)';
                btn.style.border = '1px solid var(--border)';
                btn.style.boxShadow = 'var(--shadow-sm)';
            } else {
                btn.style.background = 'transparent';
                btn.style.color = 'var(--fg-muted)';
                btn.style.border = 'none';
                btn.style.boxShadow = 'none';
            }
        });

        if (targetTab === 'followers') {
            if (paneFollowers) paneFollowers.classList.remove('d-none');
            if (paneFollowing) paneFollowing.classList.add('d-none');
        } else {
            if (paneFollowers) paneFollowers.classList.add('d-none');
            if (paneFollowing) paneFollowing.classList.remove('d-none');
        }

        // Re-run filter on newly visible pane
        if (searchInput) {
            filterUsers(searchInput.value);
        }

        // Update URL state gracefully without full reload
        var url = new URL(window.location.href);
        url.searchParams.set('tab', targetTab);
        window.history.replaceState({}, '', url.toString());
    }

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = this.getAttribute('data-target-tab');
            setActiveTab(target);
        });
    });

    // ── 2. Real-Time Search Filter ──
    function filterUsers(query) {
        var q = (query || '').trim().toLowerCase();
        var activePane = document.querySelector('.tab-pane-content:not(.d-none)');
        if (!activePane) return;

        var items = activePane.querySelectorAll('.user-card-item');
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

        var noMatchEl = document.getElementById('noSearchMatchNotice');
        if (noMatchEl) {
            if (items.length > 0 && matchCount === 0 && q !== '') {
                noMatchEl.classList.remove('d-none');
            } else {
                noMatchEl.classList.add('d-none');
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterUsers(this.value);
        });
    }

    // ── 3. Follow / Unfollow AJAX Action ──
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

                // Sync all buttons for this target user on the page
                document.querySelectorAll('.follow-toggle-btn[data-user-id="' + userId + '"]').forEach(function (b) {
                    b.setAttribute('data-following', newStatus ? '1' : '0');
                    var label = b.querySelector('.follow-btn-label');
                    if (label) {
                        label.textContent = newStatus ? 'Following' : 'Follow';
                    } else {
                        b.textContent = newStatus ? 'Following' : 'Follow';
                    }

                    if (newStatus) {
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-ghost');
                    } else {
                        b.classList.remove('btn-ghost');
                        b.classList.add('btn-primary');
                    }
                });
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
