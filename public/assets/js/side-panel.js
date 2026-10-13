/**
 * public/assets/js/side-panel.js
 *
 * Handles Left-Side Navigation Panel interactions:
 *   - Clicking logo button toggles the side panel
 *   - Clicking close button inside panel closes the side panel
 *   - Clicking backdrop closes the side panel
 *   - Pressing Escape key closes the side panel
 *   - Blocks scroll on body when side panel is open
 *   - Blocks interaction on disabled items (Messages - Coming Soon)
 */

(function () {
  'use strict';

  var hamburgerBtn = document.getElementById('hamburgerMenuBtn');
  var sidePanel    = document.getElementById('sidePanel');
  var backdrop     = document.getElementById('sidePanelBackdrop');
  var closeBtn     = document.getElementById('sidePanelCloseBtn');

  if (!sidePanel) return;

  function updateAriaState(isOpen) {
    var state = isOpen ? 'true' : 'false';
    if (hamburgerBtn) {
      hamburgerBtn.setAttribute('aria-expanded', state);
    }
    sidePanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  }

  function openPanel() {
    sidePanel.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    updateAriaState(true);
    document.body.classList.add('side-panel-active');
  }

  function closePanel() {
    sidePanel.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
    updateAriaState(false);
    document.body.classList.remove('side-panel-active');
  }

  function togglePanel() {
    if (sidePanel.classList.contains('open')) {
      closePanel();
    } else {
      openPanel();
    }
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      togglePanel();
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', function (e) {
      e.preventDefault();
      closePanel();
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function (e) {
      e.preventDefault();
      closePanel();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      if (sidePanel.classList.contains('open')) {
        closePanel();
      }
    }
  });

  // Handle links inside the panel:
  // - Prevent navigation for disabled items
  // - Auto-close panel on selecting active navigation links
  sidePanel.addEventListener('click', function (e) {
    var disabledItem = e.target.closest('.side-panel-link.disabled');
    if (disabledItem) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    var activeLink = e.target.closest('a.side-panel-link, a.side-panel-brand');
    if (activeLink) {
      closePanel();
    }
  });

})();
