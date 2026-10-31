<?php

/**
 * app/views/profile/edit.php
 *
 * Profile editing form with interactive Cropper.js for both Profile Picture and Cover Photo.
 *
 * Variables provided by ProfileController:
 *   $user       array   — current user data (used to pre-fill the form)
 *   $errors     array   — validation errors (may be empty)
 *   $pageTitle  string  — page <title>
 */

require_once BASE_PATH . '/app/views/layouts/header.php';

// Helper paths
$profileImageWebPath = '/SocialNetwork/public/assets/images/profiles/';

if (!empty($user['profile_image']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $user['profile_image'])) {
    $avatarSrc = $profileImageWebPath . rawurlencode($user['profile_image']);
} else {
    $initials  = rawurlencode($user['full_name']);
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . $initials
               . '&size=160&background=111113&color=f5f5f7&rounded=true&bold=true';
}

$coverPhotoSrc = null;
if (!empty($user['cover_photo']) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $user['cover_photo'])) {
    $coverPhotoSrc = $profileImageWebPath . rawurlencode($user['cover_photo']);
}
?>

<!-- Local Cropper.js Stylesheet -->
<link rel="stylesheet" href="/SocialNetwork/public/assets/css/cropper.min.css">

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 640px;">

        <!-- ── Page Heading ──────────────────────────────────────────────── -->
        <div class="edit-header fade-up mb-4">
            <img
                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                alt="Current profile picture"
                class="edit-header-avatar"
                id="headerAvatarThumbnail"
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
        <div class="card glass fade-up mb-4">
            <div class="card-body" style="padding: 28px !important;">
                <form
                    method="POST"
                    action="/SocialNetwork/public/?url=profile/update"
                    enctype="multipart/form-data"
                    id="profileEditForm"
                    novalidate
                >
                    <?= Csrf::field() ?>

                    <!-- 1. Cover Photo Section -->
                    <div class="mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                        <label class="form-label fw-semibold" style="color: var(--fg); font-size: 0.95rem;">
                            Profile Cover Photo
                        </label>
                        <p class="small text-muted mb-2" style="font-size: 0.8rem;">
                            Choose a cover banner to display behind your profile header.
                        </p>

                        <!-- Cover Preview Banner -->
                        <div class="position-relative rounded-3 overflow-hidden mb-2" id="coverPreviewWrap" style="height: 140px; background: var(--surface-hover); border: 1px solid var(--border);">
                            <img
                                id="coverImagePreview"
                                src="<?= htmlspecialchars($coverPhotoSrc ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                alt="Cover photo preview"
                                style="width: 100%; height: 100%; object-fit: cover; display: <?= $coverPhotoSrc ? 'block' : 'none' ?>;"
                            >
                            <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted" style="display: <?= $coverPhotoSrc ? 'none' : 'flex' ?> !important;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span class="small mt-1" style="font-size: 0.75rem;">No cover photo set</span>
                            </div>
                        </div>

                        <!-- Hidden Cropped Data & File Input -->
                        <input type="hidden" id="cover_photo_data" name="cover_photo_data" value="">
                        <input
                            type="file"
                            class="form-control form-control-sm"
                            id="cover_photo"
                            name="cover_photo"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <div class="form-text" style="font-size: 0.76rem;">
                            Accepted: JPEG, PNG, GIF, WebP &mdash; maximum 10 MB. High-resolution photos can be cropped and adjusted before saving.
                        </div>
                    </div>

                    <!-- 2. Profile Picture Section -->
                    <div class="mb-4 pb-3 border-bottom" style="border-color: var(--border) !important;">
                        <label class="form-label fw-semibold" style="color: var(--fg); font-size: 0.95rem;">
                            Profile Picture
                        </label>
                        <p class="small text-muted mb-2" style="font-size: 0.8rem;">
                            Your avatar image is displayed next to your posts, comments, and profile.
                        </p>

                        <div class="d-flex align-items-center gap-3 mb-2">
                            <!-- Preview avatar -->
                            <img
                                id="imagePreview"
                                src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Profile picture preview"
                                class="rounded-circle"
                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid var(--border); box-shadow: var(--shadow-sm);"
                            >
                            <div class="flex-grow-1">
                                <input type="hidden" id="profile_image_data" name="profile_image_data" value="">
                                <input
                                    type="file"
                                    class="form-control form-control-sm"
                                    id="profile_image"
                                    name="profile_image"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                >
                                <div class="form-text" style="font-size: 0.76rem;">
                                    Accepted: JPEG, PNG, GIF, WebP &mdash; maximum 2 MB. Square 1:1 circular crop.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Bio Section -->
                    <div class="mb-4">
                        <label for="bio" class="form-label fw-semibold" style="color: var(--fg); font-size: 0.95rem;">
                            Bio <span class="text-muted fw-normal" style="font-size: 0.8rem;">(optional)</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="bio"
                            name="bio"
                            rows="4"
                            maxlength="500"
                            placeholder="Tell others a little about yourself…"
                            style="background: var(--surface-input); border-color: var(--border); color: var(--fg);"
                        ><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text" style="font-size: 0.76rem;">Maximum 500 characters.</div>
                            <div class="form-text" id="bioCharCount" style="font-size: 0.76rem;">0 / 500</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary px-4" id="saveProfileBtn">
                            Save Changes
                        </button>
                        <a href="/SocialNetwork/public/?url=profile" class="btn btn-ghost px-3">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <p class="edit-info-note small text-muted text-center">
            Full name, username, and password can be updated from Manage Account on your <a href="/SocialNetwork/public/?url=profile" class="text-decoration-underline" style="color: var(--fg);">profile page</a>.
        </p>

    </div>
