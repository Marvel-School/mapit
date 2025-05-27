<?php
// Auto-login for Docker environment
session_start();

require_once '../vendor/autoload.php';
require_once '../app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

echo "<h1>Docker Auto-Login</h1>";

try {
    // Initialize app
    $app = new App\Core\App();
    $app->init();
    
    // Get user model
    $userModel = new \App\Models\User();
    
    // Find test user
    $testUser = $userModel->findByUsername('testuser');
    
    if ($testUser) {
        // Set up session
        $_SESSION['user_id'] = $testUser['id'];
        $_SESSION['username'] = $testUser['username'];
        $_SESSION['user_role'] = $testUser['role'] ?? 'user';
        
        echo "<p>✅ Successfully logged in as: " . $testUser['username'] . "</p>";
        echo "<p>User ID: " . $testUser['id'] . "</p>";
        echo "<p>Session ID: " . session_id() . "</p>";
        
        // Now test profile access
        echo "<h2>Testing Profile Page Access</h2>";
        
        // Create controller and test
        $controller = new \App\Controllers\DashboardController();
        
        ob_start();
        $controller->profile();
        $output = ob_get_clean();
        
        echo "<p>Profile output length: " . strlen($output) . " characters</p>";
        
        $hasCompleteHTML = strpos($output, '<!DOCTYPE html') !== false && strpos($output, '</html>') !== false;
        
        if ($hasCompleteHTML) {
            echo "<p>✅ Profile page generates complete HTML</p>";
        } else {
            echo "<p>❌ Profile page output is incomplete</p>";
            echo "<h3>Debug: First 800 characters of output:</h3>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 800)) . "</pre>";
        }
        
        echo "<hr>";
        echo "<h2>Access Links</h2>";
        echo "<p><a href='/dashboard' target='_blank'>Dashboard</a></p>";
        echo "<p><a href='/profile' target='_blank'>Profile Page</a></p>";
        
    } else {
        echo "<p>❌ Test user 'testuser' not found</p>";
        echo "<p>Run setup-test-data.php first to create the test user</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
