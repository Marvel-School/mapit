<?php
// Debug logs page layout loading
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

echo "=== DEBUGGING LOGS PAGE LAYOUT ===\n\n";

try {
    // Test 1: Check if admin layout file exists
    $adminLayoutPath = __DIR__ . '/app/Views/layouts/admin.php';
    echo "1. Admin layout file: " . (file_exists($adminLayoutPath) ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
    // Test 2: Check logs view file
    $logsViewPath = __DIR__ . '/app/Views/admin/logs/index.php';
    echo "2. Logs view file: " . (file_exists($logsViewPath) ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
    // Test 3: Check first few lines of logs view
    if (file_exists($logsViewPath)) {
        $lines = file($logsViewPath, FILE_IGNORE_NEW_LINES);
        echo "3. First line of logs view: " . $lines[0] . "\n";
        echo "   Second line: " . (isset($lines[1]) ? $lines[1] : 'N/A') . "\n";
    }
    
    // Test 4: Check admin layout content
    if (file_exists($adminLayoutPath)) {
        $layoutContent = file_get_contents($adminLayoutPath);
        $hasBootstrap = strpos($layoutContent, 'bootstrap') !== false;
        $hasContent = strpos($layoutContent, '$content') !== false;
        echo "4. Admin layout has Bootstrap: " . ($hasBootstrap ? "✓ YES" : "✗ NO") . "\n";
        echo "   Admin layout has \$content: " . ($hasContent ? "✓ YES" : "✗ NO") . "\n";
    }
    
    // Test 5: Try to render the logs page and check layout loading
    echo "5. Testing logs page render...\n";
    
    // Simulate rendering without headers
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/admin/logs';
    
    $logController = new App\Controllers\Admin\LogController();
    
    // Get log data
    $logModel = new App\Models\Log();
    $result = $logModel->getPaginated(1, 10, []);
    echo "   Log data: ✓ " . count($result['logs']) . " logs found\n";
    
    // Check view variables
    echo "6. Data passed to view:\n";
    echo "   - title: 'System Logs'\n";
    echo "   - logs: " . count($result['logs']) . " items\n";
    echo "   - pagination: totalPages=" . $result['totalPages'] . ", page=" . $result['page'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "If layout is not loading:\n";
echo "1. Check browser network tab for CSS loading errors\n";
echo "2. Verify admin.css and bootstrap CDN are accessible\n";
echo "3. Check if \$layout variable is being set correctly\n";
echo "4. Ensure Controller->view() method is passing layout parameter\n";
