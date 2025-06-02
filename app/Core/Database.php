<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    private $statement;
    private $error;    
      private function __construct()
    {
        // Ensure environment variables are loaded before database connection
        $this->loadEnvironmentIfNeeded();
        
        // Initialize logger
        $logFile = __DIR__ . '/../../storage/logs/database.log';
        Logger::setLogFile($logFile);
        
        // Get database configuration
        $dbHost = getenv('DB_HOST') ?: 'localhost';        
        $dbName = getenv('DB_DATABASE') ?: 'mapit';
        $dbUser = getenv('DB_USERNAME') ?: 'mapit_user';
        $dbPass = getenv('DB_PASSWORD') ?: 'mapit_password';
        $dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';
        
        // DSN string
        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
        
        // Options for PDO
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset}"
        ];

        // Create PDO instance
        try {
            $this->connection = new PDO(
                $dsn, 
                $dbUser, 
                $dbPass, 
                $options
            );
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->connection = null;
            
            // Log the detailed error
            Logger::error('Database Connection Error: ' . $this->error, [
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);        
        }
    }
    
    /**
     * Load environment variables from .env file if they haven't been loaded already
     * 
     * @return void
     */
    private function loadEnvironmentIfNeeded()
    {
        // Check if environment variables are already loaded
        if (getenv('DB_HOST') === false) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) {
                        continue; // Skip comments
                    }
                    
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value);
                        
                        // Remove quotes if present
                        if (preg_match('/^["\'].*["\']$/', $value)) {
                            $value = substr($value, 1, -1);
                        }
                        
                        $_ENV[$key] = $value;
                        putenv($key . '=' . $value);
                    }
                }
            }
        }
    }

    // Singleton pattern - Get database instance
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Check if the database connection is active
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->connection !== null;
    }
    
    /**
     * Get the last database error
     * 
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Prepare and execute a database query with security validation
     * 
     * @param string $query
     * @return Database
     */
    public function query($query)
    {
        // Log all queries for security monitoring in development
        if (getenv('APP_DEBUG') === 'true') {
            error_log("SQL Query: " . $query);
        }
        
        // Validate query for dangerous patterns
        $this->validateQuerySecurity($query);
        
        if ($this->connection) {
            $this->statement = $this->connection->prepare($query);
        }
        
        return $this;
    }
    
    /**
     * Validate query for potential SQL injection patterns
     * 
     * @param string $query
     * @throws \Exception
     * @return void
     */
    protected function validateQuerySecurity($query)
    {
        // Remove comments and normalize whitespace
        $normalizedQuery = preg_replace('/\/\*.*?\*\//', '', $query);
        $normalizedQuery = preg_replace('/--.*$/', '', $normalizedQuery);
        $normalizedQuery = preg_replace('/\s+/', ' ', $normalizedQuery);
        $normalizedQuery = strtolower(trim($normalizedQuery));
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/union\s+select/i',
            '/;\s*(drop|delete|truncate|alter|create)\s+/i',
            '/\'\s*;\s*(drop|delete|truncate|alter|create)\s+/i',
            '/\/\*.*?\*\//i',
            '/--\s*.*/i',
            '/xp_cmdshell/i',
            '/sp_executesql/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $normalizedQuery)) {
                // Log security violation
                Logger::error("SECURITY ALERT: Potentially dangerous SQL pattern detected", [
                    'query' => $query,
                    'pattern' => $pattern,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                throw new \Exception("Query contains potentially dangerous patterns");
            }
        }
    }
    
    /**
     * Bind parameters with enhanced validation
     * 
     * @param string $param
     * @param mixed $value
     * @param int|null $type
     * @return void
     */
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        // Additional validation for string parameters
        if ($type === PDO::PARAM_STR && is_string($value)) {
            // Remove null bytes
            $value = str_replace(chr(0), '', $value);
            
            // Validate UTF-8 encoding
            if (!mb_check_encoding($value, 'UTF-8')) {
                throw new \Exception("Invalid UTF-8 encoding in parameter: " . $param);
            }
        }
        
        if ($this->statement) {
            $this->statement->bindValue($param, $value, $type);
        }
    }
    
    /**
     * Transaction wrapper with automatic rollback on failure
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function transaction($callback)
    {
        if (!$this->connection) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        
        try {
            $this->connection->beginTransaction();
            $result = $callback($this);
            $this->connection->commit();
            return $result;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            // Log transaction failure
            Logger::error("Transaction failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Execute the prepared statement
     * 
     * @return bool
     */
    public function execute()
    {
        try {
            return $this->statement ? $this->statement->execute() : false;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            Logger::error('Database Query Error: ' . $this->error);
            return false;
        }
    }

    /**
     * Get result set as array of objects
     * 
     * @return array
     */
    public function resultSet()
    {
        $this->execute();
        return $this->statement ? $this->statement->fetchAll() : [];
    }

    /**
     * Get single record as object
     * 
     * @return array|false
     */
    public function single()
    {
        $this->execute();
        return $this->statement ? $this->statement->fetch() : false;
    }

    /**
     * Get row count
     * 
     * @return int
     */
    public function rowCount()
    {
        return $this->statement ? $this->statement->rowCount() : 0;
    }    
    
    /**
     * Get last inserted ID
     * 
     * @return string
     * @throws \Exception
     */
    public function lastInsertId()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     * 
     * @return bool
     * @throws \Exception
     */
    public function beginTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     * @throws \Exception
     */
    public function endTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->commit();
    }    /**
     * Rollback transaction
     * 
     * @return bool
     * @throws \Exception
     */
    public function cancelTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }        return $this->connection->rollBack();
    }
}
