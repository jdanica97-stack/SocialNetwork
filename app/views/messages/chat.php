<?php
/**
 * app/views/messages/chat.php
 *
 * Direct Messaging — 1-on-1 Conversation View
 *
 * Variables passed from MessageController::chat():
 *   - $otherUser: array of other user's record
 *   - $messages: array of message records between current user and other user
 *   - $conversations: array of user's active conversations
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */

$otherUserId   = (int) $otherUser['id'];
$otherFullName = htmlspecialchars($otherUser['full_name'], ENT_QUOTES, 'UTF-8');
$otherUsername = htmlspecialchars($otherUser['username'], ENT_QUOTES, 'UTF-8');
$otherBio      = htmlspecialchars($otherUser['bio'] ?? '', ENT_QUOTES, 'UTF-8');
if (!empty($otherUser['profile_image'])) {
    $img = ltrim($otherUser['profile_image'], '/');
    $otherAvatar = str_starts_with($img, 'assets/') ? '/SocialNetwork/public/' . $img : '/SocialNetwork/public/assets/images/profiles/' . $img;
} else {
    $otherAvatar = '/SocialNetwork/public/assets/images/default-avatar.svg';
}

$currentUserId = (int) $_SESSION['user_id'];
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 800px;">

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show glass mb-3" role="alert">
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show glass mb-3" role="alert">
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Chat Main Container Card -->
        <div class="card glass fade-up chat-main-card shadow-sm">
            
            <!-- Chat Header -->
            <div class="chat-header d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--border) !important;">
                <div class="d-flex align-items-center gap-3">
                    <a href="/SocialNetwork/public/?url=messages" class="btn btn-ghost btn-sm p-1 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Back to Inbox">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    </a>
                    <a href="/SocialNetwork/public/?url=profile&id=<?= $otherUserId ?>" class="d-flex align-items-center gap-2 text-decoration-none" style="color: var(--fg);">
                        <img src="<?= htmlspecialchars($otherAvatar, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= $otherFullName ?>"
                             class="rounded-circle object-fit-cover shadow-sm"
                             style="width: 42px; height: 42px; border: 2px solid var(--surface-hover);"
                             onerror="this.src='/SocialNetwork/public/assets/images/default-avatar.svg'">
                        <div>
                            <div class="fw-bold lh-sm text-truncate" style="max-width: 220px; font-size: 0.95rem; color: var(--fg);"><?= $otherFullName ?></div>
                            <div class="text-muted small lh-sm">@<?= $otherUsername ?></div>
                        </div>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="/SocialNetwork/public/?url=profile&id=<?= $otherUserId ?>" class="btn btn-ghost btn-sm" title="View Profile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="d-none d-sm-inline ms-1">Profile</span>
                    </a>
                </div>
            </div>

            <!-- Chat Messages Log (Scrollable Area) -->
            <div class="chat-messages-body p-3 p-md-4" id="chatMessagesContainer" style="height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                <?php if (empty($messages)): ?>
                    <div class="text-center my-auto py-5" id="chatEmptyState">
                        <img src="<?= htmlspecialchars($otherAvatar, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= $otherFullName ?>"
                             class="rounded-circle mb-3 shadow-sm"
                             style="width: 72px; height: 72px; object-fit: cover; border: 2px solid var(--border);"
                             onerror="this.src='/SocialNetwork/public/assets/images/default-avatar.svg'">
                        <h2 class="h6 mb-1" style="font-weight: 700; color: var(--fg);"><?= $otherFullName ?></h2>
                        <p class="text-muted small mb-3">@<?= $otherUsername ?><?= !empty($otherBio) ? ' · ' . $otherBio : '' ?></p>
                        <p class="small text-muted mb-0" style="background: var(--surface-hover); display: inline-block; padding: 6px 16px; border-radius: var(--radius-pill); border: 1px solid var(--border);">
                            This is the beginning of your conversation. Send a message to say hello!
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php
                            $msgSenderId = (int) $msg['sender_id'];
                            $isMine      = ($msgSenderId === $currentUserId);
                            $msgText     = htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8');
                            $msgTime     = date('g:i A', strtotime($msg['created_at']));
                        ?>
                        <div class="d-flex flex-column <?= $isMine ? 'align-items-end' : 'align-items-start' ?> chat-message-row" data-message-id="<?= (int)$msg['id'] ?>">
                            <div class="chat-bubble <?= $isMine ? 'chat-bubble-mine' : 'chat-bubble-theirs' ?>">
                                <?= nl2br($msgText) ?>
                            </div>
                            <span class="chat-bubble-time small text-muted mt-1 px-1" style="font-size: 0.70rem;">
                                <?= $msgTime ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Footer / Send Input Form -->
            <div class="chat-footer p-3 border-top" style="border-color: var(--border) !important; background: var(--surface);">
                <form id="chatSendForm" method="POST" action="/SocialNetwork/public/?url=messages/send" class="d-flex align-items-center gap-2">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="receiver_id" id="chatReceiverId" value="<?= $otherUserId ?>">

                    <div class="flex-grow-1 position-relative">
                        <textarea
                            name="message"
                            id="chatInputMessage"
                            class="form-control form-control-sm chat-input-field"
                            rows="1"
                            maxlength="2000"
                            placeholder="Write a message to <?= $otherFullName ?>..."
                            required
                            style="resize: none; border-radius: var(--radius-input); padding: 10px 16px; min-height: 44px; max-height: 120px; line-height: 1.4; background: var(--surface-input); color: var(--fg); border: 1px solid var(--border);"
                        ></textarea>
                    </div>

                    <button type="submit" id="chatSubmitBtn" class="btn btn-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; border-radius: 50%; cursor: pointer;" title="Send message" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('chatMessagesContainer');
    const form      = document.getElementById('chatSendForm');
    const input     = document.getElementById('chatInputMessage');
    const submitBtn = document.getElementById('chatSubmitBtn');
    const receiverId = <?= $otherUserId ?>;
    const currentUserId = <?= $currentUserId ?>;

    // Scroll chat to bottom initially
    function scrollToBottom() {
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }
    scrollToBottom();

    // Auto-expand textarea
    if (input) {
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Submit on Enter (without Shift)
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });
    }

    // Handle AJAX Send Message
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const text = input.value.trim();
            if (!text) {
                input.focus();
                return;
            }

            const formData = new FormData(form);

            // Optimistic / loading state
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            fetch('/SocialNetwork/public/?url=messages/send', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => {
                        throw new Error(errData.message || 'Server error ' + res.status);
                    }).catch(() => {
                        throw new Error('Server returned error status ' + res.status);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (data.success) {
                    // Remove empty state if present
                    const emptyState = document.getElementById('chatEmptyState');
                    if (emptyState) emptyState.remove();

                    // Append sent message bubble
                    const row = document.createElement('div');
                    row.className = 'd-flex flex-column align-items-end chat-message-row';
                    row.setAttribute('data-message-id', data.message_id);

                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble chat-bubble-mine';
                    bubble.innerHTML = data.message.replace(/\n/g, '<br>');

                    const time = document.createElement('span');
                    time.className = 'chat-bubble-time small text-muted mt-1 px-1';
                    time.style.fontSize = '0.70rem';
                    time.textContent = data.created_at;

                    row.appendChild(bubble);
                    row.appendChild(time);
                    container.appendChild(row);

                    input.value = '';
                    input.style.height = '44px';
                    input.focus();
                    scrollToBottom();
                } else {
                    alert(data.message || 'Error sending message.');
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error('Send message error:', err);
                // Fallback to standard form submission if AJAX fails
                form.submit();
            });
        });
    }

    // Polling for incoming messages every 3.5 seconds
    let isPolling = false;
    function pollMessages() {
        if (isPolling) return;
        isPolling = true;

        fetch(`/SocialNetwork/public/?url=messages/poll&user_id=${receiverId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            isPolling = false;
            if (data.success && Array.isArray(data.messages)) {
                const existingIds = new Set(
                    Array.from(document.querySelectorAll('.chat-message-row')).map(el => parseInt(el.getAttribute('data-message-id')))
                );

                let hasNew = false;
                data.messages.forEach(msg => {
                    if (!existingIds.has(msg.id)) {
                        hasNew = true;
                        const emptyState = document.getElementById('chatEmptyState');
                        if (emptyState) emptyState.remove();

                        const row = document.createElement('div');
                        row.className = `d-flex flex-column ${msg.is_mine ? 'align-items-end' : 'align-items-start'} chat-message-row`;
                        row.setAttribute('data-message-id', msg.id);

                        const bubble = document.createElement('div');
                        bubble.className = `chat-bubble ${msg.is_mine ? 'chat-bubble-mine' : 'chat-bubble-theirs'}`;
                        bubble.innerHTML = msg.message.replace(/\n/g, '<br>');

                        const time = document.createElement('span');
                        time.className = 'chat-bubble-time small text-muted mt-1 px-1';
                        time.style.fontSize = '0.70rem';
                        time.textContent = msg.created_at;

                        row.appendChild(bubble);
                        row.appendChild(time);
                        container.appendChild(row);
                    }
                });

                if (hasNew) {
                    scrollToBottom();
                }
            }
        })
        .catch(err => {
            isPolling = false;
        });
    }

    const pollInterval = setInterval(pollMessages, 3500);

    // Stop polling if page unloaded
    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
    });
});
</script>
