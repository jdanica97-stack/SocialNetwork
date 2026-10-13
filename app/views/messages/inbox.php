<?php
/**
 * app/views/messages/inbox.php
 *
 * Direct Messaging — Conversations Inbox View
 *
 * Variables passed from MessageController::index():
 *   - $conversations: array of conversation summaries
 *   - $contactList: array of other community members
 *   - $flashSuccess: string|null
 *   - $flashError: string|null
 */
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 800px;">

        <!-- Header / Navigation Bar -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <a href="/SocialNetwork/public/" class="btn btn-ghost btn-sm mb-2 d-inline-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back to Newsfeed
                </a>
                <h1 class="h3 mb-0" style="font-weight: 700; color: var(--fg); letter-spacing: -0.02em;">
                    Direct Messages
                </h1>
                <p class="small text-muted mb-0 mt-1">
                    Stay connected with community members through real-time private conversations.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newChatModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New Message
                </button>
            </div>
        </div>

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

        <!-- Conversations List Card -->
        <div class="card glass fade-up mb-4 overflow-hidden">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3 px-4 border-bottom" style="border-color: var(--border) !important;">
                <span style="font-weight: 600; color: var(--fg); font-size: 0.95rem;">Conversations</span>
                <span class="badge rounded-pill bg-secondary" style="font-size: 0.75rem;"><?= count($conversations) ?> Active</span>
            </div>

            <div class="card-body p-0">
                <?php if (empty($conversations)): ?>
                    <div class="text-center py-5 px-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; background: var(--surface-hover); color: var(--fg-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <h2 class="h5 mb-1" style="font-weight: 600; color: var(--fg);">No conversations yet</h2>
                        <p class="text-muted small mb-3">Start a private chat with any community member.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newChatModal">
                            Find someone to message
                        </button>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($conversations as $conv): ?>
                            <?php
                                $otherId     = (int) $conv['other_user_id'];
                                $fullName    = htmlspecialchars($conv['full_name'], ENT_QUOTES, 'UTF-8');
                                $username    = htmlspecialchars($conv['username'], ENT_QUOTES, 'UTF-8');
                                $preview     = htmlspecialchars($conv['latest_message'], ENT_QUOTES, 'UTF-8');
                                $unreadCount = (int) $conv['unread_count'];
                                $isMine      = ((int)$conv['latest_sender_id'] === (int)$_SESSION['user_id']);
                                $timeStr     = date('M j, g:i A', strtotime($conv['latest_message_time']));
                                if (!empty($conv['profile_image'])) {
                                    $img = ltrim($conv['profile_image'], '/');
                                    $avatarUrl = str_starts_with($img, 'assets/') ? '/SocialNetwork/public/' . $img : '/SocialNetwork/public/assets/images/profiles/' . $img;
                                } else {
                                    $avatarUrl = '/SocialNetwork/public/assets/images/default-avatar.svg';
                                }
                            ?>
                            <a href="/SocialNetwork/public/?url=messages/chat&user_id=<?= $otherId ?>"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-4 conversation-item <?= $unreadCount > 0 ? 'conversation-unread' : '' ?>"
                               style="background: transparent; border-color: var(--border); transition: background var(--transition); text-decoration: none;">
                                
                                <div class="position-relative flex-shrink-0">
                                    <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= $fullName ?>"
                                         class="rounded-circle object-fit-cover shadow-sm"
                                         style="width: 48px; height: 48px; border: 2px solid var(--surface-hover);"
                                         onerror="this.src='/SocialNetwork/public/assets/images/default-avatar.svg'">
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                            <?= $unreadCount ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <span style="font-weight: <?= $unreadCount > 0 ? '700' : '600' ?>; color: var(--fg); font-size: 0.95rem;">
                                                <?= $fullName ?>
                                            </span>
                                            <span class="text-muted small">@<?= $username ?></span>
                                        </div>
                                        <span class="text-muted small flex-shrink-0 ms-2" style="font-size: 0.75rem;">
                                            <?= $timeStr ?>
                                        </span>
                                    </div>
                                    <div class="text-truncate small <?= $unreadCount > 0 ? 'fw-bold text-body' : 'text-muted' ?>" style="max-width: 90%;">
                                        <?php if ($isMine): ?>
                                            <span class="text-muted fw-normal">You: </span>
                                        <?php endif; ?>
                                        <?= $preview ?>
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

<!-- Modal: New Conversation Contact Picker -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass shadow-lg" style="border-radius: var(--radius-card); border: 1px solid var(--border);">
            <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                <h5 class="modal-title h6 mb-0" id="newChatModalLabel" style="font-weight: 700; color: var(--fg);">
                    Start a New Conversation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <input type="text"
                           id="contactSearchInput"
                           class="form-control form-control-sm"
                           placeholder="Filter members by name or @username..."
                           style="border-radius: var(--radius-pill); background: var(--surface-input); border: 1px solid var(--border); color: var(--fg); padding: 8px 16px;">
                </div>

                <?php if (empty($contactList)): ?>
                    <p class="text-muted text-center py-3 small mb-0">No other users registered yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="contactPickerList">
                        <?php foreach ($contactList as $c): ?>
                            <?php
                                $cId     = (int) $c['id'];
                                $cName   = htmlspecialchars($c['full_name'], ENT_QUOTES, 'UTF-8');
                                $cUser   = htmlspecialchars($c['username'], ENT_QUOTES, 'UTF-8');
                                if (!empty($c['profile_image'])) {
                                    $img = ltrim($c['profile_image'], '/');
                                    $cAvatar = str_starts_with($img, 'assets/') ? '/SocialNetwork/public/' . $img : '/SocialNetwork/public/assets/images/profiles/' . $img;
                                } else {
                                    $cAvatar = '/SocialNetwork/public/assets/images/default-avatar.svg';
                                }
                            ?>
                            <a href="/SocialNetwork/public/?url=messages/chat&user_id=<?= $cId ?>"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded-3 mb-1 contact-picker-item"
                               data-search="<?= strtolower($cName . ' ' . $cUser) ?>"
                               style="background: transparent; color: var(--fg); transition: background var(--transition);">
                                <img src="<?= htmlspecialchars($cAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= $cName ?>"
                                     class="rounded-circle object-fit-cover flex-shrink-0"
                                     style="width: 38px; height: 38px; border: 1px solid var(--border);"
                                     onerror="this.src='/SocialNetwork/public/assets/images/default-avatar.svg'">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate small" style="color: var(--fg);"><?= $cName ?></div>
                                    <div class="text-muted text-truncate" style="font-size: 0.75rem;">@<?= $cUser ?></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted flex-shrink-0"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
    const searchInput = document.getElementById('contactSearchInput');
    const items = document.querySelectorAll('.contact-picker-item');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            items.forEach(function (item) {
                const searchData = item.getAttribute('data-search') || '';
                item.style.display = searchData.includes(query) ? 'flex' : 'none';
            });
        });
    }
});
</script>
