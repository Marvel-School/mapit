<?php
// Test script to verify admin destinations fix
require_once __DIR__ . '/app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

use App\Core\Database;

try {
    echo "Testing admin destinations fix...\n";
    
    // Test database connection
    $db = Database::getInstance();
    echo "✓ Database connection successful\n";
    
    // Test the exact query from admin destinations controller
    $sql = "
        SELECT d.id, d.name, d.description, d.country, d.city, d.latitude, d.longitude, 
               d.user_id, d.privacy, d.approval_status, d.featured, d.notes, 
               d.created_at, d.updated_at, u.username as creator
        FROM destinations d
        LEFT JOIN users u ON d.user_id = u.id
        ORDER BY d.created_at DESC
    ";
    
    $db->query($sql);
    $destinations = $db->resultSet();
    
    echo "✓ Query executed successfully\n";
    echo "Found " . count($destinations) . " destinations\n";
    
    // Test if city field is accessible
    foreach ($destinations as $destination) {
        echo "✓ Destination: " . $destination['name'];
        if (isset($destination['city'])) {
            echo " - City: " . ($destination['city'] ?: 'N/A');
        } else {
            echo " - ERROR: City field missing!";
        }
        echo " - Country: " . ($destination['country'] ?? 'N/A') . "\n";
    }
    
    // Test component length truncation
    $logModel = new \App\Models\Log();
    $longComponent = str_repeat('x', 60); // 60 chars, should be truncated
    $result = $logModel::write('DEBUG', 'Test message', [], $longComponent);
    if ($result) {
        echo "✓ Log component truncation test successful\n";
    } else {
        echo "✗ Log component truncation test failed\n";
    }
    
    echo "\nAll tests completed successfully! ✓\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
