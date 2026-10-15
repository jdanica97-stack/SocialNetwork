<?php
require_once 'c:/wamp64/www/SocialNetwork/config/database.php';
require_once 'c:/wamp64/www/SocialNetwork/app/models/UserModel.php';
$db = getDBConnection();
$um = new UserModel($db);
$users = $um->getAllUsers(50);
echo "TOTAL USERS FOUND: " . count($users) . "\n";
foreach ($users as $u) {
    echo "ID: {$u['id']} | Username: {$u['username']} | Name: {$u['full_name']}\n";
}
