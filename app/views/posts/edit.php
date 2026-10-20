<?php
/**
 * app/views/posts/edit.php
 *
 * Edit Post View
 *
 * Receives from PostController::edit():
 *   - $post: array representing the post being edited
 */

$currentImages = !empty($post['images']) && is_array($post['images'])
    ? $post['images']
    : (!empty($post['image']) ? [$post['image']] : []);
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 640px;">
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
                <form method="POST" action="/SocialNetwork/public/?url=posts/update" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">

                    <div class="mb-3">
                        <label for="post_content" class="form-label" style="font-weight: 600;">Post Content</label>
                        <textarea id="post_content" name="content" class="form-control" rows="4" maxlength="5000" required><?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Current Post Images (if any) -->
                    <?php if (!empty($currentImages)): ?>
                        <div class="mb-3">
                            <label class="form-label d-block" style="font-weight: 600;">Current Photos</label>
                            <span class="small text-muted d-block mb-2">Uncheck any photo you wish to remove:</span>
                            <div class="d-flex flex-wrap gap-3">
                                <?php foreach ($currentImages as $idx => $img): ?>
                                    <div class="card p-2 text-center" style="background: var(--surface-input); border: 1px solid var(--border); width: 120px;">
                                        <img
                                            src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="Post attachment"
                                            class="rounded mb-2"
                                            style="width: 100%; height: 90px; object-fit: cover;"
                                        >
                                        <div class="form-check form-check-inline m-0 justify-content-center">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="keep_images[]"
                                                value="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                                id="keep_img_<?= $idx ?>"
                                                checked
                                            >
                                            <label class="form-check-label small" for="keep_img_<?= $idx ?>">Keep</label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Add More Photos -->
                    <div class="mb-4">
                        <label for="editPostImages" class="form-label" style="font-weight: 600;">Add More Photos</label>
                        <input
                            type="file"
                            id="editPostImages"
                            name="images[]"
                            multiple
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="form-control"
                        >
                        <span class="small text-muted mt-1 d-block">You can select multiple images (JPG, PNG, GIF, WebP up to 5 MB each).</span>
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
