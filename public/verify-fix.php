<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

// Simple verification script to check if the fix is working
// Mock a user session for testing purposes
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Test user
}

try {
    $destinationModel = new App\Models\Destination();
    
    // Get all featured destinations  
    $featured = $destinationModel->getFeatured(20);
    
    // Get user's destinations using the fixed method
    $userDestinations = $destinationModel->getUserDestinationsWithTrips($_SESSION['user_id']);
    
    echo "<h1>Destination Fix Verification</h1>";
    
    echo "<h2>Featured Destinations Available</h2>";
    echo "<p>Total featured destinations: " . count($featured) . "</p>";
    if (!empty($featured)) {
        echo "<ul>";
        foreach (array_slice($featured, 0, 5) as $dest) {
            echo "<li><strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} (ID: {$dest['id']})</li>";
        }
        if (count($featured) > 5) {
            echo "<li><em>... and " . (count($featured) - 5) . " more</em></li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>User's Personal Destinations List</h2>";
    echo "<p>Total destinations in user's list: " . count($userDestinations) . "</p>";
    
    if (empty($userDestinations)) {
        echo "<div style='color: green; font-weight: bold;'>✅ EXCELLENT: No destinations automatically added to user's list!</div>";
        echo "<p>This means featured destinations are NOT automatically appearing in user's personal destination lists.</p>";
    } else {
        echo "<ul>";
        $featuredFound = 0;
        foreach ($userDestinations as $dest) {
            $isOwned = $dest['user_id'] == $_SESSION['user_id'];
            $isFeatured = $dest['featured'] == 1;
            
            if ($isFeatured && !$isOwned) {
                $featuredFound++;
                echo "<li style='color: red;'><strong>{$dest['name']}</strong> - FEATURED DESTINATION (SHOULD NOT BE HERE!)</li>";
            } else if ($isOwned) {
                echo "<li style='color: blue;'><strong>{$dest['name']}</strong> - User's own destination ✓</li>";
            } else {
                echo "<li><strong>{$dest['name']}</strong> - Public destination with user trips</li>";
            }
        }
        echo "</ul>";
        
        if ($featuredFound == 0) {
            echo "<div style='color: green; font-weight: bold;'>✅ SUCCESS: No featured destinations automatically appearing!</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ ISSUE: {$featuredFound} featured destinations still appearing automatically!</div>";
        }
    }
    
    echo "<h2>Test Dashboard Map</h2>";
    echo "<p>Featured destinations should still appear on the dashboard map for discovery.</p>";
    echo "<p><a href='/dashboard' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Test Dashboard Map →</a></p>";
    
    echo "<h2>Fix Status</h2>";
    if (empty($userDestinations) || $featuredFound == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ FIX SUCCESSFUL</strong><br>";
        echo "Featured destinations are no longer automatically appearing in user destination lists.<br>";
        echo "Users will only see destinations they explicitly own or have added to their wishlists.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<strong>❌ FIX NEEDS ATTENTION</strong><br>";
        echo "Featured destinations are still appearing automatically in user lists.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error testing fix: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; max-width: 800px; }
    h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    h2 { color: #666; margin-top: 30px; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; padding: 5px 0; }
    p { line-height: 1.6; }
</style>
