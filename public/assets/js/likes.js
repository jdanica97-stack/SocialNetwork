/**
 * public/assets/js/likes.js
 *
 * Handles AJAX / fetch() Like and Unlike actions without page reload.
 * Preserves the user's exact scroll position.
 * Prevents double-clicking by temporarily disabling the button while request is in flight.
 */

(function () {
  'use strict';

  var SVG_HEART_OUTLINE = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
  var SVG_HEART_FILLED  = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';

  document.addEventListener('click', function (e) {
    // Find closest like button
    var btn = e.target.closest('.like-btn[data-post-id], .like-button[data-post-id]');
    if (!btn) return;

    // Prevent default form submit or link jump
    e.preventDefault();

    // Prevent double clicking while request is pending
    if (btn.disabled || btn.getAttribute('data-loading') === 'true') {
      return;
    }

    var postId = btn.getAttribute('data-post-id');
    if (!postId) return;

    // Temporarily disable the button
    btn.disabled = true;
    btn.setAttribute('data-loading', 'true');
    btn.style.opacity = '0.6';
    btn.style.pointerEvents = 'none';

    var formData = new URLSearchParams();
    formData.append('post_id', postId);

    fetch('/SocialNetwork/public/?url=likes/toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData.toString()
    })
      .then(function (response) {
        if (response.status === 401) {
          // Unauthenticated user
          window.location.href = '/SocialNetwork/public/?url=auth/login';
          return null;
        }
        return response.json();
      })
      .then(function (data) {
        if (!data) return;

        if (data.success) {
          var iconEl  = btn.querySelector('.like-icon');
          var textEl  = btn.querySelector('.like-text');
          var countEl = btn.querySelector('.like-count');

          if (data.liked) {
            btn.classList.add('liked');
            btn.setAttribute('data-liked', '1');
            btn.setAttribute('title', 'Unlike this post');
            if (iconEl) iconEl.innerHTML = SVG_HEART_FILLED;
            if (textEl) textEl.textContent = 'Unlike';
          } else {
            btn.classList.remove('liked');
            btn.setAttribute('data-liked', '0');
            btn.setAttribute('title', 'Like this post');
            if (iconEl) iconEl.innerHTML = SVG_HEART_OUTLINE;
            if (textEl) textEl.textContent = 'Like';
          }

          if (countEl) {
            countEl.textContent = data.count;
          }
        } else {
          showLikeError(data.message || 'Unable to update like. Please try again.');
        }
      })
      .catch(function () {
        showLikeError('Unable to update like. Please check your connection and try again.');
      })
      .finally(function () {
        // Re-enable button after response without changing scroll position
        btn.disabled = false;
        btn.removeAttribute('data-loading');
        btn.style.opacity = '';
        btn.style.pointerEvents = '';
      });
  });

  function showLikeError(msg) {
    // Non-intrusive alert that doesn't cause page reload
    var banner = document.getElementById('like-toast-alert');
    if (!banner) {
      banner = document.createElement('div');
      banner.id = 'like-toast-alert';
      banner.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:rgba(239,68,68,0.92);color:#fff;padding:10px 20px;border-radius:999px;font-size:0.85rem;font-weight:500;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,0.25);transition:opacity 0.3s;pointer-events:none;';
      document.body.appendChild(banner);
    }
    banner.textContent = msg;
    banner.style.opacity = '1';
    setTimeout(function () {
      banner.style.opacity = '0';
    }, 3200);
  }
}());
