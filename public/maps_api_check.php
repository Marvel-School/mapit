<?php
// Maps API Key Diagnostic Tool

// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap the application
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::load('App\Core\MapsLogger');

// Initialize logger
\App\Core\MapsLogger::init();
\App\Core\MapsLogger::info("Starting Maps API Key diagnostic");

// Get environment variables
$envFile = __DIR__ . '/../.env';
$envLoaded = false;

if (file_exists($envFile)) {
    \App\Core\MapsLogger::info("Loading environment from .env file");
    
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
                
                if ($key === 'GOOGLE_MAPS_API_KEY') {
                    \App\Core\MapsLogger::info("Set GOOGLE_MAPS_API_KEY from .env file");
                }
            }
        }
    }
    
    $envLoaded = true;
} else {
    \App\Core\MapsLogger::error(".env file not found");
}

// Get API key from environment variables
$envApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: $_ENV['GOOGLE_MAPS_API_KEY'] ?? null;

// Get API key from config file
$configApiKey = null;
$configError = null;

try {
    $config = require __DIR__ . '/../config/app.php';
    $configApiKey = $config['google_maps']['api_key'] ?? null;
    
    if (!empty($configApiKey)) {
        \App\Core\MapsLogger::info("Loaded API key from config/app.php");
    } else {
        \App\Core\MapsLogger::warn("API key not found in config/app.php");
    }
} catch (\Exception $e) {
    \App\Core\MapsLogger::error("Failed to load config/app.php: " . $e->getMessage());
    $configError = $e->getMessage();
}

// Get final API key
$apiKey = $configApiKey ?: $envApiKey;

// Test API connection
if (!empty($apiKey)) {
    $connectionTest = \App\Core\MapsLogger::testApiConnection($apiKey);
} else {
    \App\Core\MapsLogger::error("No API key found, cannot test connection");
    $connectionTest = [
        'success' => false,
        'error' => 'No API key available'
    ];
}

// Check for Docker environment
$isDocker = file_exists('/.dockerenv');

if ($isDocker) {
    \App\Core\MapsLogger::info("Running in Docker environment");
} else {
    \App\Core\MapsLogger::info("Running in standard PHP environment");
}

// Check if running in a browser or from CLI
$isCli = php_sapi_name() === 'cli';

if ($isCli) {
    echo "=== Maps API Key Diagnostic ===\n\n";
    echo ".env file loaded: " . ($envLoaded ? "Yes" : "No") . "\n";
    echo "Docker environment: " . ($isDocker ? "Yes" : "No") . "\n";
    echo "Environment API key: " . ($envApiKey ? substr($envApiKey, 0, 5) . "..." . substr($envApiKey, -5) : "Not found") . "\n";
    echo "Config API key: " . ($configApiKey ? substr($configApiKey, 0, 5) . "..." . substr($configApiKey, -5) : "Not found") .
         ($configError ? " (Error: $configError)" : "") . "\n";
    echo "API connection test: " . ($connectionTest['success'] ? "Success" : "Failed - " . $connectionTest['error']) . "\n";
    echo "\nDetailed logs have been written to storage/logs/maps_api.log\n";
    exit;
}

// Log environment context
\App\Core\MapsLogger::logEnvironmentContext("Diagnostic script executed");

// If we reach here, we're running in a browser, so output HTML
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maps API Key Diagnostic</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            padding: 20px;
        }
        .diagnostic-section {
            margin-bottom: 30px;
        }
        .code-block {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .api-key {
            font-family: monospace;
            padding: 2px 4px;
            background: #f0f0f0;
            border-radius: 3px;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Maps API Key Diagnostic</h1>
                
                <div class="alert <?= $apiKey ? 'alert-success' : 'alert-danger' ?>">
                    <?php if ($apiKey): ?>
                        <strong>API Key Found:</strong> <span class="api-key"><?= substr($apiKey, 0, 5) ?>...<?= substr($apiKey, -5) ?></span>
                    <?php else: ?>
                        <strong>No API Key Found!</strong> Please check your .env file and config/app.php.
                    <?php endif; ?>
                </div>
                
                <div class="card diagnostic-section">
                    <div class="card-header">
                        <h3 class="card-title">Environment Information</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Docker Environment
                                <span class="badge <?= $isDocker ? 'bg-primary' : 'bg-secondary' ?>"><?= $isDocker ? 'Yes' : 'No' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                .env File
                                <span class="badge <?= $envLoaded ? 'bg-success' : 'bg-danger' ?>"><?= $envLoaded ? 'Loaded' : 'Not Found' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Server Software
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Host
                                <span class="badge bg-secondary"><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Unknown') ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card diagnostic-section">
                    <div class="card-header">
                        <h3 class="card-title">API Key Sources</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>.env File</h5>
                                <?php if ($envApiKey): ?>
                                    <div class="alert alert-success">
                                        <strong>API Key Found:</strong> <span class="api-key"><?= substr($envApiKey, 0, 5) ?>...<?= substr($envApiKey, -5) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <strong>No API Key Found</strong> in .env file or environment variables.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>config/app.php</h5>
                                <?php if ($configError): ?>
                                    <div class="alert alert-danger">
                                        <strong>Error Loading Config:</strong> <?= htmlspecialchars($configError) ?>
                                    </div>
                                <?php elseif ($configApiKey): ?>
                                    <div class="alert alert-success">
                                        <strong>API Key Found:</strong> <span class="api-key"><?= substr($configApiKey, 0, 5) ?>...<?= substr($configApiKey, -5) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <strong>No API Key Found</strong> in config/app.php.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($envApiKey && $configApiKey && $envApiKey !== $configApiKey): ?>
                            <div class="alert alert-warning mt-3">
                                <strong>Warning:</strong> API keys in .env and config/app.php are different!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card diagnostic-section">
                    <div class="card-header">
                        <h3 class="card-title">API Connection Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($apiKey)): ?>
                            <div class="alert alert-danger">
                                <strong>Cannot Test:</strong> No API key available.
                            </div>
                        <?php elseif ($connectionTest['success']): ?>
                            <div class="alert alert-success">
                                <strong>Connection Successful!</strong> The Google Maps API is accessible and responding correctly.
                            </div>
                            <p>API Status: <?= $connectionTest['api_status'] ?></p>
                            <p>HTTP Code: <?= $connectionTest['http_code'] ?></p>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Connection Failed:</strong> Could not connect to the Google Maps API.
                            </div>
                            <p>API Status: <?= $connectionTest['api_status'] ?? 'Unknown' ?></p>
                            <p>HTTP Code: <?= $connectionTest['http_code'] ?></p>
                            <p>Error: <?= htmlspecialchars($connectionTest['error']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card diagnostic-section">
                    <div class="card-header">
                        <h3 class="card-title">What to Check Next</h3>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Make sure your API key is correctly set in the <code>.env</code> file.</li>
                            <li>Check that the API key has the appropriate permissions for the Maps JavaScript API.</li>
                            <li>Verify that there are no domain restrictions on your API key, or that this domain is allowed.</li>
                            <li>Check your Maps JavaScript API quota to ensure you haven't exceeded usage limits.</li>
                            <li>Look at the browser console for any JavaScript errors that might be preventing map initialization.</li>
                            <li>Check the <code>storage/logs/maps_api.log</code> file for detailed diagnostic information.</li>
                        </ol>
                        
                        <div class="mt-4">
                            <button class="btn btn-primary" onclick="window.location.href='/docker_maps_diagnose.php'">Run Full Diagnostic</button>
                            <button class="btn btn-secondary" onclick="window.location.href='/dashboard'">Go to Dashboard</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
