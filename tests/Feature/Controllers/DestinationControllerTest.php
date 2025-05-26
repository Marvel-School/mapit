<?php

namespace Tests\Feature\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\DestinationController;
use App\Models\Destination;
use App\Models\User;
use App\Core\Database;

class DestinationControllerTest extends TestCase
{
    private $destinationController;
    private $destination;
    private $user;
    private $db;
    private $testUserId;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->destinationController = new DestinationController();
        $this->destination = new Destination();
        $this->user = new User();
        
        // Clean up tables before each test
        $this->db->query("DELETE FROM destinations");
        $this->db->execute();
        $this->db->query("DELETE FROM users");
        $this->db->execute();
        
        // Create a test user
        $this->testUserId = $this->user->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Simulate logged-in user
        $_SESSION['user_id'] = $this->testUserId;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'user';
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testIndexPageDisplay()
    {
        // Create some test destinations
        $this->destination->create([
            'name' => 'Paris',
            'country' => 'France',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);

        ob_start();
        $this->destinationController->index();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testShowDestination()
    {
        $destinationId = $this->destination->create([
            'name' => 'Paris',
            'description' => 'City of Light',
            'country' => 'France',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);

        ob_start();
        $this->destinationController->show($destinationId);
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testCreateDestinationForm()
    {
        ob_start();
        $this->destinationController->create();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testStoreDestination()
    {
        $_POST = [
            'name' => 'New Destination',
            'description' => 'A wonderful place',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => '48.8566',
            'longitude' => '2.3522',
            'privacy' => 'public'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->destinationController->store();
        ob_end_clean();

        // Check if destination was created
        $destinations = $this->destination->getByUserId($this->testUserId);
        $this->assertCount(1, $destinations);
        $this->assertEquals('New Destination', $destinations[0]['name']);
        $this->assertEquals('France', $destinations[0]['country']);
    }

    public function testStoreDestinationWithInvalidData()
    {
        $_POST = [
            'name' => '', // Empty name should cause validation error
            'country' => 'France'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->destinationController->store();
        ob_end_clean();

        // Check that no destination was created
        $destinations = $this->destination->getByUserId($this->testUserId);
        $this->assertCount(0, $destinations);
        
        // Check for error message in session
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testEditDestination()
    {
        $destinationId = $this->destination->create([
            'name' => 'Test Destination',
            'country' => 'France',
            'user_id' => $this->testUserId
        ]);

        ob_start();
        $this->destinationController->edit($destinationId);
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testUpdateDestination()
    {
        $destinationId = $this->destination->create([
            'name' => 'Original Name',
            'country' => 'France',
            'user_id' => $this->testUserId
        ]);

        $_POST = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'country' => 'France',
            'city' => 'Paris',
            'privacy' => 'public'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->destinationController->update($destinationId);
        ob_end_clean();

        // Check if destination was updated
        $updatedDestination = $this->destination->findById($destinationId);
        $this->assertEquals('Updated Name', $updatedDestination['name']);
        $this->assertEquals('Updated description', $updatedDestination['description']);
    }

    public function testDeleteDestination()
    {
        $destinationId = $this->destination->create([
            'name' => 'Test Destination',
            'country' => 'France',
            'user_id' => $this->testUserId
        ]);

        ob_start();
        $this->destinationController->destroy($destinationId);
        ob_end_clean();

        // Check if destination was deleted
        $deletedDestination = $this->destination->findById($destinationId);
        $this->assertNull($deletedDestination);
    }

    public function testSearchDestinations()
    {
        // Create test destinations
        $this->destination->create([
            'name' => 'Eiffel Tower',
            'country' => 'France',
            'city' => 'Paris',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);
        
        $this->destination->create([
            'name' => 'Big Ben',
            'country' => 'United Kingdom',
            'city' => 'London',
            'user_id' => $this->testUserId,
            'privacy' => 'public',
            'approval_status' => 'approved'
        ]);

        $_GET = ['search' => 'Paris'];

        ob_start();
        $this->destinationController->search();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testUnauthorizedAccess()
    {
        // Create destination owned by another user
        $anotherUserId = $this->user->create([
            'username' => 'anotheruser',
            'email' => 'another@example.com',
            'password' => 'password123'
        ]);
        
        $destinationId = $this->destination->create([
            'name' => 'Private Destination',
            'country' => 'France',
            'user_id' => $anotherUserId,
            'privacy' => 'private'
        ]);

        // Try to edit destination owned by another user
        ob_start();
        $this->destinationController->edit($destinationId);
        ob_end_clean();

        // Should redirect or show error
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testMyDestinations()
    {
        // Create destinations for current user
        $this->destination->create([
            'name' => 'My Destination 1',
            'country' => 'France',
            'user_id' => $this->testUserId
        ]);
        
        $this->destination->create([
            'name' => 'My Destination 2',
            'country' => 'Germany',
            'user_id' => $this->testUserId
        ]);

        ob_start();
        $this->destinationController->myDestinations();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
    }

    public function testDestinationValidation()
    {
        // Test various validation scenarios
        $testCases = [
            [
                'data' => ['name' => '', 'country' => 'France'],
                'should_fail' => true,
                'reason' => 'Empty name'
            ],
            [
                'data' => ['name' => 'Valid Name', 'country' => ''],
                'should_fail' => true,
                'reason' => 'Empty country'
            ],
            [
                'data' => ['name' => 'Valid Name', 'country' => 'France', 'latitude' => 'invalid'],
                'should_fail' => true,
                'reason' => 'Invalid latitude'
            ],
            [
                'data' => ['name' => 'Valid Name', 'country' => 'France'],
                'should_fail' => false,
                'reason' => 'Valid data'
            ]
        ];

        foreach ($testCases as $testCase) {
            // Clean up destinations
            $this->db->query("DELETE FROM destinations");
            $this->db->execute();
            
            $_POST = $testCase['data'];
            $_SERVER['REQUEST_METHOD'] = 'POST';

            ob_start();
            $this->destinationController->store();
            ob_end_clean();

            $destinations = $this->destination->getByUserId($this->testUserId);
            
            if ($testCase['should_fail']) {
                $this->assertCount(0, $destinations, "Should fail: " . $testCase['reason']);
            } else {
                $this->assertCount(1, $destinations, "Should pass: " . $testCase['reason']);
            }
        }
    }
}
