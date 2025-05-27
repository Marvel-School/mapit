<?php
// Auto-login script to test profile access
session_start();

require_once '../vendor/autoload.php';

// Include custom autoloader
require_once '../app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Initialize app
$app = new App\Core\App();
$app->init();

// Get user model and try to find test user
$userModel = new \App\Models\User();

// Find the test user (assuming it exists from our previous setup)
$testUser = $userModel->findByUsername('testuser');

if ($testUser) {
    // Set session for auto-login
    $_SESSION['user_id'] = $testUser['id'];
    $_SESSION['username'] = $testUser['username'];
    $_SESSION['user_role'] = $testUser['role'] ?? 'user';
    
    echo "<h2>Auto-Login Successful</h2>";
    echo "<p>✅ Logged in as: " . $testUser['username'] . " (ID: " . $testUser['id'] . ")</p>";
    echo "<p><a href='/profile' target='_blank'>Click here to access profile page</a></p>";
    echo "<p><a href='/dashboard' target='_blank'>Click here to access dashboard</a></p>";
    
    // Also provide direct links
    echo "<h3>Direct Access Links:</h3>";
    echo "<p><a href='http://localhost:8080/profile' target='_blank'>http://localhost:8080/profile</a></p>";
    echo "<p><a href='http://localhost:8080/dashboard' target='_blank'>http://localhost:8080/dashboard</a></p>";
    
} else {
    echo "<h2>Auto-Login Failed</h2>";
    echo "<p>❌ Test user 'testuser' not found in database</p>";
    echo "<p>Please run the setup-test-data.php script first</p>";
}
?>
