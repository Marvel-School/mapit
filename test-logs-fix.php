<?php
// Test the logs SQL fix
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

try {
    echo "=== TESTING LOGS SQL FIX ===\n\n";
    
    // Test Log model
    $logsModel = new App\Models\Log();
      // Test 1: No filters (this was causing the SQL error)
    echo "1. Testing getPaginated with no filters... ";
    $result = $logsModel->getPaginated(1, 10, []);
    echo "✓ Success - found " . count($result['logs']) . " logs (total: " . $result['total'] . ")\n";
    
    // Test 2: With filters
    echo "2. Testing getPaginated with level filter... ";
    $result = $logsModel->getPaginated(1, 10, ['level' => 'error']);
    echo "✓ Success - found " . count($result['logs']) . " error logs\n";
    
    // Test 3: Test LogController
    echo "3. Testing LogController instantiation... ";
    $controller = new App\Controllers\Admin\LogController();
    echo "✓ Success\n";
    
    echo "\n✓ All tests passed! The SQL fix is working correctly.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
