<?php
// Simple admin test
require_once __DIR__ . '/vendor/autoload.php';

session_start();

use App\Core\Database;

try {
    echo "Testing Admin Access\n";
    echo "===================\n\n";
    
    $db = Database::getInstance();
    
    // Test 1: Check admin user exists
    echo "1. Checking admin user... ";
    $admin = $db->query("SELECT id, username, password_hash, role FROM users WHERE username = :username")
                ->bind(':username', 'admin')
                ->single();
    
    if ($admin) {
        echo "✓ Found admin user: {$admin['username']}\n";
        
        // Test 2: Verify password
        echo "2. Testing password... ";
        if (password_verify('admin123', $admin['password_hash'])) {
            echo "✓ Password verified\n";
            
            // Test 3: Setup session (simulate login)
            echo "3. Setting up session... ";
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username']; 
            $_SESSION['role'] = $admin['role'];
            $_SESSION['logged_in'] = true;
            echo "✓ Session configured\n";
            
            echo "\nSession Details:\n";
            echo "- User ID: {$_SESSION['user_id']}\n";
            echo "- Username: {$_SESSION['username']}\n";
            echo "- Role: {$_SESSION['role']}\n";
            echo "- Logged In: " . ($_SESSION['logged_in'] ? 'Yes' : 'No') . "\n";
            
        } else {
            echo "✗ Password verification failed\n";
        }
    } else {
        echo "✗ Admin user not found\n";
    }
    
    echo "\n4. Testing admin controllers exist... ";
    
    $adminControllers = [
        'app/Controllers/Admin/DashboardController.php',
        'app/Controllers/Admin/UserController.php', 
        'app/Controllers/Admin/DestinationController.php',
        'app/Controllers/Admin/LogController.php'
    ];
    
    $allExist = true;
    foreach ($adminControllers as $controller) {
        if (!file_exists(__DIR__ . '/' . $controller)) {
            echo "\n   ✗ Missing: {$controller}";
            $allExist = false;
        }
    }
    
    if ($allExist) {
        echo "✓ All admin controllers exist\n";
    } else {
        echo "\n";
    }
    
    echo "\n5. Testing admin views exist... ";
    
    $adminViews = [
        'app/Views/admin/dashboard/index.php',
        'app/Views/admin/users/index.php',
        'app/Views/admin/destinations/index.php', 
        'app/Views/admin/logs/index.php'
    ];
    
    $allExist = true;
    foreach ($adminViews as $view) {
        if (!file_exists(__DIR__ . '/' . $view)) {
            echo "\n   ✗ Missing: {$view}";
            $allExist = false;
        }
    }
    
    if ($allExist) {
        echo "✓ All admin views exist\n";
    } else {
        echo "\n";
    }
    
    echo "\nAdmin test completed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