</div>

<!-- ── 1. Avatar Cropper Modal ─────────────────────────────────────────── -->
<div id="avatarCropModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.78);align-items:center;justify-content:center;backdrop-filter:blur(6px);" role="dialog" aria-modal="true" aria-label="Crop profile picture">
    <div class="cropper-modal-dialog" style="background:var(--surface-card);border-radius:18px;padding:22px;max-width:540px;width:95%;max-height:92vh;overflow:auto;box-shadow:0 18px 48px rgba(0,0,0,0.45);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg);"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/></svg>
                <h3 style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Crop Profile Picture</h3>
            </div>
            <button type="button" id="avatarCropClose" style="background:none;border:none;cursor:pointer;color:var(--fg-muted);font-size:1.4rem;line-height:1;" aria-label="Close">&times;</button>
        </div>

        <div class="crop-image-container cropper-circular" style="max-height:360px;overflow:hidden;border-radius:10px;background:#0d0d10;">
            <img id="avatarCropImage" src="" alt="Profile picture to crop" style="max-width:100%;display:block;">
        </div>

        <!-- Zoom & Positioning Toolbar -->
        <div class="cropper-toolbar">
            <button type="button" class="cropper-tool-btn" id="avatarZoomIn" title="Zoom In">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom In
            </button>
            <button type="button" class="cropper-tool-btn" id="avatarZoomOut" title="Zoom Out">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom Out
            </button>
            <button type="button" class="cropper-tool-btn" id="avatarReset" title="Reset Crop">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reset
            </button>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button type="button" id="avatarCropCancel" class="btn btn-ghost btn-sm px-3">Cancel</button>
            <button type="button" id="avatarCropApply" class="btn btn-primary btn-sm px-4">Crop &amp; Apply</button>
        </div>
    </div>
</div>

