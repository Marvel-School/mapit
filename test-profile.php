<?php
/**
 * Test script to verify profile page functionality
 */

// Start session
session_start();

// Simulate logged in user
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['user_role'] = 'admin';

// Include the application
require_once 'app/Core/Autoloader.php';

// Register autoloader
spl_autoload_register(['App\Core\Autoloader', 'load']);

// Load Docker environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'].*["\']$/', $value)) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

// Test the DashboardController profile method
try {
    $controller = new App\Controllers\DashboardController();
    
    echo "Testing profile page...\n";
    
    // Try to access profile method
    ob_start();
    $controller->profile();
    $output = ob_get_clean();
    
    echo "Profile page loaded successfully!\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
    // Check if output contains expected elements
    if (strpos($output, 'My Profile') !== false) {
        echo "✅ Profile title found\n";
    }
    
    if (strpos($output, 'countries') !== false) {
        echo "✅ Countries data found\n";
    }
    
    if (strpos($output, 'Administrator') !== false) {
        echo "✅ User name found\n";
    }
    
    echo "\nProfile page test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
