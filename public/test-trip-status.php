<?php
// Simple verification script to test trip status badges functionality

// Include the autoloader
require_once 'vendor/autoload.php';

// Set up basic environment for testing
session_start();

// Include database connection
$config = [
    'database' => [
        'host' => 'localhost',
        'name' => 'mapit',
        'username' => 'root',
        'password' => ''
    ]
];

try {
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Trip Status Badge Verification</h1>";
    
    // Check if we have users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p><strong>Users in database:</strong> {$userCount}</p>";
    
    if ($userCount > 0) {
        // Get a sample user
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user = $stmt->fetch();
        echo "<p><strong>Sample user:</strong> {$user['username']} (ID: {$user['id']})</p>";
        
        // Check destinations for this user
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM destinations WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $destCount = $stmt->fetch()['count'];
        echo "<p><strong>Destinations for user:</strong> {$destCount}</p>";
        
        // Check trips for this user
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM trips WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $tripCount = $stmt->fetch()['count'];
        echo "<p><strong>Trips for user:</strong> {$tripCount}</p>";
        
        if ($tripCount > 0) {
            // Show sample trips with statuses
            $stmt = $pdo->prepare("
                SELECT t.*, d.name as destination_name 
                FROM trips t 
                LEFT JOIN destinations d ON t.destination_id = d.id 
                WHERE t.user_id = ? 
                LIMIT 5
            ");
            $stmt->execute([$user['id']]);
            $trips = $stmt->fetchAll();
            
            echo "<h3>Sample Trips with Status:</h3>";
            echo "<ul>";
            foreach ($trips as $trip) {
                echo "<li>{$trip['destination_name']} - Status: <strong>{$trip['status']}</strong></li>";
            }
            echo "</ul>";
        }
        
        // Test the new getUserDestinationsWithTripStatus method
        echo "<h3>Testing New Method: getUserDestinationsWithTripStatus</h3>";
        
        // Simulate the new method query
        $stmt = $pdo->prepare("
            SELECT DISTINCT d.*, 
                d.country as country_name,
                u.username as creator,
                t.status as trip_status,
                t.created_at as trip_date,
                t.id as trip_id,
                CASE 
                    WHEN t.status = 'visited' THEN 1 
                    ELSE 0 
                END as visited,
                (SELECT COUNT(*) FROM trips WHERE destination_id = d.id AND user_id = ?) as trip_count
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            LEFT JOIN trips t ON d.id = t.destination_id AND t.user_id = ?
                AND t.id = (
                    SELECT id FROM trips t2 
                    WHERE t2.destination_id = d.id AND t2.user_id = ?
                    ORDER BY 
                        CASE t2.status 
                            WHEN 'visited' THEN 1
                            WHEN 'in_progress' THEN 2
                            WHEN 'planned' THEN 3
                            ELSE 4
                        END,
                        t2.created_at DESC 
                    LIMIT 1
                )
            WHERE d.user_id = ?
            ORDER BY d.created_at DESC
        ");
        
        $stmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
        $destinations = $stmt->fetchAll();
        
        echo "<p><strong>Destinations with trip status:</strong> " . count($destinations) . "</p>";
        
        if (count($destinations) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Destination</th><th>Trip Status</th><th>Trip Count</th><th>Visited</th></tr>";
            foreach ($destinations as $dest) {
                echo "<tr>";
                echo "<td>{$dest['name']}</td>";
                echo "<td>" . ($dest['trip_status'] ?? 'No trips') . "</td>";
                echo "<td>{$dest['trip_count']}</td>";
                echo "<td>" . ($dest['visited'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p><em>No users found. You may need to register a user first.</em></p>";
    }
    
    // Test featured destinations
    echo "<h3>Featured Destinations</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM destinations WHERE privacy = 'public' AND approval_status = 'approved'");
    $featuredCount = $stmt->fetch()['count'];
    echo "<p><strong>Featured destinations:</strong> {$featuredCount}</p>";
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f5f5f5; }
</style>
