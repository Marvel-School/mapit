<?php
// Debug script to check user data
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

require_once '../vendor/autoload.php';
require_once '../app/Core/Autoloader.php';
App\Core\Autoloader::register();

use App\Models\User;

$userModel = new User();
$user = $userModel->find(1);

echo "<h2>User Data:</h2>";
echo "<pre>";
var_dump($user);
echo "</pre>";

echo "<h2>Specific Fields:</h2>";
echo "Name: ";
var_dump($user['name'] ?? 'NULL');
echo "<br>Username: ";
var_dump($user['username'] ?? 'NULL');
echo "<br>Email: ";
var_dump($user['email'] ?? 'NULL');
echo "<br>Avatar: ";
var_dump($user['avatar'] ?? 'NULL');
echo "<br>Bio: ";
var_dump($user['bio'] ?? 'NULL');
echo "<br>Country: ";
var_dump($user['country'] ?? 'NULL');
echo "<br>Website: ";
var_dump($user['website'] ?? 'NULL');
?>
