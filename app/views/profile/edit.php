<?php

/**
 * app/views/profile/edit.php
 *
 * Profile editing form.
 *
 * Variables provided by ProfileController::showEdit() and ProfileController::update():
 *   $user       array   — current user data (used to pre-fill the form)
 *   $errors     array   — validation errors (may be empty)
 *   $pageTitle  string  — page <title>
 *
 * Rules:
 *   - No SQL queries here.
 *   - No business logic here.
 *   - All output is escaped with htmlspecialchars() before rendering.
 *   - enctype="multipart/form-data" is required for file uploads.
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

// ── Current avatar src (for the preview) ─────────────────────────────────────
$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';

if (!empty($user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    $initials  = rawurlencode($user['full_name']);
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials
               . '&size=160&background=0d6efd&color=fff&rounded=true&bold=true';
}
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <!-- ── Page Heading ──────────────────────────────────────────────── -->
        <div class="d-flex align-items-center mb-4 gap-3">
            <img
                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                alt="Current profile picture"
                class="rounded-circle border shadow-sm"
                style="width: 64px; height: 64px; object-fit: cover;"
            >
            <div>
                <h2 class="mb-0 fw-bold">Edit Profile</h2>
                <p class="text-muted mb-0 small">
                    @<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>

        <!-- ── Validation Errors ─────────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Edit Form ─────────────────────────────────────────────────── -->
        <!--
            enctype="multipart/form-data" is REQUIRED for file uploads.
            Without it, $_FILES will always be empty.
        -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form
                    method="POST"
                    action="/SocialNetwork/public/?url=profile/update"
                    enctype="multipart/form-data"
                    novalidate
                >

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            maxlength="100"
                            placeholder="Your full name"
                            value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                            autocomplete="name"
                        >
                        <div class="form-text">Maximum 100 characters.</div>
                    </div>

                    <!-- Bio -->
                    <div class="mb-3">
                        <label for="bio" class="form-label fw-semibold">
                            Bio <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="bio"
                            name="bio"
                            rows="4"
                            maxlength="500"
                            placeholder="Tell others a little about yourself…"
                        ><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="d-flex justify-content-between">
                            <div class="form-text">Maximum 500 characters.</div>
                            <div class="form-text" id="bioCharCount">0 / 500</div>
                        </div>
                    </div>

                    <!-- Profile Picture -->
                    <div class="mb-4">
                        <label for="profile_image" class="form-label fw-semibold">
                            Profile Picture <span class="text-muted fw-normal">(optional)</span>
                        </label>

                        <!-- Current picture preview -->
                        <div class="mb-2">
                            <img
                                id="imagePreview"
                                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Current profile picture"
                                class="rounded-circle border"
                                style="width: 80px; height: 80px; object-fit: cover;"
                            >
                        </div>

                        <input
                            type="file"
                            class="form-control"
                            id="profile_image"
                            name="profile_image"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <div class="form-text">
                            Accepted: JPEG, PNG, GIF, WebP &mdash; maximum 2 MB.
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                        <a href="/SocialNetwork/public/?url=profile"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- ── Read-only info note ────────────────────────────────────────── -->
        <p class="text-muted small text-center mt-3">
            <i class="bi bi-info-circle me-1"></i>
            Username cannot be changed. Password changes are not available here.
        </p>

    </div>
</div>

<!-- ── Inline JS: live preview + character counter ─────────────────────────── -->
<script>
(function () {
    'use strict';

    // ── Bio character counter ──────────────────────────────────────────────
    var bioTextarea  = document.getElementById('bio');
    var bioCharCount = document.getElementById('bioCharCount');

    function updateBioCount() {
        var len = bioTextarea.value.length;
        bioCharCount.textContent = len + ' / 500';
        bioCharCount.classList.toggle('text-danger', len > 500);
    }

    updateBioCount(); // initialise on page load
    bioTextarea.addEventListener('input', updateBioCount);

    // ── Image live preview ─────────────────────────────────────────────────
    var fileInput   = document.getElementById('profile_image');
    var imgPreview  = document.getElementById('imagePreview');

    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imgPreview.src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
