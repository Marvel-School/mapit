#!/usr/bin/env php
<?php

/**
 * Google Maps API Integration Check
 * 
 * This script provides a quick command-line check to ensure
 * that the Google Maps API integration is working properly.
 */

echo "\n===== MapIt - Google Maps API Integration Check =====\n\n";

// Load environment variables
$envFile = __DIR__ . '/.env';

echo "Checking for .env file... ";
if (file_exists($envFile)) {
    echo "FOUND\n";
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
} else {
    echo "NOT FOUND\n";
    echo "Error: .env file not found. Please make sure the file exists.\n";
    exit(1);
}

// Check if config file exists
echo "Checking for config/app.php... ";
$configFile = __DIR__ . '/config/app.php';

if (file_exists($configFile)) {
    echo "FOUND\n";
    $config = require $configFile;
} else {
    echo "NOT FOUND\n";
    echo "Error: config/app.php not found. Please make sure the file exists.\n";
    exit(1);
}

// Check for Google Maps API key
echo "Checking for Google Maps API key... ";
$apiKey = $config['google_maps']['api_key'] ?? '';

if (!empty($apiKey)) {
    echo "FOUND\n";
    echo "API Key: " . substr($apiKey, 0, 5) . '...' . substr($apiKey, -5) . "\n";
} else {
    echo "NOT FOUND\n";
    echo "Error: Google Maps API key is not configured in config/app.php or .env file.\n";
    exit(1);
}

// Check for necessary files
echo "\nChecking for required files:\n";

$files = [
    'app/Views/layouts/main.php' => 'Contains the Google Maps script tag',
    'public/js/main.js' => 'Contains the initialization code for Google Maps',
];

$allFilesExist = true;

foreach ($files as $file => $description) {
    echo "  - $file... ";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "FOUND\n";
        
        // Look for specific content in the files
        $content = file_get_contents(__DIR__ . '/' . $file);
        
        if ($file == 'app/Views/layouts/main.php') {
            echo "    - Checking for API script tag... ";
            if (strpos($content, 'maps.googleapis.com/maps/api/js') !== false) {
                echo "FOUND\n";
            } else {
                echo "NOT FOUND\n";
                echo "    - Warning: Google Maps script tag may be missing from layout file.\n";
                $allFilesExist = false;
            }
            
            echo "    - Checking for meta tag... ";
            if (strpos($content, 'google-maps-api-key') !== false) {
                echo "FOUND\n";
            } else {
                echo "NOT FOUND\n";
                echo "    - Warning: Google Maps API key meta tag may be missing.\n";
                $allFilesExist = false;
            }
        }
        
        if ($file == 'public/js/main.js') {
            echo "    - Checking for initialization function... ";
            if (strpos($content, 'function initializeGoogleMaps') !== false) {
                echo "FOUND\n";
            } else {
                echo "NOT FOUND\n";
                echo "    - Warning: Google Maps initialization function may be missing.\n";
                $allFilesExist = false;
            }
            
            echo "    - Checking for fallback loading... ";
            if (strpos($content, 'waitForGoogleMaps') !== false) {
                echo "FOUND\n";
            } else {
                echo "NOT FOUND\n";
                echo "    - Warning: Google Maps fallback loading may be missing.\n";
                $allFilesExist = false;
            }
        }
    } else {
        echo "NOT FOUND\n";
        echo "    - Error: Required file is missing.\n";
        $allFilesExist = false;
    }
}

echo "\nVerification Result: ";

if ($allFilesExist) {
    echo "SUCCESS\n";
    echo "Google Maps API integration appears to be properly configured.\n\n";
    
    // Verification URLs
    echo "To manually verify the integration, visit these URLs:\n";
    echo "  - Dashboard: http://localhost/dashboard \n";
    echo "  - Verification Page: http://localhost/verify_maps_fix.php\n\n";
    
    echo "If maps still don't load, check browser console for errors.\n";
} else {
    echo "ISSUES DETECTED\n";
    echo "Some components may be missing or misconfigured.\n";
    echo "Please review the warnings above and fix any issues.\n\n";
    
    echo "For detailed instructions, see MAPS_API_FIX_DOCUMENTATION.md\n";
    exit(1);
}

echo "\n";
