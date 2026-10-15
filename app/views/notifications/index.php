<?php
/**
 * app/views/notifications/index.php
 *
 * Full Notifications Page
 * Variables passed from NotificationController::index():
 *   - $notifications: array of notification records with joined actor data
 */

/**
 * Build avatar URL consistent with the rest of the app.
 */
function notifAvatarUrl(?string $profileImage): string {
    if (empty($profileImage)) {
        return '/SocialNetwork/public/assets/images/default-avatar.svg';
    }
    $img = ltrim($profileImage, '/');
    return str_starts_with($img, 'assets/')
        ? '/SocialNetwork/public/' . $img
        : '/SocialNetwork/public/assets/images/profiles/' . $img;
}

/**
 * Build notification link.
 */
function notifLink(array $n): string {
    $base = '/SocialNetwork/public/?url=';
    return match ($n['type']) {
        'message'      => $base . 'messages/chat&user_id=' . (int)$n['actor_id'],
        'comment'      => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
        'like_post'    => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
        'like_comment' => '/SocialNetwork/public/#post-' . (int)$n['target_id'],
        default        => '/SocialNetwork/public/',
    };
}

/**
 * Build notification description.
 */
function notifDescription(array $n): string {
    $actor = htmlspecialchars($n['actor_full_name'] ?? 'Someone', ENT_QUOTES, 'UTF-8');
    return match ($n['type']) {
        'message'      => "$actor sent you a message.",
        'comment'      => "$actor commented on your post.",
        'like_post'    => "$actor liked your post.",
        'like_comment' => "$actor liked your comment.",
        default        => "$actor interacted with your content.",
    };
}

/**
 * Build icon SVG based on notification type.
 */
function notifIcon(string $type): string {
    return match ($type) {
        'message'      => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'comment'      => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
        'like_post',
        'like_comment' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        default        => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    };
}

function notifIconColor(string $type): string {
    return match ($type) {
        'message'      => 'var(--btn-primary-bg)',
        'comment'      => '#0ea5e9',
        'like_post',
        'like_comment' => '#ef4444',
        default        => 'var(--fg-muted)',
    };
}

function notifTimeLabel(string $createdAt): string {
    $diff = time() - strtotime($createdAt);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', strtotime($createdAt));
}

$csrfToken = Csrf::getToken();
$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
?>

