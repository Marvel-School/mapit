<?php
// Test log badge styling
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

try {
    echo "=== TESTING LOG BADGE STYLING ===\n";
    
    // Test that the CSS file exists and can be read
    $cssPath = __DIR__ . '/public/css/admin.css';
    if (file_exists($cssPath)) {
        echo "✓ Admin CSS file exists\n";
        
        $cssContent = file_get_contents($cssPath);
        
        // Check for our new badge styles
        $badgeStyles = ['badge-debug', 'badge-info', 'badge-warning', 'badge-error', 'badge-critical'];
        foreach ($badgeStyles as $style) {
            if (strpos($cssContent, $style) !== false) {
                echo "✓ Found $style styling\n";
            } else {
                echo "✗ Missing $style styling\n";
            }
        }
        
        // Check that CSS is being served correctly
        echo "\n=== CSS ACCESSIBILITY TEST ===\n";
        echo "CSS file size: " . filesize($cssPath) . " bytes\n";
        echo "CSS file is readable: " . (is_readable($cssPath) ? 'Yes' : 'No') . "\n";
        
    } else {
        echo "✗ Admin CSS file not found at: $cssPath\n";
    }
    
    // Test the logs page rendering
    echo "\n=== TESTING LOGS PAGE RENDERING ===\n";
    $controller = new App\Controllers\Admin\LogController();
    
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    // Check if badges are in the output
    if (strpos($output, 'badge badge-') !== false) {
        echo "✓ Badge classes found in output\n";
    } else {
        echo "✗ No badge classes found in output\n";
    }
    
    if (strpos($output, 'bg-secondary text-white') !== false) {
        echo "✓ Component badge styling found\n";
    } else {
        echo "✗ Component badge styling not found\n";
    }
    
    echo "\n✅ Log badge styling test completed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
