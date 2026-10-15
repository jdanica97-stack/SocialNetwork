<?php
// Quick test to confirm the login page loads
$url = 'http://localhost/SocialNetwork/public/index.php?url=auth/login';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
$raw = curl_exec($ch);
$info = curl_getinfo($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($raw, $headerSize);
echo 'Status: ' . $info['http_code'] . PHP_EOL;
echo 'Has csrf-token: ' . (str_contains($body, 'csrf-token') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has username field: ' . (str_contains($body, 'username') ? 'YES' : 'NO') . PHP_EOL;
echo 'Redirected to: ' . ($info['url'] ?? $url) . PHP_EOL;
curl_close($ch);
