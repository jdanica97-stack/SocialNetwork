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

require_once BASE_PATH . '/app/helpers/Csrf.php';
Csrf::initSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <!-- CSRF Security Token for AJAX requests -->
  <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::getToken(), ENT_QUOTES, 'UTF-8') ?>">
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

<?php if (isset($_SESSION['user_id'])): ?>
  <!-- ── Left-Side Navigation Panel Backdrop ───────────────────────────── -->
  <div class="side-panel-backdrop" id="sidePanelBackdrop" aria-hidden="true"></div>

  <!-- ── Left-Side Navigation Panel (Off-Canvas Drawer) ────────────────── -->
  <aside class="side-panel" id="sidePanel" role="dialog" aria-modal="true" aria-label="Navigation Panel" aria-hidden="true">
    <!-- Panel Header with Clickable Logo Brand and Close Button -->
    <div class="side-panel-header">
      <a href="/SocialNetwork/public/" class="side-panel-brand" id="sidePanelBrandLink" aria-label="Orbit — Go to Home" title="Home">
        <img
          src="/SocialNetwork/public/assets/images/logo-light.jpg"
          alt="Orbit Logo"
          class="logo-img logo-img-light"
          width="50"
          height="27"
          loading="eager"
        >
        <img
          src="/SocialNetwork/public/assets/images/logo-dark.jpg"
          alt="Orbit Logo"
          class="logo-img logo-img-dark"
          width="50"
          height="27"
          loading="eager"
        >
        <span class="side-panel-title">Orbit</span>
      </a>
      <button type="button" class="side-panel-close-btn" id="sidePanelCloseBtn" aria-label="Close navigation panel" title="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <!-- Panel Navigation Links (Search, Profile, Messages, and Sign Out) -->
    <nav class="side-panel-nav" role="navigation" aria-label="Side navigation">
      <div class="side-panel-section-label">Navigation</div>

      <!-- 1. Search Option -->
      <a href="/SocialNetwork/public/?url=search" class="side-panel-link" id="sidePanelSearchLink">
        <div class="side-panel-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div class="side-panel-text">
          <span class="side-panel-link-title">Search</span>
          <span class="side-panel-link-desc">Find community posts & members</span>
        </div>
      </a>

      <!-- 2. Profile Option -->
      <a href="/SocialNetwork/public/?url=profile" class="side-panel-link" id="sidePanelProfileLink">
        <div class="side-panel-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="side-panel-text">
          <span class="side-panel-link-title">Profile</span>
          <span class="side-panel-link-desc">@<?= htmlspecialchars($_SESSION['user_username'] ?? 'user', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </a>

      <!-- 3. Messages Option -->
      <a href="/SocialNetwork/public/?url=messages" class="side-panel-link" id="sidePanelMessagesLink">
        <div class="side-panel-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div class="side-panel-text">
          <span class="side-panel-link-title">Messages</span>
          <span class="side-panel-link-desc">Direct conversations & chats</span>
        </div>
      </a>

      <!-- 4. Sign Out Option -->
      <div class="side-panel-divider my-2"></div>
      <a href="/SocialNetwork/public/?url=auth/logout" class="side-panel-link side-panel-logout-link" id="sidePanelLogoutLink" aria-label="Sign Out">
        <div class="side-panel-icon-wrap text-danger">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </div>
        <div class="side-panel-text">
          <span class="side-panel-link-title text-danger">Sign Out</span>
          <span class="side-panel-link-desc">Log out of your account</span>
        </div>
      </a>
    </nav>

    <!-- Panel Footer with User Details -->
    <div class="side-panel-footer">
      <div class="side-panel-user-info">
        <span class="side-panel-user-name"><?= htmlspecialchars($_SESSION['user_full_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
        <span class="side-panel-user-handle">@<?= htmlspecialchars($_SESSION['user_username'] ?? 'user', ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </aside>
<?php endif; ?>

<!-- ── Floating Glass Top Navigation Bar ─────────────────────────────── -->
<div class="halo-nav-wrapper">
  <nav class="halo-nav glass" role="navigation" aria-label="Main navigation">

    <!-- Left Section: Hamburger Menu (far left), Logo, and "Orbit" Brand Name -->
    <div class="halo-nav-left d-flex align-items-center gap-2">
      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- 1. Hamburger Menu Button (Far Left) — ONLY trigger for side panel -->
        <button
          type="button"
          class="halo-nav-btn hamburger-nav-btn"
          id="hamburgerMenuBtn"
          aria-label="Toggle navigation menu"
          aria-controls="sidePanel"
          aria-expanded="false"
          title="Menu"
        >
          <span class="hamburger-icon-symbol" aria-hidden="true">&#9776;</span>
        </button>

        <!-- 2. Orbit Logo beside it & 3. "Orbit" Brand Name — Direct Link to Home (no sidepanel trigger) -->
        <a
          href="/SocialNetwork/public/"
          class="logo-panel-btn"
          id="logoSidePanelBtn"
          aria-label="Orbit — Home"
          title="Orbit"
        >
          <!-- Rounded Logo Image Container -->
          <div class="logo-img-box">
            <img
              src="/SocialNetwork/public/assets/images/logo-light.jpg"
              alt="Orbit Logo"
              class="logo-img logo-img-light"
              width="60"
              height="32"
              loading="eager"
            >
            <img
              src="/SocialNetwork/public/assets/images/logo-dark.jpg"
              alt="Orbit Logo"
              class="logo-img logo-img-dark"
              width="60"
              height="32"
              loading="eager"
            >
          </div>
          <!-- "Orbit" Brand Name beside logo -->
          <span class="logo-title">Orbit</span>
        </a>
      <?php else: ?>
        <!-- Direct Clickable Brand Link to Home Landing for Logged-Out Users -->
        <a
          href="/SocialNetwork/public/"
          class="logo-panel-btn"
          id="logoSidePanelBtn"
          aria-label="Orbit — Home"
          title="Orbit"
        >
          <div class="logo-img-box">
            <img
              src="/SocialNetwork/public/assets/images/logo-light.jpg"
              alt="Orbit Logo"
              class="logo-img logo-img-light"
              width="60"
              height="32"
              loading="eager"
            >
            <img
              src="/SocialNetwork/public/assets/images/logo-dark.jpg"
              alt="Orbit Logo"
              class="logo-img logo-img-dark"
              width="60"
              height="32"
              loading="eager"
            >
          </div>
          <span class="logo-title">Orbit</span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Top Nav links -->
    <ul class="halo-nav-links" role="list">

      <!-- Home (always visible) -->
      <li>
        <a href="/SocialNetwork/public/" aria-label="Home">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span class="nav-label">Home</span>
        </a>
      </li>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Messages (logged-in users) -->
        <li>
          <a href="/SocialNetwork/public/?url=messages" aria-label="Messages">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span class="nav-label">Messages</span>
          </a>
        </li>
      <?php endif; ?>

      <?php if (!isset($_SESSION['user_id'])): ?>

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
