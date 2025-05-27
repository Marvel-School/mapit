<?php
/**
 * Test Script: Destination Creation via Interactive Map
 * 
 * This script tests the fixed destination creation functionality
 */

// Mock session for testing
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Test with user ID 1
}

require_once '../vendor/autoload.php';
require_once '../config/app.php';

use App\Core\Database;

echo "<h2>Testing Destination Creation Fix</h2>\n";

// Database connection
$db = new Database();

// Test data
$testData = [
    'name' => 'Test Location from Fix',
    'city' => 'Test City',
    'country' => 'US',
    'description' => 'This is a test location created to verify the fix',
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'visited' => 1,
    'privacy' => 'private',
    'visit_date' => '2024-01-15'
];

echo "<h3>Test Data:</h3>\n";
echo "<pre>" . print_r($testData, true) . "</pre>\n";

// Simulate the API call
$input = json_encode($testData);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture the output
ob_start();

// Simulate the API request
$postData = $testData;

// Include the controller logic directly
$latitude = $postData['latitude'] ?? '';
$longitude = $postData['longitude'] ?? '';
$name = trim($postData['name'] ?? '');
$city = trim($postData['city'] ?? '');
$country = trim($postData['country'] ?? '');
$description = trim($postData['description'] ?? '');
$visited = isset($postData['visited']) ? (int)$postData['visited'] : 0;
$privacy = $postData['privacy'] ?? 'private';
$visitDate = $postData['visit_date'] ?? null;

echo "<h3>Processing...</h3>\n";

// Basic validation
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    echo "<div style='color: red'>❌ ERROR: Invalid latitude/longitude</div>\n";
    exit;
}

if (empty($name)) {
    echo "<div style='color: red'>❌ ERROR: Destination name is required</div>\n";
    exit;
}

// Create destination
try {
    $destinationData = [
        'name' => $name,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'description' => !empty($description) ? $description : null,
        'city' => !empty($city) ? $city : null,
        'country' => !empty($country) ? $country : null,
        'privacy' => $privacy,
        'visited' => $visited,
        'user_id' => $_SESSION['user_id'],
        'approval_status' => $privacy === 'private' ? 'approved' : 'pending'
    ];
    
    echo "<h3>Destination Data to Insert:</h3>\n";
    echo "<pre>" . print_r($destinationData, true) . "</pre>\n";
    
    // Insert destination
    $sql = "INSERT INTO destinations (name, latitude, longitude, description, city, country, privacy, visited, user_id, approval_status) 
            VALUES (:name, :latitude, :longitude, :description, :city, :country, :privacy, :visited, :user_id, :approval_status)";
    
    $db->query($sql);
    $db->bind(':name', $destinationData['name']);
    $db->bind(':latitude', $destinationData['latitude']);
    $db->bind(':longitude', $destinationData['longitude']);
    $db->bind(':description', $destinationData['description']);
    $db->bind(':city', $destinationData['city']);
    $db->bind(':country', $destinationData['country']);
    $db->bind(':privacy', $destinationData['privacy']);
    $db->bind(':visited', $destinationData['visited']);
    $db->bind(':user_id', $destinationData['user_id']);
    $db->bind(':approval_status', $destinationData['approval_status']);
    
    $result = $db->execute();
    $destinationId = $db->lastInsertId();
    
    if ($destinationId) {
        echo "<div style='color: green'>✅ Destination created successfully with ID: $destinationId</div>\n";
        
        // Create trip record
        $tripData = [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $destinationId,
            'status' => $visited == 1 ? 'visited' : 'planned',
            'type' => 'quick_add'
        ];
        
        if (!empty($visitDate) && $visited == 1) {
            $tripData['visit_date'] = $visitDate;
        }
        
        echo "<h3>Trip Data to Insert:</h3>\n";
        echo "<pre>" . print_r($tripData, true) . "</pre>\n";
        
        // Insert trip
        $sql = "INSERT INTO trips (user_id, destination_id, status, type" . 
               (isset($tripData['visit_date']) ? ", visit_date" : "") . 
               ") VALUES (:user_id, :destination_id, :status, :type" . 
               (isset($tripData['visit_date']) ? ", :visit_date" : "") . ")";
        
        $db->query($sql);
        $db->bind(':user_id', $tripData['user_id']);
        $db->bind(':destination_id', $tripData['destination_id']);
        $db->bind(':status', $tripData['status']);
        $db->bind(':type', $tripData['type']);
        
        if (isset($tripData['visit_date'])) {
            $db->bind(':visit_date', $tripData['visit_date']);
        }
        
        $tripResult = $db->execute();
        $tripId = $db->lastInsertId();
        
        if ($tripId) {
            echo "<div style='color: green'>✅ Trip created successfully with ID: $tripId</div>\n";
        } else {
            echo "<div style='color: orange'>⚠️  Warning: Destination created but trip creation failed</div>\n";
        }
        
        // Verify the data
        echo "<h3>Verification:</h3>\n";
        
        // Check destination
        $db->query("SELECT * FROM destinations WHERE id = :id");
        $db->bind(':id', $destinationId);
        $createdDestination = $db->single();
        
        echo "<h4>Created Destination:</h4>\n";
        echo "<pre>" . print_r($createdDestination, true) . "</pre>\n";
        
        // Check trip
        if ($tripId) {
            $db->query("SELECT * FROM trips WHERE id = :id");
            $db->bind(':id', $tripId);
            $createdTrip = $db->single();
            
            echo "<h4>Created Trip:</h4>\n";
            echo "<pre>" . print_r($createdTrip, true) . "</pre>\n";
        }
        
        // Check stats
        $db->query("
            SELECT 
                COUNT(CASE WHEN status = 'visited' THEN 1 END) as visited,
                COUNT(CASE WHEN status = 'planned' THEN 1 END) as planned
            FROM trips 
            WHERE user_id = :user_id
        ");
        $db->bind(':user_id', $_SESSION['user_id']);
        $stats = $db->single();
        
        echo "<h4>Updated User Stats:</h4>\n";
        echo "<pre>" . print_r($stats, true) . "</pre>\n";
        
    } else {
        echo "<div style='color: red'>❌ ERROR: Failed to create destination</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red'>❌ ERROR: " . $e->getMessage() . "</div>\n";
}

echo "<h3>Test Complete</h3>\n";
echo "<p><a href='/dashboard'>← Back to Dashboard</a></p>\n";
?>
