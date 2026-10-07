<?php
/**
 * app/views/auth/register.php
 *
 * Registration form — redesigned with glassmorphism.
 * All PHP variables, form fields, validation, and routes are unchanged.
 *
 * Variables: $errors, $old
 */
$pageTitle = 'Register — Halo';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="auth-wrap">

  <!-- Brand -->
  <p class="auth-logo">Halo</p>

  <!-- Title -->
  <h1 class="auth-title">Join Halo.</h1>
  <p class="auth-sub">Create your account and start connecting.</p>

  <!-- Validation errors -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger fade-up visible" role="alert" style="width:100%;max-width:420px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <strong>Please fix the following:</strong>
      <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Register card -->
  <div class="card auth-card fade-up">
    <div class="card-body" style="padding: 32px !important;">

      <form method="POST" action="/SocialNetwork/public/?url=auth/register" novalidate>

        <!-- Full Name -->
        <div class="mb-3">
          <label for="full_name" class="form-label">Full Name <span style="color:var(--fg-faint)">*</span></label>
          <input
            type="text"
            class="form-control"
            id="full_name"
            name="full_name"
            placeholder="e.g. John Doe"
            value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
            autocomplete="name"
            aria-required="true"
          >
        </div>

        <!-- Username -->
        <div class="mb-3">
          <label for="username" class="form-label">Username <span style="color:var(--fg-faint)">*</span></label>
          <div class="input-group">
            <span class="input-group-text" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </span>
            <input
              type="text"
              class="form-control"
              id="username"
              name="username"
              placeholder="e.g. john_doe"
              value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              required
              autocomplete="username"
              aria-required="true"
            >
          </div>
          <div class="form-text">3–50 characters. Letters, numbers, and underscores only.</div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password <span style="color:var(--fg-faint)">*</span></label>
          <div class="input-group">
            <span class="input-group-text" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
              placeholder="Minimum 8 characters"
              required
              autocomplete="new-password"
              aria-required="true"
            >
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
          <label for="confirm_password" class="form-label">Confirm Password <span style="color:var(--fg-faint)">*</span></label>
          <div class="input-group">
            <span class="input-group-text" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              class="form-control"
              id="confirm_password"
              name="confirm_password"
              placeholder="Re-enter your password"
              required
              autocomplete="new-password"
              aria-required="true"
            >
          </div>
        </div>

        <!-- Submit -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
        </div>

      </form>
    </div>
  </div>

  <p class="auth-footer-text">
    Already have an account? <a href="/SocialNetwork/public/?url=auth/login">Log in here</a>
  </p>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
