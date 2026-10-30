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

  // ── 6. Post Composer: Textarea auto-expand and Multi-Image Preview ──
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
    var previewGrid = document.getElementById('composerPreviewGrid');
    var fileNameSpan = document.getElementById('imageFileName');

    if (!imgInput || !previewWrap || !previewGrid) return;

    // Use an array to manage selected files and sync with imgInput via DataTransfer
    var selectedFiles = [];
    var isInternalUpdate = false;
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var maxFileSize = 5 * 1024 * 1024; // 5 MB

    function syncFilesToInput() {
      var newDt = new DataTransfer();
      for (var i = 0; i < selectedFiles.length; i++) {
        newDt.items.add(selectedFiles[i]);
      }
      isInternalUpdate = true;
      imgInput.files = newDt.files;
      isInternalUpdate = false;
    }

    function renderPreviews() {
      previewGrid.innerHTML = '';

      if (selectedFiles.length === 0) {
        previewWrap.classList.add('d-none');
        if (fileNameSpan) fileNameSpan.textContent = '';
        syncFilesToInput();
        return;
      }

      previewWrap.classList.remove('d-none');
      if (fileNameSpan) {
        fileNameSpan.textContent = selectedFiles.length === 1
          ? selectedFiles[0].name
          : selectedFiles.length + ' photos selected';
      }

      for (var i = 0; i < selectedFiles.length; i++) {
        (function (index, file) {
          var item = document.createElement('div');
          item.className = 'composer-thumb-item position-relative';

          var img = document.createElement('img');
          img.className = 'composer-thumb-img rounded';
          img.alt = file.name;

          var removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.className = 'composer-thumb-remove';
          removeBtn.setAttribute('aria-label', 'Remove ' + file.name);
          removeBtn.setAttribute('title', 'Remove photo');
          removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

          removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            removeFile(index);
          });

          var reader = new FileReader();
          reader.onload = function (e) {
            img.src = e.target.result;
          };
          reader.readAsDataURL(file);

          item.appendChild(img);
          item.appendChild(removeBtn);
          previewGrid.appendChild(item);
        })(i, selectedFiles[i]);
      }
    }

    function removeFile(index) {
      selectedFiles.splice(index, 1);
      syncFilesToInput();
      renderPreviews();
    }

    imgInput.addEventListener('change', function () {
      if (isInternalUpdate) return;
      if (!this.files || this.files.length === 0) return;

      var newFiles = Array.from(this.files);
      for (var i = 0; i < newFiles.length; i++) {
        var f = newFiles[i];
        if (!allowedTypes.includes(f.type)) {
          alert('"' + f.name + '" is not a supported format. Please upload JPG, PNG, GIF, or WebP.');
          continue;
        }
        if (f.size > maxFileSize) {
          alert('"' + f.name + '" exceeds the 5 MB file size limit.');
          continue;
        }
        selectedFiles.push(f);
      }

      syncFilesToInput();
      renderPreviews();
    });

    var postForm = document.getElementById('createPostForm');
    if (postForm) {
      postForm.addEventListener('reset', function () {
        selectedFiles = [];
        syncFilesToInput();
        renderPreviews();
      });
    }
  }

  // ── 7. Image Gallery Lightbox Modal ──
  function initLightbox() {
    var modal = document.getElementById('imageLightboxModal');
    var lightboxImg = document.getElementById('lightboxImg');
    var counter = document.getElementById('lightboxCounter');
    var closeBtn = document.getElementById('lightboxCloseBtn');
    var prevBtn = document.getElementById('lightboxPrevBtn');
    var nextBtn = document.getElementById('lightboxNextBtn');

    if (!modal || !lightboxImg) return;

    var currentGallery = [];
    var currentIndex = 0;

    function showImage(index) {
      if (!currentGallery.length) return;
      if (index < 0) index = currentGallery.length - 1;
      if (index >= currentGallery.length) index = 0;
      currentIndex = index;

      lightboxImg.src = currentGallery[currentIndex];
      if (counter) {
        counter.textContent = (currentIndex + 1) + ' / ' + currentGallery.length;
        counter.style.display = currentGallery.length > 1 ? 'block' : 'none';
      }

      if (prevBtn && nextBtn) {
        var showNav = currentGallery.length > 1;
        prevBtn.style.display = showNav ? 'flex' : 'none';
        nextBtn.style.display = showNav ? 'flex' : 'none';
      }
    }

    function openLightbox(galleryImages, startIndex) {
      if (!galleryImages || !galleryImages.length) return;
      currentGallery = galleryImages;
      currentIndex = startIndex || 0;
      showImage(currentIndex);
      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden', 'true');
      lightboxImg.src = '';
      currentGallery = [];
      document.body.style.overflow = '';
    }

    // Click handler for opening gallery images
    document.addEventListener('click', function (e) {
      var target = e.target.closest('.post-gallery-img, .post-gallery-overlay, .post-gallery-item');
      if (!target) return;

      var container = target.closest('.post-gallery-container');
      if (!container) return;

      var imagesRaw = container.getAttribute('data-gallery-images');
      if (!imagesRaw) return;

      try {
        var images = JSON.parse(imagesRaw);
        var idx = target.getAttribute('data-gallery-index');
        if (idx === null) {
          var inner = target.querySelector('[data-gallery-index]');
          idx = inner ? inner.getAttribute('data-gallery-index') : 0;
        }
        openLightbox(images, parseInt(idx, 10) || 0);
      } catch (err) {
        console.error('Failed to parse gallery images JSON:', err);
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', closeLightbox);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        showImage(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        showImage(currentIndex + 1);
      });
    }

    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.classList.contains('lightbox-content-box')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (!modal.classList.contains('show')) return;
      if (e.key === 'Escape') {
        closeLightbox();
      } else if (e.key === 'ArrowLeft') {
        showImage(currentIndex - 1);
      } else if (e.key === 'ArrowRight') {
        showImage(currentIndex + 1);
      }
    });
  }


  // ── 8. AJAX Post Composer Submission ──
  function initAjaxPostSubmit() {
    var postForm = document.getElementById('createPostForm');
    if (!postForm) return;

    postForm.addEventListener('submit', function (e) {
      e.preventDefault();

      var textarea = document.getElementById('composerTextarea');
      var submitBtn = document.getElementById('composerSubmitBtn');
      var content = textarea ? textarea.value.trim() : '';

      if (!content) return;

      // Prevent double-submit
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting…';
      }

      var formData = new FormData(postForm);

      fetch('/SocialNetwork/public/?url=posts/create', {
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
            var msg = res.data && res.data.message ? res.data.message : 'Could not create post. Please try again.';
            throw new Error(msg);
          }

          var post = res.data.post;

          // Clear form & reset inputs
          if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
          }
          var fileInput = document.getElementById('postImageInput');
          if (fileInput) fileInput.value = '';
          var previewWrap = document.getElementById('composerImagePreviewWrap');
          if (previewWrap) {
            previewWrap.classList.add('d-none');
            var previewGrid = document.getElementById('composerPreviewGrid');
            if (previewGrid) previewGrid.innerHTML = '';
          }
          var fileNameSpan = document.getElementById('imageFileName');
          if (fileNameSpan) fileNameSpan.textContent = '';
          postForm.dispatchEvent(new Event('reset'));

          // Build and prepend new post card to feed
          var feed = document.getElementById('newsfeed-stream') || document.querySelector('.posts-stream');
          if (feed && post) {
            // Remove empty feed placeholder if present
            var emptyFeedCard = feed.querySelector('.empty-feed-card, .card.text-center');
            if (emptyFeedCard) emptyFeedCard.remove();

            var newCard = buildPostCard(post);
            if (newCard) {
              feed.insertBefore(newCard, feed.firstChild);
              // Animate in
              newCard.style.opacity = '0';
              newCard.style.transform = 'translateY(-10px)';
              requestAnimationFrame(function () {
                newCard.style.transition = 'opacity 0.35s, transform 0.35s';
                newCard.style.opacity = '1';
                newCard.style.transform = 'translateY(0)';
              });
            }
          }

          // Update newsfeed count text if present
          var countEl = document.querySelector('.newsfeed-header-count');
          if (countEl) {
            var currentCount = parseInt(countEl.textContent, 10) || 0;
            var newCount = currentCount + 1;
            countEl.textContent = newCount + ' ' + (newCount === 1 ? 'post' : 'posts');
          }

          // Show success toast
          showPostToast('Post published!', 'success');
        })
        .catch(function (err) {
          showPostToast(err.message || 'An error occurred.', 'error');
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Post';
          }
        });
    });
  }

  /**
   * Build a post card DOM element from post JSON data.
   * Exactly matches the server-rendered template in app/views/posts/feed.php.
   */
  function buildPostCard(post) {
    if (!post) return null;

    var avatarSrc = post.profile_image
      ? '/SocialNetwork/public/assets/images/profiles/' + encodeURIComponent(post.profile_image)
      : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(post.full_name) + '&size=80&background=111113&color=f5f5f7&rounded=true&bold=true';

    var profileUrl = post.user_id ? '/SocialNetwork/public/?url=profile&id=' + post.user_id : '/SocialNetwork/public/?url=profile';

    // Build images HTML
    var imagesHtml = '';
    if (post.images && post.images.length > 0) {
      if (post.images.length === 1) {
        imagesHtml = '<div class="post-image-single mb-3"><img src="/SocialNetwork/public/assets/images/' + esc(post.images[0]) + '" alt="Post image" class="post-single-img" loading="lazy" style="max-width:100%;border-radius:12px;cursor:pointer;"></div>';
      } else {
        var galleryUrls = post.images.map(function(img) { return '/SocialNetwork/public/assets/images/' + img; });
        imagesHtml = '<div class="post-gallery-container mb-3" data-gallery-images=\'' + esc(JSON.stringify(galleryUrls)) + '\'>';
        post.images.forEach(function(img, idx) {
          imagesHtml += '<div class="post-gallery-item" data-gallery-index="' + idx + '"><img src="/SocialNetwork/public/assets/images/' + esc(img) + '" alt="Post image ' + (idx+1) + '" class="post-gallery-img" loading="lazy"></div>';
        });
        imagesHtml += '</div>';
      }
    }

    var article = document.createElement('article');
    article.className = 'card post-card glass fade-up mb-4';
    article.id = 'post-' + post.id;

    article.innerHTML =
      '<div class="card-body">' +
        '<div class="d-flex align-items-center justify-content-between mb-3">' +
          '<div class="d-flex align-items-center gap-2">' +
            '<a href="' + esc(profileUrl) + '" title="View ' + esc(post.full_name) + '\'s profile">' +
              '<img src="' + esc(avatarSrc) + '" alt="' + esc(post.full_name) + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); display: block;" loading="lazy">' +
            '</a>' +
            '<div>' +
              '<a href="' + esc(profileUrl) + '" style="font-weight: 600; font-size: 0.92rem; color: var(--fg); text-decoration: none;" class="post-author-link">' + esc(post.full_name) + '</a>' +
              '<div style="font-size: 0.75rem; color: var(--fg-faint);">' +
                '@' + esc(post.username) + ' &bull; ' +
                '<time datetime="' + esc(post.created_at) + '" title="' + esc(post.created_at) + '">' + esc(post.relative_time || 'Just now') + '</time>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="post-menu-wrap position-relative">' +
            '<button type="button" class="post-menu-btn" aria-label="Post options" aria-expanded="false" title="More options">' +
              '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/></svg>' +
            '</button>' +
            '<div class="post-menu-dropdown glass" role="menu">' +
              '<a href="/SocialNetwork/public/?url=posts/edit&id=' + post.id + '" class="post-menu-item" role="menuitem">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                '<span>Edit Post</span>' +
              '</a>' +
              '<form method="POST" action="/SocialNetwork/public/?url=posts/delete" class="m-0 p-0" onsubmit="return confirm(\'Are you sure you want to delete this post?\');">' +
                '<input type="hidden" name="csrf_token" value="' + esc(getCsrfToken()) + '">' +
                '<input type="hidden" name="id" value="' + post.id + '">' +
                '<button type="submit" class="post-menu-item delete-item w-100 border-0 bg-transparent text-start" role="menuitem">' +
                  '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
                  '<span>Delete Post</span>' +
                '</button>' +
              '</form>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="post-content-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--fg); margin-bottom: 16px; word-break: break-word; overflow-wrap: anywhere;">' + nl2brEsc(post.content) + '</div>' +
        imagesHtml +
        '<div class="post-interactions">' +
          '<button type="button" class="like-btn like-button" data-post-id="' + post.id + '" data-liked="0" title="Like this post">' +
            '<span class="like-icon" aria-hidden="true">' +
              '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>' +
            '</span>' +
            '<span class="like-text">Like</span>' +
            '<span class="like-count">0</span>' +
          '</button>' +
          '<span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.82rem;">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>' +
            '<span class="post-comment-count-text" id="post-comment-count-' + post.id + '">0 comments</span>' +
          '</span>' +
        '</div>' +
      '</div>' +
      '<section class="comments-section" aria-label="Comments on post ' + post.id + '">' +
        '<div class="comments-list" id="comments-list-' + post.id + '"></div>' +
        '<div class="comment-form-wrap">' +
          '<form method="POST" action="/SocialNetwork/public/?url=comments/create">' +
            '<input type="hidden" name="csrf_token" value="' + esc(getCsrfToken()) + '">' +
            '<input type="hidden" name="post_id" value="' + post.id + '">' +
            '<div class="d-flex gap-2 align-items-center">' +
              '<textarea name="content" class="form-control comment-input" rows="1" placeholder="Write a comment..." maxlength="1000" required></textarea>' +
              '<button type="submit" class="btn btn-ghost btn-sm comment-submit-btn">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>' +
                '<span class="visually-hidden">Post comment</span>' +
              '</button>' +
            '</div>' +
          '</form>' +
        '</div>' +
      '</section>';

    return article;
  }

  function showPostToast(msg, type) {
    var banner = document.getElementById('post-toast-alert');
    if (!banner) {
      banner = document.createElement('div');
      banner.id = 'post-toast-alert';
      banner.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 20px;border-radius:999px;font-size:0.85rem;font-weight:500;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,0.25);transition:opacity 0.3s;pointer-events:none;';
      document.body.appendChild(banner);
    }
    if (type === 'success') {
      banner.style.background = 'rgba(34,197,94,0.92)';
    } else {
      banner.style.background = 'rgba(239,68,68,0.92)';
    }
    banner.style.color = '#fff';
    banner.textContent = msg;
    banner.style.opacity = '1';
    setTimeout(function () { banner.style.opacity = '0'; }, 3200);
  }

  function init() {
    initComposer();
    initLightbox();
    initAjaxPostSubmit();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();

