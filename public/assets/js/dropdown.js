/**
 * public/assets/js/dropdown.js
 *
 * Handles post options three-dot menu dropdown interactions:
 *   - Toggles post menu when clicking .post-menu-btn
 *   - Closes open menu when clicking outside
 *   - Closes open menu on Escape key
 *   - Closes open menu when another menu is opened
 */

(function () {
  'use strict';

  function closeAllPostMenus() {
    var openMenus = document.querySelectorAll('.post-menu-dropdown.show');
    openMenus.forEach(function (menu) {
      menu.classList.remove('show');
    });

    var activeBtns = document.querySelectorAll('.post-menu-btn[aria-expanded="true"]');
    activeBtns.forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  document.addEventListener('click', function (e) {
    var postBtn = e.target.closest('.post-menu-btn');
    if (postBtn) {
      e.preventDefault();
      e.stopPropagation();

      var postWrap = postBtn.closest('.post-menu-wrap');
      if (!postWrap) return;

      var postDropdown = postWrap.querySelector('.post-menu-dropdown');
      if (!postDropdown) return;

      var isAlreadyOpen = postDropdown.classList.contains('show');

      closeAllPostMenus();

      if (!isAlreadyOpen) {
        postDropdown.classList.add('show');
        postBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    if (e.target.closest('.post-menu-dropdown')) {
      return;
    }

    closeAllPostMenus();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      closeAllPostMenus();
    }
  });
})();
