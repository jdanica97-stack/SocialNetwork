<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=social_app', 'root', '');
$res = $pdo->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "LATEST NOTIFICATIONS:\n";
print_r($res);
$likes = $pdo->query('SELECT * FROM likes WHERE user_id = 2')->fetchAll(PDO::FETCH_ASSOC);
echo "BOB LIKES:\n";
print_r($likes);
