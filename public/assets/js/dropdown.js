/**
 * public/assets/js/dropdown.js
 *
 * Handles three-dot dropdown menus for:
 *   - Post options   (.post-menu-btn / .post-menu-dropdown)
 *   - Comment options (.comment-menu-btn / .comment-menu-dropdown)
 *
 * Handles "View all N comments" expand / collapse functionality with
 * AJAX loading and restoration of the 2-comment default.
 */

(function () {
  'use strict';

  /* ── CSRF token helper ── */
  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  /* ── Close utilities ── */
  function closeAllPostMenus() {
    document.querySelectorAll('.post-menu-dropdown.show').forEach(function (el) {
      el.classList.remove('show');
    });
    document.querySelectorAll('.post-menu-btn[aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  function closeAllCommentMenus() {
    document.querySelectorAll('.comment-menu-dropdown.show').forEach(function (el) {
      el.classList.remove('show');
    });
    document.querySelectorAll('.comment-menu-btn[aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  /* ── Single unified click handler via event delegation ── */
  document.addEventListener('click', function (e) {

    // 1. "View all N comments" expand / collapse button
    var toggleBtn = e.target.closest('.comments-toggle-btn');
    if (toggleBtn) {
      handleCommentsToggle(toggleBtn, e);
      return;
    }

    // 2. Post options menu button (three-dot)
    var postBtn = e.target.closest('.post-menu-btn');
    if (postBtn) {
      e.preventDefault();
      var postWrap = postBtn.closest('.post-menu-wrap');
      if (!postWrap) return;
      var postDropdown = postWrap.querySelector('.post-menu-dropdown');
      if (!postDropdown) return;

      var postIsOpen = postDropdown.classList.contains('show');
      closeAllPostMenus();
      closeAllCommentMenus();

      if (!postIsOpen) {
        postDropdown.classList.add('show');
        postBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    // 3. Comment options kebab button (three-dot)
    var cmtBtn = e.target.closest('.comment-menu-btn');
    if (cmtBtn) {
      e.preventDefault();
      var cmtWrap = cmtBtn.closest('.comment-menu-wrap');
      if (!cmtWrap) return;
      var cmtDropdown = cmtWrap.querySelector('.comment-menu-dropdown');
      if (!cmtDropdown) return;

      var cmtIsOpen = cmtDropdown.classList.contains('show');
      closeAllPostMenus();
      closeAllCommentMenus();

      if (!cmtIsOpen) {
        cmtDropdown.classList.add('show');
        cmtBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    // 4. Click inside an open dropdown — allow links and form buttons to proceed
    if (e.target.closest('.post-menu-dropdown') || e.target.closest('.comment-menu-dropdown')) {
      return;
    }

    // 5. Click outside any menu — close all open dropdowns
    closeAllPostMenus();
    closeAllCommentMenus();
  });

  /* ── Escape key closes all dropdown menus ── */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      closeAllPostMenus();
      closeAllCommentMenus();
    }
  });

  /* ── Handle View all / Show fewer comments toggle ── */
  function handleCommentsToggle(toggleBtn, e) {
    e.preventDefault();

    var postId = toggleBtn.getAttribute('data-post-id');
    var total = parseInt(toggleBtn.getAttribute('data-total'), 10) || 0;
    var isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    var list = document.getElementById('comments-list-' + postId);

    if (!list) return;

    // Save the original 2-comment HTML on first expand so collapse restores it perfectly
    if (!list.hasAttribute('data-initial-html')) {
      list.setAttribute('data-initial-html', list.innerHTML);
    }

    if (isExpanded) {
      // ── Collapse: restore original 2-comment view ──
      var initialHtml = list.getAttribute('data-initial-html');
      if (initialHtml !== null) {
        list.innerHTML = initialHtml;
      }
      toggleBtn.textContent = 'View all ' + total + ' comments';
      toggleBtn.setAttribute('aria-expanded', 'false');
      return;
    }

    // ── Expand: fetch all comments via AJAX ──
    toggleBtn.textContent = 'Loading…';
    toggleBtn.disabled = true;

    fetch('/SocialNetwork/public/?url=comments/fetch&post_id=' + encodeURIComponent(postId), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        if (!data.success || !Array.isArray(data.comments)) {
          throw new Error('Bad response structure');
        }

        // Clear list and append all comments in order
        list.innerHTML = '';
        data.comments.forEach(function (c) {
          var el = buildCommentEl(c);
          list.appendChild(el);
        });

        // Update button state to collapse option
        toggleBtn.textContent = 'Show fewer comments';
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.disabled = false;
      })
      .catch(function (err) {
        console.error('Failed to load comments:', err);
        toggleBtn.textContent = 'View all ' + total + ' comments';
        toggleBtn.disabled = false;
      });
  }

  /**
   * Build a DOM element for a single comment object.
   *
   * @param {Object} c - Comment record from server
   * @returns {HTMLElement}
   */
  function buildCommentEl(c) {
    var wrap = document.createElement('div');
    wrap.className = 'comment-item';
    wrap.id = 'comment-' + c.id;

    var avatarSrc = c.profile_image
      ? '/SocialNetwork/public/assets/images/profiles/' + encodeURIComponent(c.profile_image)
      : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(c.full_name) + '&size=64&background=111113&color=f5f5f7&rounded=true&bold=true';

    var authorId = c.author_id || c.user_id;
    var profileUrl = '/SocialNetwork/public/?url=profile&id=' + encodeURIComponent(authorId);

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
              '<span class="comment-time" title="' + esc(c.created_at) + '">&bull; ' + esc(c.relative_time || '') + '</span>' +
              '<div class="comment-content" style="word-break: break-word; overflow-wrap: anywhere;">' + nl2brEsc(c.content) + '</div>' +
            '</div>' +
            (c.is_owner ? buildCommentMenuHtml(c) : '') +
          '</div>' +
        '</div>' +
      '</div>';

    return wrap;
  }

  /**
   * Build the kebab (three-dot) dropdown menu HTML for comment owner.
   *
   * @param {Object} c - Comment record
   * @returns {string}
   */
  function buildCommentMenuHtml(c) {
    var token = getCsrfToken();
    return '<div class="comment-menu-wrap">' +
      '<button type="button" class="comment-menu-btn" aria-label="Comment options" aria-expanded="false" title="More options">' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
          '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>' +
        '</svg>' +
      '</button>' +
      '<div class="comment-menu-dropdown" role="menu">' +
        '<a href="/SocialNetwork/public/?url=comments/edit&id=' + encodeURIComponent(c.id) + '" class="comment-menu-item" role="menuitem">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>' +
          ' Edit' +
        '</a>' +
        '<form method="POST" action="/SocialNetwork/public/?url=comments/delete" class="m-0 p-0" onsubmit="return confirm(\'Delete this comment?\');">' +
          '<input type="hidden" name="csrf_token" value="' + esc(token) + '">' +
          '<input type="hidden" name="id" value="' + encodeURIComponent(c.id) + '">' +
          '<button type="submit" class="comment-menu-item comment-menu-delete w-100 border-0 bg-transparent text-start" role="menuitem">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
            ' Delete' +
          '</button>' +
        '</form>' +
      '</div>' +
    '</div>';
  }

  function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function nl2brEsc(s) {
    return esc(s).replace(/\r?\n/g, '<br>');
  }

  // ── 5. AJAX Comment Form Submission (Zero page reload, preserves scroll) ──
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[action*="comments/create"], .comment-form');
    if (!form) return;

    e.preventDefault();

    var input = form.querySelector('input[name="content"], textarea[name="content"]');
    var submitBtn = form.querySelector('button[type="submit"]');
    var postIdInput = form.querySelector('input[name="post_id"]');
    if (!postIdInput) return;
    var postId = postIdInput.value;
    var content = input ? input.value.trim() : '';

    if (!content) return;

    var formData = new FormData(form);

    // Loading state & avoid duplicate submissions
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('loading');
    }
    if (input) input.disabled = true;

    fetch('/SocialNetwork/public/?url=comments/create', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
      })
      .then(function (res) {
        if (!res.ok || !res.data || !res.data.success) {
          var msg = res.data && res.data.message ? res.data.message : 'Could not post comment.';
          throw new Error(msg);
        }

        // Clear input only on success
        if (input) input.value = '';

        var commentData = res.data.comment;
        var newTotal = res.data.comment_count;

        // Find or create comments list container
        var postCard = document.getElementById('post-' + postId);
        var commentsList = document.getElementById('comments-list-' + postId);

        if (!commentsList && postCard) {
          var commentsSection = postCard.querySelector('.comments-section');
          if (commentsSection) {
            commentsList = document.createElement('div');
            commentsList.className = 'comments-list';
            commentsList.id = 'comments-list-' + postId;
            var formWrap = form.closest('.comment-form-wrap') || form;
            commentsSection.insertBefore(commentsList, formWrap);
          }
        }

        if (commentsList) {
          var commentEl = buildCommentEl(commentData);
          commentsList.appendChild(commentEl);

          // Update cached initial HTML if it exists so collapse includes newly added comment
          if (commentsList.hasAttribute('data-initial-html')) {
            commentsList.setAttribute('data-initial-html', commentsList.innerHTML);
          }
        }

        // Update comment counter text on the post card
        var countEls = document.querySelectorAll('#post-comment-count-' + postId);
        countEls.forEach(function (el) {
          el.textContent = newTotal + ' ' + (newTotal === 1 ? 'comment' : 'comments');
        });

        // Update toggle button data-total if present
        if (postCard) {
          var toggleBtn = postCard.querySelector('.comments-toggle-btn');
          if (toggleBtn) {
            toggleBtn.setAttribute('data-total', newTotal);
            var isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            if (!isExpanded) {
              toggleBtn.textContent = 'View all ' + newTotal + ' comments';
            }
          } else if (newTotal > 2) {
            var expandRow = postCard.querySelector('.comments-expand-row');
            if (!expandRow && commentsList) {
              expandRow = document.createElement('div');
              expandRow.className = 'comments-expand-row';
              expandRow.innerHTML = '<button type="button" class="comments-toggle-btn" data-post-id="' + esc(postId) + '" data-total="' + esc(newTotal) + '" aria-expanded="false">View all ' + esc(newTotal) + ' comments</button>';
              commentsList.parentNode.insertBefore(expandRow, commentsList);
            }
          }
        }
      })
      .catch(function (err) {
        alert(err.message || 'An error occurred while posting your comment.');
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('loading');
        }
        if (input) {
          input.disabled = false;
          input.focus();
        }
      });
  });

  // ── 6. Post Composer: Textarea auto-expand and Image Attachment Preview ──
  function initComposer() {
    var textarea = document.getElementById('composerTextarea');
    if (textarea) {
      function adjustHeight() {
        textarea.style.height = 'auto';
        var newH = Math.min(Math.max(textarea.scrollHeight, 80), 220);
        textarea.style.height = newH + 'px';
      }
      textarea.addEventListener('input', adjustHeight);
      adjustHeight();
    }

    var imgInput = document.getElementById('postImageInput');
    var previewWrap = document.getElementById('composerImagePreviewWrap');
    var previewImg = document.getElementById('composerImagePreview');
    var removeBtn = document.getElementById('composerRemoveImgBtn');
    var fileNameSpan = document.getElementById('imageFileName');

    if (imgInput && previewWrap && previewImg) {
      imgInput.addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (file) {
          var reader = new FileReader();
          reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewWrap.classList.remove('d-none');
            if (fileNameSpan) fileNameSpan.textContent = file.name;
          };
          reader.readAsDataURL(file);
        } else {
          previewImg.src = '';
          previewWrap.classList.add('d-none');
          if (fileNameSpan) fileNameSpan.textContent = '';
        }
      });

      if (removeBtn) {
        removeBtn.addEventListener('click', function () {
          imgInput.value = '';
          previewImg.src = '';
          previewWrap.classList.add('d-none');
          if (fileNameSpan) fileNameSpan.textContent = '';
        });
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initComposer);
  } else {
    initComposer();
  }

})();
