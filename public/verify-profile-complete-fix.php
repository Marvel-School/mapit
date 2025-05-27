<?php
// Final verification script for profile page fix
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

// Test 1: Check if profile page loads without errors
echo "<h2>Profile Page Fix Verification</h2>";

// Try to load the profile page using curl to get the actual response
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Cookie: PHPSESSID=' . session_id()
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Test Results:</h3>";
echo "<p><strong>HTTP Status:</strong> " . ($httpCode == 200 ? '✅ 200 OK' : '❌ ' . $httpCode) . "</p>";

// Check if response contains HTML structure
$hasHtml = strpos($response, '<!DOCTYPE html') !== false;
echo "<p><strong>HTML Structure:</strong> " . ($hasHtml ? '✅ Found' : '❌ Missing') . "</p>";

// Check if response contains Bootstrap CSS
$hasBootstrap = strpos($response, 'bootstrap') !== false;
echo "<p><strong>Bootstrap CSS:</strong> " . ($hasBootstrap ? '✅ Found' : '❌ Missing') . "</p>";

// Check if response contains the profile form
$hasProfileForm = strpos($response, 'My Profile') !== false && strpos($response, 'form') !== false;
echo "<p><strong>Profile Form:</strong> " . ($hasProfileForm ? '✅ Found' : '❌ Missing') . "</p>";

// Check if response contains navigation
$hasNavigation = strpos($response, 'navbar') !== false;
echo "<p><strong>Navigation:</strong> " . ($hasNavigation ? '✅ Found' : '❌ Missing') . "</p>";

// Show response length (should be much longer than plain text)
$responseLength = strlen($response);
echo "<p><strong>Response Length:</strong> " . $responseLength . " characters " . 
     ($responseLength > 1000 ? '✅ Full HTML page' : '❌ Too short for full HTML') . "</p>";

// Show first 500 characters of response to verify it's HTML
echo "<h3>Response Preview (first 500 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";

if ($httpCode == 200 && $hasHtml && $hasBootstrap && $hasProfileForm && $hasNavigation && $responseLength > 1000) {
    echo "<h2 style='color: green;'>✅ PROFILE PAGE FIX SUCCESSFUL!</h2>";
    echo "<p>The profile page is now rendering with complete HTML layout, Bootstrap CSS, and proper styling.</p>";
} else {
    echo "<h2 style='color: red;'>❌ PROFILE PAGE FIX INCOMPLETE</h2>";
    echo "<p>Some issues remain. Check the test results above.</p>";
}
?>
