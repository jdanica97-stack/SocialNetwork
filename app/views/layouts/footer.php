<?php
/**
 * app/views/layouts/footer.php
 *
 * Reusable page footer included at the bottom of every view.
 * Contains: closing <main>, site footer, Bootstrap JS, closing HTML.
 */
?>

</main><!-- /.container (opened in header.php) -->

<!-- ── Site Footer ─────────────────────────────────────────────────────────── -->
<footer class="footer mt-auto py-3 bg-primary">
    <div class="container text-center">
        <span class="text-white small">
            &copy; <?= date('Y') ?> Mini Social Network &mdash; All rights reserved.
        </span>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle (CDN) — includes Popper for dropdowns/modals -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmE9d29u+a9V5hJJvHbFBgEwHm/"
    crossorigin="anonymous"
></script>

</body>
</html>
