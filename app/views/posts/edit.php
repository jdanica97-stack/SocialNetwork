<?php
/**
 * app/views/posts/edit.php
 *
 * Edit Post View
 *
 * Receives from PostController::edit():
 *   - $post: array representing the post being edited
 */
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 600px;">
        <div class="mb-3">
            <a href="/SocialNetwork/public/#post-<?= (int) $post['id'] ?>" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Newsfeed
            </a>
        </div>
        <div class="card glass fade-up">
            <div class="card-header">
                <i class="bi bi-pencil-square me-2"></i>Edit Post
            </div>
            <div class="card-body" style="padding: 28px !important;">
                <form method="POST" action="/SocialNetwork/public/?url=posts/update">
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <div class="mb-3">
                        <label for="post_content" class="form-label">Post Content</label>
                        <textarea id="post_content" name="content" class="form-control" rows="4" maxlength="5000" required><?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/SocialNetwork/public/#post-<?= (int) $post['id'] ?>" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
