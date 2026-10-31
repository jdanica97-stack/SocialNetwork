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
                        <input type="hidden" id="remove_cover_photo" name="remove_cover_photo" value="0">
                        <input
                            type="file"
                            class="form-control form-control-sm"
                            id="cover_photo"
                            name="cover_photo"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                            <div class="form-text mb-0" style="font-size: 0.76rem;">
                                Accepted: JPEG, PNG, GIF, WebP &mdash; maximum 10 MB. High-resolution photos can be cropped and adjusted before saving.
                            </div>
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm px-3"
                                id="btnRemoveCoverEdit"
                                style="font-size: 0.78rem; display: <?= $coverPhotoSrc ? 'inline-flex' : 'none' ?>; align-items: center; gap: 5px;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                Remove Cover Photo
                            </button>
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
                                <input type="hidden" id="remove_profile_image" name="remove_profile_image" value="0">
                                <input
                                    type="file"
                                    class="form-control form-control-sm"
                                    id="profile_image"
                                    name="profile_image"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                >
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                                    <div class="form-text mb-0" style="font-size: 0.76rem;">
                                        Accepted: JPEG, PNG, GIF, WebP &mdash; maximum 2 MB. Square 1:1 circular crop.
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm px-3"
                                        id="btnRemoveAvatarEdit"
                                        style="font-size: 0.78rem; display: <?= !empty($user['profile_image']) ? 'inline-flex' : 'none' ?>; align-items: center; gap: 5px;"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        Remove Profile Picture
                                    </button>
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
<!-- ── Confirmation Modal: Remove Cover Photo (Edit View) ─────────────── -->
<div id="confirmRemoveCoverModalEdit" class="custom-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="removeCoverModalTitleEdit">
    <div class="confirm-modal-dialog">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <h4 id="removeCoverModalTitleEdit" style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Remove Cover Photo?</h4>
        </div>
        <p style="font-size:0.85rem;color:var(--fg-muted);margin-bottom:20px;line-height:1.5;">
            Are you sure you want to remove your cover photo? Your profile will revert to the default background theme.
        </p>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-ghost btn-sm px-3" id="cancelRemoveCoverEditBtn">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm px-4" id="confirmRemoveCoverEditBtn">Remove</button>
        </div>
    </div>
</div>

<!-- ── Confirmation Modal: Remove Profile Picture (Edit View) ─────────── -->
<div id="confirmRemoveAvatarModalEdit" class="custom-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="removeAvatarModalTitleEdit">
    <div class="confirm-modal-dialog">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <h4 id="removeAvatarModalTitleEdit" style="margin:0;font-size:1.05rem;font-weight:600;color:var(--fg);">Remove Profile Picture?</h4>
        </div>
        <p style="font-size:0.85rem;color:var(--fg-muted);margin-bottom:20px;line-height:1.5;">
            Are you sure you want to remove your profile picture? Your profile will revert to your default initials avatar.
        </p>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-ghost btn-sm px-3" id="cancelRemoveAvatarEditBtn">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm px-4" id="confirmRemoveAvatarEditBtn">Remove</button>
        </div>
    </div>
