<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

// Set up a test user session if not already logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Test as admin user
}

try {
    $destinationModel = new App\Models\Destination();
    $tripModel = new App\Models\Trip();
    
    echo "<h1>Post-Cleanup Test Verification</h1>";
    
    // Get featured destinations
    $featured = $destinationModel->getFeatured(10);
    
    // Get user's current destinations
    $userDestinations = $destinationModel->getUserDestinationsWithTrips($_SESSION['user_id']);
    
    // Get all trips (should only be visited ones now)
    $db = App\Core\Database::getInstance();
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'planned'");
    $plannedTrips = $db->single()['count'];
    
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'visited'");
    $visitedTrips = $db->single()['count'];
    
    echo "<div style='background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;'>";
    echo "<h2>Current State After Cleanup</h2>";
    echo "<ul>";
    echo "<li><strong>Featured destinations available:</strong> " . count($featured) . "</li>";
    echo "<li><strong>User's destinations:</strong> " . count($userDestinations) . "</li>";
    echo "<li><strong>Planned trips (wishlists):</strong> {$plannedTrips}</li>";
    echo "<li><strong>Visited trips:</strong> {$visitedTrips}</li>";
    echo "</ul>";
    echo "</div>";
    
    if ($plannedTrips == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<h3>✅ Perfect! All Wishlists Cleared</h3>";
        echo "<p>Now you can test the featured destinations fix properly:</p>";
        echo "<ol>";
        echo "<li>Featured destinations should appear on the dashboard map</li>";
        echo "<li>No featured destinations should appear in your personal destinations list</li>";
        echo "<li>You can manually add featured destinations by interacting with them on the map</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "<h2>Featured Destinations Available for Testing</h2>";
    if (!empty($featured)) {
        echo "<p>These should appear on the dashboard map but NOT in your personal destinations list:</p>";
        echo "<ul>";
        foreach ($featured as $dest) {
            echo "<li><strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} <span style='color: #ff6b35;'>[Featured ⭐]</span></li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: #856404;'>⚠️ No featured destinations found. You may need to add some for testing.</p>";
    }
    
    echo "<h2>User's Personal Destinations</h2>";
    if (empty($userDestinations)) {
        echo "<div style='color: green; font-weight: bold;'>✅ EXCELLENT: Personal destinations list is clean!</div>";
        echo "<p>This confirms that featured destinations are not automatically appearing.</p>";
    } else {
        echo "<p>Current destinations in your personal list:</p>";
        echo "<ul>";
        foreach ($userDestinations as $dest) {
            $isOwned = $dest['user_id'] == $_SESSION['user_id'];
            $isFeatured = $dest['featured'] == 1;
            
            echo "<li>";
            echo "<strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} ";
            if ($isFeatured && !$isOwned) {
                echo "<span style='color: red;'>[FEATURED - SHOULD NOT BE HERE!]</span>";
            } else if ($isOwned) {
                echo "<span style='color: blue;'>[Owned by you]</span>";
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>Testing Instructions</h2>";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Test the Fix:</h3>";
    echo "<ol>";
    echo "<li><strong>Dashboard Test:</strong> Go to <a href='/dashboard' target='_blank'>/dashboard</a> and verify featured destinations appear on the map</li>";
    echo "<li><strong>Destinations Test:</strong> Go to <a href='/destinations' target='_blank'>/destinations</a> and verify the list is clean (no automatic featured destinations)</li>";
    echo "<li><strong>Manual Add Test:</strong> Click on a featured destination on the dashboard map and manually add it to your wishlist</li>";
    echo "<li><strong>Verification:</strong> Check that it now appears in <a href='/destinations' target='_blank'>/destinations</a> because you chose to add it</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>Quick Links</h2>";
    echo "<p>";
    echo "<a href='/dashboard' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Dashboard (Map) →</a>";
    echo "<a href='/destinations' target='_blank' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>My Destinations →</a>";
    echo "<a href='/verify-fix.php' target='_blank' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Verify Fix →</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; max-width: 900px; }
    h1 { color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
    h2 { color: #666; margin-top: 30px; }
    h3 { color: #495057; }
    ol, ul { margin: 10px 0; }
    li { margin: 5px 0; padding: 2px 0; }
    p { line-height: 1.6; }
    a { color: #007bff; }
</style>
