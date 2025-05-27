<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

// Test script to demonstrate the intended featured destination behavior
// Mock a user session for testing purposes
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Test user
}

try {
    $destinationModel = new App\Models\Destination();
    $tripModel = new App\Models\Trip();
    
    // Get some featured destinations
    $featured = $destinationModel->getFeatured(5);
    
    echo "<h1>Featured Destinations - Expected Behavior Test</h1>";
    
    echo "<h2>How Featured Destinations Should Work</h2>";
    echo "<div style='background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;'>";
    echo "<strong>✅ CORRECT BEHAVIOR:</strong><br>";
    echo "1. Featured destinations appear on the dashboard map for all users to discover<br>";
    echo "2. Featured destinations do NOT automatically appear in anyone's personal destination lists<br>";
    echo "3. Users can click featured destinations on the map to view details and manually add them to their wishlist<br>";
    echo "4. Only when a user explicitly chooses to wishlist a featured destination does it appear in their personal list";
    echo "</div>";
    
    echo "<h2>Featured Destinations Available for Discovery</h2>";
    echo "<p>These appear on the dashboard map but NOT in personal destination lists:</p>";
    echo "<ul>";
    foreach ($featured as $dest) {
        echo "<li>";
        echo "<strong>{$dest['name']}</strong> - {$dest['city']}, {$dest['country']} ";
        echo "<span style='color: #ff6b35;'>[Featured ⭐]</span>";
        echo "</li>";
    }
    echo "</ul>";
    
    echo "<h2>User Choice Test</h2>";
    echo "<p>Users can manually add featured destinations by:</p>";
    echo "<ol>";
    echo "<li>Viewing a featured destination (e.g., visiting its detail page)</li>";
    echo "<li>Clicking 'Add to Wishlist' or 'Mark as Visited' buttons</li>";
    echo "<li>The destination then appears in their personal list because they chose to add it</li>";
    echo "</ol>";
    
    if (!empty($featured)) {
        $testDest = $featured[0];
        echo "<p><strong>Example:</strong> ";
        echo "<a href='/destinations/{$testDest['id']}' target='_blank' style='background: #28a745; color: white; padding: 6px 12px; text-decoration: none; border-radius: 3px;'>";
        echo "View {$testDest['name']} Details →</a>";
        echo "</p>";
        echo "<p><small>On the destination detail page, you can choose to add it to your wishlist or mark as visited.</small></p>";
    }
    
    echo "<h2>Current Fix Status</h2>";
    
    // Check current user destinations
    $userDestinations = $destinationModel->getUserDestinationsWithTrips($_SESSION['user_id']);
    $autoFeaturedCount = 0;
    
    foreach ($userDestinations as $dest) {
        if ($dest['featured'] == 1 && $dest['user_id'] != $_SESSION['user_id']) {
            $autoFeaturedCount++;
        }
    }
    
    if ($autoFeaturedCount == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ FIX WORKING CORRECTLY</strong><br>";
        echo "• Featured destinations are NOT automatically appearing in user lists<br>";
        echo "• Users maintain full control over their personal destination collections<br>";
        echo "• Featured destinations remain discoverable on the dashboard map";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<strong>❌ ISSUE DETECTED</strong><br>";
        echo "• {$autoFeaturedCount} featured destinations are still auto-appearing in user lists<br>";
        echo "• This is the bug that was supposed to be fixed";
        echo "</div>";
    }
    
    echo "<h2>Test Links</h2>";
    echo "<p>";
    echo "<a href='/dashboard' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Dashboard (Featured Map) →</a>";
    echo "<a href='/destinations' target='_blank' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>My Destinations →</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; max-width: 900px; }
    h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    h2 { color: #666; margin-top: 30px; }
    ol, ul { margin: 10px 0; }
    li { margin: 5px 0; padding: 2px 0; }
    p { line-height: 1.6; }
</style>
