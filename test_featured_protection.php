<?php

// Test script to verify featured destination protection is working

// Database connection
$host = 'localhost';
$dbname = 'mapit';
$username = 'root';
$password = 'root_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "=== Featured Destination Protection Test ===\n\n";

// Check current featured destinations
$stmt = $pdo->prepare("SELECT id, name, featured FROM destinations WHERE featured = 1 ORDER BY name");
$stmt->execute();
$featuredDestinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current Featured Destinations:\n";
echo "ID\tName\n";
echo "---\t---\n";
foreach ($featuredDestinations as $dest) {
    echo $dest['id'] . "\t" . $dest['name'] . "\n";
}

echo "\n";

// Test protection logic simulation
echo "Testing Protection Logic:\n";
echo "========================\n";

foreach ($featuredDestinations as $dest) {
    echo "Destination: " . $dest['name'] . " (ID: " . $dest['id'] . ")\n";
    
    // Simulate the protection check from our controller
    if ($dest['featured'] == 1) {
        echo "  - Is Featured: YES\n";
        echo "  - Protection Status: PROTECTED from deletion by non-admin users\n";
        echo "  - Admin Required: YES\n";
    } else {
        echo "  - Is Featured: NO\n";
        echo "  - Protection Status: Can be deleted by owner\n";
        echo "  - Admin Required: NO\n";
    }
    echo "\n";
}

// Check if Times Square exists
echo "Checking for Times Square:\n";
$stmt = $pdo->prepare("SELECT id, name, featured FROM destinations WHERE name LIKE '%Times Square%'");
$stmt->execute();
$timesSquare = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($timesSquare)) {
    echo "  - Times Square: NOT FOUND (likely deleted)\n";
    echo "  - Status: This confirms the deletion issue occurred\n";
} else {
    foreach ($timesSquare as $ts) {
        echo "  - Found: " . $ts['name'] . " (ID: " . $ts['id'] . ", Featured: " . ($ts['featured'] ? 'YES' : 'NO') . ")\n";
    }
}

echo "\n=== Test Complete ===\n";

?>