<div class="row justify-content-center">
  <div class="col-12" style="max-width: 760px;">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="h3 mb-0" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
          Notifications
        </h1>
        <p class="small text-muted mb-0 mt-1">
          <?php if ($unreadCount > 0): ?>
            You have <strong><?= $unreadCount ?></strong> unread notification<?= $unreadCount !== 1 ? 's' : '' ?>.
          <?php else: ?>
            All caught up!
          <?php endif; ?>
        </p>
      </div>
      <?php if ($unreadCount > 0): ?>
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
          id="markAllReadBtn"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Mark all as read
        </button>
      <?php endif; ?>
    </div>

    <!-- Notifications Card -->
    <div class="card glass fade-up overflow-hidden">
      <?php if (empty($notifications)): ?>
        <div class="text-center py-5 px-4">
          <div class="mb-3 d-inline-flex align-items-center justify-content-center"
               style="width: 64px; height: 64px; border-radius: 50%; background: var(--surface-hover); color: var(--fg-muted);">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <h2 class="h5 mb-1" style="font-weight: 600; color: var(--fg);">No notifications yet</h2>
          <p class="text-muted small mb-0">When someone messages, comments, or likes your content, you'll see it here.</p>
        </div>
      <?php else: ?>
        <div class="list-group list-group-flush" id="notificationsList">
          <?php foreach ($notifications as $notif): ?>
            <?php
              $nId     = (int) $notif['id'];
              $isRead  = (bool) $notif['is_read'];
              $link    = notifLink($notif);
              $desc    = notifDescription($notif);
              $avatar  = notifAvatarUrl($notif['actor_profile_image'] ?? null);
              $time    = notifTimeLabel($notif['created_at']);
              $icon    = notifIcon($notif['type']);
              $iconClr = notifIconColor($notif['type']);
              $actorName = htmlspecialchars($notif['actor_full_name'] ?? 'Someone', ENT_QUOTES, 'UTF-8');
            ?>
            <div
              class="list-group-item d-flex align-items-start gap-3 py-3 px-4 notification-item <?= $isRead ? '' : 'notification-unread' ?>"
              data-notif-id="<?= $nId ?>"
              style="background: <?= $isRead ? 'transparent' : 'rgba(var(--btn-primary-bg-rgb, 79, 70, 229), 0.04)' ?>; border-color: var(--border); transition: background var(--transition);"
            >
              <!-- Avatar with type badge -->
              <div class="position-relative flex-shrink-0">
                <img
                  src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= $actorName ?>"
                  class="rounded-circle object-fit-cover shadow-sm"
                  style="width: 44px; height: 44px; border: 2px solid var(--surface-hover);"
                  onerror="this.src='/SocialNetwork/public/assets/images/default-avatar.svg'"
                >
                <span
                  class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center rounded-circle"
                  style="width: 20px; height: 20px; background: <?= $iconClr ?>; color: white; font-size: 0.6rem; border: 2px solid var(--surface);"
                >
                  <?= $icon ?>
                </span>
              </div>

              <!-- Content -->
              <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div class="flex-grow-1">
                    <p class="mb-1 small" style="font-weight: <?= $isRead ? '400' : '600' ?>; color: var(--fg); line-height: 1.4;">
                      <?= $desc ?>
                    </p>
                    <?php if (!empty($notif['preview'])): ?>
                      <p class="mb-0 small text-muted text-truncate" style="font-size: 0.78rem; max-width: 400px;">
                        "<?= htmlspecialchars($notif['preview'], ENT_QUOTES, 'UTF-8') ?>"
                      </p>
                    <?php endif; ?>
                  </div>
                  <!-- Unread dot -->
                  <?php if (!$isRead): ?>
                    <span class="flex-shrink-0 rounded-circle"
                          style="width: 8px; height: 8px; background: var(--btn-primary-bg); margin-top: 4px; flex-shrink: 0;"></span>
                  <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-3 mt-1">
                  <span class="text-muted" style="font-size: 0.72rem;"><?= $time ?></span>
                  <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                     class="text-muted small mark-read-link"
                     data-notif-id="<?= $nId ?>"
                     style="font-size: 0.72rem; color: var(--btn-primary-bg) !important; text-decoration: none;">
                    View →
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = <?= json_encode($csrfToken) ?>;
    var base = '/SocialNetwork/public/?url=';

    // Mark single notification read when clicking View link
    document.querySelectorAll('.mark-read-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var notifId = this.getAttribute('data-notif-id');
            if (!notifId) return;

            var href = this.getAttribute('href');

            // Fire-and-forget mark read
            fetch(base + 'notifications/mark-read', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new URLSearchParams({ id: notifId, csrf_token: csrfToken })
            }).then(function (res) { return res.json(); }).then(function (data) {
                if (data.success) {
                    var row = document.querySelector('.notification-item[data-notif-id="' + notifId + '"]');
                    if (row) {
                        row.classList.remove('notification-unread');
                        row.style.background = 'transparent';
                        var dot = row.querySelector('.rounded-circle[style*="width: 8px"]');
                        if (dot) dot.remove();
                        updateBadge(data.unread);
                    }
                }
            }).catch(function () {});
        });
    });

    // Mark all read
    var markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            fetch(base + 'notifications/mark-all-read', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new URLSearchParams({ csrf_token: csrfToken })
            }).then(function (res) { return res.json(); }).then(function (data) {
                if (data.success) {
                    document.querySelectorAll('.notification-unread').forEach(function (row) {
                        row.classList.remove('notification-unread');
                        row.style.background = 'transparent';
                        var dot = row.querySelector('.rounded-circle[style*="width: 8px"]');
                        if (dot) dot.remove();
                        var fw = row.querySelector('p[style*="font-weight"]');
                        if (fw) fw.style.fontWeight = '400';
                    });
                    updateBadge(0);
                    markAllBtn.remove();
                }
            }).catch(function () {});
        });
    }

    function updateBadge(count) {
        var badge = document.getElementById('notifBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
});
</script>
