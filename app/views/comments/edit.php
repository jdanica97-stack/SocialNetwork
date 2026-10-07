<?php
/**
 * app/views/comments/edit.php
 *
 * Edit comment view — Step 8 Comments CRUD.
 *
 * Variables provided by CommentController::edit():
 *   $comment   array  — Associative array with id, post_id, content, created_at, username, full_name
 *   $errors    array  — Array of validation errors (if any)
 *   $pageTitle string — Document title
 */

require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 600px;">

        <!-- ── Navigation breadcrumb ─────────────────────────────────────── -->
        <div class="mb-3">
            <a href="/SocialNetwork/public/#post-<?= (int) $comment['post_id'] ?>" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Post
            </a>
        </div>

        <!-- ── Validation errors ─────────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger fade-up visible" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Glass card container ──────────────────────────────────────── -->
        <div class="card glass fade-up">
            <div class="card-header">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Comment
            </div>
            <div class="card-body" style="padding: 28px !important;">

                <form method="POST" action="/SocialNetwork/public/?url=comments/update" novalidate>
                    <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">

                    <div class="mb-3">
                        <label for="comment_content" class="form-label">
                            Your Comment <span style="color:var(--fg-faint)">*</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="comment_content"
                            name="content"
                            rows="4"
                            maxlength="1000"
                            placeholder="Write your comment..."
                            required
                        ><?= htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="form-text">Maximum 1000 characters.</span>
                            <span class="form-text" id="commentCharCount">0 / 1000</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                        <a href="/SocialNetwork/public/#post-<?= (int) $comment['post_id'] ?>" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
(function () {
    'use strict';
    var textarea  = document.getElementById('comment_content');
    var charCount = document.getElementById('commentCharCount');

    function updateCount() {
        if (!textarea || !charCount) return;
        charCount.textContent = textarea.value.length + ' / 1000';
    }

    if (textarea) {
        updateCount();
        textarea.addEventListener('input', updateCount);
    }
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
