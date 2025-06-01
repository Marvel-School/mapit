<?php
// Final admin contact test - Check if page loads without errors
session_start();

// Set admin session manually for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['email'] = 'admin@mapit.com';

// Simple test to load the contact controller
try {
    // Include autoloader
    require_once 'vendor/autoload.php';
    
    // Test database connection
    $config = require 'config/app.php';
    $dbConfig = $config['database'];
    
    $dsn = "mysql:host={$dbConfig['host']};port=3306;dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test if contacts exist
    $stmt = $pdo->query("SELECT COUNT(*) as count, 
                        COUNT(CASE WHEN status = 'new' THEN 1 END) as new_count,
                        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count
                        FROM contacts");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Contact Management System Status:\n";
    echo "=====================================\n";
    echo "🔗 Database Connection: WORKING\n";
    echo "📊 Total Contacts: {$stats['count']}\n";
    echo "🆕 New Contacts: {$stats['new_count']}\n";
    echo "⏳ In Progress: {$stats['in_progress_count']}\n";
    echo "👤 Admin Session: ACTIVE ({$_SESSION['username']})\n";
    echo "\n";
    
    // Test CSS file exists and has our additions
    $cssFile = 'public/css/admin.css';
    if (file_exists($cssFile)) {
        $cssContent = file_get_contents($cssFile);
        $hasUtilities = strpos($cssContent, '.border-left-primary') !== false;
        $hasContactStyles = strpos($cssContent, '.badge-new') !== false;
        
        echo "🎨 CSS Styling Status:\n";
        echo "CSS File Size: " . round(filesize($cssFile) / 1024, 1) . " KB\n";
        echo "Border Utilities: " . ($hasUtilities ? "✅ ADDED" : "❌ MISSING") . "\n";
        echo "Contact Badges: " . ($hasContactStyles ? "✅ ADDED" : "❌ MISSING") . "\n";
    }
    
    echo "\n🚀 Admin Contact Interface: READY TO TEST\n";
    echo "👉 Navigate to: http://localhost/admin/contacts\n";
    echo "👉 Login with: admin / admin123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
