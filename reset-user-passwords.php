<?php
/**
 * Script to reset admin password
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

// Create a new password hash for admin
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generated password hash for admin: {$hash}\n";

// Update the admin user password
$db->query("UPDATE users SET password_hash = :password_hash WHERE username = :username");
$db->bind(':username', 'admin');
$db->bind(':password_hash', $hash);

$success = $db->execute();

if ($success) {
    echo "✅ Admin password updated successfully!\n";
} else {
    echo "❌ Failed to update admin password.\n";
}

// Update the Test User password as well
$hash2 = password_hash($password, PASSWORD_DEFAULT);
echo "Generated password hash for Test User: {$hash2}\n";

$db->query("UPDATE users SET password_hash = :password_hash WHERE username = :username");
$db->bind(':username', 'Test User');
$db->bind(':password_hash', $hash2);

$success = $db->execute();

if ($success) {
    echo "✅ Test User password updated successfully!\n";
} else {
    echo "❌ Failed to update Test User password.\n";
}

// Test password verification
echo "\nTesting password verification for admin:\n";
echo "Password: {$password}\n";
echo "Hash: {$hash}\n";

$valid = password_verify($password, $hash);
echo "Verification result: " . ($valid ? "✅ Success" : "❌ Failed") . "\n";

// Print login information
echo "\n=== ADMIN LOGIN CREDENTIALS ===\n";
echo "Username: admin\n";
echo "Password: {$password}\n";

echo "\n=== TEST USER LOGIN CREDENTIALS ===\n";
echo "Username: Test User\n";
echo "Password: {$password}\n";
echo "================================\n";
