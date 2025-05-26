<?php
// Test admin logs page complete HTML output
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1; 
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

try {
    echo "Testing complete logs page HTML output...\n";
    
    // Disable header modifications for testing
    ob_start();
    
    // Create controller and manually call the view render
    $logModel = new App\Models\Log();
    $logsData = $logModel->getPaginated(1, 50, []);
    $levels = $logModel->getLevels();
    $components = $logModel->getComponents();
    
    $viewData = [
        'title' => 'System Logs',
        'logs' => $logsData['logs'],
        'pagination' => [
            'page' => $logsData['page'],
            'perPage' => $logsData['perPage'],
            'total' => $logsData['total'],
            'totalPages' => $logsData['totalPages']
        ],
        'filters' => [
            'level' => null,
            'component' => null,
            'from_date' => null,
            'to_date' => null
        ],
        'filterOptions' => [
            'levels' => $levels,
            'components' => $components
        ]
    ];
    
    // Use View class directly to avoid header issues
    App\Core\View::render('admin/logs/index', $viewData, 'admin');
    
    $fullHtml = ob_get_clean();
    
    // Check various elements
    $checks = [
        'DOCTYPE html' => strpos($fullHtml, '<!DOCTYPE html') !== false,
        'Bootstrap CSS' => strpos($fullHtml, 'bootstrap') !== false,
        'MapIt Admin title' => strpos($fullHtml, 'MapIt Admin') !== false,
        'System Logs heading' => strpos($fullHtml, 'System Logs') !== false,
        'Table structure' => strpos($fullHtml, '<table') !== false,
        'Pagination' => strpos($fullHtml, 'pagination') !== false,
        'Admin CSS' => strpos($fullHtml, '/css/admin.css') !== false,
        'Container fluid' => strpos($fullHtml, 'container-fluid') !== false
    ];
    
    echo "HTML Output Analysis:\n";
    echo "===================\n";
    foreach ($checks as $check => $result) {
        echo sprintf("%-20s: %s\n", $check, $result ? "✓ FOUND" : "✗ MISSING");
    }
    
    echo "\nHTML Length: " . strlen($fullHtml) . " characters\n";
    
    if (strlen($fullHtml) > 5000) {
        echo "✓ Output looks complete (sufficient length)\n";
    } else {
        echo "✗ Output seems too short\n";
    }
    
    // Save a sample to file for inspection
    $sampleSize = min(2000, strlen($fullHtml));
    file_put_contents(__DIR__ . '/logs-page-sample.html', substr($fullHtml, 0, $sampleSize));
    echo "\nFirst " . $sampleSize . " characters saved to logs-page-sample.html\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
