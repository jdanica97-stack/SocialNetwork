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

// Resolve logged-in user profile details for shared navigation & sidebar
$navUserAvatar    = null;
$navUserName      = null;
$navUserFirstName = null;
$navUserHandle    = null;

if (isset($_SESSION['user_id'])) {
    $currentSessionUserId = (int) $_SESSION['user_id'];

    // Ensure session has profile information cached without unnecessary DB hits
    if (!isset($_SESSION['user_profile_image'])) {
        require_once BASE_PATH . '/config/database.php';
        require_once BASE_PATH . '/app/models/UserModel.php';
        $sessionUserModel = new UserModel(getDBConnection());
        $sessionUserData  = $sessionUserModel->findById($currentSessionUserId);
        if ($sessionUserData) {
            $_SESSION['user_profile_image'] = $sessionUserData['profile_image'] ?? '';
            $_SESSION['user_full_name']     = $sessionUserData['full_name'] ?? ($_SESSION['user_full_name'] ?? 'User');
            $_SESSION['user_username']      = $sessionUserData['username'] ?? ($_SESSION['user_username'] ?? 'user');
        } else {
            $_SESSION['user_profile_image'] = '';
        }
    }

    $navUserName   = $_SESSION['user_full_name'] ?? 'User';
    $navUserHandle = $_SESSION['user_username'] ?? 'user';

    // First name extraction for compact Facebook-style display
    $nameParts = preg_split('/\s+/', trim($navUserName));
    $navUserFirstName = !empty($nameParts[0]) ? $nameParts[0] : $navUserName;

    // Validate avatar image path or fallback to initial avatar
    $profileImgFile = $_SESSION['user_profile_image'] ?? '';
    if (!empty($profileImgFile) && file_exists(BASE_PATH . '/public/assets/images/profiles/' . $profileImgFile)) {
        $navUserAvatar = '/SocialNetwork/public/assets/images/profiles/' . rawurlencode($profileImgFile);
    } else {
        $navUserAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($navUserName) . '&size=120&background=111113&color=f5f5f7&rounded=true&bold=true';
    }
}
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
  <link rel="stylesheet" href="/SocialNetwork/public/assets/css/style.css?v=<?= filemtime(BASE_PATH . '/public/assets/css/style.css') ?>">

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

<!-- ── Floating Glass Top Navigation Bar ─────────────────────────────── -->
<div class="halo-nav-wrapper">
  <nav class="halo-nav glass" role="navigation" aria-label="Main navigation">

    <!-- Left Section: Orbit Logo and Brand Name (Links to Home) -->
    <div class="halo-nav-left d-flex align-items-center">
      <a
        href="/SocialNetwork/public/"
        class="logo-panel-btn"
        id="logoBrandBtn"
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
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
      <!-- Center Section: Top Search Bar -->
      <div class="halo-nav-search">
        <form action="/SocialNetwork/public/" method="GET" class="halo-search-form" role="search">
          <input type="hidden" name="url" value="search">
          <div class="halo-search-wrap">
            <svg class="halo-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="search"
              name="q"
              class="halo-search-input"
              placeholder="Search users or posts..."
              value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              aria-label="Search users or posts"
              autocomplete="off"
            >
          </div>
        </form>
      </div>
    <?php endif; ?>

    <!-- Top Nav links -->
    <ul class="halo-nav-links" role="list">

      <!-- Home (always visible) -->
      <li>
        <a href="/SocialNetwork/public/" aria-label="Home">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span class="nav-label">Home</span>
        </a>
      </li>

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

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Profile Dropdown at Top Right (logged-in users) -->
        <li class="position-relative profile-dropdown-wrapper" id="profileDropdownWrapper">
          <button
            type="button"
            class="nav-profile-btn"
            id="profileDropdownBtn"
            aria-label="Account menu for <?= htmlspecialchars($navUserName, ENT_QUOTES, 'UTF-8') ?>"
            aria-expanded="false"
            aria-haspopup="true"
            aria-controls="profileDropdownMenu"
            title="<?= htmlspecialchars($navUserName, ENT_QUOTES, 'UTF-8') ?>"
          >
            <img
              src="<?= htmlspecialchars($navUserAvatar, ENT_QUOTES, 'UTF-8') ?>"
              alt="<?= htmlspecialchars($navUserName, ENT_QUOTES, 'UTF-8') ?>"
              class="nav-user-avatar"
              width="28"
              height="28"
              loading="eager"
            >
            <span class="nav-user-name"><?= htmlspecialchars($navUserFirstName, ENT_QUOTES, 'UTF-8') ?></span>
            <svg class="profile-chevron-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>

          <!-- Dropdown Menu -->
          <div
            class="profile-dropdown-menu"
            id="profileDropdownMenu"
            role="menu"
            aria-label="User Account Menu"
          >
            <!-- User summary header -->
            <div class="profile-dropdown-header">
              <div class="profile-dropdown-name text-truncate"><?= htmlspecialchars($navUserName, ENT_QUOTES, 'UTF-8') ?></div>
              <div class="profile-dropdown-handle text-truncate">@<?= htmlspecialchars($navUserHandle, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="profile-dropdown-divider"></div>

            <!-- 1. View Profile -->
            <a href="/SocialNetwork/public/?url=profile" class="profile-dropdown-link" role="menuitem" id="dropdownProfileLink">
              <div class="profile-dropdown-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </div>
              <span>View Profile</span>
            </a>

            <!-- 2. Sign Out -->
            <a href="/SocialNetwork/public/?url=auth/logout" class="profile-dropdown-link profile-dropdown-logout" role="menuitem" id="dropdownLogoutLink">
              <div class="profile-dropdown-icon text-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              </div>
              <span class="text-danger">Sign Out</span>
            </a>
          </div>
        </li>
      <?php endif; ?>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <?php
          // If we are on the landing page (home / empty url), do not show redundant login/register links in top nav
          $currentReqUrl = trim($_GET['url'] ?? '', '/');
          $isLandingPage = ($currentReqUrl === '' || $currentReqUrl === 'home');
        ?>
        <?php if (!$isLandingPage): ?>
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
      <?php endif; ?>

    </ul>
  </nav>
</div>

<!-- ── Main Content ────────────────────────────────────────────────────── -->
<main id="main-content" class="container">
