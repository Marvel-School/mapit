<?php
/**
 * Check if Google Maps API has HTTP Referer restrictions affecting Docker containers
 * This script makes various HTTP requests to the Google Maps API to determine
 * if HTTP Referer restrictions are affecting your Docker environment.
 */

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

// Load environment variables
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

// Get API key
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?? '';

// Utility functions
function getServerInfo() {
    return [
        'hostname' => gethostname(),
        'ip' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
        'port' => $_SERVER['SERVER_PORT'] ?? '80',
        'docker_detected' => file_exists('/.dockerenv') ? 'Yes' : 'No',
        'server_name' => $_SERVER['SERVER_NAME'] ?? '',
        'http_host' => $_SERVER['HTTP_HOST'] ?? '',
        'request_scheme' => $_SERVER['REQUEST_SCHEME'] ?? 'http',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
    ];
}

function checkHttpReferrer($url, $referrer = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Set referrer if provided
    if ($referrer) {
        curl_setopt($ch, CURLOPT_REFERER, $referrer);
    }
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'url' => $url,
        'referrer' => $referrer,
        'status_code' => $info['http_code'],
        'success' => $info['http_code'] >= 200 && $info['http_code'] < 300,
        'error' => $error,
        'response_time' => $info['total_time']
    ];
}

// Get server information
$serverInfo = getServerInfo();

// Current URL
$currentUrl = $serverInfo['request_scheme'] . '://' . $serverInfo['http_host'] . $serverInfo['request_uri'];

// Test various referrer scenarios
$referrerTests = [
    'no_referrer' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey"),
    'localhost' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey", "http://localhost"),
    'localhost_port' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey", "http://localhost:80"),
    'server_name' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey", "http://{$serverInfo['server_name']}"),
    'server_ip' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey", "http://{$serverInfo['ip']}"),
    'current_url' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey", $currentUrl),
];

// Check different API services
$apiTests = [
    'js_api' => checkHttpReferrer("https://maps.googleapis.com/maps/api/js?key=$apiKey"),
    'geocode_api' => checkHttpReferrer("https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=$apiKey"),
    'staticmap_api' => checkHttpReferrer("https://maps.googleapis.com/maps/api/staticmap?center=New+York&zoom=13&size=600x300&key=$apiKey"),
];

// Generate recommendations based on results
$recommendations = [];

// If any access is denied, it might be HTTP referrer restrictions
if (array_sum(array_map(function($test) { return $test['success'] ? 0 : 1; }, $referrerTests)) > 0) {
    $recommendations[] = "Your API key may have HTTP referrer restrictions. Check your Google Cloud Console settings.";
    
    // Check which referrers work
    $workingReferrers = array_filter($referrerTests, function($test) { return $test['success']; });
    $failingReferrers = array_filter($referrerTests, function($test) { return !$test['success']; });
    
    if (!empty($workingReferrers)) {
        $recommendations[] = "Working referrers: " . implode(', ', array_keys($workingReferrers));
    }
    
    if (!empty($failingReferrers)) {
        $recommendations[] = "Add these referrers to your API key restrictions: " . implode(', ', array_keys($failingReferrers));
    }
    
    // Special recommendation for Docker
    if ($serverInfo['docker_detected'] === 'Yes') {
        $recommendations[] = "For Docker: Add both the host machine's address and Docker container IP to allowed referrers.";
        $recommendations[] = "Consider adding '*' to temporarily disable referrer checks for testing.";
    }
}

