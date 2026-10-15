<?php
$content = file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js');
$hash = base64_encode(hash('sha384', $content, true));
echo 'ACTUAL HASH: sha384-' . $hash . "\n";
echo 'IN FOOTER:   sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmE9d29u+a9V5hJJvHbFBgEwHm/' . "\n";
