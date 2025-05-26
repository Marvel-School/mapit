<?php
// Google Maps API Docker Diagnostic Tool

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

// Define test scenarios
$testScenarios = [
    [
        'name' => 'Direct API Key Test',
        'endpoint' => 'https://maps.googleapis.com/maps/api/js?key=' . $apiKey,
        'method' => 'HEAD'
    ],
    [
        'name' => 'Geocoding API Test',
        'endpoint' => 'https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=' . $apiKey,
        'method' => 'GET'
    ],
    [
        'name' => 'Static Map API Test',
        'endpoint' => 'https://maps.googleapis.com/maps/api/staticmap?center=New+York&zoom=13&size=600x300&key=' . $apiKey,
        'method' => 'HEAD'
    ]
];

// Function to run HTTP test
function runApiTest($test) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test['endpoint']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($test['method'] === 'HEAD') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'httpCode' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'error' => $error
    ];
}

// Run tests
$testResults = [];
foreach ($testScenarios as $scenario) {
    $result = runApiTest($scenario);
    $testResults[] = [
        'scenario' => $scenario,
        'result' => $result
    ];
}

// Check environment
$serverInfo = [
    'hostname' => gethostname(),
    'ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
    'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'docker' => file_exists('/.dockerenv') ? 'Yes' : 'No',
    'php_version' => phpversion()
];

