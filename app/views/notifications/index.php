<?php
/**
 * app/views/notifications/index.php
 *
 * Notifications Center Page
 *
 * Variables passed from NotificationController:
 *   - $notifications: array of notification records with actor profile info
 *   - $unreadCount: int total unread notifications
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$currentUserId = (int) $_SESSION['user_id'];
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 720px;">

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
        <div class="card glass notifications-card fade-up">
            <div class="card-body p-3 p-md-4">

                <!-- Header: Title + Unread Count + Mark All Read Action -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:36px;height:36px;border-radius:10px;background:var(--surface-input);display:flex;align-items:center;justify-content:center;color:var(--fg);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <div>
                            <h1 class="h5 mb-0 fw-bold" style="color: var(--fg);">Notifications</h1>
                            <span class="small text-muted">
                                <?= $unreadCount > 0 ? "You have {$unreadCount} unread notification" . ($unreadCount > 1 ? 's' : '') : "All caught up" ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($unreadCount > 0): ?>
                        <form method="POST" action="/SocialNetwork/public/?url=notifications/read-all" class="m-0">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm px-3 py-1" style="font-size: 0.8rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"/></svg>
                                Mark all as read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Notifications List -->
                <?php if (empty($notifications)): ?>
                    <div class="p-5 text-center my-4 text-muted">
                        <div class="mb-3 opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <h2 class="h6 mb-2 fw-semibold" style="color: var(--fg);">No notifications yet</h2>
                        <p class="small text-muted mb-0" style="max-width: 340px; margin-inline: auto;">
                            When other members interact with your posts or send you messages, you'll see updates right here.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list d-flex flex-column gap-2">
                        <?php foreach ($notifications as $n): ?>
                            <?php
                                $isUnread = ((int) $n['is_read'] === 0);
                                $type     = $n['type'] ?? 'system';
                                $timeAgo  = date('M j, g:i A', strtotime($n['created_at']));

                                // Resolve target URL
                                if ($type === 'message') {
                                    $targetUrl = '/SocialNetwork/public/?url=messages&user_id=' . (int) ($n['target_id'] ?: $n['actor_id']);
                                } elseif ($type === 'like' || $type === 'comment') {
                                    $targetUrl = '/SocialNetwork/public/#post-' . (int) $n['target_id'];
                                } else {
                                    $targetUrl = '/SocialNetwork/public/';
                                }

                                // Actor avatar
                                $actorName = $n['actor_name'] ?? 'System';
                                if (!empty($n['actor_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $n['actor_image'])) {
                                    $actorAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($n['actor_image']);
                                } else {
                                    $actorAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($actorName) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                }
                            ?>
                            <div class="notification-item p-3 rounded-3 d-flex align-items-center gap-3 transition-all <?= $isUnread ? 'notification-unread' : 'notification-read' ?>" style="border: 1px solid var(--border);">
                                <!-- Actor Avatar with Badge Icon -->
                                <div class="position-relative flex-shrink-0">
                                    <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>">
                                        <img src="<?= htmlspecialchars($actorAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($actorName, ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="44" height="44" style="object-fit: cover;">
                                    </a>
                                    <!-- Type Icon Badge -->
                                    <span class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center notif-type-badge <?= 'notif-badge-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($type === 'message'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <?php elseif ($type === 'like'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                        <?php elseif ($type === 'comment'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <!-- Notification Content -->
                                <div class="flex-grow-1 min-w-0">
                                    <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block">
                                        <p class="mb-1 notif-message" style="color: var(--fg); font-size: 0.9rem; font-weight: <?= $isUnread ? '600' : '400' ?>; line-height: 1.4;">
                                            <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <span class="small" style="color: var(--fg-faint); font-size: 0.75rem;">
                                            <?= htmlspecialchars($timeAgo, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </a>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <?php if ($isUnread): ?>
                                        <!-- Unread Dot Indicator -->
                                        <span class="p-1 rounded-circle bg-primary" title="Unread" style="width: 8px; height: 8px; display: inline-block;"></span>

                                        <form method="POST" action="/SocialNetwork/public/?url=notifications/read" class="m-0">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                            <button type="submit" class="btn btn-ghost btn-sm px-2 py-1" title="Mark as read" aria-label="Mark as read" style="font-size: 0.75rem;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <form method="POST" action="/SocialNetwork/public/?url=notifications/delete" class="m-0">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                        <button type="submit" class="btn btn-ghost btn-sm px-2 py-1 text-danger opacity-75" title="Delete notification" aria-label="Delete notification" style="font-size: 0.75rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
