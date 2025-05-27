<?php
// Test the fixed profile page in Docker
session_start();

// Set up session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

echo "<h1>Profile Page Fix Test - Docker</h1>";

require_once '../vendor/autoload.php';
require_once '../app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

try {
    $controller = new \App\Controllers\DashboardController();
    
    echo "<h2>Testing Fixed Controller</h2>";
    
    ob_start();
    $controller->profile();
    $output = ob_get_clean();
    
    echo "<p>Output length: " . strlen($output) . " characters</p>";
    
    $hasDoctype = strpos($output, '<!DOCTYPE html') !== false;
    $hasHtml = strpos($output, '<html') !== false;
    $hasBootstrap = strpos($output, 'bootstrap') !== false;
    $hasContent = strpos($output, 'My Profile') !== false;
    $hasForm = strpos($output, '<form') !== false;
    $hasNav = strpos($output, '<nav') !== false;
    $hasFooter = strpos($output, '<footer') !== false;
    
    echo "<h3>Analysis:</h3>";
    echo "<ul>";
    echo "<li>Has DOCTYPE: " . ($hasDoctype ? "✅" : "❌") . "</li>";
    echo "<li>Has HTML tag: " . ($hasHtml ? "✅" : "❌") . "</li>";
    echo "<li>Has Bootstrap: " . ($hasBootstrap ? "✅" : "❌") . "</li>";
    echo "<li>Has Profile Content: " . ($hasContent ? "✅" : "❌") . "</li>";
    echo "<li>Has Form: " . ($hasForm ? "✅" : "❌") . "</li>";
    echo "<li>Has Navigation: " . ($hasNav ? "✅" : "❌") . "</li>";
    echo "<li>Has Footer: " . ($hasFooter ? "✅" : "❌") . "</li>";
    echo "</ul>";
    
    if ($hasDoctype && $hasHtml && $hasBootstrap && $hasContent) {
        echo "<h3>🎉 SUCCESS! Profile page is now working correctly!</h3>";
        echo "<p>The fix resolved the issue - profile page now has complete HTML structure.</p>";
    } else {
        echo "<h3>❌ Still having issues</h3>";
        echo "<p>Showing first 600 characters of output:</p>";
        echo "<textarea rows='12' cols='100'>" . htmlspecialchars(substr($output, 0, 600)) . "</textarea>";
    }
    
    // Test direct access
    echo "<h2>Direct Access Test</h2>";
    echo "<p><a href='/profile' target='_blank'>Test Profile Page</a></p>";
    echo "<p><strong>Expected:</strong> Full profile page with proper styling and layout</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
