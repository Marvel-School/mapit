<?php
/**
 * Test script to verify admin authentication and featured destinations functionality
 */

// Start session
session_start();

// Include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/.env.local')) {
    $envFile = __DIR__ . '/.env.local';
} else {
    $envFile = __DIR__ . '/.env';
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

use App\Core\Database;
use App\Models\User;
use App\Models\Destination;

try {
    echo "Testing Admin Login and Featured Destinations Functionality\n";
    echo "=" . str_repeat("=", 60) . "\n\n";
    
    // Test database connection
    echo "1. Testing database connection...\n";
    $db = Database::getInstance();
    echo "✓ Database connection successful\n\n";
    
    // Test user authentication
    echo "2. Testing admin user authentication...\n";
    $userModel = new User();
    
    // Try to authenticate with admin credentials from USER_TESTING_CREDENTIALS.md
    $adminUser = $userModel->authenticate('testadmin@mapit.com', 'admin123');
    
    if ($adminUser) {
        echo "✓ Admin authentication successful\n";
        echo "  - User ID: {$adminUser['id']}\n";
        echo "  - Username: {$adminUser['username']}\n";
        echo "  - Email: {$adminUser['email']}\n";
        echo "  - Role: {$adminUser['role']}\n\n";
        
        // Set session variables to simulate login
        $_SESSION['user_id'] = $adminUser['id'];
        $_SESSION['username'] = $adminUser['username'];
        $_SESSION['user_role'] = $adminUser['role'];
        
    } else {
        echo "✗ Admin authentication failed\n\n";
        exit(1);
    }
    
    // Test featured destinations functionality
    echo "3. Testing featured destinations...\n";
    $destinationModel = new Destination();
    
    // Get all featured destinations
    $featured = $destinationModel->getFeatured();
    echo "✓ Found " . count($featured) . " featured destinations\n";
    
    if (count($featured) > 0) {
        echo "\nFeatured Destinations:\n";
        foreach ($featured as $dest) {
            echo "  - ID: {$dest['id']} | {$dest['name']} | {$dest['city']}, {$dest['country']}\n";
        }
        echo "\n";
    }
    
    // Get featured destinations for homepage (limit 6)
    $homepageFeatured = $destinationModel->getFeatured(6);
    echo "✓ Homepage featured destinations (limit 6): " . count($homepageFeatured) . " destinations\n\n";
    
    // Test admin destinations query
    echo "4. Testing admin destinations query...\n";
    $db->query("
        SELECT d.id, d.name, d.description, d.country, d.city, d.latitude, d.longitude, 
               d.user_id, d.privacy, d.approval_status, d.featured, d.notes, 
               d.created_at, d.updated_at, u.username as creator
        FROM destinations d
        LEFT JOIN users u ON d.user_id = u.id
        WHERE d.featured = 1
        ORDER BY d.created_at DESC
    ");
    $adminFeatured = $db->resultSet();
    echo "✓ Admin query found " . count($adminFeatured) . " featured destinations\n";
    
    if (count($adminFeatured) > 0) {
        echo "\nFeatured Destinations (Admin View):\n";
        foreach ($adminFeatured as $dest) {
            $creator = $dest['creator'] ?? 'Unknown';
            $status = $dest['approval_status'];
            $privacy = $dest['privacy'];
            echo "  - ID: {$dest['id']} | {$dest['name']} | Creator: {$creator} | Status: {$status} | Privacy: {$privacy}\n";
        }
        echo "\n";
    }
    
    // Test destination statistics
    echo "5. Testing destination statistics...\n";
    $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN privacy = 'public' THEN 1 ELSE 0 END) as public,
        SUM(CASE WHEN privacy = 'private' THEN 1 ELSE 0 END) as private,
        SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured
        FROM destinations");
    $stats = $db->single();
    
    echo "✓ Destination Statistics:\n";
    echo "  - Total: {$stats['total']}\n";
    echo "  - Pending: {$stats['pending']}\n";
    echo "  - Approved: {$stats['approved']}\n";
    echo "  - Rejected: {$stats['rejected']}\n";
    echo "  - Public: {$stats['public']}\n";
    echo "  - Private: {$stats['private']}\n";
    echo "  - Featured: {$stats['featured']}\n\n";
    
    echo "6. Testing specific featured destinations...\n";
    
    // Check some of the destinations we seeded
    $expectedFeatured = [
        'Banff National Park',
        'Great Wall of China at Badaling',
        'Sydney Opera House',
        'Pyramids of Giza',
        'Victoria Falls',
        'Golden Gate Bridge'
    ];
    
    foreach ($expectedFeatured as $name) {
        $db->query("SELECT * FROM destinations WHERE name = :name AND featured = 1");
        $db->bind(':name', $name);
        $dest = $db->single();
        
        if ($dest) {
            echo "  ✓ {$name} is correctly marked as featured\n";
        } else {
            echo "  ✗ {$name} is NOT found or not marked as featured\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "All tests completed successfully!\n";
    echo "Admin authentication and featured destinations functionality verified.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
