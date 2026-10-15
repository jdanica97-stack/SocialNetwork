<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=social_app','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $t) {
    echo "\n=== $t ===\n";
    $cols = $pdo->query("SHOW CREATE TABLE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    echo $cols[0]['Create Table'] . "\n";
}
