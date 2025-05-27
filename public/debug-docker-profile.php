<?php
// Debug profile page in Docker environment
session_start();

echo "<h1>Docker Profile Page Debug</h1>";

// Set up test user session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

echo "<h2>Step 1: Environment Check</h2>";
echo "<p>Server: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "</p>";
echo "<p>Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'unknown') . "</p>";

// Include autoloader
require_once '../vendor/autoload.php';
require_once '../app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

echo "<h2>Step 2: Test DashboardController Profile Method</h2>";

try {
    $controller = new \App\Controllers\DashboardController();
    echo "<p>✅ Controller created</p>";
    
    // Test if we can access the profile method
    ob_start();
    $controller->profile();
    $output = ob_get_clean();
    
    echo "<p>Output length: " . strlen($output) . " characters</p>";
    
    // Check what we got
    $hasDoctype = strpos($output, '<!DOCTYPE html') !== false;
    $hasHtml = strpos($output, '<html') !== false;
    $hasHead = strpos($output, '<head') !== false;
    $hasBootstrap = strpos($output, 'bootstrap') !== false;
    $hasCSS = strpos($output, '/css/style.css') !== false;
    
    echo "<h3>Output Analysis:</h3>";
    echo "<ul>";
    echo "<li>Has DOCTYPE: " . ($hasDoctype ? "✅" : "❌") . "</li>";
    echo "<li>Has HTML tag: " . ($hasHtml ? "✅" : "❌") . "</li>";
    echo "<li>Has HEAD tag: " . ($hasHead ? "✅" : "❌") . "</li>";
    echo "<li>Has Bootstrap: " . ($hasBootstrap ? "✅" : "❌") . "</li>";
    echo "<li>Has Custom CSS: " . ($hasCSS ? "✅" : "❌") . "</li>";
    echo "</ul>";
    
    if (!$hasDoctype || !$hasHtml) {
        echo "<h3>❌ ISSUE: Output is not complete HTML!</h3>";
        echo "<p>The view rendering is not working properly in Docker.</p>";
    }
    
    // Show the actual output
    echo "<h3>Actual Output (first 1000 chars):</h3>";
    echo "<textarea rows='20' cols='100'>" . htmlspecialchars(substr($output, 0, 1000)) . "</textarea>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Step 3: Check File Paths</h2>";

// Check if view files exist
$profileView = '../app/Views/dashboard/profile.php';
$mainLayout = '../app/Views/layouts/main.php';

echo "<p>Profile view exists: " . (file_exists($profileView) ? "✅" : "❌") . " ($profileView)</p>";
echo "<p>Main layout exists: " . (file_exists($mainLayout) ? "✅" : "❌") . " ($mainLayout)</p>";

if (file_exists($profileView)) {
    echo "<p>Profile view size: " . filesize($profileView) . " bytes</p>";
}

if (file_exists($mainLayout)) {
    echo "<p>Main layout size: " . filesize($mainLayout) . " bytes</p>";
}

echo "<h2>Step 4: Test Direct Router</h2>";

try {
    $app = new App\Core\App();
    $app->init();
    
    $router = new App\Core\Router();
    $router->load('../config/routes.php');
    
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/profile';
    
    $uri = \App\Core\Request::uri();
    echo "<p>Parsed URI: '$uri'</p>";
    
    $route = $router->match($uri, 'GET');
    if ($route) {
        echo "<p>✅ Route matched: {$route['controller']}::{$route['action']}</p>";
    } else {
        echo "<p>❌ No route matched</p>";
    }
    
    // Test direct dispatch
    ob_start();
    $router->dispatch($uri, 'GET');
    $routerOutput = ob_get_clean();
    
    echo "<p>Router output length: " . strlen($routerOutput) . " characters</p>";
    
    if (strlen($routerOutput) > 0) {
        $routerHasDoctype = strpos($routerOutput, '<!DOCTYPE html') !== false;
        echo "<p>Router output has DOCTYPE: " . ($routerHasDoctype ? "✅" : "❌") . "</p>";
        
        if (!$routerHasDoctype) {
            echo "<h3>❌ Router also produces incomplete output</h3>";
            echo "<p>First 500 chars of router output:</p>";
            echo "<textarea rows='10' cols='100'>" . htmlspecialchars(substr($routerOutput, 0, 500)) . "</textarea>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Router error: " . $e->getMessage() . "</p>";
}
?>
