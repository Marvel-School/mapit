<?php
// Simple database connection test
$host = 'localhost';
$dbname = 'mapit';
$username = 'mapit_user';
$password = 'mapit_password';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if contacts table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) FROM contacts");
    $count = $stmt->fetchColumn();
    echo "Total contacts in database: $count\n";
    
    // Get a few sample contacts
    $stmt = $pdo->query("SELECT id, name, email, subject, status, created_at FROM contacts LIMIT 5");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nSample contacts:\n";
    foreach ($contacts as $contact) {
        echo "ID: {$contact['id']}, Name: {$contact['name']}, Email: {$contact['email']}, Status: {$contact['status']}\n";
    }
    
    // Check admin user exists
    $stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "\nAdmin user found: {$admin['username']} ({$admin['email']})\n";
    } else {
        echo "\nNo admin user found!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
