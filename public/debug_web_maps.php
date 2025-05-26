<?php
/**
 * Web debug script for Google Maps API key
 */

// Load environment variables (same method as App.php)
$envFile = __DIR__ . '/../.env';
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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Google Maps API Debug</title>
</head>
<body>
    <h2>Web Environment Debug</h2>
    <p><strong>GOOGLE_MAPS_API_KEY from $_ENV:</strong> <?= $_ENV['GOOGLE_MAPS_API_KEY'] ?? 'NOT SET' ?></p>
    <p><strong>GOOGLE_MAPS_API_KEY from getenv():</strong> <?= getenv('GOOGLE_MAPS_API_KEY') ?: 'NOT SET' ?></p>
      <?php
    // Load config file
    $config = require __DIR__ . '/../config/app.php';
    ?>
    <p><strong>API key from config:</strong> <?= $config['google_maps']['api_key'] ?? 'NOT SET' ?></p>
    
    <?php    // Test the Controller method
    require_once __DIR__ . '/../app/Core/Autoloader.php';
    spl_autoload_register(['App\\Core\\Autoloader', 'load']);

    $controller = new App\Core\Controller();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getCommonViewData');
    $method->setAccessible(true);
    $commonData = $method->invoke($controller);
    ?>
    
    <p><strong>API key from Controller::getCommonViewData():</strong> <?= $commonData['googleMapsApiKey'] ?? 'NOT SET' ?></p>
    
    <h2>Test Google Maps Script Tag</h2>
    <p>This is how the script tag should render:</p>
    <pre>&lt;script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $commonData['googleMapsApiKey'] ?? 'NOT SET' ?>&amp;libraries=places,marker&amp;callback=initializeGoogleMaps"&gt;&lt;/script&gt;</pre>
    
    <h2>Actual Google Maps Script</h2>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $commonData['googleMapsApiKey'] ?? 'NOT SET' ?>&libraries=places,marker&callback=initializeGoogleMaps"></script>
    <p>Check the browser's Network tab to see if the Google Maps API loads successfully.</p>
    
    <script>
    function initializeGoogleMaps() {
        console.log('Google Maps API loaded successfully!');
        document.body.innerHTML += '<p style="color: green;"><strong>✓ Google Maps API loaded successfully!</strong></p>';
    }
    
    // Check for errors
    window.gm_authFailure = function() {
        console.error('Google Maps API authentication failed');
        document.body.innerHTML += '<p style="color: red;"><strong>✗ Google Maps API authentication failed</strong></p>';
    };
    </script>
</body>
</html>