</div>
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

    // ── Remove Cover Photo Logic (Edit View) ──────────────────────────────────
    var btnRemoveCoverEdit     = document.getElementById('btnRemoveCoverEdit');
    var removeCoverModalEdit   = document.getElementById('confirmRemoveCoverModalEdit');
    var cancelRemoveCoverEdit  = document.getElementById('cancelRemoveCoverEditBtn');
    var confirmRemoveCoverEdit = document.getElementById('confirmRemoveCoverEditBtn');
    var removeCoverFlag        = document.getElementById('remove_cover_photo');

    if (btnRemoveCoverEdit && removeCoverModalEdit) {
        btnRemoveCoverEdit.addEventListener('click', function () {
            removeCoverModalEdit.style.display = 'flex';
        });
    }

    if (cancelRemoveCoverEdit && removeCoverModalEdit) {
        cancelRemoveCoverEdit.addEventListener('click', function () {
            removeCoverModalEdit.style.display = 'none';
        });
    }

    if (confirmRemoveCoverEdit && removeCoverModalEdit) {
        confirmRemoveCoverEdit.addEventListener('click', function () {
            confirmRemoveCoverEdit.disabled = true;
            confirmRemoveCoverEdit.textContent = 'Removing…';

            var formData = new FormData();
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
            formData.append('csrf_token', csrf);

            fetch('/SocialNetwork/public/?url=profile/remove-cover', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (removeCoverFlag) removeCoverFlag.value = '1';
                    if (coverDataInput) coverDataInput.value = '';
                    if (coverPreview) {
                        coverPreview.src = '';
                        coverPreview.style.display = 'none';
                    }
                    if (coverPlaceholder) {
                        coverPlaceholder.style.setProperty('display', 'flex', 'important');
                    }
                    if (btnRemoveCoverEdit) btnRemoveCoverEdit.style.display = 'none';
                    removeCoverModalEdit.style.display = 'none';
                } else {
                    alert(data.message || 'Failed to remove cover photo.');
                }
            })
            .catch(function () {
                // Fallback: set form flag
                if (removeCoverFlag) removeCoverFlag.value = '1';
                if (coverPreview) coverPreview.style.display = 'none';
                if (coverPlaceholder) coverPlaceholder.style.setProperty('display', 'flex', 'important');
                if (btnRemoveCoverEdit) btnRemoveCoverEdit.style.display = 'none';
                removeCoverModalEdit.style.display = 'none';
            })
            .finally(function () {
                confirmRemoveCoverEdit.disabled = false;
                confirmRemoveCoverEdit.textContent = 'Remove';
            });
        });
    }

    // ── Remove Profile Picture Logic (Edit View) ──────────────────────────────
    var btnRemoveAvatarEdit     = document.getElementById('btnRemoveAvatarEdit');
    var removeAvatarModalEdit   = document.getElementById('confirmRemoveAvatarModalEdit');
    var cancelRemoveAvatarEdit  = document.getElementById('cancelRemoveAvatarEditBtn');
    var confirmRemoveAvatarEdit = document.getElementById('confirmRemoveAvatarEditBtn');
    var removeAvatarFlag        = document.getElementById('remove_profile_image');

    if (btnRemoveAvatarEdit && removeAvatarModalEdit) {
        btnRemoveAvatarEdit.addEventListener('click', function () {
            removeAvatarModalEdit.style.display = 'flex';
        });
    }

    if (cancelRemoveAvatarEdit && removeAvatarModalEdit) {
        cancelRemoveAvatarEdit.addEventListener('click', function () {
            removeAvatarModalEdit.style.display = 'none';
        });
    }

    if (confirmRemoveAvatarEdit && removeAvatarModalEdit) {
        confirmRemoveAvatarEdit.addEventListener('click', function () {
            confirmRemoveAvatarEdit.disabled = true;
            confirmRemoveAvatarEdit.textContent = 'Removing…';

            var formData = new FormData();
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
            formData.append('csrf_token', csrf);

            fetch('/SocialNetwork/public/?url=profile/remove-avatar', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    if (removeAvatarFlag) removeAvatarFlag.value = '1';
                    if (avatarDataInput) avatarDataInput.value = '';
                    if (previewImg) previewImg.src = data.avatar_url || 'https://ui-avatars.com/api/?name=User&size=180&background=111113&color=f5f5f7&rounded=true&bold=true';
                    if (btnRemoveAvatarEdit) btnRemoveAvatarEdit.style.display = 'none';
                    removeAvatarModalEdit.style.display = 'none';
                } else {
                    alert(data.message || 'Failed to remove profile picture.');
                }
            })
            .catch(function () {
                // Fallback: set form flag
                if (removeAvatarFlag) removeAvatarFlag.value = '1';
                if (previewImg) previewImg.src = 'https://ui-avatars.com/api/?name=User&size=180&background=111113&color=f5f5f7&rounded=true&bold=true';
                if (btnRemoveAvatarEdit) btnRemoveAvatarEdit.style.display = 'none';
                removeAvatarModalEdit.style.display = 'none';
            })
            .finally(function () {
                confirmRemoveAvatarEdit.disabled = false;
                confirmRemoveAvatarEdit.textContent = 'Remove';
            });
        });
    }

    // Close Modals on Backdrop Click or Escape
    [removeCoverModalEdit, removeAvatarModalEdit].forEach(function (m) {
        if (!m) return;
        m.addEventListener('click', function (e) {
            if (e.target === m) m.style.display = 'none';
        });
    });

    // Global Escape Key Handler
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (avatarModal && avatarModal.style.display === 'flex') closeAvatarModal();
            if (coverModal && coverModal.style.display === 'flex') closeCoverModal();
            if (removeCoverModalEdit && removeCoverModalEdit.style.display === 'flex') removeCoverModalEdit.style.display = 'none';
            if (removeAvatarModalEdit && removeAvatarModalEdit.style.display === 'flex') removeAvatarModalEdit.style.display = 'none';
        }
    });
}());
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
