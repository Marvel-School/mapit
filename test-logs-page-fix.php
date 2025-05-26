<?php
// Test admin logs page fix
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin'; 
$_SESSION['logged_in'] = true;

try {
    echo "=== TESTING ADMIN LOGS PAGE FIX ===\n";
    
    // Test LogController
    echo "1. Testing LogController... ";
    $controller = new App\Controllers\Admin\LogController();
    echo "✓ Success\n";
    
    // Test Log model
    echo "2. Testing Log model pagination... ";
    $logModel = new App\Models\Log();
    $result = $logModel->getPaginated(1, 10, []);
    echo "✓ Success - found " . $result['total'] . " total logs\n";
    
    // Check view file
    echo "3. Checking logs view file... ";
    $viewPath = __DIR__ . '/app/Views/admin/logs/index.php';
    if (file_exists($viewPath)) {
        $content = file_get_contents($viewPath);
        if (strpos($content, "<?php \$layout = 'admin'; ?>") !== false) {
            echo "✓ Success - view uses correct layout system\n";
        } else {
            echo "✗ Error - view doesn't use layout system\n";
        }
    } else {
        echo "✗ Error - view file missing\n";
    }
    
    // Check admin layout
    echo "4. Checking admin layout file... ";
    $layoutPath = __DIR__ . '/app/Views/layouts/admin.php';
    if (file_exists($layoutPath)) {
        echo "✓ Success - admin layout exists\n";
    } else {
        echo "✗ Error - admin layout missing\n";
    }
    
    echo "\n✅ Admin logs page should now be working!\n";
    echo "Visit: http://localhost:8080/admin/logs\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