// Function to check if request is AJAX
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Return JSON for AJAX requests
if (isAjax()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'apiKey' => substr($apiKey, 0, 5) . '...' . substr($apiKey, -5),
        'server' => $serverInfo,
        'tests' => $testResults
    ]);
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapIt - Google Maps API Docker Diagnostic</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Meta tag with API key for JavaScript -->
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($apiKey); ?>">
    
    <style>
        #test-map {
            height: 300px;
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
        .test-result {
            margin-bottom: 10px;
        }
        .test-success {
            border-left: 4px solid #28a745;
        }
        .test-failure {
            border-left: 4px solid #dc3545;
        }
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Google Maps API Docker Diagnostic</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4>Server Environment</h4>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Hostname
                                        <span><?= htmlspecialchars($serverInfo['hostname']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        IP Address
                                        <span><?= htmlspecialchars($serverInfo['ip']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Server Software
                                        <span><?= htmlspecialchars($serverInfo['software']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Docker Container
                                        <span><?= $serverInfo['docker']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        PHP Version
                                        <span><?= htmlspecialchars($serverInfo['php_version']); ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4>API Key Information</h4>
                                <div class="alert <?= !empty($apiKey) ? 'alert-success' : 'alert-danger'; ?>">
                                    <?php if (!empty($apiKey)): ?>
                                        <strong>API Key Found:</strong> <?= substr($apiKey, 0, 5); ?>...<?= substr($apiKey, -5); ?>
                                    <?php else: ?>
                                        <strong>Warning:</strong> No API key found in configuration.
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <h5>Server-side Tests</h5>
                                    <?php foreach ($testResults as $index => $test): ?>
                                        <div class="test-result p-2 <?= $test['result']['success'] ? 'test-success' : 'test-failure'; ?>">
                                            <strong><?= htmlspecialchars($test['scenario']['name']); ?>:</strong>
                                            <?php if ($test['result']['success']): ?>
                                                <span class="badge bg-success">Success (<?= $test['result']['httpCode']; ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed (<?= $test['result']['httpCode']; ?>)</span>
                                                <?php if (!empty($test['result']['error'])): ?>
                                                    <br><small><?= htmlspecialchars($test['result']['error']); ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4>Client-side Tests</h4>
                                <div class="alert alert-info" id="status-message">
                                    Running client-side tests...
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Direct Script Load Test</h5>
                                    <div id="script-test-result">
                                        <span class="loader"></span> Testing script loading...
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Map Initialization Test</h5>
                                    <div id="test-map"></div>
                                </div>
                                
                                <div class="mt-3">
                                    <h5>Console Output</h5>
                                    <pre id="console-output">Waiting for test results...</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-primary" id="run-tests-btn">Run Tests Again</button>
                            <a href="/dashboard" class="btn btn-secondary">Go to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Store console logs
        const consoleOutput = document.getElementById('console-output');
        const originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn
        };
        
        const logs = [];
        
        console.log = function() {
            originalConsole.log.apply(console, arguments);
            const message = Array.from(arguments).join(' ');
            logs.push(`[LOG] ${message}`);
            updateConsoleOutput();
        };
        
        console.error = function() {
            originalConsole.error.apply(console, arguments);
            const message = Array.from(arguments).join(' ');
            logs.push(`[ERROR] ${message}`);
            updateConsoleOutput();
        };
        
        console.warn = function() {
            originalConsole.warn.apply(console, arguments);
            const message = Array.from(arguments).join(' ');
            logs.push(`[WARN] ${message}`);
            updateConsoleOutput();
        };
        
        function updateConsoleOutput() {
            consoleOutput.textContent = logs.join('\n');
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }
        
        // Test direct script loading
        function testScriptLoading() {
            return new Promise((resolve, reject) => {
                const scriptTest = document.getElementById('script-test-result');
                const apiKey = document.querySelector('meta[name="google-maps-api-key"]')?.getAttribute('content');
                
                if (!apiKey) {
                    scriptTest.innerHTML = `
                        <span class="badge bg-danger">Failed</span>
                        No API key found in meta tag
                    `;
                    reject(new Error('No API key found'));
                    return;
                }
                
                console.log('Testing direct script loading with API key: ' + apiKey.substring(0, 5) + '...');
                
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=scriptLoadCallback`;
                script.async = true;
                script.defer = true;
                
                // Success callback
                window.scriptLoadCallback = function() {
                    console.log('Script loaded successfully');
                    scriptTest.innerHTML = `
                        <span class="badge bg-success">Success</span>
                        API script loaded successfully
                    `;
                    resolve(true);
                };
                
                // Error handling
                script.onerror = function() {
                    console.error('Failed to load API script');
                    scriptTest.innerHTML = `
                        <span class="badge bg-danger">Failed</span>
                        Failed to load API script. Check console for details.
                    `;
                    reject(new Error('Script loading error'));
                };
                
                // Timeout for unreliable callbacks
                setTimeout(() => {
                    if (typeof google === 'undefined') {
                        console.warn('Script may have loaded but callback not called');
                        scriptTest.innerHTML += `
                            <br><small class="text-warning">Warning: Script may have loaded but callback wasn't called</small>
                        `;
                    }
                }, 5000);
                
                document.body.appendChild(script);
            });
        }
        
        // Test map initialization
        function testMapInitialization() {
            const mapContainer = document.getElementById('test-map');
            
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.error('Google Maps API not loaded');
                mapContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Maps API not loaded, cannot initialize test map
                    </div>
                `;
                return Promise.reject(new Error('Maps API not loaded'));
            }
            
            try {
                console.log('Initializing test map');
                const map = new google.maps.Map(mapContainer, {
                    center: { lat: 40.7128, lng: -74.0060 },
                    zoom: 8
                });
                
                // Add a marker
                const marker = new google.maps.Marker({
                    position: { lat: 40.7128, lng: -74.0060 },
                    map: map,
                    title: 'New York'
                });
                
                console.log('Test map initialized successfully');
                return Promise.resolve(true);
            } catch (error) {
                console.error('Failed to initialize test map:', error);
                mapContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to initialize test map: ${error.message}
                    </div>
                `;
                return Promise.reject(error);
            }
        }
        
        // Run all tests
        function runTests() {
            const statusMessage = document.getElementById('status-message');
            statusMessage.className = 'alert alert-info';
            statusMessage.innerHTML = '<span class="loader"></span> Running tests...';
            
            logs.length = 0;
            logs.push('[INFO] Starting client-side tests...');
            updateConsoleOutput();
            
            testScriptLoading()
                .then(() => {
                    logs.push('[INFO] Script loading test passed');
                    return testMapInitialization();
                })
                .then(() => {
                    logs.push('[SUCCESS] All tests passed successfully!');
                    statusMessage.className = 'alert alert-success';
                    statusMessage.textContent = 'All tests passed successfully!';
                })
                .catch(error => {
                    logs.push(`[FAILURE] Tests failed: ${error.message}`);
                    statusMessage.className = 'alert alert-danger';
                    statusMessage.textContent = 'Some tests failed. See details below.';
                });
        }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, starting tests');
            runTests();
            
            document.getElementById('run-tests-btn').addEventListener('click', function() {
                location.reload();
            });
        });
    </script>
</body>
</html>
