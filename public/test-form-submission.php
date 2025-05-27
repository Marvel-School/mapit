<?php
// Test form submission to profile update
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

echo "<h2>Testing Profile Form Submission</h2>";

// Test data to submit
$testData = [
    'name' => 'Updated Test Name',
    'username' => 'admin',
    'email' => 'admin@mapit.com',
    'bio' => 'This is my updated bio from form test!',
    'website' => 'https://updated-website.com',
    'country' => 'CA',
    'settings' => [
        'public_profile' => '1',
        'email_notifications' => '1'
    ]
];

// Use cURL to submit the form
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/profile');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Cookie: PHPSESSID=' . session_id(),
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Form Submission Results:</h3>";
echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";
echo "<p><strong>Response Length:</strong> " . strlen($response) . " characters</p>";

// Check if redirect happened (success message in response)
$hasSuccessMessage = strpos($response, 'Profile updated successfully') !== false;
echo "<p><strong>Success Message:</strong> " . ($hasSuccessMessage ? '✅ Found' : '❌ Not found') . "</p>";

// Show response preview
echo "<h3>Response Preview (first 1000 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";

// Wait a moment and check database
sleep(1);

try {
    require_once '../vendor/autoload.php';
    require_once '../app/Core/Autoloader.php';
    App\Core\Autoloader::register();
    
    use App\Models\User;
    
    $userModel = new User();
    $updatedUser = $userModel->find(1);
    
    echo "<h3>Database Check:</h3>";
    echo "<p><strong>Name:</strong> " . ($updatedUser['name'] ?? 'NULL') . "</p>";
    echo "<p><strong>Bio:</strong> " . ($updatedUser['bio'] ?? 'NULL') . "</p>";
    echo "<p><strong>Website:</strong> " . ($updatedUser['website'] ?? 'NULL') . "</p>";
    echo "<p><strong>Country:</strong> " . ($updatedUser['country'] ?? 'NULL') . "</p>";
    
    $isUpdated = ($updatedUser['name'] === 'Updated Test Name' && 
                  $updatedUser['bio'] === 'This is my updated bio from form test!');
                  
    echo "<h2>" . ($isUpdated ? "✅ PROFILE UPDATE WORKING!" : "❌ PROFILE UPDATE NOT WORKING") . "</h2>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database check error: " . $e->getMessage() . "</p>";
}
?>
