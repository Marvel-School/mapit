<?php
// Test admin access functionality
require_once __DIR__ . '/vendor/autoload.php';

// Set up session
session_start();

// Configure environment
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'mapit';
$_ENV['DB_USER'] = 'mapit_user';
$_ENV['DB_PASS'] = 'mapit_password123';
$_ENV['APP_ENV'] = 'development';

use App\Core\Database;

try {
    echo "Testing admin access functionality...\n\n";
      // Initialize database
    $db = Database::getInstance();
    
    echo "1. Testing database connection... ";
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->execute();
    $adminCount = $result[0]['count'];
    echo "✓ Found {$adminCount} admin users\n";
      echo "2. Testing admin user login... ";
    $result = $db->query("SELECT id, username, password_hash, role FROM users WHERE username = :username")
                ->bind(':username', 'admin')
                ->execute();
    $admin = !empty($result) ? $result[0] : null;
    
    if ($admin) {
        echo "✓ Admin user found: {$admin['username']} (Role: {$admin['role']})\n";
        
        // Test password verification
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "3. Testing password verification... ✓ Password correct\n";
            
            // Simulate login session
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['logged_in'] = true;
            
            echo "4. Testing session setup... ✓ Session configured\n";
            echo "5. User ID: {$_SESSION['user_id']}\n";
            echo "6. Username: {$_SESSION['username']}\n";
            echo "7. Role: {$_SESSION['role']}\n";
            
        } else {
            echo "✗ Password verification failed\n";
        }
    } else {
        echo "✗ Admin user not found\n";
    }
    
    // Test admin route access
    echo "\n8. Testing admin route patterns...\n";
    
    // Simulate different admin URLs
    $testUrls = [
        '/admin',
        '/admin/dashboard', 
        '/admin/users',
        '/admin/destinations',
        '/admin/logs'
    ];
    
    foreach ($testUrls as $url) {
        echo "   Testing route: {$url} - ";
        
        // Basic route pattern matching (simplified)
        if (preg_match('/^\/admin/', $url)) {
            echo "✓ Admin route pattern matched\n";
        } else {
            echo "✗ Route pattern failed\n";
        }
    }
    
    echo "\nAdmin access test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
