/**
 * public/assets/js/profile-dropdown.js
 *
 * Handles Top Navigation Profile Dropdown Menu:
 * - Toggle dropdown menu visibility on trigger button click
 * - Close dropdown when clicking outside
 * - Close dropdown on Escape key and return focus
 * - Close dropdown when an item/link inside is clicked
 * - Maintain ARIA accessibility attributes (aria-expanded)
 */

(function () {
  'use strict';

  function initProfileDropdown() {
    var wrapper = document.getElementById('profileDropdownWrapper');
    var btn = document.getElementById('profileDropdownBtn');
    var menu = document.getElementById('profileDropdownMenu');

    if (!wrapper || !btn || !menu) return;

    function openMenu() {
      menu.classList.add('show');
      btn.setAttribute('aria-expanded', 'true');
      btn.classList.add('active');
    }

    function closeMenu() {
      menu.classList.remove('show');
      btn.setAttribute('aria-expanded', 'false');
      btn.classList.remove('active');
    }

    function toggleMenu() {
      if (menu.classList.contains('show')) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    // Toggle menu on button click
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      toggleMenu();
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
      if (!wrapper.contains(e.target)) {
        if (menu.classList.contains('show')) {
          closeMenu();
        }
      }
    });

    // Close on pressing Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        if (menu.classList.contains('show')) {
          closeMenu();
          btn.focus();
        }
      }
    });

    // Close when clicking any menu item (e.g. links)
    menu.addEventListener('click', function (e) {
      var link = e.target.closest('a');
      if (link) {
        closeMenu();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileDropdown);
  } else {
    initProfileDropdown();
  }
})();
