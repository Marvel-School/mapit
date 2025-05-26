<?php
// Final verification page for Google Maps API Integration

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

// Get configuration
$config = require __DIR__ . '/../config/app.php';
$apiKey = $config['google_maps']['api_key'] ?? '';

// Verify server environment
$isDocker = file_exists('/.dockerenv');
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$phpVersion = PHP_VERSION;

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - Google Maps Verification</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Meta tag with API key for JavaScript -->
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($apiKey); ?>">
    
    <style>
        #test-map {
            height: 400px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        .debug-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .status-container {
            margin-top: 20px;
        }
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .status-icon.loading {
            background-color: #ffc107;
            animation: pulse 1s infinite;
        }
        .status-icon.success {
            background-color: #28a745;
        }
        .status-icon.error {
            background-color: #dc3545;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <h1 class="text-center mb-4">Google Maps Integration Verification</h1>
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Environment Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="debug-item">
                                    <strong>API Key Status:</strong> 
                                    <span id="api-key-status">
                                        <?php if (!empty($apiKey)): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Missing</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="debug-item">
                                    <strong>API Key:</strong> 
                                    <code><?= !empty($apiKey) ? substr($apiKey, 0, 5) . '...' . substr($apiKey, -5) : 'Not found'; ?></code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="debug-item">
                                    <strong>Environment:</strong>
                                    <?php if ($isDocker): ?>
                                        <span class="badge bg-info">Docker Container</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Standard PHP</span>
                                    <?php endif; ?>
                                </div>
                                <div class="debug-item">
                                    <strong>Server:</strong> <?= htmlspecialchars($serverSoftware); ?>
                                </div>
                                <div class="debug-item">
                                    <strong>PHP Version:</strong> <?= $phpVersion; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Google Maps Test</h5>
                    </div>
                    <div class="card-body">
                        <div id="test-map"></div>
                        
                        <div class="status-container mt-4">
                            <h5>Loading Status:</h5>
                            <div id="status-list">
                                <div class="status-item">
                                    <div class="status-icon loading" id="api-loading-status"></div>
                                    <span>Waiting for Google Maps API to load...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Console Output</h5>
                    </div>
                    <div class="card-body">
                        <pre id="console-output">Initializing...</pre>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="/" class="btn btn-primary me-2">Go to Home</a>
                    <a href="/dashboard" class="btn btn-success me-2">Go to Dashboard</a>
                    <button type="button" id="test-again-btn" class="btn btn-warning">Test Again</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Console output capture
        const consoleOutput = document.getElementById('console-output');
        const originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn
        };
        
        console.log = function() {
            originalConsole.log.apply(console, arguments);
            const args = Array.from(arguments).join(' ');
            appendToConsole('LOG: ' + args);
        };
        
        console.error = function() {
            originalConsole.error.apply(console, arguments);
            const args = Array.from(arguments).join(' ');
            appendToConsole('ERROR: ' + args, 'error');
        };
        
        console.warn = function() {
            originalConsole.warn.apply(console, arguments);
            const args = Array.from(arguments).join(' ');
            appendToConsole('WARNING: ' + args, 'warning');
        };
        
        function appendToConsole(message, type = 'log') {
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'text-danger' : (type === 'warning' ? 'text-warning' : '');
            
            if (consoleOutput.textContent === 'Initializing...') {
                consoleOutput.innerHTML = '';
            }
            
            const line = document.createElement('div');
            line.className = className;
            line.textContent = `[${timestamp}] ${message}`;
            consoleOutput.appendChild(line);
            
            // Auto-scroll to bottom
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }
        
        // Status logging function
        function logStatus(message, status = 'loading') {
            const statusList = document.getElementById('status-list');
            const statusItem = document.createElement('div');
            statusItem.className = 'status-item';
            
            const icon = document.createElement('div');
            icon.className = `status-icon ${status}`;
            
            const text = document.createElement('span');
            text.textContent = message;
            
            statusItem.appendChild(icon);
            statusItem.appendChild(text);
            statusList.appendChild(statusItem);
            
            // Update the loading icon if Maps API is loaded
            if (status === 'success' && message.includes('loaded')) {
                document.getElementById('api-loading-status').className = 'status-icon success';
            } else if (status === 'error') {
                document.getElementById('api-loading-status').className = 'status-icon error';
            }
        }
        
        let map;
        let markers = [];
        let googleMapsInitialized = false;
        
        // Function to initialize Google Maps
        function initializeGoogleMaps() {
            googleMapsInitialized = true;
            console.log('Google Maps API loaded successfully');
            logStatus('Google Maps API loaded successfully', 'success');
            
            try {
                // Initialize map
                map = new google.maps.Map(document.getElementById('test-map'), {
                    center: { lat: 40.7128, lng: -74.0060 }, // New York
                    zoom: 8
                });
                
                console.log('Map instance created successfully');
                logStatus('Map instance created successfully', 'success');
                
                // Add a marker to NYC
                addMarker({ lat: 40.7128, lng: -74.0060 }, 'New York');
                
                // Add markers for a few other cities
                addMarker({ lat: 34.0522, lng: -118.2437 }, 'Los Angeles');
                addMarker({ lat: 41.8781, lng: -87.6298 }, 'Chicago');
                addMarker({ lat: 51.5074, lng: -0.1278 }, 'London');
                addMarker({ lat: 48.8566, lng: 2.3522 }, 'Paris');
                
                // Fit bounds to include all markers
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => bounds.extend(marker.getPosition()));
                map.fitBounds(bounds);
                
                // Test Google Maps events
                map.addListener('click', function(event) {
                    console.log('Map clicked at:', event.latLng.lat(), event.latLng.lng());
                    addMarker(event.latLng, 'Clicked Location');
                });
                
                console.log('Map initialization complete');
                logStatus('Map initialization complete - Try clicking on the map!', 'success');
            } catch (error) {
                console.error('Error initializing map:', error);
                logStatus('Failed to initialize map: ' + error.message, 'error');
            }
        }
        
        // Function to add a marker to the map
        function addMarker(position, title) {
            try {
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: title,
                    animation: google.maps.Animation.DROP
                });
                
                // Add info window
                const infoWindow = new google.maps.InfoWindow({
                    content: `<div><strong>${title}</strong><br>
                             Lat: ${position.lat.toFixed(4)}<br>
                             Lng: ${position.lng.toFixed(4)}</div>`
                });
                
                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });
                
                markers.push(marker);
                console.log('Added marker:', title);
                return marker;
            } catch (error) {
                console.error('Error adding marker:', error);
                return null;
            }
        }
        
        // Error handler for Google Maps API loading
        function handleMapError() {
            console.error('Failed to load Google Maps API');
            logStatus('Failed to load Google Maps API', 'error');
            document.getElementById('test-map').innerHTML = '<div class="alert alert-danger p-3">Failed to load Google Maps. Please check the API key and browser console for errors.</div>';
        }
        
        // Google Maps authentication error handler
        function gm_authFailure() {
            console.error('Google Maps API authentication failed - invalid API key');
            logStatus('Google Maps API authentication failed - invalid API key', 'error');
            document.getElementById('test-map').innerHTML = '<div class="alert alert-danger p-3">Failed to authenticate with Google Maps API. The API key is invalid or has restrictions that prevent it from working.</div>';
        }
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document loaded, waiting for Google Maps API...');
            
            // Check if API key is missing
            const apiKey = document.querySelector('meta[name="google-maps-api-key"]')?.getAttribute('content');
            if (!apiKey || apiKey.trim() === '') {
                console.error('No Google Maps API key found');
                logStatus('No Google Maps API key found', 'error');
            }
            
            // Add test again button functionality
            document.getElementById('test-again-btn').addEventListener('click', function() {
                window.location.reload();
            });
            
            // Check if Google Maps failed to load after 5 seconds
            setTimeout(function() {
                if (!googleMapsInitialized) {
                    console.warn('Google Maps API not loaded after 5 seconds timeout');
                    logStatus('Google Maps API not loaded after timeout, attempting manual loading', 'warning');
                    
                    // Try loading Google Maps manually
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initializeGoogleMaps`;
                    script.async = true;
                    script.defer = true;
                    script.onerror = handleMapError;
                    document.body.appendChild(script);
                }
            }, 5000);
        });
    </script>
    
    <!-- Google Maps API - With error handling -->
    <script async defer 
        src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey; ?>&libraries=places&callback=initializeGoogleMaps"
        onerror="handleMapError()">
    </script>
</body>
</html>
