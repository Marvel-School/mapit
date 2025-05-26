<?php
// Load autoloader
require_once 'app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Set environment variables for local connection to Docker MySQL
putenv('DB_HOST=localhost');
putenv('DB_PORT=3306');
putenv('DB_DATABASE=mapit');
putenv('DB_USERNAME=mapit_user');
putenv('DB_PASSWORD=mapit_password');

// Create database connection
$db = App\Core\Database::getInstance();

// Check if connection works
if (!$db) {
    echo "Database connection failed!\n";
    exit(1);
}

echo "=== CREATING TEST USER ACCOUNTS ===\n\n";

// Test user credentials
$testUsers = [
    [
        'username' => 'testadmin',
        'email' => 'testadmin@mapit.com',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'username' => 'testmod',
        'email' => 'testmod@mapit.com', 
        'password' => 'mod123',
        'role' => 'moderator'
    ],
    [
        'username' => 'testuser',
        'email' => 'testuser@mapit.com',
        'password' => 'user123',
        'role' => 'user'
    ]
];

foreach ($testUsers as $userData) {
    // Check if user already exists
    $db->query('SELECT id FROM users WHERE email = :email OR username = :username');
    $db->bind(':email', $userData['email']);
    $db->bind(':username', $userData['username']);
    $existing = $db->single();
    
    if ($existing) {
        echo "User {$userData['username']} already exists, skipping...\n";
        continue;
    }
    
    // Hash the password
    $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Insert new user
    $db->query('
        INSERT INTO users (username, email, password_hash, role, created_at) 
        VALUES (:username, :email, :password_hash, :role, NOW())
    ');
    
    $db->bind(':username', $userData['username']);
    $db->bind(':email', $userData['email']);
    $db->bind(':password_hash', $passwordHash);
    $db->bind(':role', $userData['role']);
    
    if ($db->execute()) {
        echo "✓ Created user: {$userData['username']} ({$userData['role']})\n";
    } else {
        echo "✗ Failed to create user: {$userData['username']}\n";
    }
}

echo "\n=== ALL USER ACCOUNTS FOR TESTING ===\n\n";

// Get all users
$db->query('SELECT id, username, email, role, created_at FROM users ORDER BY id');
$users = $db->resultSet();

foreach ($users as $user) {
    echo "USER ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Email: {$user['email']}\n";
    echo "Role: {$user['role']}\n";
    echo "Created: {$user['created_at']}\n";
    
    // Show test passwords for accounts we just created
    if (in_array($user['username'], ['testadmin', 'testmod', 'testuser'])) {
        $passwords = [
            'testadmin' => 'admin123',
            'testmod' => 'mod123', 
            'testuser' => 'user123'
        ];
        echo "Password: {$passwords[$user['username']]}\n";
    } else {
        echo "Password: [Unknown - existing account]\n";
    }
    echo "----------------------------------------\n\n";
}

echo "LOGIN INFORMATION:\n";
echo "==================\n";
echo "Login URL: http://localhost/login\n";
echo "Admin Panel: http://localhost/admin (admin/moderator roles only)\n";
echo "User Dashboard: http://localhost/dashboard\n\n";

echo "TEST ACCOUNTS WITH KNOWN PASSWORDS:\n";
echo "===================================\n";
echo "1. testadmin@mapit.com / admin123 (Admin role)\n";
echo "2. testmod@mapit.com / mod123 (Moderator role)\n";
echo "3. testuser@mapit.com / user123 (User role)\n\n";

echo "EXISTING ACCOUNTS (unknown passwords):\n";
echo "======================================\n";
echo "- admin@mapit.com (Admin role)\n";
echo "- test@example.com (User role)\n";
echo "- demo@example.com (User role)\n\n";

echo "NOTE: You can also login with usernames instead of emails\n";
?>
