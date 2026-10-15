<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
/* Copy exact CSS from style.css */
.comment-menu-wrap { position: relative; flex-shrink: 0; }
.comment-menu-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 24px; height: 24px; border-radius: 50%; border: none;
  background: #eee; color: #666; cursor: pointer; padding: 0;
}
.comment-menu-dropdown {
  display: none; position: absolute; top: calc(100% + 4px); right: 0;
  min-width: 120px; padding: 4px; border-radius: 12px;
  background: white; border: 1px solid #ccc; box-shadow: 0 4px 12px rgba(0,0,0,.15);
  z-index: 200;
}
.comment-menu-dropdown.show { display: block; }
.comment-menu-item { display: flex; align-items: center; gap: 6px; padding: 6px 10px; font-size: 0.8rem; color: #333; text-decoration: none; border-radius: 8px; cursor: pointer; width: 100%; }
.comment-menu-item:hover { background: #f5f5f5; }
/* Simulate card with border-radius */
.card { background: white; border: 1px solid #ddd; border-radius: 12px; padding: 16px; max-width: 400px; margin: 40px auto; }
/* Simulate comment row */
.d-flex { display: flex; }
.align-items-start { align-items: flex-start; }
.justify-content-between { justify-content: space-between; }
.gap-1 { gap: 4px; }
.min-w-0 { min-width: 0; }
.flex-grow-1 { flex-grow: 1; }
</style>
</head>
<body>
<div class="card">
  <div style="background:#f9f9f9;border-radius:8px;padding:8px;">
    <div style="display:flex;align-items:flex-start;gap:8px;">
      <div style="width:28px;height:28px;border-radius:50%;background:#ccc;flex-shrink:0;"></div>
      <div style="flex:1;min-width:0;">
        <div class="d-flex align-items-start justify-content-between gap-1">
          <div class="min-w-0">
            <strong>Alice</strong>
            <span style="color:#999;font-size:.75rem;">@alice</span>
            <div>Test comment content here</div>
          </div>
          <!-- Comment Kebab Menu -->
          <div class="comment-menu-wrap">
            <button type="button" class="comment-menu-btn" aria-label="Comment options" aria-expanded="false" title="More options">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
              </svg>
            </button>
            <div class="comment-menu-dropdown" role="menu">
              <a href="#" class="comment-menu-item">Edit</a>
              <a href="#" class="comment-menu-item" style="color:red;">Delete</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="log" style="max-width:400px;margin:0 auto;padding:10px;background:#f0f0f0;border-radius:8px;font-family:monospace;font-size:13px;"></div>

<script>
function log(msg) {
  document.getElementById('log').innerHTML += msg + '<br>';
}

// Exact copy of dropdown.js logic
(function () {
  'use strict';

  function closeAll(btnSel, dropSel) {
    document.querySelectorAll(dropSel + '.show').forEach(function (el) {
      el.classList.remove('show');
    });
    document.querySelectorAll(btnSel + '[aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  function closeAllCommentMenus() { closeAll('.comment-menu-btn', '.comment-menu-dropdown'); }

  document.addEventListener('click', function (e) {
    log('click fired on: ' + e.target.tagName + '.' + e.target.className);
    
    var cmtBtn = e.target.closest('.comment-menu-btn');
    log('closest .comment-menu-btn = ' + (cmtBtn ? 'FOUND' : 'null'));
    
    if (cmtBtn) {
      e.preventDefault();
      e.stopPropagation();
      var cmtWrap = cmtBtn.closest('.comment-menu-wrap');
      log('closest .comment-menu-wrap = ' + (cmtWrap ? 'FOUND' : 'null'));
      if (!cmtWrap) return;
      var cmtDropdown = cmtWrap.querySelector('.comment-menu-dropdown');
      log('dropdown = ' + (cmtDropdown ? 'FOUND' : 'null'));
      if (!cmtDropdown) return;
      var cmtIsOpen = cmtDropdown.classList.contains('show');
      closeAllCommentMenus();
      if (!cmtIsOpen) {
        cmtDropdown.classList.add('show');
        cmtBtn.setAttribute('aria-expanded', 'true');
        log('OPENED dropdown!');
      } else {
        log('CLOSED dropdown');
      }
      return;
    }

    if (e.target.closest('.comment-menu-dropdown')) {
      log('click inside dropdown - letting through');
      return;
    }

    closeAllCommentMenus();
  });
})();
</script>
</body>
</html>
