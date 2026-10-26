<?php
/**
 * app/views/messages/index.php
 *
 * Direct Messages Page
 *
 * Variables passed from MessageController:
 *   - $conversations: array of conversation threads with latest message & unread count
 *   - $selectedUserId: int|null (active partner ID)
 *   - $activePartner: array|null (active partner user row)
 *   - $messages: array of message history between current user and active partner
 *   - $availableUsers: array of all other registered users to start new chat
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$currentUserId = (int) $_SESSION['user_id'];
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 1040px;">

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

        <!-- ── Messages Container Card ──────────────────────────────────────── -->
        <div class="card glass messages-main-card fade-up visible">
            <div class="row g-0 messages-layout">

                <!-- ── Left Column: Conversations Sidebar ─────────────────────── -->
                <div class="col-12 col-md-5 col-lg-4 messages-sidebar border-end" style="border-color: var(--border) !important;">

                    <!-- Sidebar Header: Title + New Chat Button -->
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--border) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <h1 class="h6 mb-0 fw-bold" style="color: var(--fg);">Messages</h1>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm px-2 py-1" data-bs-toggle="modal" data-bs-target="#newChatModal" title="Start new conversation" aria-label="New Message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            <span class="d-none d-sm-inline ms-1 small">New Chat</span>
                        </button>
                    </div>

                    <!-- Conversations List -->
                    <div class="conversations-list" style="max-height: 520px; overflow-y: auto;">
                        <?php if (empty($conversations)): ?>
                            <div class="p-4 text-center text-muted">
                                <div class="mb-2 opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                </div>
                                <p class="small mb-2">No conversations yet.</p>
                                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                                    Start a Chat
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $c): ?>
                                <?php
                                    $cUserId = (int) $c['user_id'];
                                    $isActive = ($selectedUserId === $cUserId);
                                    $hasUnread = ((int) $c['unread_count'] > 0);

                                    // Avatar
                                    if (!empty($c['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $c['profile_image'])) {
                                        $avatarUrl = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($c['profile_image']);
                                    } else {
                                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . rawurlencode($c['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                    }

                                    // Date formatting
                                    $timeAgo = date('M j', strtotime($c['last_message_at']));
                                ?>
                                <a href="/SocialNetwork/public/?url=messages&user_id=<?= $cUserId ?>" class="conversation-item d-flex align-items-center gap-3 p-3 text-decoration-none <?= $isActive ? 'active' : '' ?>" data-user-id="<?= $cUserId ?>">
                                    <div class="position-relative flex-shrink-0">
                                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($c['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="42" height="42" style="object-fit: cover;">
                                        <?php if ($hasUnread): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                                <span class="visually-hidden">New alert</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <span class="fw-semibold text-truncate conversation-name" style="color: var(--fg); font-size: 0.9rem;">
                                                <?= htmlspecialchars($c['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span class="small conversation-time" style="color: var(--fg-faint); font-size: 0.72rem;">
                                                <?= htmlspecialchars($timeAgo, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="small text-truncate mb-0 conversation-snippet" style="color: <?= $hasUnread ? 'var(--fg)' : 'var(--fg-muted)' ?>; font-weight: <?= $hasUnread ? '600' : '400' ?>; max-width: 170px;">
                                                <?= ((int)$c['last_sender_id'] === $currentUserId ? 'You: ' : '') . htmlspecialchars($c['last_message'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                            <?php if ($hasUnread): ?>
                                                <span class="badge rounded-pill bg-primary" style="font-size: 0.7rem; padding: 2px 7px;">
                                                    <?= (int) $c['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Right Column: Active Conversation / Chat Window ─────────── -->
                <div class="col-12 col-md-7 col-lg-8 messages-content d-flex flex-column">

                    <?php if ($activePartner !== null): ?>
                        <?php
                            if (!empty($activePartner['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $activePartner['profile_image'])) {
                                $partnerAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($activePartner['profile_image']);
                            } else {
                                $partnerAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($activePartner['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                            }
                        ?>

                        <!-- Chat Header -->
                        <div class="chat-header p-3 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--border) !important;">
                            <div class="d-flex align-items-center gap-3">
                                <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$activePartner['id'] ?>" class="text-decoration-none">
                                    <img src="<?= htmlspecialchars($partnerAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($activePartner['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="38" height="38" style="object-fit: cover;">
                                </a>
                                <div>
                                    <h2 class="h6 mb-0 fw-bold" style="color: var(--fg); font-size: 0.95rem;">
                                        <?= htmlspecialchars($activePartner['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </h2>
                                    <span class="small" style="color: var(--fg-muted); font-size: 0.78rem;">
                                        @<?= htmlspecialchars($activePartner['username'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            </div>
                            <a href="/SocialNetwork/public/?url=profile&id=<?= (int)$activePartner['id'] ?>" class="btn btn-ghost btn-sm px-3 py-1" style="font-size: 0.8rem;">
                                View Profile
                            </a>
                        </div>

                        <!-- Chat History Scroll Box -->
                        <div class="chat-history p-3 p-md-4 flex-grow-1" id="chatHistory" data-partner-id="<?= (int)$activePartner['id'] ?>" data-last-id="<?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>" style="height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                            <?php if (empty($messages)): ?>
                                <div class="text-center my-auto py-5 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 opacity-50"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                    <p class="small mb-0">No messages in this conversation yet.</p>
                                    <p class="small text-muted">Say hello to <?= htmlspecialchars($activePartner['full_name'], ENT_QUOTES, 'UTF-8') ?>!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <?php
                                        $isMine = ((int)$msg['sender_id'] === $currentUserId);
                                        $msgTime = date('g:i A · M j', strtotime($msg['created_at']));
                                    ?>
                                    <div class="chat-bubble-row d-flex <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>">
                                        <div class="chat-bubble <?= $isMine ? 'chat-bubble-out' : 'chat-bubble-in' ?>">
                                            <div class="chat-text">
                                                <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
                                            </div>
                                            <div class="chat-meta d-flex align-items-center gap-1 justify-content-end mt-1">
                                                <span class="chat-time"><?= htmlspecialchars($msgTime, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if ($isMine): ?>
                                                    <?php if ((int)$msg['is_read'] === 1): ?>
                                                        <!-- Read checkmark -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary" title="Read" aria-label="Read"><polyline points="20 6 9 17 4 12"/><polyline points="20 12 15 17 12 14"/></svg>
                                                    <?php else: ?>
                                                        <!-- Delivered checkmark -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-50" title="Delivered" aria-label="Delivered"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Chat Input Box -->
                        <div class="chat-composer p-3 border-top" style="border-color: var(--border) !important;">
                            <form method="POST" action="/SocialNetwork/public/?url=messages/send" id="messageSendForm" class="d-flex align-items-end gap-2">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="recipient_id" value="<?= (int) $activePartner['id'] ?>">

                                <div class="flex-grow-1">
                                    <textarea
                                        name="message"
                                        id="messageInput"
                                        class="form-control"
                                        rows="1"
                                        maxlength="5000"
                                        placeholder="Write a message to <?= htmlspecialchars($activePartner['full_name'], ENT_QUOTES, 'UTF-8') ?>..."
                                        required
                                        style="resize: none; border-radius: 20px; padding: 10px 16px; min-height: 42px; max-height: 120px;"
                                    ></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center" style="border-radius: 20px; width: 44px; height: 42px; padding: 0;" aria-label="Send Message" title="Send">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                </button>
                            </form>
                        </div>

                    <?php else: ?>
                        <!-- Empty State: No Conversation Selected -->
                        <div class="p-5 text-center my-auto text-muted">
                            <div class="mb-3 opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <h2 class="h5 mb-2 fw-semibold" style="color: var(--fg);">Your Messages</h2>
                            <p class="small text-muted mb-4" style="max-width: 360px; margin-inline: auto;">
                                Send private messages, share thoughts, and stay connected with people in your network.
                            </p>
                            <button type="button" class="btn btn-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#newChatModal">
                                Send a Message
                            </button>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- ── New Chat Modal (User Selector) ──────────────────────────────────────── -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass" style="background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-card);">
            <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                <h5 class="modal-title h6 fw-bold" id="newChatModalLabel" style="color: var(--fg);">New Conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <?php if (empty($availableUsers)): ?>
                    <div class="p-4 text-center text-muted small">
                        No other users found on the platform yet.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                        <?php foreach ($availableUsers as $u): ?>
                            <?php
                                if (!empty($u['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $u['profile_image'])) {
                                    $uAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($u['profile_image']);
                                } else {
                                    $uAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($u['full_name']) . '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';
                                }
                            ?>
                            <a href="/SocialNetwork/public/?url=messages&user_id=<?= (int)$u['id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none" style="background: transparent; border-color: var(--border) !important; color: var(--fg);">
                                <img src="<?= htmlspecialchars($uAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.88rem; color: var(--fg);">
                                        <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="small text-muted text-truncate" style="font-size: 0.75rem;">
                                        @<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            </a>
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

    // ── 1. Clean up any leftover modal backdrops from bfcache or stalled transitions ──
    function cleanModalBackdrops() {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) {
            el.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
    cleanModalBackdrops();

    window.addEventListener('pageshow', function () {
        cleanModalBackdrops();
        var modalEl = document.getElementById('newChatModal');
        if (modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
        }
    });

    // ── 2. Robust Vanilla JS Modal Controls for #newChatModal ──
    var newChatModal = document.getElementById('newChatModal');

    function openNewChatModal() {
        if (!newChatModal) return;
        cleanModalBackdrops();
        newChatModal.style.display = 'block';
        void newChatModal.offsetHeight; // Force reflow
        newChatModal.classList.add('show');
        newChatModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'newChatModalBackdrop';
        document.body.appendChild(backdrop);

        backdrop.addEventListener('click', closeNewChatModal);
    }

    function closeNewChatModal() {
        if (!newChatModal) return;
        newChatModal.classList.remove('show');
        newChatModal.style.display = 'none';
        newChatModal.setAttribute('aria-hidden', 'true');
        cleanModalBackdrops();
    }

    // Modal open buttons
    document.querySelectorAll('[data-bs-target="#newChatModal"], [data-target="#newChatModal"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openNewChatModal();
        });
    });

    // Modal close buttons & links
    if (newChatModal) {
        newChatModal.querySelectorAll('[data-bs-dismiss="modal"], [data-dismiss="modal"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                closeNewChatModal();
            });
        });

        // Close on clicking outside modal dialog
        newChatModal.addEventListener('click', function (e) {
            if (e.target === newChatModal) {
                closeNewChatModal();
            }
        });

        // Close when clicking user contact link before navigating
        newChatModal.querySelectorAll('a[href*="url=messages"]').forEach(function (link) {
            link.addEventListener('click', function () {
                closeNewChatModal();
            });
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && newChatModal && newChatModal.classList.contains('show')) {
            closeNewChatModal();
        }
    });

    // ── 3. Auto-scroll chat history helper ──
    var chatBox = document.getElementById('chatHistory');
    function scrollToBottom(smooth) {
        if (!chatBox) return;
        if (smooth) {
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        } else {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
    scrollToBottom(false);

    // ── 4. Message Composer: Auto-grow, Enter to Send, AJAX Submission ──
    var msgInput   = document.getElementById('messageInput');
    var sendForm   = document.getElementById('messageSendForm');
    var sendBtn    = sendForm ? sendForm.querySelector('button[type="submit"]') : null;
    var partnerId  = chatBox ? parseInt(chatBox.getAttribute('data-partner-id'), 10) : 0;
    var lastMsgId  = chatBox ? parseInt(chatBox.getAttribute('data-last-id'), 10) || 0 : 0;
    var myUserId   = <?= (int) $currentUserId ?>;

    function adjustInputHeight() {
        if (!msgInput) return;
        msgInput.style.height = 'auto';
        msgInput.style.height = Math.min(Math.max(msgInput.scrollHeight, 42), 120) + 'px';
    }

    if (msgInput) {
        msgInput.addEventListener('input', adjustInputHeight);

        // Enter key to send (Shift+Enter for newline)
        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Focus input automatically when chat is open
        setTimeout(function () {
            if (msgInput) msgInput.focus();
        }, 150);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function appendMessageBubble(messageText, timeStr, isMine, msgId) {
        if (!chatBox) return;

        // Remove empty placeholder state if present
        var emptyPlaceholder = chatBox.querySelector('.text-muted.my-auto');
        if (emptyPlaceholder) {
            emptyPlaceholder.remove();
        }

        var row = document.createElement('div');
        row.className = 'chat-bubble-row d-flex ' + (isMine ? 'justify-content-end' : 'justify-content-start');
        if (msgId) row.setAttribute('data-msg-id', msgId);

        var checkmarkHtml = isMine
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-50 msg-status-icon" title="Delivered" aria-label="Delivered"><polyline points="20 6 9 17 4 12"/></svg>'
            : '';

        row.innerHTML =
            '<div class="chat-bubble ' + (isMine ? 'chat-bubble-out' : 'chat-bubble-in') + '">' +
                '<div class="chat-text">' + escapeHtml(messageText).replace(/\n/g, '<br>') + '</div>' +
                '<div class="chat-meta d-flex align-items-center gap-1 justify-content-end mt-1">' +
                    '<span class="chat-time">' + escapeHtml(timeStr) + '</span>' +
                    checkmarkHtml +
                '</div>' +
            '</div>';

        chatBox.appendChild(row);
        scrollToBottom(true);
    }

    function updateSidebarSnippet(text, isMine) {
        if (!partnerId) return;
        var sidebarItem = document.querySelector('.conversation-item[data-user-id="' + partnerId + '"]');
        if (sidebarItem) {
            var snippetEl = sidebarItem.querySelector('.conversation-snippet');
            if (snippetEl) {
                snippetEl.textContent = (isMine ? 'You: ' : '') + text;
                snippetEl.style.fontWeight = isMine ? '400' : '600';
            }
            var timeEl = sidebarItem.querySelector('.conversation-time');
            if (timeEl) {
                timeEl.textContent = 'Just now';
            }
            // Move active conversation to top of sidebar
            var parent = sidebarItem.parentNode;
            if (parent && parent.firstElementChild !== sidebarItem) {
                parent.insertBefore(sidebarItem, parent.firstElementChild);
            }
        }
    }

    function showChatToast(msg, isError) {
        var toast = document.getElementById('chatToastAlert');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'chatToastAlert';
            toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 22px;border-radius:999px;font-size:0.85rem;font-weight:500;z-index:9999;box-shadow:0 6px 20px rgba(0,0,0,0.3);transition:opacity 0.25s,transform 0.25s;pointer-events:none;';
            document.body.appendChild(toast);
        }
        toast.style.background = isError ? 'rgba(239,68,68,0.95)' : 'rgba(16,185,129,0.95)';
        toast.style.color = '#ffffff';
        toast.textContent = msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(10px)';
        }, 3200);
    }

    var isSending = false;
    function sendMessage() {
        if (!sendForm || !msgInput || isSending) return;

        var messageText = msgInput.value.trim();
        if (messageText.length === 0) {
            msgInput.focus();
            return;
        }

        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        var recipientInput = sendForm.querySelector('input[name="recipient_id"]');
        var recipientId = recipientInput ? recipientInput.value : partnerId;

        // Current formatted time for instant optimistic display
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        var minutesStr = minutes < 10 ? '0' + minutes : minutes;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var timeStr = hours + ':' + minutesStr + ' ' + ampm + ' · ' + months[now.getMonth()] + ' ' + now.getDate();

        var originalText = msgInput.value;
        msgInput.value = '';
        adjustInputHeight();

        // Optimistically render bubble
        appendMessageBubble(messageText, timeStr, true, 0);
        updateSidebarSnippet(messageText, true);

        isSending = true;
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.6';
        }

        var formData = new FormData();
        formData.append('recipient_id', recipientId);
        formData.append('message', messageText);
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        fetch('/SocialNetwork/public/?url=messages/send', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, status: res.status, data: data };
            });
        })
        .then(function (res) {
            if (!res.ok || !res.data || !res.data.success) {
                var err = res.data && res.data.message ? res.data.message : 'Failed to send message.';
                throw new Error(err);
            }
            if (res.data.message && res.data.message.id) {
                var newId = parseInt(res.data.message.id, 10);
                if (newId > lastMsgId) {
                    lastMsgId = newId;
                    if (chatBox) chatBox.setAttribute('data-last-id', lastMsgId);
                }
            }
        })
        .catch(function (err) {
            console.error('Send message error:', err);
            showChatToast(err.message || 'Unable to send message. Please try again.', true);
            msgInput.value = originalText;
            adjustInputHeight();
        })
        .finally(function () {
            isSending = false;
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.style.opacity = '';
            }
            if (msgInput) {
                msgInput.disabled = false;
                msgInput.focus();
            }
        });
    }

    if (sendForm) {
        sendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            sendMessage();
        });
    }

    // ── 5. Real-time Incoming Message Polling (Non-blocking) ──
    if (partnerId > 0 && chatBox) {
        setInterval(function () {
            if (document.hidden) return; // Skip when browser tab is inactive

            fetch('/SocialNetwork/public/?url=messages/fetch&partner_id=' + encodeURIComponent(partnerId) + '&last_id=' + encodeURIComponent(lastMsgId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                if (!res.ok) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success || !Array.isArray(data.messages)) return;
                data.messages.forEach(function (m) {
                    var mId = parseInt(m.id, 10);
                    if (mId > lastMsgId) {
                        lastMsgId = mId;
                        chatBox.setAttribute('data-last-id', lastMsgId);

                        var isMine = parseInt(m.sender_id, 10) === myUserId;
                        if (!isMine) {
                            appendMessageBubble(m.message, m.formatted_time || '', false, mId);
                            updateSidebarSnippet(m.message, false);
                        }
                    }
                });
            })
            .catch(function () {
                // Silently ignore network polling errors to keep UI fluid
            });
        }, 4000);
    }
});
</script>
