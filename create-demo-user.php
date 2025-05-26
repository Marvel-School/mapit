<?php
/**
 * Script to create a new user with a simple password
 */

// Include the application
require_once 'app/Core/Autoloader.php';

// Register autoloader
spl_autoload_register(['App\Core\Autoloader', 'load']);

// Load environment variables
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

// Session start for any session-dependent code
session_start();

// Database connection
$db = \App\Core\Database::getInstance();

// Create a new password hash
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generated password hash: {$hash}\n";

// Check if the demo user exists
$db->query("SELECT id FROM users WHERE username = :username");
$db->bind(':username', 'demo');
$user = $db->single();

if ($user) {
    // Update the existing demo user
    $db->query("UPDATE users SET 
        password_hash = :password_hash,
        name = :name,
        email = :email,
        bio = :bio,
        country = :country,
        website = :website,
        settings = :settings
        WHERE id = :id");
    
    $db->bind(':id', $user['id']);
    $db->bind(':password_hash', $hash);
    $db->bind(':name', 'Demo User');
    $db->bind(':email', 'demo@example.com');
    $db->bind(':bio', 'This is a demo account for testing.');
    $db->bind(':country', 'US');
    $db->bind(':website', 'https://example.com');
    $db->bind(':settings', json_encode([
        'public_profile' => true,
        'email_notifications' => true,
        'show_visited_places' => true
    ]));
    
    $success = $db->execute();
    
    if ($success) {
        echo "✅ Demo user updated successfully!\n";
    } else {
        echo "❌ Failed to update demo user.\n";
    }
    
} else {
    // Create a new demo user
    $db->query("INSERT INTO users (
        username, 
        password_hash, 
        email, 
        name,
        bio, 
        country, 
        website, 
        role,
        settings
    ) VALUES (
        :username,
        :password_hash,
        :email,
        :name,
        :bio,
        :country,
        :website,
        :role,
        :settings
    )");
    
    $db->bind(':username', 'demo');
    $db->bind(':password_hash', $hash);
    $db->bind(':email', 'demo@example.com');
    $db->bind(':name', 'Demo User');
    $db->bind(':bio', 'This is a demo account for testing.');
    $db->bind(':country', 'US');
    $db->bind(':website', 'https://example.com');
    $db->bind(':role', 'user');
    $db->bind(':settings', json_encode([
        'public_profile' => true,
        'email_notifications' => true,
        'show_visited_places' => true
    ]));
    
    $success = $db->execute();
    
    if ($success) {
        echo "✅ Demo user created successfully!\n";
    } else {
        echo "❌ Failed to create demo user.\n";
    }
}

// Test password verification
echo "\nTesting password verification:\n";
echo "Password: {$password}\n";
echo "Hash: {$hash}\n";

$valid = password_verify($password, $hash);
echo "Verification result: " . ($valid ? "✅ Success" : "❌ Failed") . "\n";

// Print login information
echo "\n=== LOGIN CREDENTIALS ===\n";
echo "Username: demo\n";
echo "Password: {$password}\n";
echo "========================\n";
