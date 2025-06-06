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
     * Debug endpoint to check database schema
     * 
     * @return void
     */
    public function debugSchema()
    {
        try {
            $db = Database::getInstance();
            
            // Check if users table exists and its structure
            $db->query("SHOW TABLES LIKE 'users'");
            $usersTableExists = $db->single();
            
            if ($usersTableExists) {
                $db->query("DESCRIBE users");
                $columns = $db->resultSet();
            }
            
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Schema check completed',
                'data' => [
                    'users_table_exists' => (bool)$usersTableExists,
                    'users_columns' => $columns ?? null,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Schema check failed: ' . $e->getMessage(),
                'error_code' => 'SCHEMA_ERROR'
            ], 500);
        }
    }

    /**
     * Migration endpoint to update production database schema
     * WARNING: This should only be used once and then removed
     * 
     * @return void
     */
    public function migrateProdSchema()
    {
        try {
            $db = Database::getInstance();
            
            // Execute migration queries one by one
            $migrations = [
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash varchar(255) DEFAULT NULL AFTER email",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS name varchar(100) DEFAULT NULL AFTER password_hash",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio text DEFAULT NULL AFTER name",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS country varchar(2) DEFAULT NULL AFTER bio",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS website varchar(255) DEFAULT NULL AFTER country",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar varchar(255) DEFAULT NULL AFTER website",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS settings json DEFAULT NULL AFTER avatar",
                "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login timestamp DEFAULT NULL AFTER settings",
                "ALTER TABLE users MODIFY COLUMN role enum('user','admin','moderator') NOT NULL DEFAULT 'user'",
                "UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password IS NOT NULL",
                "ALTER TABLE users MODIFY COLUMN password_hash varchar(255) NOT NULL",
                "ALTER TABLE users DROP COLUMN IF EXISTS password"
            ];
            
            $results = [];
            foreach ($migrations as $sql) {
                try {
                    $db->query($sql);
                    $results[] = "SUCCESS: " . $sql;
                } catch (\Exception $e) {
                    $results[] = "ERROR: " . $sql . " - " . $e->getMessage();
                }
            }
            
            // Get final schema
            $db->query("DESCRIBE users");
            $finalSchema = $db->resultSet();
            
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Migration completed',
                'data' => [
                    'migration_results' => $results,
                    'final_schema' => $finalSchema,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'error_code' => 'MIGRATION_ERROR'
            ], 500);
        }
    }

    /**
     * Fixed migration endpoint with proper MySQL syntax
     * 
     * @return void
     */
    public function migrateProdSchemaFixed()
    {
        try {
            $db = Database::getInstance();
            
            // First, check which columns exist
            $db->query("DESCRIBE users");
            $currentColumns = $db->resultSet();
            $columnNames = array_column($currentColumns, 'Field');
            
            $results = [];
            
            // Add missing columns only if they don't exist
            if (!in_array('password_hash', $columnNames)) {
                try {
                    $db->query("ALTER TABLE users ADD COLUMN password_hash varchar(255) DEFAULT NULL AFTER email");
                    $results[] = "SUCCESS: Added password_hash column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Adding password_hash - " . $e->getMessage();
                }
            } else {
                $results[] = "SKIPPED: password_hash column already exists";
            }
            
            if (!in_array('name', $columnNames)) {
                try {
                    $db->query("ALTER TABLE users ADD COLUMN name varchar(100) DEFAULT NULL AFTER password_hash");
                    $results[] = "SUCCESS: Added name column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Adding name - " . $e->getMessage();
                }
            } else {
                $results[] = "SKIPPED: name column already exists";
            }
            
            if (!in_array('country', $columnNames)) {
                try {
                    $db->query("ALTER TABLE users ADD COLUMN country varchar(2) DEFAULT NULL");
                    $results[] = "SUCCESS: Added country column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Adding country - " . $e->getMessage();
                }
            } else {
                $results[] = "SKIPPED: country column already exists";
            }
            
            if (!in_array('settings', $columnNames)) {
                try {
                    $db->query("ALTER TABLE users ADD COLUMN settings json DEFAULT NULL");
                    $results[] = "SUCCESS: Added settings column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Adding settings - " . $e->getMessage();
                }
            } else {
                $results[] = "SKIPPED: settings column already exists";
            }
            
            if (!in_array('last_login', $columnNames)) {
                try {
                    $db->query("ALTER TABLE users ADD COLUMN last_login timestamp DEFAULT NULL");
                    $results[] = "SUCCESS: Added last_login column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Adding last_login - " . $e->getMessage();
                }
            } else {
                $results[] = "SKIPPED: last_login column already exists";
            }
            
            // Update the role enum to include moderator
            try {
                $db->query("ALTER TABLE users MODIFY COLUMN role enum('user','admin','moderator') NOT NULL DEFAULT 'user'");
                $results[] = "SUCCESS: Updated role enum";
            } catch (\Exception $e) {
                $results[] = "ERROR: Updating role enum - " . $e->getMessage();
            }
            
            // Copy existing password data to password_hash column if password_hash exists and password exists
            if (in_array('password_hash', $columnNames) && in_array('password', $columnNames)) {
                try {
                    $db->query("UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password IS NOT NULL");
                    $results[] = "SUCCESS: Copied password data to password_hash";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Copying password data - " . $e->getMessage();
                }
                
                // Make password_hash NOT NULL after copying data
                try {
                    $db->query("ALTER TABLE users MODIFY COLUMN password_hash varchar(255) NOT NULL");
                    $results[] = "SUCCESS: Made password_hash NOT NULL";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Making password_hash NOT NULL - " . $e->getMessage();
                }
                
                // Drop the old password column
                try {
                    $db->query("ALTER TABLE users DROP COLUMN password");
                    $results[] = "SUCCESS: Dropped old password column";
                } catch (\Exception $e) {
                    $results[] = "ERROR: Dropping password column - " . $e->getMessage();
                }
            }
            
            // Get final schema
            $db->query("DESCRIBE users");
            $finalSchema = $db->resultSet();
            
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'Fixed migration completed',
                'data' => [
                    'migration_results' => $results,
                    'final_schema' => $finalSchema,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Fixed migration failed: ' . $e->getMessage(),
                'error_code' => 'MIGRATION_ERROR'
            ], 500);
        }
    }

    /**
     * Migrate user_badges table schema to match local development
     * 
     * @return void
     */
    public function migrateUserBadgesSchema()
    {
        try {
            $db = Database::getInstance();
            
            // Check current column name
            $db->query("SHOW COLUMNS FROM user_badges WHERE Field LIKE 'earned_%'");
            $currentColumn = $db->single();
            
            if (!$currentColumn) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'No earned column found in user_badges table',
                    'data' => []
                ]);
                return;
            }
            
            $currentColumnName = $currentColumn['Field'];
            
            if ($currentColumnName === 'earned_date') {
                $this->cleanJsonResponse([
                    'success' => true,
                    'message' => 'user_badges table already has earned_date column',
                    'data' => ['current_column' => $currentColumnName]
                ]);
                return;
            }
            
            // Rename earned_at to earned_date
            if ($currentColumnName === 'earned_at') {
                $db->query("ALTER TABLE user_badges CHANGE COLUMN earned_at earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                $db->execute();
                
                $this->cleanJsonResponse([
                    'success' => true,
                    'message' => 'Successfully renamed earned_at to earned_date',
                    'data' => [
                        'old_column' => $currentColumnName,
                        'new_column' => 'earned_date',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Unexpected column name in user_badges table',
                    'data' => ['current_column' => $currentColumnName]
                ]);
            }
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Error migrating user_badges schema',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Debug endpoint to check user_badges table schema
     * 
     * @return void
     */
    public function debugUserBadgesSchema()
    {
        try {
            $db = Database::getInstance();
            
            // Check if user_badges table exists
            $db->query("SHOW TABLES LIKE 'user_badges'");
            $tableExists = $db->single();
            
            if (!$tableExists) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'user_badges table does not exist',
                    'data' => []
                ]);
                return;
            }
            
            // Get table schema
            $db->query("SHOW COLUMNS FROM user_badges");
            $columns = $db->resultSet();
            
            $this->cleanJsonResponse([
                'success' => true,
                'message' => 'user_badges table schema retrieved',
                'data' => [
                    'table_exists' => true,
                    'columns' => $columns,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Error checking user_badges schema',
                'error' => $e->getMessage(),
                'data' => []
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
