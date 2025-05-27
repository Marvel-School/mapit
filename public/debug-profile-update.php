<?php
// Debug profile update - capture POST data and test update
session_start();

// Auto-login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

echo "<h2>Profile Update Debug</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    try {
        require_once '../vendor/autoload.php';
        require_once '../app/Core/Autoloader.php';
        App\Core\Autoloader::register();
        
        use App\Models\User;
        
        $userModel = new User();
        $userId = 1;
        
        // Get current user data
        $userBefore = $userModel->find($userId);
        echo "<h3>User Data Before Update:</h3>";
        echo "<pre>";
        print_r($userBefore);
        echo "</pre>";
        
        // Prepare update data
        $userData = [
            'name' => $_POST['name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'website' => $_POST['website'] ?? '',
            'country' => $_POST['country'] ?? ''
        ];
        
        // Handle settings
        if (isset($_POST['settings'])) {
            $userData['settings'] = json_encode([
                'public_profile' => isset($_POST['settings']['public_profile']),
                'email_notifications' => isset($_POST['settings']['email_notifications']),
                'show_visited_places' => isset($_POST['settings']['show_visited_places'])
            ]);
        }
        
        echo "<h3>Update Data:</h3>";
        echo "<pre>";
        print_r($userData);
        echo "</pre>";
        
        // Perform update
        $updated = $userModel->update($userId, $userData);
        
        echo "<h3>Update Result:</h3>";
        echo "<p>Update successful: " . ($updated ? "YES" : "NO") . "</p>";
        
        // Get user data after update
        $userAfter = $userModel->find($userId);
        echo "<h3>User Data After Update:</h3>";
        echo "<pre>";
        print_r($userAfter);
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    // Show a simple form for testing
    echo '<form method="POST" action="">
        <div style="margin: 10px 0;">
            <label>Name: <input type="text" name="name" value="Test User Updated"></label>
        </div>
        <div style="margin: 10px 0;">
            <label>Username: <input type="text" name="username" value="testuser"></label>
        </div>
        <div style="margin: 10px 0;">
            <label>Email: <input type="email" name="email" value="test@example.com"></label>
        </div>
        <div style="margin: 10px 0;">
            <label>Bio: <textarea name="bio">Updated bio content</textarea></label>
        </div>
        <div style="margin: 10px 0;">
            <label>Website: <input type="url" name="website" value="https://example.com"></label>
        </div>
        <div style="margin: 10px 0;">
            <label>Country: 
                <select name="country">
                    <option value="US">United States</option>
                    <option value="CA">Canada</option>
                </select>
            </label>
        </div>
        <div style="margin: 10px 0;">
            <label><input type="checkbox" name="settings[public_profile]" value="1"> Public Profile</label>
        </div>
        <div style="margin: 10px 0;">
            <label><input type="checkbox" name="settings[email_notifications]" value="1"> Email Notifications</label>
        </div>
        <button type="submit">Test Update</button>
    </form>';
}
?>
