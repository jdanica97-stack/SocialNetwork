<?php

/**
 * app/views/profile/edit.php
 *
 * Profile editing form — redesigned with monochrome glassmorphism.
 *
 * Variables provided by ProfileController:
 *   $user       array   — current user data (used to pre-fill the form)
 *   $errors     array   — validation errors (may be empty)
 *   $pageTitle  string  — page <title>
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

// Helper: current avatar src for preview
$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';

if (!empty($user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    $initials  = rawurlencode($user['full_name']);
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials
               . '&size=160&background=111113&color=f5f5f7&rounded=true&bold=true';
}
?>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 580px;">

        <!-- ── Page Heading ──────────────────────────────────────────────── -->
        <div class="edit-header fade-up">
            <img
                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                alt="Current profile picture"
                class="edit-header-avatar"
            >
            <div>
                <h1 class="edit-title">Edit Profile</h1>
                <p class="edit-username mb-0">
                    @<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>

        <!-- ── Validation Errors ─────────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger fade-up visible" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ── Edit Form Glass Card ──────────────────────────────────────── -->
        <div class="card glass fade-up">
            <div class="card-body" style="padding: 32px !important;">
                <form
                    method="POST"
                    action="/SocialNetwork/public/?url=profile/update"
                    enctype="multipart/form-data"
                    novalidate
                >

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label">
                            Full Name <span style="color:var(--fg-faint)">*</span>
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
                        <label for="bio" class="form-label">
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
                        <label for="profile_image" class="form-label">
                            Profile Picture <span class="text-muted fw-normal">(optional)</span>
                        </label>

                        <!-- Preview avatar -->
                        <div class="mb-2">
                            <img
                                id="imagePreview"
                                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Current profile picture"
                                class="rounded-circle border"
                                style="width: 72px; height: 72px; object-fit: cover;"
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

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                        <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <p class="edit-info-note">
            Username cannot be changed. Password changes are not available here.
        </p>

    </div>
</div>

<!-- Inline script for character counter and live image preview -->
<script>
(function () {
    'use strict';

    var bioTextarea  = document.getElementById('bio');
    var bioCharCount = document.getElementById('bioCharCount');

    function updateBioCount() {
        if (!bioTextarea || !bioCharCount) return;
        var len = bioTextarea.value.length;
        bioCharCount.textContent = len + ' / 500';
    }

    if (bioTextarea) {
        updateBioCount();
        bioTextarea.addEventListener('input', updateBioCount);
    }

    var fileInput  = document.getElementById('profile_image');
    var imgPreview = document.getElementById('imagePreview');

    if (fileInput && imgPreview) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.src = e.target.result;
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
    }
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
