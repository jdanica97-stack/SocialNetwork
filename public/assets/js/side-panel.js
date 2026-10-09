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

  var triggerBtn = document.getElementById('logoSidePanelBtn');
  var sidePanel  = document.getElementById('sidePanel');
  var backdrop   = document.getElementById('sidePanelBackdrop');
  var closeBtn   = document.getElementById('sidePanelCloseBtn');

  if (!sidePanel) return;

  function openPanel() {
    sidePanel.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'true');
    sidePanel.setAttribute('aria-hidden', 'false');
    document.body.classList.add('side-panel-active');
  }

  function closePanel() {
    sidePanel.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
    if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'false');
    sidePanel.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('side-panel-active');
  }

  function togglePanel() {
    if (sidePanel.classList.contains('open')) {
      closePanel();
    } else {
      openPanel();
    }
  }

  if (triggerBtn) {
    triggerBtn.addEventListener('click', function (e) {
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

  // Handle disabled links in the panel
  sidePanel.addEventListener('click', function (e) {
    var disabledItem = e.target.closest('.side-panel-link.disabled');
    if (disabledItem) {
      e.preventDefault();
      e.stopPropagation();
    }
  });

})();
