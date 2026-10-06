<?php
/**
 * public/index.php
 *
 * Temporary layout test page.
 * Verifies that Bootstrap, the navbar, header, footer,
 * and custom CSS all load correctly.
 *
 * This test content will be replaced by the front controller
 * router in a later step.
 */

define('BASE_PATH', dirname(__DIR__));

$pageTitle = 'Mini Social Network — Home';

require_once BASE_PATH . '/app/views/layouts/header.php';
?>

    <!-- ── Test Content ──────────────────────────────────────── -->
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Layout Test
                </div>
                <div class="card-body">
                    <h5 class="card-title">Bootstrap + Base Layout is working!</h5>
                    <p class="card-text text-muted">
                        The header, navbar, footer, Bootstrap 5, Bootstrap Icons,
                        and custom CSS are all loading correctly.
                    </p>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Bootstrap CSS loaded
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Bootstrap Icons loaded
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Custom style.css loaded
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Navbar renders correctly
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Footer renders correctly
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check2 text-success me-2"></i>Responsive on all screen sizes
                        </li>
                    </ul>
                    <span class="badge bg-success">Ready for next step</span>
                </div>
            </div>

        </div>
    </div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
