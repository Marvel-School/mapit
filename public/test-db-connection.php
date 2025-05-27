<?php
// Simple test to verify database connection and featured destinations
try {
    // Include database configuration
    $config = require_once '../config/app.php';
    
    $host = $config['database']['host'];
    $dbname = $config['database']['dbname'];
    $username = $config['database']['username'];
    $password = $config['database']['password'];
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query for featured destinations
    $stmt = $pdo->prepare("
        SELECT d.*, u.username 
        FROM destinations d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.featured = 1 AND d.status = 'approved'
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Database Connection: SUCCESS</h3>";
    echo "<p>Found " . count($featured) . " featured destinations:</p>";
    echo "<ul>";
    foreach ($featured as $dest) {
        echo "<li><strong>{$dest['name']}</strong> (ID: {$dest['id']}) - Coordinates: {$dest['latitude']}, {$dest['longitude']}</li>";
    }
    echo "</ul>";
    
    if (count($featured) > 0) {
        echo "<p><strong>Featured destinations are available for testing!</strong></p>";
        echo '<p><a href="/test-featured-destinations.php">→ Go to Map Test Page</a></p>';
    } else {
        echo "<p><strong>No featured destinations found. The map test will be empty.</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<h3>Database Connection: FAILED</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
