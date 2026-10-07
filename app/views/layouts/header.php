<?php
/**
 * app/views/layouts/header.php
 *
 * Reusable page header — outputs HTML <head>, background blobs,
 * floating glass navigation, and opens <main>.
 *
 * Variables consumed:
 *   $pageTitle  string  — unique <title> for this page (set by each controller/view)
 *
 * Session variables read (set by AuthController):
 *   $_SESSION['user_id']        — determines logged-in state
 *   $_SESSION['user_full_name'] — displayed in nav
 *   $_SESSION['user_username']  — displayed in nav
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= htmlspecialchars($pageTitle ?? 'Mini Social Network', ENT_QUOTES, 'UTF-8') ?></title>

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
  >

  <!-- Bootstrap Icons -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet"
  >

  <!-- Custom Design System -->
  <link rel="stylesheet" href="/SocialNetwork/public/assets/css/style.css">

  <!-- Theme init — run before <body> renders to prevent flash -->
  <script>
    (function () {
      var saved  = localStorage.getItem('sn-theme');
      var system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', saved || system);
    }());
  </script>
</head>

<body>

<!-- Accessibility skip link -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- ── Background Blobs ──────────────────────────────────────────────── -->
<div class="bg-blobs" aria-hidden="true">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
</div>

<!-- ── Floating Glass Navigation ─────────────────────────────────────── -->
<div class="halo-nav-wrapper">
  <nav class="halo-nav glass" role="navigation" aria-label="Main navigation">

    <!-- Brand -->
    <a href="/SocialNetwork/public/" class="halo-nav-brand" aria-label="Mini Social Network — Home">
      Mini Social Network
    </a>

    <!-- Nav links -->
    <ul class="halo-nav-links" role="list">

      <!-- Home (always visible) -->
      <li>
        <a href="/SocialNetwork/public/" aria-label="Home">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span class="nav-label">Home</span>
        </a>
      </li>

      <!-- Search (always visible) -->
      <li>
        <a href="/SocialNetwork/public/?url=search" aria-label="Search">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span class="nav-label">Search</span>
        </a>
      </li>


      <?php if (isset($_SESSION['user_id'])): ?>

        <!-- Profile -->
        <li>
          <a href="/SocialNetwork/public/?url=profile" aria-label="My Profile">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span class="nav-label">Profile</span>
          </a>
        </li>

        <!-- Logout -->
        <li>
          <a href="/SocialNetwork/public/?url=auth/logout" aria-label="Logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span class="nav-label">Logout</span>
          </a>
        </li>

      <?php else: ?>

        <!-- Login -->
        <li>
          <a href="/SocialNetwork/public/?url=auth/login" aria-label="Login">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            <span class="nav-label">Login</span>
          </a>
        </li>

        <!-- Register -->
        <li>
          <a href="/SocialNetwork/public/?url=auth/register" aria-label="Register">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            <span class="nav-label">Register</span>
          </a>
        </li>

      <?php endif; ?>

      <!-- Theme toggle -->
      <li>
        <button
          class="halo-nav-btn"
          id="themeToggle"
          aria-label="Toggle dark mode"
          title="Toggle theme"
        >
          <!-- Moon (shown in light mode) -->
          <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          <!-- Sun (shown in dark mode) -->
          <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
      </li>

    </ul>
  </nav>
</div>

<!-- ── Main Content ────────────────────────────────────────────────────── -->
<main id="main-content" class="container">