<!-- ── 2. Cover Photo Cropper Modal ─────────────────────────────────────── -->
<div id="coverCropModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.78);align-items:center;justify-content:center;backdrop-filter:blur(6px);" role="dialog" aria-modal="true" aria-label="Crop cover photo">
    <div class="cropper-modal-dialog" style="background:var(--surface-card);border-radius:18px;padding:22px;max-width:720px;width:95%;max-height:92vh;overflow:auto;box-shadow:0 18px 48px rgba(0,0,0,0.45);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <h3 style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Crop Cover Photo</h3>
            </div>
            <button type="button" id="coverCropClose" style="background:none;border:none;cursor:pointer;color:var(--fg-muted);font-size:1.4rem;line-height:1;" aria-label="Close">&times;</button>
        </div>

        <div class="crop-image-container" style="max-height:380px;overflow:hidden;border-radius:10px;background:#0d0d10;">
            <img id="coverCropImage" src="" alt="Cover photo to crop" style="max-width:100%;display:block;">
        </div>

        <!-- Zoom & Positioning Toolbar -->
        <div class="cropper-toolbar">
            <button type="button" class="cropper-tool-btn" id="coverZoomIn" title="Zoom In">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom In
            </button>
            <button type="button" class="cropper-tool-btn" id="coverZoomOut" title="Zoom Out">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Zoom Out
            </button>
            <button type="button" class="cropper-tool-btn" id="coverReset" title="Reset Crop">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reset
            </button>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button type="button" id="coverCropCancel" class="btn btn-ghost btn-sm px-3">Cancel</button>
            <button type="button" id="coverCropApply" class="btn btn-primary btn-sm px-4">Crop &amp; Apply</button>
        </div>
    </div>
</div>

<!-- Local Cropper.js Script -->
<script src="/SocialNetwork/public/assets/js/cropper.min.js"></script>

