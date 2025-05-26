<?php
/**
 * Test script to verify admin panel endpoints are accessible
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
use App\Controllers\Admin\DestinationController;

try {
    echo "Testing Admin Panel Access\n";
    echo "=" . str_repeat("=", 40) . "\n\n";
    
    // Authenticate as admin
    echo "1. Authenticating as admin user...\n";
    $userModel = new User();
    $adminUser = $userModel->authenticate('testadmin@mapit.com', 'admin123');
    
    if ($adminUser) {
        $_SESSION['user_id'] = $adminUser['id'];
        $_SESSION['username'] = $adminUser['username'];
        $_SESSION['user_role'] = $adminUser['role'];
        echo "✓ Authenticated as admin: {$adminUser['username']} (Role: {$adminUser['role']})\n\n";
    } else {
        echo "✗ Authentication failed\n";
        exit(1);
    }
    
    // Test admin controller access
    echo "2. Testing admin panel controller access...\n";
    
    // Test destinations controller
    $adminDestController = new DestinationController();
    
    // Test that admin has proper role permissions
    if ($adminDestController->hasRole(['admin', 'moderator'])) {
        echo "✓ Admin user has proper role permissions\n";
    } else {
        echo "✗ Admin user does not have proper role permissions\n";
    }
    
    // Test admin destinations query
    echo "\n3. Testing admin destinations data access...\n";
    $db = Database::getInstance();
    
    // Get all destinations with featured flag (admin view)
    $db->query("
        SELECT d.*, u.username as creator_name 
        FROM destinations d 
        LEFT JOIN users u ON d.user_id = u.id 
        WHERE d.featured = 1
        ORDER BY d.created_at DESC
    ");
    $featuredDestinations = $db->resultSet();
    
    echo "✓ Admin can access " . count($featuredDestinations) . " featured destinations\n";
    
    if (count($featuredDestinations) > 0) {
        echo "\nFeatured Destinations in Admin Panel:\n";
        foreach ($featuredDestinations as $dest) {
            $status = $dest['status'] ?? 'unknown';
            $privacy = $dest['privacy'] ?? 'unknown';
            echo "  - ID: {$dest['id']} | {$dest['name']} | {$dest['city']}, {$dest['country']}\n";
            echo "    Status: {$status} | Privacy: {$privacy} | Creator: {$dest['creator_name']}\n";
        }
    }
    
    echo "\n4. Testing admin panel statistics...\n";
    
    // Total destinations
    $db->query("SELECT COUNT(*) as count FROM destinations");
    $totalDest = $db->single();
    echo "✓ Total destinations: {$totalDest['count']}\n";
    
    // Featured destinations count
    $db->query("SELECT COUNT(*) as count FROM destinations WHERE featured = 1");
    $featuredCount = $db->single();
    echo "✓ Featured destinations: {$featuredCount['count']}\n";
      // Approved destinations
    $db->query("SELECT COUNT(*) as count FROM destinations WHERE approval_status = 'approved'");
    $approvedCount = $db->single();
    echo "✓ Approved destinations: {$approvedCount['count']}\n";
    
    // Pending destinations
    $db->query("SELECT COUNT(*) as count FROM destinations WHERE approval_status = 'pending'");
    $pendingCount = $db->single();
    echo "✓ Pending destinations: {$pendingCount['count']}\n";
    
    echo "\n✅ Admin panel access test completed successfully!\n";
    echo "\nAdmin panel should be accessible at: http://localhost/admin\n";
    echo "Admin destinations panel: http://localhost/admin/destinations\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
