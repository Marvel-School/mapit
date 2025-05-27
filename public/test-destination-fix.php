<?php
// Test script to verify that featured destinations no longer automatically appear in user destination lists

// Define base path
define('BASE_PATH', __DIR__ . '/..');

// Include autoloader
require_once BASE_PATH . '/app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Load vendor dependencies
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// Authenticate as test user (simulate being logged in)
session_start();
$_SESSION['user_id'] = 1; // Test with user ID 1

try {
    $destinationModel = new \App\Models\Destination();
    
    echo "<h1>Destination Fix Verification</h1>\n";
    
    // Get featured destinations
    $featured = $destinationModel->getFeatured();
    echo "<h2>Featured Destinations (Should NOT appear in user lists automatically)</h2>\n";
    echo "<p>Total featured destinations: " . count($featured) . "</p>\n";
    echo "<ul>\n";
    foreach ($featured as $dest) {
        echo "<li><strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} (ID: {$dest['id']})</li>\n";
    }
    echo "</ul>\n";
    
    // Get user's destinations using the fixed method
    $userDestinations = $destinationModel->getUserDestinationsWithTrips(1);
    echo "<h2>User's Destinations (After Fix)</h2>\n";
    echo "<p>Total user destinations: " . count($userDestinations) . "</p>\n";
    
    if (empty($userDestinations)) {
        echo "<p><strong>✅ GOOD:</strong> User has no destinations appearing automatically.</p>\n";
    } else {
        echo "<ul>\n";
        foreach ($userDestinations as $dest) {
            $isFeatured = $dest['featured'] == 1 ? ' <strong>(FEATURED - SHOULD NOT BE HERE)</strong>' : '';
            $isOwnedByUser = $dest['user_id'] == 1 ? ' (owned by user)' : ' (public destination)';
            echo "<li><strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} (ID: {$dest['id']}){$isOwnedByUser}{$isFeatured}</li>\n";
        }
        echo "</ul>\n";
    }
    
    // Check if any featured destinations appear in user list
    $featuredInUserList = array_filter($userDestinations, function($dest) {
        return $dest['featured'] == 1 && $dest['user_id'] != 1;
    });
    
    if (empty($featuredInUserList)) {
        echo "<h3>✅ SUCCESS: No featured destinations are automatically appearing in user's destination list!</h3>\n";
    } else {
        echo "<h3>❌ ERROR: Featured destinations are still appearing in user's destination list:</h3>\n";
        echo "<ul>\n";
        foreach ($featuredInUserList as $dest) {
            echo "<li><strong>{$dest['name']}</strong> (ID: {$dest['id']})</li>\n";
        }
        echo "</ul>\n";
    }
    
    // Test the original getUserDestinationsWithTrips method to see what changed
    echo "<h2>Technical Details</h2>\n";
    echo "<p>The fix involved removing the OR condition that included public/featured destinations with trips.</p>\n";
    echo "<p>Now only destinations owned by the user (user_id = {$_SESSION['user_id']}) appear in their list.</p>\n";
    echo "<p>Featured destinations are only shown on the dashboard map, not in personal destination lists.</p>\n";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>\n";
    echo "<p>Error testing fix: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #333; }
    h2 { color: #666; border-bottom: 1px solid #ccc; }
    h3 { color: #28a745; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
    .error { color: #dc3545; }
    .success { color: #28a745; }
</style>
