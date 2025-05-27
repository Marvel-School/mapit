<?php
session_start();

// Auto-login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\View;

echo "<h1>Profile Page Fix Verification</h1>";

// Test the View::render method directly with profile data
echo "<h2>Testing Profile View Rendering:</h2>";

$profileData = [
    'title' => 'Profile Page Test',
    'user' => [
        'id' => 1,
        'username' => 'testuser',
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'profile_picture' => null
    ]
];

echo "<h3>Rendered Profile Page:</h3>";
echo "<div style='border: 2px solid #007bff; padding: 10px; margin: 10px 0;'>";

// Capture the rendered output
ob_start();
View::render('dashboard/profile', $profileData);
$renderedContent = ob_get_clean();

echo $renderedContent;
echo "</div>";

// Check if Bootstrap CSS is present
if (strpos($renderedContent, 'bootstrap') !== false) {
    echo "<p style='color: green;'>✅ Bootstrap CSS detected in rendered content</p>";
} else {
    echo "<p style='color: red;'>❌ Bootstrap CSS NOT detected in rendered content</p>";
}

// Check if proper HTML structure is present
if (strpos($renderedContent, '<!DOCTYPE html>') !== false) {
    echo "<p style='color: green;'>✅ Full HTML structure with DOCTYPE detected</p>";
} else {
    echo "<p style='color: red;'>❌ Full HTML structure NOT detected</p>";
}

// Check if navigation is present
if (strpos($renderedContent, 'navbar') !== false) {
    echo "<p style='color: green;'>✅ Navigation bar detected</p>";
} else {
    echo "<p style='color: red;'>❌ Navigation bar NOT detected</p>";
}

// Check if the profile content is present
if (strpos($renderedContent, 'My Profile') !== false) {
    echo "<p style='color: green;'>✅ Profile content detected</p>";
} else {
    echo "<p style='color: red;'>❌ Profile content NOT detected</p>";
}

echo "<h3>Content Length: " . strlen($renderedContent) . " characters</h3>";

if (strlen($renderedContent) > 5000) {
    echo "<p style='color: green;'>✅ Content appears to be full page (good length)</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Content might be partial (short length)</p>";
}
?>
