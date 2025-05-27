<?php
// Debug script to capture what the profile page actually outputs
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

// Capture the output
ob_start();

// Include the main application
require_once '../app/Core/Autoloader.php';
App\Core\Autoloader::register();

use App\Core\App;
use App\Core\Request;

$app = new App();
$request = new Request();

// Manually set the path to profile
$_SERVER['REQUEST_URI'] = '/profile';
$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    $app->run($request);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

$output = ob_get_clean();

// Show both raw and escaped output
echo "<h2>Raw Output:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

echo "<h2>Rendered Output:</h2>";
echo $output;
?>
