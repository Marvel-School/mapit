<?php
// Test admin users functionality specifically
require_once __DIR__ . '/vendor/autoload.php';

session_start();

use App\Core\Database;
use App\Models\User;

try {
    echo "=== TESTING ADMIN USERS FUNCTIONALITY ===\n\n";
    
    $db = Database::getInstance();
    
    // Step 1: Login as admin
    echo "1. Setting up admin session... ";
    $admin = $db->query("SELECT id, username, password_hash, role FROM users WHERE username = :username")
                ->bind(':username', 'admin')
                ->single();
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username']; 
        $_SESSION['role'] = $admin['role'];
        $_SESSION['logged_in'] = true;
        echo "✓ Admin session established\n";
    } else {
        echo "✗ Admin user not found\n";
        exit(1);
    }
    
    // Step 2: Test User model instantiation
    echo "2. Testing User model... ";
    $userModel = new User();
    echo "✓ User model instantiated\n";
    
    // Step 3: Test User model all() method
    echo "3. Testing User model all() method... ";
    try {
        $users = $userModel->all();
        echo "✓ Found " . count($users) . " users\n";
        
        // Display user details
        foreach ($users as $user) {
            echo "   - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}\n";
        }
        
    } catch (Exception $e) {
        echo "✗ all() method failed: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Test UserController instantiation
    echo "4. Testing UserController... ";
    try {
        require_once __DIR__ . '/app/Controllers/Admin/UserController.php';
        echo "✓ UserController class loaded\n";
    } catch (Exception $e) {
        echo "✗ UserController failed: " . $e->getMessage() . "\n";
    }
    
    // Step 5: Check users view template
    echo "5. Testing users view template... ";
    $viewPath = __DIR__ . '/app/Views/admin/users/index.php';
    if (file_exists($viewPath)) {
        $content = file_get_contents($viewPath);
        if (strpos($content, 'last_login') !== false) {
            echo "✓ View template exists and references have been updated\n";
        } else {
            echo "⚠ View template exists but may need updates\n";
        }
    } else {
        echo "✗ View template not found\n";
    }
    
    echo "\n=== USERS TESTING COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
