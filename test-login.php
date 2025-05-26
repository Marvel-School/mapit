<?php
/**
 * Test script to verify login functionality
 */

// Include the application
require_once 'app/Core/Autoloader.php';

// Register autoloader
spl_autoload_register(['App\Core\Autoloader', 'load']);

// Load environment variables from .env file
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

// Session start
session_start();

// Test login for both users
$tests = [
    ['username' => 'admin', 'password' => 'admin123', 'label' => 'Admin User'],
    ['username' => 'Test User', 'password' => 'admin123', 'label' => 'Test User']
];

// Create User model directly
$userModel = new App\Models\User();

// Create a database instance to see raw queries
$db = App\Core\Database::getInstance();

foreach ($tests as $test) {
    $username = $test['username'];
    $password = $test['password'];
    $label = $test['label'];
    
    echo "=== Testing login for {$label} ===\n";
    echo "Username: {$username}\n";
    echo "Password: {$password}\n";
    
    // Check if user exists
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        echo "Searching by email...\n";
        $user = $userModel->findByEmail($username);
    } else {
        echo "Searching by username...\n";
        $user = $userModel->findByUsername($username);
    }
    
    if (!$user) {
        echo "❌ ERROR: User not found!\n";
        continue;
    }
    
    echo "✅ User found: ID #{$user['id']}, Username: {$user['username']}\n";
    echo "Password hash in DB: {$user['password_hash']}\n";
    
    // Test password verification
    $valid = password_verify($password, $user['password_hash']);
    
    if ($valid) {
        echo "✅ Password is valid!\n";
    } else {
        echo "❌ Password is incorrect!\n";
        
        // Debug information
        echo "Password hash verification failed.\n";
        echo "Raw password: {$password}\n";
        echo "Stored hash: {$user['password_hash']}\n";
        
        // Test with known good values
        echo "Testing with known default password...\n";
        echo "Default admin hash should be: \$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\n";
        $validDefault = password_verify('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
        echo "Verification with default hash: " . ($validDefault ? "✅ Works" : "❌ Fails") . "\n";
    }
    
    echo "\n";
}

echo "Tests completed!\n";
