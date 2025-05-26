<?php
// Docker Google Maps API Key Diagnostic Tool

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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

// Get API key from environment
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?? '';

// Get server information
$serverInfo = [
    'hostname' => gethostname(),
    'ip' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
    'server_name' => $_SERVER['SERVER_NAME'] ?? '',
    'docker_detected' => file_exists('/.dockerenv') ? 'Yes' : 'No'
];

// Check if running in Docker
function checkDockerEnvironment() {
    // Check for .dockerenv file (most reliable)
    if (file_exists('/.dockerenv')) {
        return true;
    }
    
    // Check for Docker in cgroups
    if (file_exists('/proc/self/cgroup')) {
        $cgroup = file_get_contents('/proc/self/cgroup');
        if (strpos($cgroup, 'docker') !== false) {
            return true;
        }
    }
    
    // Check hostname (often container ID)
    $hostname = gethostname();
    if (strlen($hostname) === 12 && ctype_xdigit($hostname)) {
        return true;
    }
    
    return false;
}

// Get external IP address
function getExternalIp() {
    $externalIp = '';
    
    // Different services to try
    $services = [
        'https://api.ipify.org',
        'https://ipinfo.io/ip',
        'https://icanhazip.com',
        'https://ifconfig.me/ip'
    ];
    
    foreach ($services as $service) {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]);
            
            $externalIp = @file_get_contents($service, false, $context);
            if ($externalIp && preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $externalIp)) {
                break;
            }
        } catch (Exception $e) {
            // Skip to next service
        }
    }
    
    return trim($externalIp);
}

// Get network interfaces
function getNetworkInterfaces() {
    $interfaces = [];
    
    if (function_exists('shell_exec')) {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $output = @shell_exec('ipconfig');
            if ($output) {
                $interfaces[] = $output;
            }
        } else {
            // Linux/Unix
            $output = @shell_exec('ifconfig -a || ip addr show');
            if ($output) {
                $interfaces[] = $output;
            }
        }
    }
    
    return $interfaces;
}

// Test the API key
function testGoogleMapsApi($apiKey) {
    $results = [];
    
    // Test different API endpoints
    $endpoints = [
        'js' => 'https://maps.googleapis.com/maps/api/js?key=' . $apiKey,
        'geocode' => 'https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=' . $apiKey,
        'staticmap' => 'https://maps.googleapis.com/maps/api/staticmap?center=New+York&zoom=13&size=600x300&key=' . $apiKey
    ];
    
    foreach ($endpoints as $type => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Get headers and body
        $headerSize = $info['header_size'];
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        // Check for error response
        $errorMessage = '';
        if ($type === 'geocode') {
            $json = json_decode($body);
            if ($json && isset($json->status) && $json->status !== 'OK') {
                $errorMessage = $json->status . (isset($json->error_message) ? ': ' . $json->error_message : '');
            }
        }
        
        $results[$type] = [
            'status_code' => $info['http_code'],
            'success' => $info['http_code'] >= 200 && $info['http_code'] < 300,
            'error' => $error,
            'api_error' => $errorMessage,
            'content_type' => $info['content_type'] ?? '',
            'response_time' => $info['total_time']
        ];
    }
    
    return $results;
}

// Run diagnostics
$isDocker = checkDockerEnvironment();
$externalIp = getExternalIp();
$networkInterfaces = getNetworkInterfaces();

