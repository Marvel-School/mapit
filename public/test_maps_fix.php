<?php
/**
 * Test page for Google Maps integration after fix
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

// Load autoloader
require_once __DIR__ . '/../app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Get Google Maps API key from config
$config = require __DIR__ . '/../config/app.php';
$apiKey = $config['google_maps']['api_key'] ?? '';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - Google Maps Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        #test-map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1>Google Maps Integration Test</h1>
                <p class="lead">This page tests if the Google Maps API is working properly.</p>
                <p>API Key Status: <strong id="api-status">Checking...</strong></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div id="test-map"></div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let map;
        
        function initializeGoogleMaps() {
            document.getElementById('api-status').innerHTML = '<span class="text-success">API Loaded Successfully!</span>';
            
            // Initialize a simple map centered on a default location
            map = new google.maps.Map(document.getElementById('test-map'), {
                center: { lat: 40.7128, lng: -74.0060 }, // New York
                zoom: 8
            });
            
            // Add a marker
            const marker = new google.maps.Marker({
                position: { lat: 40.7128, lng: -74.0060 },
                map: map,
                title: 'New York'
            });
        }
        
        function handleMapError() {
            document.getElementById('api-status').innerHTML = '<span class="text-danger">API Failed to Load</span>';
            document.getElementById('test-map').innerHTML = '<div class="alert alert-danger">Failed to load Google Maps. Please check the API key and browser console for errors.</div>';
        }
    </script>
    
    <!-- Google Maps API - With error handling -->
    <script async defer 
        src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey; ?>&libraries=places,marker&callback=initializeGoogleMaps"
        onerror="handleMapError()">
    </script>
</body>
</html>
