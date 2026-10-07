<?php
/**
 * app/views/auth/login.php
 *
 * Login form — redesigned with glassmorphism.
 * All PHP variables, form fields, validation, and routes are unchanged.
 *
 * Variables: $errors, $old, $flashSuccess
 */
$pageTitle = 'Login — Halo';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="auth-wrap">

  <!-- Brand -->
  <p class="auth-logo">Halo</p>

  <!-- Title -->
  <h1 class="auth-title">Welcome back.</h1>
  <p class="auth-sub">Log in to your account to continue.</p>

  <!-- Flash success (e.g. after registration or logout) -->
  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success fade-up visible" role="alert" style="width:100%;max-width:420px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Validation errors -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger fade-up visible" role="alert" style="width:100%;max-width:420px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Login card -->
  <div class="card auth-card fade-up">
    <div class="card-body" style="padding: 32px !important;">

      <form method="POST" action="/SocialNetwork/public/?url=auth/login" novalidate>

        <!-- Username -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </span>
            <input
              type="text"
              class="form-control"
              id="username"
              name="username"
              placeholder="Your username"
              value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              required
              autocomplete="username"
              aria-required="true"
            >
          </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
              placeholder="Your password"
              required
              autocomplete="current-password"
              aria-required="true"
            >
          </div>
        </div>

        <!-- Submit -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">Log In</button>
        </div>

      </form>
    </div>
  </div>

  <p class="auth-footer-text">
    Don't have an account? <a href="/SocialNetwork/public/?url=auth/register">Register here</a>
  </p>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
