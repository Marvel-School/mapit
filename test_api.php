<?php
/**
 * Test API endpoint with HTTP requests
 */

echo "Testing MapIt Interactive Map API\n";
echo "=================================\n\n";

// Test 1: Test unauthenticated API call
echo "1. Testing unauthenticated API call...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/destinations/quick-create');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Test Destination',
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'city' => 'New York',
    'country' => 'US'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Status: $httpCode\n";

// Split headers and body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
if ($headerSize === false) {
    // If header size is not available, split manually
    $parts = explode("\r\n\r\n", $response, 2);
    $headers = $parts[0] ?? '';
    $body = $parts[1] ?? $response;
} else {
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
}

echo "  Response Body: $body\n";

// Test 2: Test with valid session (simulate login)
echo "\n2. Testing with simulated login session...\n";

// Start a session and simulate login
session_start();
session_id('test_session_' . time());

// Use a session-aware curl request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'testuser@mapit.com',
    'password' => 'user123'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  Login HTTP Status: $loginHttpCode\n";

// Now try the API call with the session cookies
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/destinations/quick-create');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Test Destination',
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'city' => 'New York',
    'country' => 'US'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$apiResponse = curl_exec($ch);
$apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  API HTTP Status: $apiHttpCode\n";
echo "  API Response: $apiResponse\n";

echo "\n3. Testing debug endpoint...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/destinations/debug');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$debugResponse = curl_exec($ch);
$debugHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  Debug HTTP Status: $debugHttpCode\n";
echo "  Debug Response: $debugResponse\n";

echo "\nTest completed!\n";
