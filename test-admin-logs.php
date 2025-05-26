<?php
// Test admin logs functionality specifically
require_once __DIR__ . '/vendor/autoload.php';

session_start();

use App\Core\Database;
use App\Models\Log;

try {
    echo "=== TESTING ADMIN LOGS FUNCTIONALITY ===\n\n";
    
    $db = Database::getInstance();
    
    // Step 1: Login as admin
    echo "1. Setting up admin session... ";
    $admin = $db->query("SELECT id, username, password_hash, role FROM users WHERE username = :username")
                ->bind(':username', 'admin')
                ->single();
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username']; 
        $_SESSION['role'] = $admin['role'];
        $_SESSION['logged_in'] = true;
        echo "✓ Admin session established\n";
    } else {
        echo "✗ Admin user not found\n";
        exit(1);
    }
    
    // Step 2: Test basic logs query
    echo "2. Testing basic logs query... ";
    $basicQuery = $db->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 5")->resultSet();
    echo "✓ Found " . count($basicQuery) . " logs\n";
    
    // Step 3: Test Log model instantiation
    echo "3. Testing Log model... ";
    $logModel = new Log();
    echo "✓ Log model instantiated\n";
    
    // Step 4: Test pagination parameters
    echo "4. Testing pagination query directly... ";
    try {
        $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Test the exact query the LogController uses
        $sql = "SELECT * FROM logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $db->query($sql);
        $db->bind(':limit', $perPage, \PDO::PARAM_INT);
        $db->bind(':offset', $offset, \PDO::PARAM_INT);
        
        $logs = $db->resultSet();
        echo "✓ Pagination query successful, found " . count($logs) . " logs\n";
        
    } catch (Exception $e) {
        echo "✗ Pagination query failed: " . $e->getMessage() . "\n";
    }
    
    // Step 5: Test Log model getPaginated method
    echo "5. Testing Log model getPaginated method... ";
    try {
        $paginatedResult = $logModel->getPaginated(1, 10, []);
        echo "✓ getPaginated successful, found " . $paginatedResult['total'] . " total logs\n";
        echo "   - Current page: " . $paginatedResult['page'] . "\n";
        echo "   - Per page: " . $paginatedResult['perPage'] . "\n";
        echo "   - Total pages: " . $paginatedResult['totalPages'] . "\n";
        
    } catch (Exception $e) {
        echo "✗ getPaginated failed: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    // Step 6: Test LogController instantiation
    echo "6. Testing LogController... ";
    try {
        require_once __DIR__ . '/app/Controllers/Admin/LogController.php';
        echo "✓ LogController class loaded\n";
    } catch (Exception $e) {
        echo "✗ LogController failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== LOG TESTING COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
