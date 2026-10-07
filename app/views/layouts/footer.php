<?php
/**
 * app/views/layouts/footer.php
 *
 * Reusable page footer — closes <main>, renders the minimal footer,
 * loads Bootstrap JS, and injects the theme-toggle + fade-in script.
 */
?>

</main><!-- /#main-content -->

<!-- ── Minimal Footer ──────────────────────────────────────────────────── -->
<footer class="halo-footer" role="contentinfo">
  <span>Mini Social Network &mdash; &copy; <?= date('Y') ?></span>
</footer>

<!-- Bootstrap 5 JS (for alert dismissal, dropdowns, etc.) -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmE9d29u+a9V5hJJvHbFBgEwHm/"
  crossorigin="anonymous"
></script>

<!-- ── Theme Toggle ───────────────────────────────────────────────────── -->
<script>
(function () {
  'use strict';
  var STORAGE_KEY = 'sn-theme';
  var root        = document.documentElement;
  var btn         = document.getElementById('themeToggle');

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    if (btn) {
      btn.setAttribute('aria-label',
        theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    }
  }

  if (btn) {
    btn.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      localStorage.setItem(STORAGE_KEY, next);
      apply(next);
    });
  }

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
    if (!localStorage.getItem(STORAGE_KEY)) {
      apply(e.matches ? 'dark' : 'light');
    }
  });
}());
</script>

<!-- ── Fade-in on Scroll ───────────────────────────────────────────────── -->
<script>
(function () {
  'use strict';
  var els = document.querySelectorAll('.fade-up');
  if (!els.length) return;

  if ('IntersectionObserver' in window) {
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.10 });
    els.forEach(function (el) { obs.observe(el); });
  } else {
    els.forEach(function (el) { el.classList.add('visible'); });
  }
}());
</script>

<!-- ── AJAX Likes Script (Step 9) ─────────────────────────────────── -->
<script src="/SocialNetwork/public/assets/js/likes.js"></script>

</body>
</html>
