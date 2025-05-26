<?php
/**
 * Debug script to trace Google Maps API key loading
 * This will help identify where the API key is being lost in the data transmission chain
 */

echo "<h1>Google Maps API Key Debug</h1>\n";
echo "<pre>\n";

// Step 1: Check if .env file exists and is readable
echo "=== STEP 1: Environment File Check ===\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✓ .env file exists at: $envFile\n";
    echo "✓ .env file is readable: " . (is_readable($envFile) ? "YES" : "NO") . "\n";
    
    // Read .env content to verify API key is present
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'GOOGLE_MAPS_API_KEY') !== false) {
        echo "✓ GOOGLE_MAPS_API_KEY found in .env file\n";
        
        // Extract the actual value
        preg_match('/GOOGLE_MAPS_API_KEY=(.+)/', $envContent, $matches);
        if (!empty($matches[1])) {
            $envApiKey = trim($matches[1]);
            echo "✓ API Key value in .env: " . substr($envApiKey, 0, 10) . "..." . substr($envApiKey, -5) . "\n";
        }
    } else {
        echo "✗ GOOGLE_MAPS_API_KEY not found in .env file\n";
    }
} else {
    echo "✗ .env file not found at: $envFile\n";
}

echo "\n=== STEP 2: Bootstrap Application ===\n";

// Step 2: Load the application like index.php does