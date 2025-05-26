<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Destination;
use App\Models\User;
use App\Core\Database;

class DestinationTest extends TestCase
{
    private $destination;
    private $user;
    private $db;
    private $testUserId;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->destination = new Destination();
        $this->user = new User();
        
        // Clean up tables before each test
        $this->db->query("DELETE FROM destinations");
        $this->db->execute();
        $this->db->query("DELETE FROM users");
        $this->db->execute();
        
        // Create a test user for foreign key relationships
        $this->testUserId = $this->user->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
    }

    public function testCreateDestination()
    {
        $destinationData = [
            'name' => 'Paris',
            'description' => 'City of Light',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'user_id' => $this->testUserId,
            'privacy' => 'public'
        ];

        $destinationId = $this->destination->create($destinationData);
        
        $this->assertIsInt($destinationId);
        $this->assertGreaterThan(0, $destinationId);
        
        // Verify destination was created
        $createdDestination = $this->destination->findById($destinationId);
        $this->assertEquals('Paris', $createdDestination['name']);
        $this->assertEquals('France', $createdDestination['country']);
        $this->assertEquals('public', $createdDestination['privacy']);
        $this->assertEquals('pending', $createdDestination['approval_status']);
    }

    public function testGetPublicDestinations()
    {
        // Create public and private destinations
        $this->destination->create([
            'name' => 'Public Destination',
            'country' => 'France',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Private Destination',
            'country' => 'Germany',
            'user_id' => $this->testUserId,
            'privacy' => 'private',
            'approval_status' => 'approved'
        ]);

        $publicDestinations = $this->destination->getPublic();
        
        $this->assertCount(1, $publicDestinations);
        $this->assertEquals('Public Destination', $publicDestinations[0]['name']);
        $this->assertEquals('public', $publicDestinations[0]['privacy']);
    }

    public function testGetFeaturedDestinations()
    {
        // Create destinations with different approval statuses
        $this->destination->create([
            'name' => 'Featured Destination 1',
            'country' => 'France',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Featured Destination 2',
            'country' => 'Italy',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Pending Destination',
            'country' => 'Spain',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'pending'
        ]);

        $featured = $this->destination->getFeatured(5);
        
        $this->assertCount(2, $featured);
        foreach ($featured as $dest) {
            $this->assertEquals('approved', $dest['approval_status']);
            $this->assertEquals('public', $dest['privacy']);
        }
    }

    public function testSearchDestinations()
    {
        $this->destination->create([
            'name' => 'Eiffel Tower',
            'description' => 'Famous tower in Paris',
            'country' => 'France',
            'city' => 'Paris',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Louvre Museum',
            'description' => 'Art museum in Paris',
            'country' => 'France',
            'city' => 'Paris',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);

        // Search by city
        $results = $this->destination->search(['city' => 'Paris']);
        $this->assertCount(2, $results);

        // Search by country
        $results = $this->destination->search(['country' => 'France']);
        $this->assertCount(2, $results);

        // Search by keyword
        $results = $this->destination->search(['keyword' => 'tower']);
        $this->assertCount(1, $results);
        $this->assertEquals('Eiffel Tower', $results[0]['name']);
    }

    public function testUpdateDestination()
    {
        $destinationData = [
            'name' => 'Original Name',
            'country' => 'France',
            'user_id' => $this->testUserId
        ];
        $destinationId = $this->destination->create($destinationData);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'approval_status' => 'approved'
        ];
        
        $result = $this->destination->update($destinationId, $updateData);
        $this->assertTrue($result);

        $updatedDestination = $this->destination->findById($destinationId);
        $this->assertEquals('Updated Name', $updatedDestination['name']);
        $this->assertEquals('Updated description', $updatedDestination['description']);
        $this->assertEquals('approved', $updatedDestination['approval_status']);
    }

    public function testDeleteDestination()
    {
        $destinationData = [
            'name' => 'Test Destination',
            'country' => 'France',
            'user_id' => $this->testUserId
        ];
        $destinationId = $this->destination->create($destinationData);

        $result = $this->destination->delete($destinationId);
        $this->assertTrue($result);

        $deletedDestination = $this->destination->findById($destinationId);
        $this->assertNull($deletedDestination);
    }

    public function testGetDestinationStats()
    {
        // Create destinations with different statuses
        $this->destination->create([
            'name' => 'Approved Dest 1',
            'country' => 'France',
            'user_id' => $this->testUserId,
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Pending Dest 1',
            'country' => 'Germany',
            'user_id' => $this->testUserId,
            'approval_status' => 'pending'
        ]);
        
        $this->destination->create([
            'name' => 'Rejected Dest 1',
            'country' => 'Spain',
            'user_id' => $this->testUserId,
            'approval_status' => 'rejected'
        ]);

        $stats = $this->destination->getStats();
        
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('approved', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('rejected', $stats);
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['approved']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['rejected']);
    }

    public function testGetUserDestinations()
    {
        // Create another user
        $user2Id = $this->user->create([
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => 'password123'
        ]);

        // Create destinations for both users
        $this->destination->create([
            'name' => 'User 1 Destination',
            'country' => 'France',
            'user_id' => $this->testUserId
        ]);
        
        $this->destination->create([
            'name' => 'User 2 Destination',
            'country' => 'Germany',
            'user_id' => $user2Id
        ]);

        $user1Destinations = $this->destination->getByUserId($this->testUserId);
        $user2Destinations = $this->destination->getByUserId($user2Id);

        $this->assertCount(1, $user1Destinations);
        $this->assertCount(1, $user2Destinations);
        $this->assertEquals('User 1 Destination', $user1Destinations[0]['name']);
        $this->assertEquals('User 2 Destination', $user2Destinations[0]['name']);
    }
}
