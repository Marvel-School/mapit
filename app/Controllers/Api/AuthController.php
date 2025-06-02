<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Validator;
use App\Core\Database;

class AuthController extends Controller
{
    /**
     * Constructor - Set JSON headers for API responses
     */
    public function __construct()
    {
        // Start output buffering to capture any unwanted output
        if (!ob_get_level()) {
            ob_start();
        }
        
        // Set secure JSON response headers
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * Handle user login via API
     * 
     * @return void
     */
    public function login()
    {
        try {
            // Check rate limiting for login attempts
            if (!$this->checkRateLimit('api_login', 5, 300)) { // 5 attempts per 5 minutes
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }

            // Get JSON input
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'No input data provided',
                    'error_code' => 'NO_INPUT'
                ], 400);
                return;
            }

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg(),
                    'error_code' => 'INVALID_JSON'
                ], 400);
                return;
            }            // Validate required fields - accept either username or email
            $username = $this->sanitizeInput($input['username'] ?? $input['email'] ?? '');
            $password = $input['password'] ?? '';

            $errors = [];
            if (empty($username)) {
                $errors['login'] = 'Username or email is required';
            }
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }

            if (!empty($errors)) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
                return;
            }

            // Authenticate user
            $userModel = $this->model('User');
            $user = $userModel->authenticate($username, $password);

            if (!$user) {
                // Log failed login attempt
                $logModel = $this->model('Log');
                $logModel::write('WARNING', "Failed API login attempt", [
                    'username' => $username,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ], 'Authentication');

                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Invalid username or password',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
                return;
            }

            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login timestamp
            $userModel->updateLastLogin($user['id']);

            // Log successful login
            $logModel = $this->model('Log');
            $logModel::write('INFO', "API login successful: {$user['username']}", [
                'user_id' => $user['id']
            ], 'Authentication');

            // Return user data (excluding sensitive information)
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'name' => $user['name'] ?? ''
                ]
            ]);        } catch (\Exception $e) {
            error_log("API login error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine() . " | Trace: " . $e->getTraceAsString());
            
            // In development, show the actual error
            $debugMessage = 'Internal server error';
            if (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'development') {
                $debugMessage = $e->getMessage();
            }
            
            $this->cleanJsonResponse([
                'success' => false,
                'message' => $debugMessage,
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Handle user registration via API
     * 
     * @return void
     */
    public function register()
    {
        try {
            // Check rate limiting for registration attempts
            if (!$this->checkRateLimit('api_register', 3, 900)) { // 3 attempts per 15 minutes
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }

            // Get JSON input
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'No input data provided',
                    'error_code' => 'NO_INPUT'
                ], 400);
                return;
            }

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg(),
                    'error_code' => 'INVALID_JSON'
                ], 400);
                return;
            }

            // Sanitize and validate input data
            $username = $this->sanitizeInput($input['username'] ?? '');
            $email = $this->sanitizeInput($input['email'] ?? '');
            $password = $input['password'] ?? '';

            // Validate form data using the Validator class
            $validator = new Validator($input);
            $validator->validate([
                'username' => 'required|min:3|max:50|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ]);

            $errors = $validator->errors();

            // Additional password strength validation
            $passwordErrors = $this->validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                $errors['password'] = implode(', ', $passwordErrors);
            }

            if (!empty($errors)) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
                return;
            }

            // Register user
            $userModel = $this->model('User');
            $userId = $userModel->register([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => 'user'
            ]);

            if (!$userId) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Failed to register user',
                    'error_code' => 'REGISTRATION_FAILED'
                ], 500);
                return;
            }

            // Log the registration
            $logModel = $this->model('Log');
            $logModel::write('INFO', "API user registered: {$username}", [
                'user_id' => $userId
            ], 'Authentication');

            // Return success response
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email
                ]
            ], 201);        } catch (\Exception $e) {
            error_log("API registration error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine() . " | Trace: " . $e->getTraceAsString());
            
            // In development, show the actual error
            $debugMessage = 'Internal server error';
            if (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'development') {
                $debugMessage = $e->getMessage();
            }
            
            $this->cleanJsonResponse([
                'success' => false,
                'message' => $debugMessage,
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Simple health check endpoint to test database connectivity
     * 
     * @return void
     */
    public function healthCheck()
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1 as test");
            $result = $db->single();
            
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Database connection successful',
                'data' => [
                    'database' => $result ? 'connected' : 'failed',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'environment' => getenv('APP_ENV') ?: 'unknown'
                ]
            ], 200);
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'error_code' => 'DATABASE_ERROR'
            ], 500);
        }
    }

    /**
     * Send clean JSON response by discarding any previous output
     * 
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    private function cleanJsonResponse($data, $statusCode = 200)
    {
        // Clean any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set status code
        http_response_code($statusCode);
        
        // Send JSON response
        echo json_encode($data);
        exit();
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @return array
     */
    private function validatePasswordStrength($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        return $errors;
    }
}
