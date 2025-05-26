<?php
/**
 * Test authentication and database connectivity
 */

require_once 'app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Start session
session_start();

try {
    echo "Testing database connection...\n";
    $db = App\Core\Database::getInstance();
    echo "Database connection: OK\n\n";
    
    // Check if there are test users
    $db->query('SELECT id, username, email, role FROM users WHERE username LIKE "test%" OR email LIKE "test%" LIMIT 5');
    $users = $db->resultSet();
    
    echo "Test users found: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "  ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
    echo "\n";
    
    // Test user authentication
    if (!empty($users)) {
        $testUser = $users[0];
        echo "Testing authentication with user: {$testUser['username']}\n";
        
        // Simulate login
        $_SESSION['user_id'] = $testUser['id'];
        $_SESSION['username'] = $testUser['username'];
        $_SESSION['user_role'] = $testUser['role'];
        
        echo "Session variables set:\n";
        echo "  user_id: {$_SESSION['user_id']}\n";
        echo "  username: {$_SESSION['username']}\n";
        echo "  user_role: {$_SESSION['user_role']}\n";
        
        // Test controller authentication
        $controller = new App\Core\Controller();
        echo "  isLoggedIn(): " . ($controller->isLoggedIn() ? 'true' : 'false') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
