<?php
// Test trips show functionality

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Start session
session_start();
$_SESSION['user_id'] = 1;

try {
    $db = \App\Core\Database::getInstance();
    
    echo "<h1>Testing Trips Show Functionality</h1>";
    
    // Get a sample trip
    $db->query("
        SELECT t.*, d.name as destination_name, d.latitude, d.longitude, 
            d.description as destination_description
        FROM trips t
        JOIN destinations d ON t.destination_id = d.id
        WHERE t.user_id = :user_id
        LIMIT 1
    ");
    $db->bind(':user_id', $_SESSION['user_id']);
    $trip = $db->single();
    
    if (!$trip) {
        echo "<p>No trips found for user. Let's create one for testing...</p>";
        
        // Get a destination
        $db->query("SELECT * FROM destinations LIMIT 1");
        $destination = $db->single();
        
        if ($destination) {
            // Create a test trip
            $db->query("
                INSERT INTO trips (user_id, destination_id, status, type, created_at) 
                VALUES (:user_id, :destination_id, 'planned', 'adventure', NOW())
            ");
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':destination_id', $destination['id']);
            $db->execute();
            
            // Get the created trip
            $db->query("
                SELECT t.*, d.name as destination_name, d.latitude, d.longitude, 
                    d.description as destination_description
                FROM trips t
                JOIN destinations d ON t.destination_id = d.id
                WHERE t.user_id = :user_id
                ORDER BY t.id DESC
                LIMIT 1
            ");
            $db->bind(':user_id', $_SESSION['user_id']);
            $trip = $db->single();
        }
    }
    
    if ($trip) {
        echo "<p>✓ Found trip with ID: " . $trip['id'] . "</p>";
        echo "<p>✓ Destination: " . htmlspecialchars($trip['destination_name']) . "</p>";
        echo "<p>✓ Status: " . $trip['status'] . "</p>";
        echo "<p>✓ Type: " . $trip['type'] . "</p>";
        
        echo "<p><a href='/trips/{$trip['id']}' target='_blank'>Test Trip Show Page</a></p>";
        
        echo "<h2>Trip Data Structure:</h2>";
        echo "<pre>";
        print_r($trip);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>Could not find or create a test trip.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>
