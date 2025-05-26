<?php
// Simple test page for Google Maps integration in Docker

require_once __DIR__ . '/../app/Core/Autoloader.php';

// Load environment variables from file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Get Google Maps API key from config
$config = require __DIR__ . '/../config/app.php';
$apiKey = $config['google_maps']['api_key'] ?? '';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - Google Maps Docker Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Meta tag with API key for JavaScript -->
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($apiKey); ?>">
    
    <style>
        #test-map {
            height: 400px;
            width: 100%;
        }
        .status-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1>Google Maps Integration Docker Test</h1>
                <p class="lead">This page tests if the Google Maps API is working properly in the Docker container environment.</p>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Environment Check</h5>
                    </div>
                    <div class="card-body">
                        <p>API Key Status: <strong id="api-status">Checking...</strong></p>
                        <p>API Key Value: <code><?= substr($apiKey, 0, 5) . (strlen($apiKey) > 5 ? '...' . substr($apiKey, -5) : ''); ?></code></p>
                        <p>Environment: <code>Docker Container</code></p>
                        <p>Server Software: <code><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></code></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Test Map</h5>
                    </div>
                    <div class="card-body">
                        <div id="test-map"></div>
                        <div class="status-container mt-3" id="map-status">
                            <h5>Loading Status:</h5>
                            <ul id="status-list">
                                <li>Waiting for Google Maps API to load...</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Status logging function
        function logStatus(message, isSuccess = true) {
            const statusList = document.getElementById('status-list');
            const listItem = document.createElement('li');
            listItem.textContent = message;
            if (!isSuccess) {
                listItem.style.color = 'red';
            } else if (isSuccess === true) {
                listItem.style.color = 'green';
            }
            statusList.appendChild(listItem);
        }
        
        let map;
        
        // This function is called when the Maps API loads successfully
        function initializeGoogleMaps() {
            document.getElementById('api-status').innerHTML = '<span class="text-success">API Loaded Successfully!</span>';
            logStatus('Google Maps API loaded successfully');
            
            try {
                // Initialize a simple map centered on a default location
                map = new google.maps.Map(document.getElementById('test-map'), {
                    center: { lat: 40.7128, lng: -74.0060 }, // New York
                    zoom: 8
                });
                
                logStatus('Map instance created successfully');
                
                // Add a marker
                const marker = new google.maps.Marker({
                    position: { lat: 40.7128, lng: -74.0060 },
                    map: map,
                    title: 'New York'
                });
                
                logStatus('Marker added to map');
                
                // Add custom controls to test more functionality
                addMapControls(map);
            } catch (error) {
                logStatus('Error initializing map: ' + error.message, false);
                console.error('Map initialization error:', error);
            }
        }
        
        // Add custom controls to the map
        function addMapControls(map) {
            try {
                // Create a search box for demonstration
                const input = document.createElement('input');
                input.type = 'text';
                input.placeholder = 'Search locations';
                input.classList.add('form-control');
                
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                
                // Bias the SearchBox results towards current map's viewport
                map.addListener('bounds_changed', () => {
                    searchBox.setBounds(map.getBounds());
                });
                
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place
                searchBox.addListener('places_changed', () => {
                    const places = searchBox.getPlaces();
                    
                    if (places.length === 0) {
                        return;
                    }
                    
                    // Clear out the old markers
                    markers.forEach(marker => {
                        marker.setMap(null);
                    });
                    markers = [];
                    
                    // For each place, get the icon, name and location
                    const bounds = new google.maps.LatLngBounds();
                    places.forEach(place => {
                        if (!place.geometry || !place.geometry.location) {
                            logStatus(`Place contains no geometry: ${place.name}`, false);
                            return;
                        }
                        
                        // Create a marker for each place
                        markers.push(new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location
                        }));
                        
                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                    logStatus(`Found ${places.length} places`);
                });
                
                logStatus('Added search controls to map');
            } catch (error) {
                logStatus('Error adding map controls: ' + error.message, false);
                console.error('Map controls error:', error);
            }
        }
        
        // Error handler for Google Maps API loading
        function handleMapError() {
            document.getElementById('api-status').innerHTML = '<span class="text-danger">API Failed to Load</span>';
            logStatus('Failed to load Google Maps API', false);
            document.getElementById('test-map').innerHTML = '<div class="alert alert-danger">Failed to load Google Maps. Please check the API key and browser console for errors.</div>';
        }
        
        // Google Maps API authentication error handler
        function gm_authFailure() {
            document.getElementById('api-status').innerHTML = '<span class="text-danger">API Authentication Failed</span>';
            logStatus('Google Maps API authentication failed - invalid API key', false);
            document.getElementById('test-map').innerHTML = '<div class="alert alert-danger">Failed to authenticate with Google Maps API. The API key is invalid or has restrictions that prevent it from working.</div>';
        }
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            logStatus('Document loaded, waiting for Google Maps API...', null);
            
            // Check if Google Maps failed to load after 5 seconds
            setTimeout(function() {
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    logStatus('Google Maps API not loaded after timeout', false);
                    // Try loading Google Maps manually
                    tryLoadMapsManually();
                }
            }, 5000);
        });
        
        // Fallback function to load Google Maps API if it fails to load automatically
        function tryLoadMapsManually() {
            logStatus('Attempting to load Google Maps API manually...', null);
            
            // Get the API key from meta tag
            const apiKey = document.querySelector('meta[name="google-maps-api-key"]')?.getAttribute('content');
            if (!apiKey) {
                logStatus('No Google Maps API key found in meta tag', false);
                return;
            }
            
            // Create script element
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initializeGoogleMaps`;
            script.async = true;
            script.defer = true;
            script.onerror = handleMapError;
            
            // Append to document
            document.body.appendChild(script);
            logStatus('Manual API script injection attempted', null);
        }
        
        // Global array for markers
        let markers = [];
    </script>
    
    <!-- Google Maps API - With error handling -->
    <script async defer 
        src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey; ?>&libraries=places&callback=initializeGoogleMaps"
        onerror="handleMapError()">
    </script>
</body>
</html>
