<?php
// Setup script to create test data for trip status badges

require_once 'vendor/autoload.php';

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
    
    echo "<h1>Setting up test data for Trip Status Badges</h1>";
    
    // Check if test user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'testuser'");
    $stmt->execute();
    $testUser = $stmt->fetch();
    
    if (!$testUser) {
        // Create test user
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, country, created_at) 
            VALUES ('testuser', 'test@example.com', ?, 'US', NOW())
        ");
        $stmt->execute([$hashedPassword]);
        $userId = $pdo->lastInsertId();
        echo "<p>✅ Created test user (ID: {$userId})</p>";
    } else {
        $userId = $testUser['id'];
        echo "<p>✅ Test user already exists (ID: {$userId})</p>";
    }
    
    // Create sample destinations
    $destinations = [
        ['name' => 'Paris, France', 'lat' => 48.8566, 'lng' => 2.3522, 'country' => 'FR'],
        ['name' => 'Tokyo, Japan', 'lat' => 35.6762, 'lng' => 139.6503, 'country' => 'JP'],
        ['name' => 'New York, USA', 'lat' => 40.7128, 'lng' => -74.0060, 'country' => 'US'],
        ['name' => 'Sydney, Australia', 'lat' => -33.8688, 'lng' => 151.2093, 'country' => 'AU']
    ];
    
    foreach ($destinations as $dest) {
        // Check if destination exists
        $stmt = $pdo->prepare("SELECT id FROM destinations WHERE name = ? AND user_id = ?");
        $stmt->execute([$dest['name'], $userId]);
        $existingDest = $stmt->fetch();
        
        if (!$existingDest) {
            // Create destination
            $stmt = $pdo->prepare("
                INSERT INTO destinations (name, description, latitude, longitude, country, user_id, privacy, approval_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'private', 'approved', NOW())
            ");
            $stmt->execute([
                $dest['name'],
                "A beautiful destination in " . $dest['name'],
                $dest['lat'],
                $dest['lng'],
                $dest['country'],
                $userId
            ]);
            $destId = $pdo->lastInsertId();
            echo "<p>✅ Created destination: {$dest['name']} (ID: {$destId})</p>";
        } else {
            $destId = $existingDest['id'];
            echo "<p>✅ Destination already exists: {$dest['name']} (ID: {$destId})</p>";
        }
    }
    
    // Create trips with different statuses
    $tripStatuses = [
        ['name' => 'Paris, France', 'status' => 'visited'],
        ['name' => 'Tokyo, Japan', 'status' => 'in_progress'],
        ['name' => 'New York, USA', 'status' => 'planned'],
        ['name' => 'Sydney, Australia', 'status' => 'planned']
    ];
    
    foreach ($tripStatuses as $trip) {
        // Get destination ID
        $stmt = $pdo->prepare("SELECT id FROM destinations WHERE name = ? AND user_id = ?");
        $stmt->execute([$trip['name'], $userId]);
        $dest = $stmt->fetch();
        
        if ($dest) {
            // Check if trip exists
            $stmt = $pdo->prepare("SELECT id FROM trips WHERE destination_id = ? AND user_id = ?");
            $stmt->execute([$dest['id'], $userId]);
            $existingTrip = $stmt->fetch();
            
            if (!$existingTrip) {
                // Create trip
                $stmt = $pdo->prepare("
                    INSERT INTO trips (destination_id, user_id, status, type, created_at) 
                    VALUES (?, ?, ?, 'vacation', NOW())
                ");
                $stmt->execute([$dest['id'], $userId, $trip['status']]);
                echo "<p>✅ Created trip to {$trip['name']} with status: {$trip['status']}</p>";
            } else {
                // Update existing trip status
                $stmt = $pdo->prepare("UPDATE trips SET status = ? WHERE id = ?");
                $stmt->execute([$trip['status'], $existingTrip['id']]);
                echo "<p>✅ Updated trip to {$trip['name']} with status: {$trip['status']}</p>";
            }
        }
    }
    
    echo "<h3>Test Data Setup Complete!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='/auth/login'>Login</a> with username: <strong>testuser</strong> and password: <strong>password123</strong></li>";
    echo "<li>Visit the <a href='/'>Dashboard</a> to see trip status badges on the map</li>";
    echo "<li>Check <a href='/destinations'>Your Destinations</a> to see the destinations list</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
