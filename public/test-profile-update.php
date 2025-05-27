<?php
// Test profile update functionality
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

// Simulate form data
$_POST = [
    'name' => 'Test User Updated',
    'username' => 'testuser',
    'email' => 'test@example.com',
    'bio' => 'This is my updated bio!',
    'website' => 'https://example.com',
    'country' => 'US',
    'settings' => [
        'public_profile' => '1',
        'email_notifications' => '1'
    ]
];

echo "<h2>Profile Update Test</h2>";

try {
    // Include the application bootstrap
    require_once '../vendor/autoload.php';
    require_once '../app/Core/Autoloader.php';
    App\Core\Autoloader::register();
    
    use App\Controllers\DashboardController;
    
    // Create controller and call updateProfile
    $controller = new DashboardController();
    
    echo "<p>Before update - checking current user data...</p>";
    
    // Get current user data
    $userModel = new App\Models\User();
    $userBefore = $userModel->find(1);
    
    echo "<pre>User data before update:\n";
    print_r($userBefore);
    echo "</pre>";
    
    // Capture any output from the update method
    ob_start();
    $controller->updateProfile();
    $output = ob_get_clean();
    
    echo "<p>Update method executed. Output: " . htmlspecialchars($output) . "</p>";
    
    // Get updated user data
    $userAfter = $userModel->find(1);
    
    echo "<pre>User data after update:\n";
    print_r($userAfter);
    echo "</pre>";
    
    // Check if data was actually updated
    $updated = false;
    if ($userAfter['name'] === 'Test User Updated' && 
        $userAfter['bio'] === 'This is my updated bio!' &&
        $userAfter['website'] === 'https://example.com' &&
        $userAfter['country'] === 'US') {
        $updated = true;
    }
    
    echo "<h3>" . ($updated ? "✅ Profile Update SUCCESSFUL!" : "❌ Profile Update FAILED!") . "</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
