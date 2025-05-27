<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

try {
    $db = App\Core\Database::getInstance();
    
    echo "<h1>Clear All Users' Wishlists</h1>";
    
    // First, let's see what we have before clearing
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'planned'");
    $plannedCount = $db->single()['count'];
    
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'visited'");
    $visitedCount = $db->single()['count'];
    
    $db->query("SELECT COUNT(DISTINCT user_id) as count FROM trips WHERE status = 'planned'");
    $usersWithWishlists = $db->single()['count'];
    
    echo "<div style='background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;'>";
    echo "<h2>Current State</h2>";
    echo "<ul>";
    echo "<li><strong>Planned trips (wishlists):</strong> {$plannedCount}</li>";
    echo "<li><strong>Visited trips:</strong> {$visitedCount}</li>";
    echo "<li><strong>Users with wishlists:</strong> {$usersWithWishlists}</li>";
    echo "</ul>";
    echo "</div>";
    
    if ($plannedCount > 0) {
        echo "<h2>Clearing Wishlists...</h2>";
        
        // Get details of what we're about to delete for logging
        $db->query("
            SELECT t.id, t.user_id, t.destination_id, d.name, u.username
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            JOIN users u ON t.user_id = u.id
            WHERE t.status = 'planned'
            ORDER BY u.username, d.name
        ");
        $plannedTrips = $db->resultSet();
        
        echo "<h3>Planned Trips to be Removed:</h3>";
        echo "<ul>";
        $currentUser = '';
        foreach ($plannedTrips as $trip) {
            if ($currentUser !== $trip['username']) {
                if ($currentUser !== '') echo "</ul></li>";
                echo "<li><strong>{$trip['username']}</strong><ul>";
                $currentUser = $trip['username'];
            }
            echo "<li>{$trip['name']} (ID: {$trip['destination_id']})</li>";
        }
        if ($currentUser !== '') echo "</ul></li>";
        echo "</ul>";
        
        // Clear all planned trips (wishlists)
        $db->query("DELETE FROM trips WHERE status = 'planned'");
        $deletedCount = $db->rowCount();
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ Wishlists Cleared Successfully!</h3>";
        echo "<p><strong>{$deletedCount}</strong> planned trips (wishlist items) have been removed.</p>";
        echo "<p>All visited trips have been preserved.</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>ℹ️ No Wishlists to Clear</h3>";
        echo "<p>There are currently no planned trips (wishlist items) in the database.</p>";
        echo "</div>";
    }
    
    // Show final state
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'planned'");
    $finalPlannedCount = $db->single()['count'];
    
    $db->query("SELECT COUNT(*) as count FROM trips WHERE status = 'visited'");
    $finalVisitedCount = $db->single()['count'];
    
    echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>Final State</h2>";
    echo "<ul>";
    echo "<li><strong>Planned trips (wishlists):</strong> {$finalPlannedCount}</li>";
    echo "<li><strong>Visited trips:</strong> {$finalVisitedCount}</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>Next Steps for Testing</h2>";
    echo "<ol>";
    echo "<li>Visit <a href='/destinations' target='_blank'>/destinations</a> - Should show empty or only user-owned destinations</li>";
    echo "<li>Visit <a href='/dashboard' target='_blank'>/dashboard</a> - Should show featured destinations on map</li>";
    echo "<li>Click on featured destinations on the map to manually add them to wishlists</li>";
    echo "<li>Verify they appear in <a href='/destinations' target='_blank'>/destinations</a> only after manual addition</li>";
    echo "</ol>";
    
    echo "<h2>Verification Links</h2>";
    echo "<p>";
    echo "<a href='/verify-fix.php' target='_blank' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Verify Fix →</a>";
    echo "<a href='/destinations' target='_blank' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>My Destinations →</a>";
    echo "<a href='/dashboard' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Dashboard →</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>Error clearing wishlists: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; max-width: 900px; }
    h1 { color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
    h2 { color: #666; margin-top: 30px; }
    h3 { color: #495057; }
    ol, ul { margin: 10px 0; }
    li { margin: 5px 0; padding: 2px 0; }
    p { line-height: 1.6; }
    a { color: #007bff; }
</style>
