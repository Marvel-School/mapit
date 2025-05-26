<?php
// Test logs page rendering after pagination fix
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

try {
    echo "=== TESTING LOGS PAGE RENDERING ===\n";
    
    $controller = new App\Controllers\Admin\LogController();
    
    // Capture output
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (strlen($output) > 1000) {
        echo "✓ Page rendered successfully - " . strlen($output) . " characters\n";
        
        // Check for Bootstrap classes (indicates styling is included)
        if (strpos($output, 'class="container-fluid"') !== false) {
            echo "✓ Bootstrap styling detected\n";
        } else {
            echo "✗ No Bootstrap styling found\n";
        }
        
        // Check for admin layout elements
        if (strpos($output, 'MapIt Admin') !== false) {
            echo "✓ Admin layout loaded\n";
        } else {
            echo "✗ Admin layout not loaded\n";
        }
        
        // Check if error still exists
        if (strpos($output, 'Undefined array key') !== false) {
            echo "✗ Still has undefined array key errors\n";
        } else {
            echo "✓ No undefined array key errors\n";
        }
        
    } else {
        echo "✗ Page render failed or too short: " . $output . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
