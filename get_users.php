<?php
// Load autoloader
require_once 'app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Create database connection
$db = App\Core\Database::getInstance();

// Query all users with their details
$db->query('SELECT id, username, email, role, created_at, last_login FROM users ORDER BY id');
$users = $db->resultSet();

if ($users) {
    echo "\n=== USER LOGIN CREDENTIALS FOR TESTING ===\n\n";
    
    foreach ($users as $user) {
        echo "USER ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Created: " . $user['created_at'] . "\n";
        echo "Last Login: " . ($user['last_login'] ?: 'Never') . "\n";
        echo "Password: [Hashed - see below for test info]\n";
        echo "----------------------------------------\n\n";
    }
    
    echo "TESTING INFORMATION:\n";
    echo "===================\n";
    echo "- All passwords in database are hashed for security\n";
    echo "- Need to either:\n";
    echo "  1. Create new test accounts with known passwords\n";
    echo "  2. Reset existing user passwords to known values\n";
    echo "  3. Check if any test users were already created\n\n";
    
    echo "LOGIN URLS:\n";
    echo "===========\n";
    echo "- Login Page: http://localhost/login\n";
    echo "- Admin Panel: http://localhost/admin (requires admin role)\n";
    echo "- User Dashboard: http://localhost/dashboard (for regular users)\n\n";
    
    echo "SUGGESTED TEST ACCOUNTS TO CREATE:\n";
    echo "==================================\n";
    echo "1. admin@test.com / admin123 (Admin role)\n";
    echo "2. mod@test.com / mod123 (Moderator role)\n";
    echo "3. user@test.com / user123 (User role)\n\n";
    
} else {
    echo "No users found in database\n";
}
?>
