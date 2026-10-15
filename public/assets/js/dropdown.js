/**
 * public/assets/js/dropdown.js
 *
 * Handles three-dot dropdown menus for:
 *   - Post options   (.post-menu-btn / .post-menu-dropdown)
 *   - Comment options (.comment-menu-btn / .comment-menu-dropdown)
 *
 * Also manages the "View all N comments" expand/collapse toggle.
 */

(function () {
  'use strict';

  /* ── Utility: close all open menus of a given btn/dropdown class ── */
  function closeAll(btnSel, dropSel) {
    document.querySelectorAll(dropSel + '.show').forEach(function (el) {
      el.classList.remove('show');
    });
    document.querySelectorAll(btnSel + '[aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  function closeAllPostMenus()    { closeAll('.post-menu-btn',    '.post-menu-dropdown');    }
  function closeAllCommentMenus() { closeAll('.comment-menu-btn', '.comment-menu-dropdown'); }

  /* ── Global click handler (event delegation) ── */
  document.addEventListener('click', function (e) {

    /* --- Post menu button --- */
    var postBtn = e.target.closest('.post-menu-btn');
    if (postBtn) {
      e.preventDefault();
      e.stopPropagation();
      var postWrap     = postBtn.closest('.post-menu-wrap');
      if (!postWrap) return;
      var postDropdown = postWrap.querySelector('.post-menu-dropdown');
      if (!postDropdown) return;
      var postIsOpen   = postDropdown.classList.contains('show');
      closeAllPostMenus();
      closeAllCommentMenus();
      if (!postIsOpen) {
        postDropdown.classList.add('show');
        postBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    /* --- Comment kebab button --- */
    var cmtBtn = e.target.closest('.comment-menu-btn');
    if (cmtBtn) {
      e.preventDefault();
      e.stopPropagation();
      var cmtWrap     = cmtBtn.closest('.comment-menu-wrap');
      if (!cmtWrap) return;
      var cmtDropdown = cmtWrap.querySelector('.comment-menu-dropdown');
      if (!cmtDropdown) return;
      var cmtIsOpen   = cmtDropdown.classList.contains('show');
      closeAllPostMenus();
      closeAllCommentMenus();
      if (!cmtIsOpen) {
        cmtDropdown.classList.add('show');
        cmtBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    /* --- Click inside an open dropdown — let it propagate (for form/link clicks) --- */
    if (e.target.closest('.post-menu-dropdown') || e.target.closest('.comment-menu-dropdown')) {
      return;
    }

    /* --- Click anywhere else — close everything --- */
    closeAllPostMenus();
    closeAllCommentMenus();
  });

  /* ── Escape key closes all menus ── */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      closeAllPostMenus();
      closeAllCommentMenus();
    }
  });

  /* ── "View all N comments" expand/collapse ── */
  document.addEventListener('click', function (e) {
    var toggleBtn = e.target.closest('.comments-toggle-btn');
    if (!toggleBtn) return;

    e.preventDefault();

    var postId    = toggleBtn.getAttribute('data-post-id');
    var total     = parseInt(toggleBtn.getAttribute('data-total'), 10) || 0;
    var isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    var list      = document.getElementById('comments-list-' + postId);

    if (!list) return;

    if (isExpanded) {
      /* ── Collapse: remove extra comments, restore button label ── */
      var extras = list.querySelectorAll('.comment-item.comment-extra');
      extras.forEach(function (el) { el.remove(); });
      toggleBtn.textContent = 'View all ' + total + ' comments';
      toggleBtn.setAttribute('aria-expanded', 'false');
      return;
    }

    /* ── Expand: fetch all comments via AJAX ── */
    toggleBtn.textContent = 'Loading…';
    toggleBtn.disabled    = true;

    fetch('/SocialNetwork/public/?url=comments/fetch&post_id=' + encodeURIComponent(postId), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function (data) {
      if (!data.success || !Array.isArray(data.comments)) {
        throw new Error('Bad response');
      }

      /* Remove existing comment items (we'll re-render all from server) */
      list.querySelectorAll('.comment-item').forEach(function (el) { el.remove(); });

      /* Render each comment returned by server */
      data.comments.forEach(function (c) {
        var item = buildCommentEl(c);
        list.appendChild(item);
      });

      toggleBtn.textContent = 'Show fewer comments';
      toggleBtn.setAttribute('aria-expanded', 'true');
      toggleBtn.disabled    = false;
    })
    .catch(function () {
      /* Fallback: just show the button again */
      toggleBtn.textContent = 'View all ' + total + ' comments';
      toggleBtn.disabled    = false;
    });
  });

  /**
   * Build a comment <div> from a plain data object returned by the AJAX endpoint.
   * This is intentionally simple — no inline styles beyond what is already in CSS.
   */
  function buildCommentEl(c) {
    var wrap = document.createElement('div');
    wrap.className = 'comment-item comment-extra';
    wrap.id = 'comment-' + c.id;

    var avatarSrc = c.profile_image
      ? '/SocialNetwork/public/assets/images/profiles/' + encodeURIComponent(c.profile_image)
      : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(c.full_name) + '&size=64&background=111113&color=f5f5f7&rounded=true&bold=true';

    var profileUrl = '/SocialNetwork/public/?url=profile&id=' + encodeURIComponent(c.author_id || c.user_id);

    wrap.innerHTML =
      '<div class="d-flex align-items-start gap-2">' +
        '<a href="' + esc(profileUrl) + '" title="View profile">' +
          '<img src="' + esc(avatarSrc) + '" alt="' + esc(c.full_name) + '" class="comment-avatar" loading="lazy">' +
        '</a>' +
        '<div class="flex-grow-1 min-w-0">' +
          '<div class="d-flex align-items-start justify-content-between gap-1">' +
            '<div class="min-w-0">' +
              '<a href="' + esc(profileUrl) + '" class="comment-author-link">' + esc(c.full_name) + '</a>' +
              '<span class="comment-username">@' + esc(c.username) + '</span>' +
              '<span class="comment-time">&bull; ' + esc(c.relative_time || '') + '</span>' +
              '<div class="comment-content" style="word-break:break-word;overflow-wrap:anywhere;">' + nl2brEsc(c.content) + '</div>' +
            '</div>' +
            (c.is_owner ? buildCommentMenuHtml(c) : '') +
          '</div>' +
        '</div>' +
      '</div>';

    return wrap;
  }

  function buildCommentMenuHtml(c) {
    return '<div class="comment-menu-wrap">' +
      '<button type="button" class="comment-menu-btn" aria-label="Comment options" aria-expanded="false" title="More options">' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>' +
      '</button>' +
      '<div class="comment-menu-dropdown" role="menu">' +
        '<a href="/SocialNetwork/public/?url=comments/edit&id=' + c.id + '" class="comment-menu-item" role="menuitem">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit' +
        '</a>' +
        '<form method="POST" action="/SocialNetwork/public/?url=comments/delete" class="m-0 p-0" onsubmit="return confirm(\'Delete this comment?\');">' +
          '<input type="hidden" name="_csrf_token" value="' + (window._csrfToken || '') + '">' +
          '<input type="hidden" name="id" value="' + c.id + '">' +
          '<button type="submit" class="comment-menu-item comment-menu-delete w-100 border-0 bg-transparent text-start" role="menuitem">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Delete' +
          '</button>' +
        '</form>' +
      '</div>' +
    '</div>';
  }

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function nl2brEsc(s) {
    return esc(s).replace(/\n/g, '<br>');
  }

  /* Expose CSRF token for dynamically built forms (read from meta tag) */
  (function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) window._csrfToken = meta.getAttribute('content');
  })();

})();
