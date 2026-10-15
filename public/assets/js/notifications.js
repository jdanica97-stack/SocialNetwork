/**
 * public/assets/js/notifications.js
 *
 * Handles the notification bell dropdown panel, unread badge polling,
 * mark-as-read, and mark-all-read interactions.
 *
 * Runs only when the user is logged in (presence of #notifBellBtn).
 */
(function () {
    'use strict';

    var bellBtn       = document.getElementById('notifBellBtn');
    var badge         = document.getElementById('notifBadge');
    var dropdown      = document.getElementById('notifDropdown');
    var dropdownBody  = document.getElementById('notifDropdownBody');
    var markAllBtn    = document.getElementById('notifMarkAllBtn');

    if (!bellBtn || !dropdown) return; // Not logged in or bell not rendered

    var BASE = '/SocialNetwork/public/?url=notifications';
    var isOpen   = false;
    var isLoaded = false;

    // ─── CSRF token ─────────────────────────────────────────────────────────
    var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // ─── Badge update ────────────────────────────────────────────────────────
    function updateBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // ─── Poll unread count every 30 seconds ──────────────────────────────────
    function pollCount() {
        fetch('/SocialNetwork/public/?url=notifications/count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
            if (data && data.success) {
                updateBadge(data.unread);
            }
        })
        .catch(function () {});
    }

    // Initial count load
    pollCount();
    // Poll every 30 seconds
    setInterval(pollCount, 30000);

    // ─── Build notification icon SVG ─────────────────────────────────────────
    function getIcon(type) {
        switch (type) {
            case 'message':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
            case 'comment':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';
            case 'like_post':
            case 'like_comment':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
            default:
                return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        }
    }

    function getIconColor(type) {
        switch (type) {
            case 'message':      return '#6366f1';
            case 'comment':      return '#0ea5e9';
            case 'like_post':
            case 'like_comment': return '#ef4444';
            default:             return '#6b7280';
        }
    }

    function getAvatarUrl(img) {
        if (!img) return '/SocialNetwork/public/assets/images/default-avatar.svg';
        var clean = img.replace(/^\//, '');
        return clean.startsWith('assets/')
            ? '/SocialNetwork/public/' + clean
            : '/SocialNetwork/public/assets/images/profiles/' + clean;
    }

    // ─── Render notifications list in dropdown ───────────────────────────────
    function renderNotifications(notifs) {
        if (!notifs || notifs.length === 0) {
            dropdownBody.innerHTML =
                '<div class="text-center py-4 px-3">' +
                '<p class="text-muted small mb-0">You have no notifications yet.</p>' +
                '</div>';
            return;
        }

        var html = '';
        notifs.forEach(function (n) {
            var avatarUrl = getAvatarUrl(n.actor_profile_image);
            var iconSvg   = getIcon(n.type);
            var iconClr   = getIconColor(n.type);
            var bg        = n.is_read ? 'transparent' : 'rgba(99,102,241,0.05)';
            var fw        = n.is_read ? '400' : '600';

            html += '<div class="notif-item d-flex align-items-start gap-2 px-3 py-2 ' + (n.is_read ? '' : 'notif-unread') + '"' +
                    ' data-notif-id="' + n.id + '"' +
                    ' style="background:' + bg + '; border-bottom:1px solid var(--border); cursor:pointer; transition:background 0.15s;"' +
                    ' onclick="notifItemClick(this, \'' + escHtml(n.link) + '\', ' + n.id + ')">' +

                    // Avatar
                    '<div class="position-relative flex-shrink-0">' +
                    '<img src="' + escHtml(avatarUrl) + '" alt="' + escHtml(n.actor_full_name) + '"' +
                    ' style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1.5px solid var(--border);"' +
                    ' onerror="this.src=\'/SocialNetwork/public/assets/images/default-avatar.svg\'">' +
                    '<span style="position:absolute;bottom:-2px;right:-2px;width:16px;height:16px;background:' + iconClr + ';border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid var(--surface);color:#fff;">' +
                    iconSvg + '</span>' +
                    '</div>' +

                    // Text
                    '<div style="flex:1;min-width:0;">' +
                    '<p style="margin:0;font-size:0.82rem;font-weight:' + fw + ';color:var(--fg);line-height:1.35;">' + escHtml(n.description) + '</p>' +
                    (n.preview ? '<p style="margin:0;font-size:0.74rem;color:var(--fg-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">&ldquo;' + escHtml(n.preview) + '&rdquo;</p>' : '') +
                    '<span style="font-size:0.70rem;color:var(--fg-muted);">' + escHtml(n.time_label) + '</span>' +
                    '</div>' +

                    // Unread dot
                    (!n.is_read ? '<span style="width:8px;height:8px;min-width:8px;background:#ef4444;border-radius:50%;margin-top:6px;"></span>' : '') +
                    '</div>';
        });

        dropdownBody.innerHTML = html;
    }

    // ─── Notification item click → mark read + navigate ──────────────────────
    window.notifItemClick = function (el, link, id) {
        // Mark read
        if (el.classList.contains('notif-unread')) {
            fetch('/SocialNetwork/public/?url=notifications/mark-read', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new URLSearchParams({ id: id, csrf_token: csrfToken })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) { updateBadge(data.unread); }
            })
            .catch(function () {});
            el.classList.remove('notif-unread');
            el.style.background = 'transparent';
        }
        // Navigate
        window.location.href = link;
    };

    // ─── Load notifications into dropdown ────────────────────────────────────
    function loadDropdown() {
        isLoaded = false;
        dropdownBody.innerHTML = '<div class="text-center py-4" id="notifDropdownLoading"><span class="text-muted small">Loading\u2026</span></div>';

        fetch('/SocialNetwork/public/?url=notifications/fetch', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
            isLoaded = true;
            if (data && data.success) {
                updateBadge(data.unread);
                renderNotifications(data.notifications);
            } else {
                dropdownBody.innerHTML = '<div class="text-center py-4"><span class="text-muted small">Could not load notifications.</span></div>';
            }
        })
        .catch(function () {
            isLoaded = true;
            dropdownBody.innerHTML = '<div class="text-center py-4"><span class="text-muted small">Error loading notifications.</span></div>';
        });
    }

    // ─── Toggle dropdown ─────────────────────────────────────────────────────
    bellBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen = !isOpen;
        dropdown.style.display = isOpen ? 'block' : 'none';
        if (isOpen && !isLoaded) {
            loadDropdown();
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        if (isOpen && !dropdown.contains(e.target) && e.target !== bellBtn) {
            isOpen = false;
            dropdown.style.display = 'none';
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            isOpen = false;
            dropdown.style.display = 'none';
        }
    });

    // ─── Mark all read ───────────────────────────────────────────────────────
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fetch('/SocialNetwork/public/?url=notifications/mark-all-read', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new URLSearchParams({ csrf_token: csrfToken })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    updateBadge(0);
                    // Remove unread styling from all dropdown items
                    document.querySelectorAll('.notif-item.notif-unread').forEach(function (item) {
                        item.classList.remove('notif-unread');
                        item.style.background = 'transparent';
                        var dot = item.querySelector('span[style*="width:8px"]');
                        if (dot) dot.remove();
                    });
                }
            })
            .catch(function () {});
        });
    }

    // ─── Utility: HTML escape ────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

}());
