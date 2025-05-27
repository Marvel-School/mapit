<?php
// Test profile page rendering in Docker
session_start();

// Simulate being logged in
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

// Include the autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Try to render the profile page
try {
    $controller = new App\Controllers\DashboardController();
    
    echo "<h1>Testing Profile Page Rendering</h1>";
    echo "<hr>";
    
    ob_start();
    $controller->profile();
    $output = ob_get_clean();
    
    // Check what we got
    echo "<h2>Profile Page Output:</h2>";
    echo "<p><strong>Length:</strong> " . strlen($output) . " characters</p>";
    
    // Show first part of output
    echo "<h3>First 500 characters:</h3>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
    
    // Check for HTML structure
    echo "<h3>HTML Structure Check:</h3>";
    echo "<ul>";
    echo "<li>Contains DOCTYPE: " . (strpos($output, '<!DOCTYPE') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains HTML tag: " . (strpos($output, '<html') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains BODY tag: " . (strpos($output, '<body') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains Bootstrap CSS: " . (strpos($output, 'bootstrap') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains Navigation: " . (strpos($output, '<nav') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains Footer: " . (strpos($output, '<footer') !== false ? "✅" : "❌") . "</li>";
    echo "<li>Contains Profile Content: " . (strpos($output, 'My Profile') !== false ? "✅" : "❌") . "</li>";
    echo "</ul>";
    
    // Show if only content (no layout)
    if (strpos($output, '<!DOCTYPE') === false) {
        echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
        echo "<strong>⚠️ WARNING: Output contains only content, no layout!</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
