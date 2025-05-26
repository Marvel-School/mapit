<?php
/**
 * Debug script to test Google Maps API key loading
 */

// Load environment variables (same method as App.php)
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'].*["\']$/', $value)) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

echo "<h2>Environment Variable Debug</h2>";
echo "<p><strong>GOOGLE_MAPS_API_KEY from \$_ENV:</strong> " . ($_ENV['GOOGLE_MAPS_API_KEY'] ?? 'NOT SET') . "</p>";
echo "<p><strong>GOOGLE_MAPS_API_KEY from getenv():</strong> " . (getenv('GOOGLE_MAPS_API_KEY') ?: 'NOT SET') . "</p>";

// Load config file
$config = require __DIR__ . '/config/app.php';
echo "<p><strong>API key from config:</strong> " . ($config['google_maps']['api_key'] ?? 'NOT SET') . "</p>";

// Test the Controller method
require_once __DIR__ . '/app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

$controller = new App\Core\Controller();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getCommonViewData');
$method->setAccessible(true);
$commonData = $method->invoke($controller);

echo "<p><strong>API key from Controller::getCommonViewData():</strong> " . ($commonData['googleMapsApiKey'] ?? 'NOT SET') . "</p>";

// Test if .env file exists and is readable
echo "<h2>File System Debug</h2>";
echo "<p><strong>.env file exists:</strong> " . (file_exists($envFile) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>.env file readable:</strong> " . (is_readable($envFile) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>config/app.php exists:</strong> " . (file_exists(__DIR__ . '/config/app.php') ? 'YES' : 'NO') . "</p>";
