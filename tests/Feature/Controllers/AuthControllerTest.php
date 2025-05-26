<?php

namespace Tests\Feature\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\User;
use App\Core\Database;

class AuthControllerTest extends TestCase
{
    private $authController;
    private $user;
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->authController = new AuthController();
        $this->user = new User();
        
        // Clean up users table before each test
        $this->db->query("DELETE FROM users");
        $this->db->execute();
        
        // Start a new session for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_start();
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testLoginPageDisplay()
    {
        // Capture output
        ob_start();
        $this->authController->login();
        $output = ob_get_clean();
        
        // Basic check that login view is rendered
        $this->assertNotEmpty($output);
    }

    public function testRegisterPageDisplay()
    {
        // Capture output
        ob_start();
        $this->authController->register();
        $output = ob_get_clean();
        
        // Basic check that register view is rendered
        $this->assertNotEmpty($output);
    }

    public function testSuccessfulRegistration()
    {
        // Simulate POST data
        $_POST = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Capture output to prevent headers from being sent
        ob_start();
        $this->authController->processRegister();
        ob_end_clean();

        // Check if user was created
        $createdUser = $this->user->findByEmail('newuser@example.com');
        $this->assertNotNull($createdUser);
        $this->assertEquals('newuser', $createdUser['username']);
        $this->assertEquals('newuser@example.com', $createdUser['email']);
    }

    public function testRegistrationWithMismatchedPasswords()
    {
        $_POST = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'confirm_password' => 'different_password'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->authController->processRegister();
        ob_end_clean();

        // Check that user was not created
        $createdUser = $this->user->findByEmail('newuser@example.com');
        $this->assertNull($createdUser);
        
        // Check for error message in session
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testRegistrationWithDuplicateEmail()
    {
        // Create a user first
        $this->user->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ]);

        $_POST = [
            'username' => 'newuser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->authController->processRegister();
        ob_end_clean();

        // Check for error message
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testSuccessfulLogin()
    {
        // Create a test user
        $this->user->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->authController->processLogin();
        ob_end_clean();

        // Check if user is logged in
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertArrayHasKey('username', $_SESSION);
        $this->assertEquals('testuser', $_SESSION['username']);
    }

    public function testLoginWithInvalidCredentials()
    {
        // Create a test user
        $this->user->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $_POST = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->authController->processLogin();
        ob_end_clean();

        // Check that user is not logged in
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testLoginWithNonexistentUser()
    {
        $_POST = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->authController->processLogin();
        ob_end_clean();

        // Check that user is not logged in
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayHasKey('error', $_SESSION);
    }

    public function testLogout()
    {
        // First, simulate a logged-in user
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'user';

        ob_start();
        $this->authController->logout();
        ob_end_clean();

        // Check that session variables are cleared
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }

    public function testIsLoggedInMethod()
    {
        // Test when not logged in
        $this->assertFalse($this->authController->isLoggedIn());

        // Test when logged in
        $_SESSION['user_id'] = 1;
        $this->assertTrue($this->authController->isLoggedIn());
    }

    public function testRequireLoginRedirect()
    {
        // This would require mocking the redirect functionality
        // For now, we'll just test the logic
        $this->assertFalse($this->authController->isLoggedIn());
        
        // When user is logged in
        $_SESSION['user_id'] = 1;
        $this->assertTrue($this->authController->isLoggedIn());
    }

    protected function simulatePost($data)
    {
        $_POST = $data;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    protected function clearPost()
    {
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD']);
    }
}
