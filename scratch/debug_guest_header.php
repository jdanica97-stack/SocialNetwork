<?php
define('BASE_PATH', dirname(__DIR__));
$_SESSION = [];
ob_start();
require BASE_PATH . '/app/views/layouts/header.php';
$html = ob_get_clean();

echo 'Length: ' . strlen($html) . PHP_EOL;
echo 'Has sidePanel: ' . (strpos($html, 'id="sidePanel"') !== false ? 'yes' : 'no') . PHP_EOL;
if (preg_match('/<[a-z]+[^>]*logo-panel-btn[^>]*>/i', $html, $m)) {
    echo 'Matched element: ' . $m[0] . PHP_EOL;
}
