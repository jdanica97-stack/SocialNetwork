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
                    <?= Csrf::field() ?>

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

                        <input type="hidden" id="profile_image_data" name="profile_image_data" value="">
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
                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
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
            Full name, username, and password can be changed from Manage Account on your <a href="/SocialNetwork/public/?url=profile">profile page</a>.
        </p>

    </div>
</div>

<!-- ── Avatar Cropper Modal ─────────────────────────────────────────── -->
<div id="avatarCropModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.75);align-items:center;justify-content:center;" role="dialog" aria-modal="true" aria-label="Crop profile picture">
    <div style="background:var(--surface-card);border-radius:16px;padding:24px;max-width:520px;width:95%;max-height:90vh;overflow:auto;box-shadow:0 12px 32px rgba(0,0,0,0.35);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Crop Profile Picture</h3>
            <button type="button" id="avatarCropClose" style="background:none;border:none;cursor:pointer;color:var(--fg-muted);font-size:1.3rem;line-height:1;" aria-label="Close">&times;</button>
        </div>
        <div style="max-height:380px;overflow:hidden;border-radius:8px;background:#000;">
            <img id="avatarCropImage" src="" alt="Profile picture to crop" style="max-width:100%;display:block;">
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button type="button" id="avatarCropCancel" class="btn btn-ghost btn-sm">Cancel</button>
            <button type="button" id="avatarCropApply" class="btn btn-primary btn-sm">Crop &amp; Apply</button>
        </div>
    </div>
</div>

<!-- Cropper.js CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" integrity="sha512-hvNR0F/e2J7zPPfLC9auFe3/zuAVFs9mTeKgMNyHOKa74hjFO3UfqYEDCGKOgSK0E4QQgmXX2REkEAqMBzqMQ==" crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" integrity="sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3WyfVF09uAuXNcqe+AHKnicS5GuFNQ==" crossorigin="anonymous"></script>

<!-- Inline script for character counter and Cropper.js live image preview -->
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

    var fileInput       = document.getElementById('profile_image');
    var hiddenDataInput = document.getElementById('profile_image_data');
    var imgPreview      = document.getElementById('imagePreview');
    var modal           = document.getElementById('avatarCropModal');
    var cropImage       = document.getElementById('avatarCropImage');
    var closeBtn        = document.getElementById('avatarCropClose');
    var cancelBtn       = document.getElementById('avatarCropCancel');
    var applyBtn        = document.getElementById('avatarCropApply');
    var cropper         = null;

    function openModal() {
        if (modal) modal.style.display = 'flex';
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (cropImage) cropImage.src = '';
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG, PNG, GIF, and WebP images are allowed.');
                this.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Selected image must be 10 MB or smaller.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                if (cropImage) cropImage.src = e.target.result;
                openModal();

                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                // 1:1 square crop for profile avatar
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: true,
                    cropBoxResizable: true,
                });
            };
            reader.readAsDataURL(file);
        });
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            if (!cropper) return;

            var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
            if (!canvas) {
                alert('Failed to crop image.');
                return;
            }

            var base64Data = canvas.toDataURL('image/jpeg', 0.9);
            if (hiddenDataInput) {
                hiddenDataInput.value = base64Data;
            }
            if (imgPreview) {
                imgPreview.src = base64Data;
            }

            closeModal();
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && modal && modal.style.display === 'flex') {
            closeModal();
        }
    });
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