<script>
(function () {
    'use strict';

    // ── Bio Counter ─────────────────────────────────────────────────────
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

    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // ── 1. Avatar Cropper Logic ─────────────────────────────────────────
    var avatarInput       = document.getElementById('profile_image');
    var avatarDataInput   = document.getElementById('profile_image_data');
    var avatarPreview     = document.getElementById('imagePreview');
    var headerAvatarThumb = document.getElementById('headerAvatarThumbnail');
    var avatarModal       = document.getElementById('avatarCropModal');
    var avatarCropImage   = document.getElementById('avatarCropImage');
    var avatarCloseBtn    = document.getElementById('avatarCropClose');
    var avatarCancelBtn   = document.getElementById('avatarCropCancel');
    var avatarApplyBtn    = document.getElementById('avatarCropApply');
    var avatarZoomInBtn   = document.getElementById('avatarZoomIn');
    var avatarZoomOutBtn  = document.getElementById('avatarZoomOut');
    var avatarResetBtn    = document.getElementById('avatarReset');
    var avatarCropper     = null;

    function openAvatarModal() {
        if (avatarModal) avatarModal.style.display = 'flex';
    }

    function closeAvatarModal() {
        if (avatarModal) avatarModal.style.display = 'none';
        if (avatarCropper) {
            avatarCropper.destroy();
            avatarCropper = null;
        }
        if (avatarCropImage) avatarCropImage.src = '';
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

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
                if (avatarCropImage) avatarCropImage.src = e.target.result;
                openAvatarModal();

                if (avatarCropper) {
                    avatarCropper.destroy();
                    avatarCropper = null;
                }

                // 1:1 circular/square crop for avatar
                avatarCropper = new Cropper(avatarCropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: true,
                    cropBoxResizable: true,
                    dragMode: 'move'
                });
            };
            reader.readAsDataURL(file);
        });
    }

    if (avatarZoomInBtn) {
        avatarZoomInBtn.addEventListener('click', function () {
            if (avatarCropper) avatarCropper.zoom(0.1);
        });
    }
    if (avatarZoomOutBtn) {
        avatarZoomOutBtn.addEventListener('click', function () {
            if (avatarCropper) avatarCropper.zoom(-0.1);
        });
    }
    if (avatarResetBtn) {
        avatarResetBtn.addEventListener('click', function () {
            if (avatarCropper) avatarCropper.reset();
        });
    }

    if (avatarApplyBtn) {
        avatarApplyBtn.addEventListener('click', function () {
            if (!avatarCropper) return;

            var canvas = avatarCropper.getCroppedCanvas({ width: 400, height: 400 });
            if (!canvas) {
                alert('Failed to crop image.');
                return;
            }

            var base64Data = canvas.toDataURL('image/jpeg', 0.9);
            if (avatarDataInput) {
                avatarDataInput.value = base64Data;
            }
            if (avatarPreview) {
                avatarPreview.src = base64Data;
            }
            if (headerAvatarThumb) {
                headerAvatarThumb.src = base64Data;
            }

            closeAvatarModal();
        });
    }

    if (avatarCloseBtn) avatarCloseBtn.addEventListener('click', closeAvatarModal);
    if (avatarCancelBtn) avatarCancelBtn.addEventListener('click', closeAvatarModal);

    if (avatarModal) {
        avatarModal.addEventListener('click', function (e) {
            if (e.target === avatarModal) closeAvatarModal();
        });
    }

    // ── 2. Cover Photo Cropper Logic ────────────────────────────────────
    var coverInput       = document.getElementById('cover_photo');
    var coverDataInput   = document.getElementById('cover_photo_data');
    var coverPreview     = document.getElementById('coverImagePreview');
    var coverPlaceholder = document.getElementById('coverPlaceholder');
    var coverModal       = document.getElementById('coverCropModal');
    var coverCropImage   = document.getElementById('coverCropImage');
    var coverCloseBtn    = document.getElementById('coverCropClose');
    var coverCancelBtn   = document.getElementById('coverCropCancel');
    var coverApplyBtn    = document.getElementById('coverCropApply');
    var coverZoomInBtn   = document.getElementById('coverZoomIn');
    var coverZoomOutBtn  = document.getElementById('coverZoomOut');
    var coverResetBtn    = document.getElementById('coverReset');
    var coverCropper     = null;

    function openCoverModal() {
        if (coverModal) coverModal.style.display = 'flex';
    }

    function closeCoverModal() {
        if (coverModal) coverModal.style.display = 'none';
        if (coverCropper) {
            coverCropper.destroy();
            coverCropper = null;
        }
        if (coverCropImage) coverCropImage.src = '';
    }

    if (coverInput) {
        coverInput.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

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
                if (coverCropImage) coverCropImage.src = e.target.result;
                openCoverModal();

                if (coverCropper) {
                    coverCropper.destroy();
                    coverCropper = null;
                }

                // Aspect ratio 16:7 for rectangular cover photo
                coverCropper = new Cropper(coverCropImage, {
                    aspectRatio: 16 / 7,
                    viewMode: 1,
                    autoCropArea: 1,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: true,
                    cropBoxResizable: true,
                    dragMode: 'move'
                });
            };
            reader.readAsDataURL(file);
        });
    }

    if (coverZoomInBtn) {
        coverZoomInBtn.addEventListener('click', function () {
            if (coverCropper) coverCropper.zoom(0.1);
        });
    }
    if (coverZoomOutBtn) {
        coverZoomOutBtn.addEventListener('click', function () {
            if (coverCropper) coverCropper.zoom(-0.1);
        });
    }
    if (coverResetBtn) {
        coverResetBtn.addEventListener('click', function () {
            if (coverCropper) coverCropper.reset();
        });
    }

    if (coverApplyBtn) {
        coverApplyBtn.addEventListener('click', function () {
            if (!coverCropper) return;

            var canvas = coverCropper.getCroppedCanvas({ maxWidth: 1200, maxHeight: 525 });
            if (!canvas) {
                alert('Failed to crop image.');
                return;
            }

            var base64Data = canvas.toDataURL('image/jpeg', 0.88);
            if (coverDataInput) {
                coverDataInput.value = base64Data;
            }
            if (coverPreview) {
                coverPreview.src = base64Data;
                coverPreview.style.display = 'block';
            }
            if (coverPlaceholder) {
                coverPlaceholder.style.setProperty('display', 'none', 'important');
            }

            closeCoverModal();
        });
    }

    if (coverCloseBtn) coverCloseBtn.addEventListener('click', closeCoverModal);
    if (coverCancelBtn) coverCancelBtn.addEventListener('click', closeCoverModal);

    if (coverModal) {
        coverModal.addEventListener('click', function (e) {
            if (e.target === coverModal) closeCoverModal();
        });
    }

    // Global Escape Key Handler
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (avatarModal && avatarModal.style.display === 'flex') closeAvatarModal();
            if (coverModal && coverModal.style.display === 'flex') closeCoverModal();
        }
    });
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
