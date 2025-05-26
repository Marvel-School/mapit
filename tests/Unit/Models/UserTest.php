<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Core\Database;

class UserTest extends TestCase
{
    private $user;
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->user = new User();
        
        // Clean up users table before each test
        $this->db->query("DELETE FROM users");
        $this->db->execute();
    }

    public function testCreateUser()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];

        $userId = $this->user->create($userData);
        
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
        
        // Verify user was created
        $createdUser = $this->user->findById($userId);
        $this->assertEquals('testuser', $createdUser['username']);
        $this->assertEquals('test@example.com', $createdUser['email']);
        $this->assertEquals('user', $createdUser['role']);
    }

    public function testFindByEmail()
    {
        // Create a test user first
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        $this->user->create($userData);

        $foundUser = $this->user->findByEmail('test@example.com');
        
        $this->assertNotNull($foundUser);
        $this->assertEquals('testuser', $foundUser['username']);
        $this->assertEquals('test@example.com', $foundUser['email']);
    }

    public function testFindByEmailNotFound()
    {
        $foundUser = $this->user->findByEmail('nonexistent@example.com');
        $this->assertNull($foundUser);
    }

    public function testValidatePassword()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        $userId = $this->user->create($userData);
        $user = $this->user->findById($userId);

        $this->assertTrue($this->user->validatePassword('password123', $user['password_hash']));
        $this->assertFalse($this->user->validatePassword('wrongpassword', $user['password_hash']));
    }

    public function testGetAllUsers()
    {
        // Create multiple users
        $this->user->create([
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => 'password123'
        ]);
        
        $this->user->create([
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => 'password123'
        ]);

        $users = $this->user->getAll();
        
        $this->assertCount(2, $users);
        $this->assertEquals('user1', $users[0]['username']);
        $this->assertEquals('user2', $users[1]['username']);
    }

    public function testUpdateUser()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        $userId = $this->user->create($userData);

        $updateData = [
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ];
        
        $result = $this->user->update($userId, $updateData);
        $this->assertTrue($result);

        $updatedUser = $this->user->findById($userId);
        $this->assertEquals('updateduser', $updatedUser['username']);
        $this->assertEquals('updated@example.com', $updatedUser['email']);
    }

    public function testDeleteUser()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        $userId = $this->user->create($userData);

        $result = $this->user->delete($userId);
        $this->assertTrue($result);

        $deletedUser = $this->user->findById($userId);
        $this->assertNull($deletedUser);
    }

    public function testUserStats()
    {
        // Create users with different roles
        $this->user->create([
            'username' => 'admin1',
            'email' => 'admin1@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ]);
        
        $this->user->create([
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $stats = $this->user->getStats();
        
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('admins', $stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['admins']);
        $this->assertEquals(1, $stats['users']);
    }
}