// Test API key
$apiTests = testGoogleMapsApi($apiKey);

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - Docker Google Maps Diagnosis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container py-5">
        <h1>MapIt - Docker Google Maps API Diagnostic</h1>
        <p class="lead mb-4">This tool helps diagnose issues with Google Maps API in Docker environments.</p>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Environment Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Docker Environment:</strong> <span class="<?= $isDocker ? 'success' : '' ?>"><?= $isDocker ? 'Detected' : 'Not Detected' ?></span></p>
                        <p><strong>Hostname:</strong> <?= htmlspecialchars($serverInfo['hostname']) ?></p>
                        <p><strong>Server IP:</strong> <?= htmlspecialchars($serverInfo['ip']) ?></p>
                        <p><strong>Server Name:</strong> <?= htmlspecialchars($serverInfo['server_name']) ?></p>
                        <p><strong>External IP:</strong> <?= htmlspecialchars($externalIp) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Google Maps API Key</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>API Key:</strong> <?= substr($apiKey, 0, 5) ?>...<?= substr($apiKey, -5) ?></p>
                        <p><strong>Key Length:</strong> <?= strlen($apiKey) ?> characters</p>
                        <p><strong>From Environment:</strong> <?= !empty($_ENV['GOOGLE_MAPS_API_KEY']) ? 'Yes' : 'No' ?></p>
                        <p><strong>From getenv():</strong> <?= !empty(getenv('GOOGLE_MAPS_API_KEY')) ? 'Yes' : 'No' ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">API Test Results</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($apiTests as $type => $result): ?>
                    <div class="col-md-4">
                        <div class="card mb-3 <?= $result['success'] ? 'border-success' : 'border-danger' ?>">
                            <div class="card-header <?= $result['success'] ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <?= strtoupper($type) ?> API Test
                            </div>
                            <div class="card-body">
                                <p><strong>Status:</strong> <?= $result['status_code'] ?></p>
                                <p><strong>Success:</strong> <?= $result['success'] ? 'Yes' : 'No' ?></p>
                                <?php if (!empty($result['error'])): ?>
                                <p><strong>Error:</strong> <?= htmlspecialchars($result['error']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($result['api_error'])): ?>
                                <p><strong>API Error:</strong> <?= htmlspecialchars($result['api_error']) ?></p>
                                <?php endif; ?>
                                <p><strong>Response Time:</strong> <?= round($result['response_time'] * 1000) ?> ms</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Network Interfaces</h5>
            </div>
            <div class="card-body">
                <?php if (empty($networkInterfaces)): ?>
                <p>No network interface information available</p>
                <?php else: ?>
                <pre><?= htmlspecialchars(implode("\n\n", $networkInterfaces)) ?></pre>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Browser Test</h5>
            </div>
            <div class="card-body">
                <p>Testing Google Maps API in the browser:</p>
                <div id="map-test" style="height: 300px; border: 1px solid #ddd; border-radius: 4px;"></div>
                <div id="map-error" class="alert alert-danger mt-3" style="display:none;"></div>
                <div id="map-success" class="alert alert-success mt-3" style="display:none;">Google Maps loaded successfully!</div>
                <hr>
                <h5>Debug Information</h5>
                <pre id="debug-info"></pre>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Recommendations:</h4>
            <ul class="list-group">
                <li class="list-group-item list-group-item-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Use the Docker Maps Loader:</strong> Ensure you are using the docker-maps-loader.js script in your layout.
                </li>
                <li class="list-group-item list-group-item-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>API Key Restrictions:</strong> Check if your API key has domain restrictions in the Google Cloud Console.
                </li>
                <li class="list-group-item list-group-item-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Add HTTP Referrer:</strong> Add localhost and your Docker container IP to the allowed HTTP referrers.
                </li>
            </ul>
        </div>
    </div>
    
    <script>
        // Debug output container
        const debugLog = document.getElementById('debug-info');
        const mapContainer = document.getElementById('map-test');
        const mapError = document.getElementById('map-error');
        const mapSuccess = document.getElementById('map-success');
        
        // Log helper
        function log(message) {
            const timestamp = new Date().toISOString();
            debugLog.textContent += `[${timestamp}] ${message}\n`;
            console.log(message);
        }
        
        // Test Google Maps loading
        function testGoogleMaps() {
            log('Starting Google Maps test...');
            
            // Get API key from PHP
            const apiKey = '<?= $apiKey ?>';
            log(`API Key: ${apiKey.substring(0, 5)}...${apiKey.substring(apiKey.length - 5)}`);
            
            try {
                log('Creating script element');
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMap`;
                script.async = true;
                script.defer = true;
                
                // Handle loading errors
                script.onerror = function(error) {
                    log(`Error loading script: ${error}`);
                    mapError.textContent = 'Failed to load Google Maps API script. Check the API key and network connection.';
                    mapError.style.display = 'block';
                };
                
                // Init function
                window.initMap = function() {
                    try {
                        log('Google Maps API loaded, initializing map');
                        
                        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                            throw new Error('Google Maps not available despite callback');
                        }
                        
                        const map = new google.maps.Map(mapContainer, {
                            center: { lat: 40.7128, lng: -74.0060 },
                            zoom: 10
                        });
                        
                        log('Map initialized successfully');
                        mapSuccess.style.display = 'block';
                        
                        // Add a marker
                        new google.maps.Marker({
                            position: { lat: 40.7128, lng: -74.0060 },
                            map: map,
                            title: 'New York'
                        });
                    } catch (error) {
                        log(`Error initializing map: ${error.message}`);
                        mapError.textContent = `Error initializing map: ${error.message}`;
                        mapError.style.display = 'block';
                    }
                };
                
                // Add authentication failure handler
                window.gm_authFailure = function() {
                    log('Google Maps authentication failed');
                    mapError.textContent = 'Google Maps API authentication failed. The API key may be invalid or have restrictions.';
                    mapError.style.display = 'block';
                };
                
                log('Adding script to document');
                document.head.appendChild(script);
            } catch (error) {
                log(`Exception: ${error.message}`);
                mapError.textContent = `Exception: ${error.message}`;
                mapError.style.display = 'block';
            }
        }
        
        // Run the test after page loads
        document.addEventListener('DOMContentLoaded', testGoogleMaps);
    </script>
</body>
</html>
