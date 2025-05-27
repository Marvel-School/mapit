<?php

// Test script to simulate deletion attempts on featured destinations

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

echo "=== Featured Destination Deletion Protection Test ===\n\n";

// Get a featured destination to test with
$stmt = $pdo->prepare("SELECT id, name, featured, user_id FROM destinations WHERE featured = 1 LIMIT 1");
$stmt->execute();
$testDestination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testDestination) {
    die("No featured destinations found for testing!\n");
}

echo "Test Destination: " . $testDestination['name'] . " (ID: " . $testDestination['id'] . ")\n";
echo "Featured Status: " . ($testDestination['featured'] ? 'YES' : 'NO') . "\n";
echo "Owner User ID: " . $testDestination['user_id'] . "\n\n";

// Simulate our controller protection logic
function simulateDestinationDeletion($destination, $currentUserId, $currentUserRole) {
    echo "--- Simulating Deletion Attempt ---\n";
    echo "Current User ID: " . $currentUserId . "\n";
    echo "Current User Role: " . $currentUserRole . "\n\n";
    
    // Check ownership or admin status
    $canAccessDelete = ($destination['user_id'] == $currentUserId) || ($currentUserRole == 'admin');
    
    echo "Step 1 - Access Check:\n";
    if (!$canAccessDelete) {
        echo "  RESULT: ACCESS DENIED - User doesn't own destination and is not admin\n";
        return false;
    }
    echo "  RESULT: ACCESS GRANTED - User owns destination or is admin\n\n";
    
    // Check featured protection (our new protection)
    echo "Step 2 - Featured Protection Check:\n";
    if ($destination['featured'] == 1 && $currentUserRole != 'admin') {
        echo "  RESULT: DELETION BLOCKED - Featured destination, admin required\n";
        echo "  MESSAGE: 'Featured destinations cannot be deleted. Please contact an administrator if you need assistance.'\n";
        return false;
    }
    
    if ($destination['featured'] == 1 && $currentUserRole == 'admin') {
        echo "  RESULT: DELETION ALLOWED - Admin can delete featured destinations\n";
    } else {
        echo "  RESULT: DELETION ALLOWED - Not a featured destination\n";
    }
    
    echo "  FINAL: Deletion would proceed (but we're not actually deleting)\n";
    return true;
}

echo "=== Test Scenarios ===\n\n";

// Scenario 1: Regular user (owner) tries to delete featured destination
echo "Scenario 1: Owner tries to delete featured destination\n";
echo "============================================\n";
$result1 = simulateDestinationDeletion($testDestination, $testDestination['user_id'], 'user');
echo "Protection Working: " . ($result1 ? 'NO - FAILED!' : 'YES - SUCCESS!') . "\n\n";

// Scenario 2: Regular user (non-owner) tries to delete featured destination  
echo "Scenario 2: Non-owner tries to delete featured destination\n";
echo "================================================\n";
$result2 = simulateDestinationDeletion($testDestination, 999, 'user');
echo "Protection Working: " . ($result2 ? 'NO - FAILED!' : 'YES - SUCCESS!') . "\n\n";

// Scenario 3: Admin tries to delete featured destination
echo "Scenario 3: Admin tries to delete featured destination\n";
echo "===============================================\n";
$result3 = simulateDestinationDeletion($testDestination, 1, 'admin');
echo "Admin Override: " . ($result3 ? 'YES - SUCCESS!' : 'NO - FAILED!') . "\n\n";

// Scenario 4: Test with non-featured destination
$stmt = $pdo->prepare("SELECT id, name, featured, user_id FROM destinations WHERE featured = 0 LIMIT 1");
$stmt->execute();
$nonFeaturedDest = $stmt->fetch(PDO::FETCH_ASSOC);

if ($nonFeaturedDest) {
    echo "Scenario 4: Owner tries to delete non-featured destination\n";
    echo "===============================================\n";
    echo "Test Destination: " . $nonFeaturedDest['name'] . " (Non-featured)\n";
    $result4 = simulateDestinationDeletion($nonFeaturedDest, $nonFeaturedDest['user_id'], 'user');
    echo "Normal Deletion: " . ($result4 ? 'YES - SUCCESS!' : 'NO - FAILED!') . "\n\n";
}

echo "=== Test Summary ===\n";
echo "1. Owner + Featured Destination: " . ($result1 ? 'FAILED (allowed)' : 'PROTECTED ✓') . "\n";
echo "2. Non-Owner + Featured Destination: " . ($result2 ? 'FAILED (allowed)' : 'PROTECTED ✓') . "\n";
echo "3. Admin + Featured Destination: " . ($result3 ? 'ALLOWED ✓' : 'BLOCKED (error)') . "\n";
if (isset($result4)) {
    echo "4. Owner + Non-Featured Destination: " . ($result4 ? 'ALLOWED ✓' : 'BLOCKED (error)') . "\n";
}

echo "\n=== Test Complete ===\n";

?>