// If all tests failed, might be IP restrictions
if (array_sum(array_map(function($test) { return $test['success'] ? 0 : 1; }, $apiTests)) === count($apiTests)) {
    $recommendations[] = "All API tests failed. Your API key may have IP address restrictions that are blocking the Docker container.";
    $recommendations[] = "Add your Docker host's external IP address to the allowed IPs in Google Cloud Console.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - HTTP Referrer Check for Docker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            max-height: 300px;
            overflow: auto;
        }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .card { margin-bottom: 20px; }
        .table-sm td, .table-sm th { padding: 0.5rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1><i class="fas fa-map-marked-alt me-2"></i> MapIt - HTTP Referrer Diagnostic</h1>
        <p class="lead mb-4">This tool checks if HTTP Referrer restrictions are causing Google Maps API issues in Docker.</p>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Server Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <?php foreach($serverInfo as $key => $value): ?>
                            <tr>
                                <th><?= ucwords(str_replace('_', ' ', $key)) ?>:</th>
                                <td><?= htmlspecialchars($value) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">API Key Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>API Key:</strong> <?= substr($apiKey, 0, 5) ?>...<?= substr($apiKey, -5) ?></p>
                        <p><strong>Key Length:</strong> <?= strlen($apiKey) ?> characters</p>
                        <p><strong>Current URL:</strong> <?= htmlspecialchars($currentUrl) ?></p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Google Maps API keys can have both HTTP referrer and IP address restrictions.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">HTTP Referrer Tests</h5>
            </div>
            <div class="card-body">
                <p>Testing API access with different HTTP referrers:</p>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Referrer</th>
                                <th>Status</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($referrerTests as $name => $test): ?>
                            <tr>
                                <td><?= ucwords(str_replace('_', ' ', $name)) ?></td>
                                <td><?= $test['referrer'] ? htmlspecialchars($test['referrer']) : '<em>None</em>' ?></td>
                                <td><?= $test['status_code'] ?></td>
                                <td class="<?= $test['success'] ? 'success' : 'error' ?>">
                                    <?= $test['success'] ? 'Success' : 'Failed' ?>
                                    <?= $test['error'] ? ' - ' . htmlspecialchars($test['error']) : '' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">API Service Tests</h5>
            </div>
            <div class="card-body">
                <p>Testing access to different Google Maps API services:</p>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>API Service</th>
                                <th>Status</th>
                                <th>Result</th>
                                <th>Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($apiTests as $name => $test): ?>
                            <tr>
                                <td><?= ucwords(str_replace('_', ' ', $name)) ?></td>
                                <td><?= $test['status_code'] ?></td>
                                <td class="<?= $test['success'] ? 'success' : 'error' ?>">
                                    <?= $test['success'] ? 'Success' : 'Failed' ?>
                                </td>
                                <td><?= round($test['response_time'] * 1000) ?> ms</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recommendations</h5>
            </div>
            <div class="card-body">
                <?php if(empty($recommendations)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Good news!</strong> All tests passed. Your API key appears to be correctly configured for use in Docker.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Some issues detected.</strong> Here are our recommendations:
                    </div>
                    
                    <ol class="list-group list-group-numbered">
                        <?php foreach($recommendations as $recommendation): ?>
                            <li class="list-group-item"><?= htmlspecialchars($recommendation) ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h5>Next Steps:</h5>
                    <ol>
                        <li>Open the <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></li>
                        <li>Find your Maps API key and click "Edit"</li>
                        <li>Under "Application restrictions", check your settings:
                            <ul>
                                <li>If set to "HTTP referrers", make sure to add all necessary referrers including:
                                    <ul>
                                        <li>http://localhost/*</li>
                                        <li>http://<?= htmlspecialchars($serverInfo['ip']) ?>/*</li>
                                        <li>http://<?= htmlspecialchars($serverInfo['server_name']) ?>/*</li>
                                    </ul>
                                </li>
                                <li>If set to "IP addresses", add your Docker host's external IP</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Test Maps Loading</h5>
            </div>
            <div class="card-body">
                <p>Testing if Google Maps loads in your browser:</p>
                <div id="test-map" style="height: 300px; border: 1px solid #ddd; border-radius: 4px;"></div>
                <div id="map-error" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
    
    <script>
        // Map testing code
        let map;
        let mapLoaded = false;
        let apiKey = '<?= $apiKey ?>';
        const mapError = document.getElementById('map-error');
        
        // Clean error handler
        function showError(message) {
            mapError.textContent = message;
            mapError.style.display = 'block';
        }
        
        // Init function for Google Maps
        function initMap() {
            try {
                map = new google.maps.Map(document.getElementById('test-map'), {
                    center: { lat: 0, lng: 0 },
                    zoom: 2
                });
                mapLoaded = true;
            } catch (e) {
                showError('Error initializing map: ' + e.message);
            }
        }
        
        // Load the API with error handling
        function loadGoogleMaps() {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMap`;
            script.async = true;
            script.defer = true;
            script.onerror = function() {
                showError('Failed to load Google Maps API script');
            };
            
            // Add handler for auth failures
            window.gm_authFailure = function() {
                showError('Google Maps authentication failed. Check API key restrictions.');
            };
            
            // Add the script
            document.body.appendChild(script);
            
            // Set a timeout to detect if the API takes too long to load
            setTimeout(function() {
                if (!mapLoaded) {
                    showError('Google Maps API is taking too long to load. There may be connectivity issues.');
                }
            }, 10000);
        }
        
        // Start loading when the page is ready
        document.addEventListener('DOMContentLoaded', loadGoogleMaps);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
